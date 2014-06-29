<?php
/**
 * Copyright (C) 2014 Eric Hauswald
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\Elasticsearch\Facet;

/**
* @name \Rbs\Elasticsearch\Facet\ProductSkuThresholdFacetDefinition
*/
class ProductSkuThresholdFacetDefinition extends \Rbs\Elasticsearch\Facet\DocumentFacetDefinition
{

	/**
	 * @var \Change\Documents\DocumentManager
	 */
	protected $documentManager;

	function __construct(\Rbs\Elasticsearch\Documents\Facet $facet)
	{
		parent::__construct($facet);
		$this->mappingName  = 'stocks';
	}

	/**
	 * @param \Change\Documents\DocumentManager $documentManager
	 * @return $this
	 */
	public function setDocumentManager($documentManager)
	{
		$this->documentManager = $documentManager;
		return $this;
	}

	/**
	 * @return \Change\Documents\DocumentManager
	 */
	protected function getDocumentManager()
	{
		return $this->documentManager;
	}

	/**
	 * @return array
	 */
	protected function getDefaultParameters()
	{
		return  ['thresholdCollectionId' => null, 'showEmptyItem' => false, 'multipleChoice' => false];
	}

	/**
	 * @param \Rbs\Elasticsearch\Documents\Facet $facet
	 */
	public function validateConfiguration($facet)
	{
		$facet->setIndexCategory('store');
		$validParameters = $this->getDefaultParameters();
		$currentParameters = $facet->getParameters();
		foreach ($currentParameters as $name => $value)
		{
			switch ($name)
			{
				case 'thresholdCollectionId':
					if ($value)
					{
						$coll = $this->getDocumentManager()->getDocumentInstance($value, 'Rbs_Collection_Collection');
						if ($coll)
						{
							$validParameters[$name] = $coll->getId();
						}
					}
					break;
				case 'showEmptyItem':
				case 'multipleChoice':
					$validParameters[$name] = $value === 'false' ? false : boolval($value);
					break;
			}
		}
		if (!isset($validParameters['thresholdCollectionId']))
		{
			$query = $this->getDocumentManager()->getNewQuery('Rbs_Collection_Collection');
			$coll = $query->andPredicates($query->eq('code', 'Rbs_Stock_Collection_Threshold'))->getFirstDocument();
			if ($coll)
			{
				$validParameters['thresholdCollectionId'] = $coll->getId();
			}
		}
		$facet->getParameters()->fromArray($validParameters);
	}

	/**
	 * @param array $facetFilters
	 * @param array $context
	 * @return \Elastica\Filter\AbstractFilter[]
	 */
	public function getFiltersQuery(array $facetFilters, array $context = [])
	{
		$filtersQuery = [];
		$filterName = $this->getFieldName();
		if (isset($facetFilters[$filterName]))
		{
			$facetFilter = is_array($facetFilters[$filterName]) ? $facetFilters[$filterName] : [$facetFilters[$filterName]];
			$thresholds = [];
			foreach ($facetFilter as $key)
			{
				$key = strval($key);
				if (!empty($key))
				{
					$thresholds[] = new \Elastica\Query\Term(['stocks.threshold' => $key]);
				}
			}

			if (count($thresholds))
			{
				$context = $context + ['warehouseId' => 0];
				$warehouseId = intval($context['warehouseId']);
				$filterQuery = new \Elastica\Filter\Nested();
				$filterQuery->setPath('stocks');
				$nestedBool = new \Elastica\Query\Bool();
				$nestedBool->addMust(new \Elastica\Query\Term(['stocks.warehouseId' => $warehouseId]));
				foreach ($thresholds as $threshold)
				{
					$nestedBool->addShould($threshold);
				}
				$nestedBool->setMinimumNumberShouldMatch(1);
				$filterQuery->setQuery($nestedBool);
				$filtersQuery[] = $filterQuery;
			}
		}
		return $filtersQuery;
	}

	/**
	 * @param array $context
	 * @return \Elastica\Aggregation\AbstractAggregation
	 */
	public function getAggregation(array $context = [])
	{

		$context = $context + ['warehouseId' => 0];

		$nestedPrice = new \Elastica\Aggregation\Nested('stocks', 'stocks');

		$contextFilter = new \Elastica\Aggregation\Filter('context');
		$warehouseId = intval($context['warehouseId']);

		$bool = new \Elastica\Filter\Bool();
		$bool->addMust(new \Elastica\Filter\Term(['stocks.warehouseId' => $warehouseId]));
		$contextFilter->setFilter($bool);

		$field = 'stocks.threshold';
		$term = (new \Elastica\Aggregation\Terms('threshold'))->setField($field);
		$contextFilter->addAggregation($term);
		$nestedPrice->addAggregation($contextFilter);

		return $nestedPrice;
	}

	/**
	 * @param $aggregations
	 * @return \Rbs\Elasticsearch\Facet\AggregationValues
	 */
	public function formatAggregation(array $aggregations)
	{
		$av = new \Rbs\Elasticsearch\Facet\AggregationValues($this);
		if (isset($aggregations['stocks']['context']['threshold']['buckets']))
		{
			$buckets = $aggregations['stocks']['context']['threshold']['buckets'];
			foreach ($buckets as $bucket)
			{
				$v = new \Rbs\Elasticsearch\Facet\AggregationValue($bucket['key'], $bucket['doc_count']);
				$av->addValue($v);
			}
		}
		return $av;
	}
} 