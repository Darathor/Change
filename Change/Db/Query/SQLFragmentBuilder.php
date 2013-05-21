<?php
namespace Change\Db\Query;

use Change\Db\Query\InterfaceSQLFragment;
use Change\Db\Query\Expressions\Assignment;
use Change\Db\Query\Expressions\AbstractExpression;
use Change\Db\Query\Expressions\BinaryOperation;
use Change\Db\Query\Expressions\Func;
use Change\Db\Query\Expressions\Table;
use Change\Db\Query\Expressions\Identifier;
use Change\Db\Query\Expressions\Column;
use Change\Db\Query\Expressions\Alias;
use Change\Db\Query\Expressions\Parameter;
use Change\Db\Query\Expressions\Numeric;
use Change\Db\Query\Expressions\String;
use Change\Db\Query\Expressions\ExpressionList;
use Change\Db\Query\Expressions\SubQuery;
use Change\Db\Query\Expressions\Raw;
use Change\Db\Query\Predicates\Conjunction;
use Change\Db\Query\Predicates\Disjunction;
use Change\Db\Query\Predicates\UnaryPredicate;
use Change\Db\Query\Predicates\BinaryPredicate;
use Change\Db\Query\Predicates\Like;
use Change\Db\Query\Predicates\In;
use Change\Db\ScalarType;
use Change\Db\SqlMapping;

/**
 * @api
 * @name \Change\Db\Query\SQLFragmentBuilder
 */
class SQLFragmentBuilder
{	
	/**
	 * @var SqlMapping
	 */
	protected $sqlMapping;
	
	/**
	 * @param SqlMapping $sqlMapping
	 */
	public function __construct(SqlMapping $sqlMapping)
	{
		$this->sqlMapping = $sqlMapping;
	}

	/**
	 * Build a function argument after $name assumed as function arguments
	 * @api
	 * @param string $name
	 * @return \Change\Db\Query\Expressions\Func
	 */
	public function func($name)
	{
		$funcArgs = func_get_args();
		array_shift($funcArgs);
		return new Func($name, $this->normalizeValue($funcArgs));
	}
	
	/**
	 * @api
	 * @return \Change\Db\Query\Expressions\Func
	 */
	public function sum()
	{
		return new Func('SUM', $this->normalizeValue(func_get_args()));
	}
	
	
	/**
	 * Build a reference to a given table
	 * 
	 * @api
	 * @param string $tableName
	 * @param string $dbName
	 * @return \Change\Db\Query\Expressions\Table
	 */
	public function table($tableName, $dbName = null)
	{
		return new Table($tableName, $dbName);
	}

	/**
	 * Build an identifier string (eg: `test` on MySQL) which can be passed
	 * for instance as the second argument of the alias method.
	 *
	 * @api
	 * @internal string $tableName
	 * @internal string $dbName
	 * @return \Change\Db\Query\Expressions\Identifier
	 */
	public function identifier()
	{
		return new Identifier(func_get_args());
	}
	
	/**
	 * @api
	 * @param string $name
	 * @param \Change\Db\Query\Expressions\Table | \Change\Db\Query\Expressions\Identifier | string $tableOrIdentifier
	 * @return \Change\Db\Query\Expressions\Column
	 */
	public function column($name, $tableOrIdentifier = null)
	{
		if (is_string($name))
		{
			$name = $this->identifier($name);
		}
		
		if (is_string($tableOrIdentifier))
		{
			$tableOrIdentifier = $this->identifier($tableOrIdentifier);
		}
		return new Column($name, $tableOrIdentifier);
	}
	
	/**
	 * @api
	 * @throws \InvalidArgumentException
	 * @param \Change\Db\Query\Expressions\AbstractExpression $lhs
	 * @param string | \Change\Db\Query\Expressions\Identifier $rhs (if string assume identifier)
	 * @return \Change\Db\Query\Expressions\Alias
	 */
	public function alias(AbstractExpression $lhs, $rhs)
	{
		if (is_string($rhs))
		{
			$rhs = $this->identifier($rhs);
		}
		if (!($rhs instanceof Identifier))
		{
			throw new \InvalidArgumentException('Could not convert argument 2 to an Identifier', 42012);
		}
		return new Alias($lhs, $rhs);
	}

