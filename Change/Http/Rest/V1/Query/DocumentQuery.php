<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Change\Http\Rest\V1\Query;

use Change\Documents\Query\JSONDecoder;
use Change\Http\Rest\V1\CollectionResult;
use Change\Http\Rest\V1\Link;
use Change\Http\Rest\V1\Resources\DocumentLink;
use Zend\Http\Response as HttpResponse;

/**
 * @name \Change\Http\Rest\V1\Query\DocumentQuery
 */
class DocumentQuery
{
	/**
	 * @var \Change\Documents\TreeManager
	 */
	protected $treeManager;

	/**
	 * Use Event Params: documentId, modelName, LCID
	 * @param \Change\Http\Event $event
	 * @throws \RuntimeException
	 * @throws \Exception
	 */
	public function execute($event)
	{
		$query = $event->getRequest()->getPost()->toArray();
		if (is_array($query) && isset($query['model']))
		{
			$applicationServices = $event->getApplicationServices();
			$modelName = $query['model'];

			$model = $applicationServices->getModelManager()->getModelByName($modelName);
			if (!$model)
			{
				throw new \RuntimeException('Invalid Parameter: model', 71000);
			}
			$startIndex = isset($query['offset']) ? intval($query['offset']) : 0;
			$maxResults = isset($query['limit']) ? intval($query['limit']) : 10;
			$LCID = isset($query['LCID']) ? $query['LCID'] : null;

			$urlManager = $event->getUrlManager();
			$result = new CollectionResult();
			$selfLink = new Link($urlManager, $event->getRequest()->getPath());
			$result->addLink($selfLink);
			$result->setOffset($startIndex);
			$result->setLimit($maxResults);
			$result->setSort(null);

			$this->treeManager = $applicationServices->getTreeManager();

			if ($LCID)
			{
				$applicationServices->getDocumentManager()->pushLCID($LCID);
			}

			try
			{
				$decoder = new JSONDecoder();
				$decoder->setDocumentManager($applicationServices->getDocumentManager())
				->setModelManager($applicationServices->getModelManager())
				->setTreeManager($applicationServices->getTreeManager());

				$queryBuilder = $decoder->getQuery($query);
				$count = $queryBuilder->getCountDocuments();
				$result->setCount($count);
				if ($count && $startIndex < $count)
				{
					$extraColumn = $event->getRequest()->getQuery('column', array());
					$collection = $queryBuilder->getDocuments($startIndex, $maxResults);
					foreach ($collection as $document)
					{
						$result->addResource(new DocumentLink($urlManager, $document, DocumentLink::MODE_PROPERTY, $extraColumn));
					}
				}

				$result->setHttpStatusCode(HttpResponse::STATUS_CODE_200);
				$event->setResult($result);

				if ($LCID)
				{
					$applicationServices->getDocumentManager()->popLCID();
				}
			}
			catch (\Exception $e)
			{
				if ($LCID)
				{
					$applicationServices->getDocumentManager()->popLCID();
				}
				throw $e;
			}
		}
	}
}