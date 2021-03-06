<?php
/**
 * Copyright (C) 2014 Ready Business System
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Change\Db\Query\Expressions;

/**
 * @name \Change\Db\Query\Expressions\OrderingSpecification
 */
class OrderingSpecification extends UnaryOperation
{
	const ASC = 'ASC';
	const DESC = 'DESC';
	
	/**
	 * @param \Change\Db\Query\Expressions\AbstractExpression $expression
	 * @param string $directionOperator
	 */
	public function __construct(\Change\Db\Query\Expressions\AbstractExpression $expression, $directionOperator = self::ASC)
	{
		parent::__construct($expression, $directionOperator);
	}
	
	/**
	 * @throws \InvalidArgumentException
	 * @param string $operator
	 */
	public function setOperator($operator)
	{
		switch ($operator) 
		{
			case self::ASC:
			case self::DESC:
				parent::setOperator($operator);
				return;
		}
		throw new \InvalidArgumentException('Argument 1 must be a valid const', 42027);
	}
	
	/**
	 * @return string
	 */
	public function toSQL92String()
	{
		return $this->getExpression()->toSQL92String() . ' ' . $this->getOperator();
	}
	

}