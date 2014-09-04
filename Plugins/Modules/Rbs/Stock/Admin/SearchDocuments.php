<?php
/**
 * Copyright (C) 2014 Proximis
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\Stock\Admin;

/**
 * @name \Rbs\Stock\Admin\SearchDocuments
 */
class SearchDocuments
{
	/**
	 * @param \Change\Events\Event $event
	 * @throws \Exception
	 */
	public function execute(\Change\Events\Event $event)
	{
		if (is_array($event->getParam('documents')))
		{
			return;
		}

		$modelName = $event->getParam('modelName');
		if ($modelName == 'Rbs_Stock_Sku')
		{
			$query = $event->getApplicationServices()->getDocumentManager()->getNewQuery($modelName);
			$query->andPredicates($query->like('code', $event->getParam('searchString')));
			$query->addOrder('code');
			$query->addOrder('id');
			$event->setParam('documents', $query->getDocuments(0, $event->getParam('limit'))->toArray());
		}
	}
} 