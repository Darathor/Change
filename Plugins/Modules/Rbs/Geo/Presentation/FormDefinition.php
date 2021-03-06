<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\Geo\Presentation;

use Rbs\geo\Documents\AddressFields;

/**
 * @name \Rbs\Geo\Presentation\FormDefinition
 */
class FormDefinition
{
	/**
	 * @var AddressFields
	 */
	protected $addressFields;

	/**
	 * @var \Change\Collection\CollectionManager
	 */
	protected $collectionManager;

	function __construct(AddressFields $addressFields)
	{
		$this->addressFields = $addressFields;
	}

	/**
	 * @param \Change\Collection\CollectionManager $collectionManager
	 * @return $this
	 */
	public function setCollectionManager(\Change\Collection\CollectionManager $collectionManager)
	{
		$this->collectionManager = $collectionManager;
		return $this;
	}

	/**
	 * @throws \RuntimeException
	 * @return \Change\Collection\CollectionManager
	 */
	protected function getCollectionManager()
	{
		if ($this->collectionManager === null)
		{
			throw new \RuntimeException('Collection manager not set', 999999);
		}
		return $this->collectionManager;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		$array = array('definition' => $this->addressFields->getId(), 'rows' => array());
		$idPrefix = uniqid($this->addressFields->getId() . '_') . '_';
		foreach ($this->addressFields->getFields() as $addressField)
		{
			$input = array(
				'name' => $addressField->getCode(),
				'title' => $addressField->getTitle(),
				'required' => $addressField->getRequired(),
				'match' => $addressField->getMatch(),
				'matchErrorMessage' => $addressField->getCurrentLocalization()->getMatchErrorMessage(),
				'defaultValue' => $addressField->getDefaultValue()
			);
			if ($addressField->getCollectionCode())
			{
				$collection = $this->getCollectionManager()->getCollection($addressField->getCollectionCode());
				if ($collection)
				{
					$values = array();
					foreach ($collection->getItems() as $item)
					{
						$values[$item->getValue()] = array('value' => $item->getValue(), 'title' => $item->getTitle());
					}
					$input['values'] = $values;
				}
			}
			$input['id'] = $idPrefix . $input['name'];
			$array['rows'][] = $input;
		}
		return $array;
	}
}