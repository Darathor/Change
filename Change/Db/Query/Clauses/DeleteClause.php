<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Change\Db\Query\Clauses;

/**
 * @name \Change\Db\Query\Clauses\DeleteClause
 * @api
 */
class DeleteClause extends AbstractClause
{	

	public function __construct()
	{
		$this->setName('DELETE');
	}
		
	/**
	 * @api
	 * @return string
	 */
	public function toSQL92String()
	{
		return 'DELETE';
	}
}