	/**
	 * @api
	 * @throws \InvalidArgumentException
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $lhs (if string assume identifier)
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $rhs (if string assume string)
	 * @return \Change\Db\Query\Expressions\Assignment
	 */
	public function assignment($lhs, $rhs)
	{
		if (is_string($lhs))
		{
			$lhs = $this->identifier($lhs);
		}
		if (!($lhs instanceof AbstractExpression))
		{
			throw new \InvalidArgumentException('Could not convert argument 1 to an Expression', 42013);
		}
		
		if (is_string($rhs))
		{
			$rhs = $this->string($rhs);
		}
		if (!($rhs instanceof AbstractExpression))
		{
			throw new \InvalidArgumentException('Could not convert argument 2 to an Expression', 42012);
		}
		
		return new Assignment($lhs, $rhs);
	}

	/**
	 * @api
	 * Assume a string parameter
	 * @param string $parameterName
	 * @param AbstractQuery|Builder|StatementBuilder $queryOrBuilder
	 * @return \Change\Db\Query\Expressions\Parameter
	 */
	public function parameter($parameterName, $queryOrBuilder = null)
	{
		$p = new Parameter($parameterName);
		if ($queryOrBuilder !== null)
		{
			$this->bindParameter($p, $queryOrBuilder);
		}
		return $p;
	}

	/**
	 * @api
	 * @param string $parameterName
	 * @param string $scalarType \Change\Db\ScalarType::*
	 * @param AbstractQuery|Builder|StatementBuilder $queryOrBuilder
	 * @return \Change\Db\Query\Expressions\Parameter
	 */
	public function typedParameter($parameterName, $scalarType, $queryOrBuilder = null)
	{
		$p = new Parameter($parameterName, $scalarType);
		if ($queryOrBuilder !== null)
		{
			$this->bindParameter($p, $queryOrBuilder);
		}
		return $p;
	}
	
	/**
	 * @api
	 * @param string $parameterName
	 * @param AbstractQuery|Builder|StatementBuilder $queryOrBuilder
	 * @return \Change\Db\Query\Expressions\Parameter
	 */
	public function integerParameter($parameterName, $queryOrBuilder = null)
	{
		$p = new Parameter($parameterName, ScalarType::INTEGER);
		if ($queryOrBuilder !== null)
		{
			$this->bindParameter($p, $queryOrBuilder);
		}
		return $p;
	}
	
	/**
	 * @api
	 * @param string $parameterName
	 * @param AbstractQuery|Builder|StatementBuilder $queryOrBuilder
	 * @return \Change\Db\Query\Expressions\Parameter
	 */
	public function dateTimeParameter($parameterName, $queryOrBuilder = null)
	{
		$p = new Parameter($parameterName, ScalarType::DATETIME);
		if ($queryOrBuilder !== null)
		{
			$this->bindParameter($p, $queryOrBuilder);
		}
		return $p;
	}

	/**
	 * @param \Change\Db\Query\Expressions\Parameter $parameter
	 * @param AbstractQuery|Builder|StatementBuilder $queryOrBuilder
	 * @throws \InvalidArgumentException
	 */
	protected function bindParameter($parameter, $queryOrBuilder)
	{
		if ($queryOrBuilder instanceof AbstractQuery
			|| $queryOrBuilder instanceof Builder
			|| $queryOrBuilder instanceof StatementBuilder
		)
		{
			$queryOrBuilder->addParameter($parameter);
		}
		else
		{
			throw new \InvalidArgumentException('Argument 2 must be a valid Query or Builder', 42014);
		}
	}
	
	/**
	 * @api
	 * @param integer|float $number
	 * @return \Change\Db\Query\Expressions\Numeric
	 */
	public function number($number)
	{
		return new Numeric($number);
	}
	
	/**
	 * @api
	 * @param string $string
	 * @return \Change\Db\Query\Expressions\String
	 */
	public function string($string)
	{
		return new String($string);
	}
	
	/**
	 * @api
	 * @return ExpressionList
	 */
	public function expressionList()
	{
		return new ExpressionList($this->normalizeValue(func_get_args()));
	}
	
	/**
	 * @api
	 * @param SelectQuery $selectQuery
	 * @return SubQuery
	 */
	public function subQuery(SelectQuery $selectQuery)
	{
		return new SubQuery($selectQuery);
	}
	
	/**
	 * @api
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $lhs
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $rhs
	 * @return \Change\Db\Query\Predicates\BinaryPredicate
	 */
	public function eq($lhs, $rhs)
	{
		$lhs = $this->normalizeValue($lhs);
		$rhs = $this->normalizeValue($rhs);
		return new BinaryPredicate($lhs, $rhs, BinaryPredicate::EQUAL);
	}
	
