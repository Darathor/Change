<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Rbs\User\Blocks;

use Change\Presentation\Blocks\Event;
use Change\Presentation\Blocks\Parameters;
use Change\Presentation\Blocks\Standard\Block;

/**
 * @name \Rbs\User\Blocks\CreateAccount
 */
class CreateAccount extends Block
{
	/**
	 * @api
	 * Set Block Parameters on $event
	 * Required Event method: getBlockLayout, getApplication, getApplicationServices, getServices, getHttpRequest
	 * Optional Event method: getHttpRequest
	 * @param Event $event
	 * @return Parameters
	 */
	protected function parameterize($event)
	{
		$parameters = parent::parameterize($event);
		$parameters->addParameterMeta('authenticated', false);
		$parameters->addParameterMeta('errId');
		$parameters->addParameterMeta('context');
		$parameters->addParameterMeta('initialValues', array());
		$parameters->addParameterMeta('readonlyNames', array());
		$parameters->addParameterMeta('formAction', 'Action/Rbs/User/CreateAccountRequest');

		$parameters->setLayoutParameters($event->getBlockLayout());
		$request = $event->getHttpRequest();

		$parameters->setParameterValue('errId', $request->getQuery('errId'));

		$user = $event->getAuthenticationManager()->getCurrentUser();
		if ($user->authenticated())
		{
			$parameters->setParameterValue('authenticated', true);
		}

		$parameters->setParameterValue('context', $event->getHttpRequest()->getQuery('context'));

		return $parameters;
	}

	/**
	 * Set $attributes and return a twig template file name OR set HtmlCallback on result
	 * Required Event method: getBlockLayout, getBlockParameters, getApplication, getApplicationServices, getServices, getHttpRequest
	 * @param Event $event
	 * @param \ArrayObject $attributes
	 * @return string|null
	 */
	protected function execute($event, $attributes)
	{
		$parameters = $event->getBlockParameters();

		// Handle errors.
		$errId = $parameters->getParameterValue('errId');
		if ($errId)
		{
			$session = new \Zend\Session\Container('Change_Errors');
			$sessionErrors = isset($session[$errId]) ? $session[$errId] : null;
			if ($sessionErrors && is_array($sessionErrors))
			{
				$attributes['errors'] = isset($sessionErrors['errors']) ? $sessionErrors['errors'] : [];
				$attributes['inputData'] = isset($sessionErrors['inputData']) ? $sessionErrors['inputData'] : null;
			}
		}
		else
		{
			$attributes['inputData'] = $parameters->getParameterValue('initialValues');
		}
		return 'create-account.twig';
	}
}