<?php
/**
 * Copyright (C) 2014 Proximis
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\User\Admin;

/**
 * @name \Rbs\User\Admin\SearchDocuments
 */
class SearchDocuments
{
	/**
	 * @param \Change\Events\Event $event
	 * @throws \Exception
	 */
	public function execute(\Change\Events\Event $event)
	{
		if ($event->getParam('modelName') == 'Rbs_User_User')
		{
			$event->setParam('propertyNames', ['login', 'email']);
		}
	}
} 