	/**
	 * @api
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $lhs
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $rhs
	 * @return \Change\Db\Query\Predicates\BinaryPredicate
	 */
	public function neq($lhs, $rhs)
	{
		$lhs = $this->normalizeValue($lhs);
		$rhs = $this->normalizeValue($rhs);
		return new BinaryPredicate($lhs, $rhs, BinaryPredicate::NOTEQUAL);
	}	
	
	/**
	 * @api
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $lhs
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $rhs
	 * @return \Change\Db\Query\Predicates\BinaryPredicate
	 */
	public function gt($lhs, $rhs)
	{
		$lhs = $this->normalizeValue($lhs);
		$rhs = $this->normalizeValue($rhs);
		return new BinaryPredicate($lhs, $rhs, BinaryPredicate::GREATERTHAN);
	}
	
	/**
	 * @api
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $lhs
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $rhs
	 * @return \Change\Db\Query\Predicates\BinaryPredicate
	 */
	public function gte($lhs, $rhs)
	{
		$lhs = $this->normalizeValue($lhs);
		$rhs = $this->normalizeValue($rhs);
		return new BinaryPredicate($lhs, $rhs, BinaryPredicate::GREATERTHANOREQUAL);
	}
	
	/**
	 * @api
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $lhs
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $rhs
	 * @return \Change\Db\Query\Predicates\BinaryPredicate
	 */
	public function lt($lhs, $rhs)
	{
		$lhs = $this->normalizeValue($lhs);
		$rhs = $this->normalizeValue($rhs);
		return new BinaryPredicate($lhs, $rhs, BinaryPredicate::LESSTHAN);
	}
	
	/**
	 * @api
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $lhs
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $rhs
	 * @return \Change\Db\Query\Predicates\BinaryPredicate
	 */
	public function lte($lhs, $rhs)
	{
		$lhs = $this->normalizeValue($lhs);
		$rhs = $this->normalizeValue($rhs);
		return new BinaryPredicate($lhs, $rhs, BinaryPredicate::LESSTHANOREQUAL);
	}


	/**
	 * @api
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $lhs
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $rhs
	 * @param integer $matchMode
	 * @param bool $caseSensitive
	 * @return \Change\Db\Query\Predicates\Like
	 */
	public function like($lhs, $rhs, $matchMode = Like::ANYWHERE, $caseSensitive = false)
	{
		$lhs = $this->normalizeValue($lhs);
		$rhs = $this->normalizeValue($rhs);
		return new Like($lhs, $rhs, $matchMode, ($caseSensitive == true));
	}

	/**
	 * @api
	 * @param string|\Change\Db\Query\Expressions\AbstractExpression $lhs
	 * @param string|array|\Change\Db\Query\Expressions\AbstractExpression $rhs1
	 * @internal string|array|\Change\Db\Query\Expressions\AbstractExpression $_
	 * @return \Change\Db\Query\Predicates\In
	 */
	public function in($lhs, $rhs1)
	{
		$lhs = $this->normalizeValue($lhs);
		$rhs1 = $this->normalizeValue($rhs1);
		if ($rhs1 instanceof SelectQuery)
		{
			$rhs = $this->subQuery($rhs1);
		}
		elseif ($rhs1 instanceof Subquery || $rhs1 instanceof ExpressionList)
		{
			$rhs = $rhs1;
		}
		else
		{
			$rhs = new ExpressionList();

			$items = func_get_args();
			array_shift($items);
			if (count($items))
			{
				$converter = function ($item) {return new String(strval($item));};
				foreach ($items as $item)
				{
					if (is_array($item))
					{
						foreach ($item as $si)
						{
							$rhs->add($this->normalizeValue($si, $converter));
						}
					}
					else
					{
						$rhs->add($this->normalizeValue($item, $converter));
					}
				}
			}
		}
		return new In($lhs, $rhs);
	}
	
	/**
	 * @api
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $lhs
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $rhs
	 * @return \Change\Db\Query\Predicates\In
	 */
	public function notIn($lhs, $rhs)
	{
		$pre = call_user_func_array(array($this, 'in'), func_get_args());
		$pre->setNot(true);
		return $pre;
	}
	
	/**
	 * @api
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $lhs
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $rhs
	 * @return BinaryOperation
	 */
	public function addition($lhs, $rhs)
	{
		$lhs = $this->normalizeValue($lhs);
		$rhs = $this->normalizeValue($rhs);
		return new BinaryOperation($lhs, $rhs, '+');
	}
	
	/**
	 * @api
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $lhs
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $rhs
	 * @return BinaryOperation
	 */
	public function subtraction($lhs, $rhs)
	{
		$lhs = $this->normalizeValue($lhs);
		$rhs = $this->normalizeValue($rhs);
		return new BinaryOperation($lhs, $rhs, '-');
	}
	
	
	/**
	 * @api
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $expression
	 * @return \Change\Db\Query\Predicates\UnaryPredicate
	 */
	public function isNull($expression)
	{
		$expression = $this->normalizeValue($expression);
		return new UnaryPredicate($expression, UnaryPredicate::ISNULL);
	}

	/**
	 * @api
	 * @param string | \Change\Db\Query\Expressions\AbstractExpression $expression
	 * @return \Change\Db\Query\Predicates\UnaryPredicate
	 */
	public function isNotNull($expression)
	{
		$expression = $this->normalizeValue($expression);
		return new UnaryPredicate($expression, UnaryPredicate::ISNOTNULL);
	}
		
	/**
	 * @api
	 * @return Conjunction
	 */
	public function logicAnd()
	{
		$result = new Conjunction();
		$result->setArguments($this->normalizeValue(func_get_args()));
		return $result;
	}
	
	/**
	 * @api
	 * @return Disjunction
	 */
	public function logicOr()
	{
		$result = new Disjunction();
		$result->setArguments($this->normalizeValue(func_get_args()));
		return $result;
	}

	/**
	 * @api
	 * @return \Change\Db\Query\Expressions\Table
	 */
	public function getPluginTable()
	{
		return $this->table($this->sqlMapping->getPluginTableName());
	}
	
	/**
	 * @api
	 * @return \Change\Db\Query\Expressions\Table
	 */
	public function getDocumentIndexTable()
	{
		return $this->table($this->sqlMapping->getDocumentIndexTableName());
	}
	
	/**
	 * @api
	 * @return \Change\Db\Query\Expressions\Table
	 */
	public function getDocumentDeletedTable()
	{
		return $this->table($this->sqlMapping->getDocumentDeletedTable());
	}
	
	/**
	 * @api
	 * @param string $rootDocumentName
	 * @return \Change\Db\Query\Expressions\Table
	 */
	public function getDocumentTable($rootDocumentName)
	{
		return $this->table($this->sqlMapping->getDocumentTableName($rootDocumentName));
	}
	
	/**
	 * @api
	 * @param string $rootDocumentName
	 * @return \Change\Db\Query\Expressions\Table
	 */
	public function getDocumentI18nTable($rootDocumentName)
	{
		return $this->table($this->sqlMapping->getDocumentI18nTableName($rootDocumentName));
	}
	
	/**
	 * @api
	 * @param string $rootDocumentName
	 * @return \Change\Db\Query\Expressions\Table
	 */
	public function getDocumentRelationTable($rootDocumentName)
	{
		return $this->table($this->sqlMapping->getDocumentRelationTableName($rootDocumentName));
	}
	
	/**
	 * @api
	 * @param string $propertyName
	 * @param \Change\Db\Query\Expressions\Table | \Change\Db\Query\Expressions\Identifier | string $tableOrIdentifier
	 * @return \Change\Db\Query\Expressions\Column
	 */
	public function getDocumentColumn($propertyName, $tableOrIdentifier = null)
	{
		return $this->column($this->sqlMapping->getDocumentFieldName($propertyName), $tableOrIdentifier); 
	}
	
	/**
	 * @api
	 * @param string $moduleName
	 * @return \Change\Db\Query\Expressions\Table
	 */
	public function getTreeTable($moduleName)
	{
		return $this->table($this->sqlMapping->getTreeTableName($moduleName));
	}
		
	/**
	 * @api
	 * @return \Change\Db\Query\Expressions\Table
	 */
	public function getDocumentMetasTable()
	{
		return $this->table($this->sqlMapping->getDocumentMetasTableName());
	}

	/**
	 * For internal use only.
	 * @param \Change\Db\Query\Expressions\AbstractExpression|array|string $object
	 * @param \Closure|null $converter
	 * @throws \InvalidArgumentException
	 * @return \Change\Db\Query\Expressions\AbstractExpression|array
	 */
	public function normalizeValue($object, $converter = null)
	{
		if ($converter == null)
		{
			$converter = function ($item) {
				return new Raw(strval($item));
			};
		}

		if (is_array($object))
		{
			$builder = $this;
			return array_map(function ($item) use($builder, $converter) {
				return $builder->normalizeValue($item, $converter);
			}, $object);
		}

		if (!($object instanceof AbstractExpression))
		{
			if (is_callable($converter))
			{
				return call_user_func($converter, $object);
			}
			else
			{
				throw new \InvalidArgumentException('Argument 2 is not valid \Closure', 42015);
			}
		}
		return $object;
	}
}