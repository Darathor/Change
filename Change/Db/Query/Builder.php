<?php
namespace Change\Db\Query;

use Change\Db\DbProvider;

/**
 * @api
 * @name \Change\Db\Query\Builder
 */
class Builder
{
	/**
	 * @var SelectQuery
	 */
	protected $query;

	/**
	 * @var string
	 */
	protected $cacheKey;
	
	/**
	 * @var DbProvider
	 */
	protected $dbProvider;
	
	/**
	 * @var SQLFragmentBuilder
	 */
	protected $fragmentBuilder;

	/**
	 * If you are looking to get a builder instance, please get it from
	 * the application services which will properly inject the correct DB provider for you.
	 * @param DbProvider $dbProvider
	 * @param string $cacheKey
	 * @param SelectQuery $query
	 */
	public function __construct(DbProvider $dbProvider, $cacheKey = null, SelectQuery $query = null)
	{
		$this->setDbProvider($dbProvider);
		$this->fragmentBuilder = new SQLFragmentBuilder($dbProvider->getSqlMapping());
		if ($cacheKey !== null)
		{
			$this->cacheKey = $cacheKey;
			$this->query = $query;
		}
	}

	/**
	 * @return boolean
	 */
	public function isCached()
	{
		return $this->cacheKey !== null && $this->query !== null && $this->query->getCachedKey() === $this->cacheKey;
	}
	
	/**
	 * @api
	 * @return DbProvider
	 */
	public function getDbProvider()
	{
		return $this->dbProvider;
	}

	/**
	 * @api
	 * @param DbProvider $dbProvider
	 */
	public function setDbProvider(DbProvider $dbProvider)
	{
		$this->dbProvider = $dbProvider;
	}

	/**
	 * @api
	 * Explicitly reset the builder (which will destroy the current query).
	 */
	public function reset()
	{
		$this->cacheKey = null;
		$this->query = null;
	}
	
	/**
	 * @api
	 * @return SQLFragmentBuilder
	 */
	public function getFragmentBuilder()
	{
		return $this->fragmentBuilder;
	}
	
	/**
	 * @api
	 * @return \Change\Db\SqlMapping
	 */
	public function getSqlMapping()
	{
		return $this->dbProvider->getSqlMapping();
	}
	
	/**
	 * Start building the select query. You can pass any number of AbstractExpression to this
	 * method corresponding to the (derived-)columns you wish to select.
	 * 
	 * @api
	 * @return \Change\Db\Query\Builder
	 */
	public function select()
	{
		if ($this->query)
		{
			$this->reset();
		}
		$this->query = new SelectQuery($this->dbProvider, $this->cacheKey);
		$selectClause = new \Change\Db\Query\Clauses\SelectClause();
		$this->query()->setSelectClause($selectClause);
		
		foreach (func_get_args() as $column)
		{
			$this->addColumn($column);
		}
		return $this;
	}
	
	/**
	 * Add a column expression to the existing select clause
	 *
	 * @api
	 * @param \Change\Db\Query\Expressions\AbstractExpression|string $expression
	 * @throws \LogicException
	 * @return \Change\Db\Query\Builder
	 */
	public function addColumn($expression)
	{
		if (is_string($expression))
		{
			$expression = $this->fragmentBuilder->column($expression);
		}
		$this->query()->getSelectClause()->addSelect($expression);
		return $this;
	}
		
	/**
	 * Build a "SELECT DISTINCT ..." query.
	 * 
	 * @api
	 * @return \Change\Db\Query\Builder
	 */
	public function distinct()
	{
		$this->query()->getSelectClause()->setQuantifier(\Change\Db\Query\Clauses\SelectClause::QUANTIFIER_DISTINCT);
		return $this;
	}
	
	/**
	 * Build the "FROM" clause. You can pass a string (table name), a \Change\Db\Query\Expressions\Table object or an \Change\Db\Query\Expressions\Alias to a table object.
	 * @api
	 * @see Builder::table()
	 * @see Builder::alias()
	 * @throws \InvalidArgumentException
	 * @param string | \Change\Db\Query\Expressions\Table | \Change\Db\Query\Expressions\Alias $table
	 * @return \Change\Db\Query\Builder
	 */
	public function from($table)
	{
		if (is_string($table))
		{
			$tableExpression = $this->fragmentBuilder->table($table);
		}
		elseif ($table instanceof \Change\Db\Query\Expressions\Table || $table instanceof \Change\Db\Query\Expressions\Alias)
		{
			$tableExpression = $table;
		}
		else
		{
			throw new \InvalidArgumentException('first argument must be a string, a \Change\Db\Query\Expressions\Table, or a \Change\Db\Query\Expressions\Alias', 42003);
		}
		$fromClause = new Clauses\FromClause();
		$fromClause->setTableExpression($tableExpression);
		$this->query()->setFromClause($fromClause);
		return $this;
	}
	

	
	/**
	 * @api
	 * @param \Change\Db\Query\Predicates\InterfacePredicate $predicate
	 * @return \Change\Db\Query\Builder
	 */
	public function where(\Change\Db\Query\Predicates\InterfacePredicate $predicate)
	{
		$whereClause = new \Change\Db\Query\Clauses\WhereClause($predicate);
		$this->query()->setWhereClause($whereClause);
		return $this;
	}
	
	/**
	 * @api
	 * @param  \Change\Db\Query\Expressions\AbstractExpression $tableExpression
	 * @param  \Change\Db\Query\Expressions\AbstractExpression $joinCondition
	 * @return \Change\Db\Query\Builder
	 */
	public function innerJoin(\Change\Db\Query\Expressions\AbstractExpression $tableExpression, $joinCondition = null)
	{
		$join = new \Change\Db\Query\Expressions\Join($tableExpression, \Change\Db\Query\Expressions\Join::INNER_JOIN, $this->processJoinCondition($joinCondition));
		$this->query()->getFromClause()->addJoin($join);
		return $this;
	}
	
	/**
	 * @api
	 * @param \Change\Db\Query\Expressions\AbstractExpression $tableExpression
	 * @param \Change\Db\Query\Expressions\AbstractExpression $joinCondition
	 * @return \Change\Db\Query\Builder
	 */
	public function leftJoin(\Change\Db\Query\Expressions\AbstractExpression $tableExpression, $joinCondition = null)
	{
		$join = new \Change\Db\Query\Expressions\Join($tableExpression, \Change\Db\Query\Expressions\Join::LEFT_OUTER_JOIN, $this->processJoinCondition($joinCondition));
		$this->query()->getFromClause()->addJoin($join);
		return $this;
	}
	
	/**
	 * @api
	 * @param \Change\Db\Query\Expressions\AbstractExpression $tableExpression
	 * @param \Change\Db\Query\Expressions\AbstractExpression $joinCondition
	 * @return \Change\Db\Query\Builder
	 */
	public function rightJoin(\Change\Db\Query\Expressions\AbstractExpression $tableExpression, $joinCondition = null)
	{
		$join = new \Change\Db\Query\Expressions\Join($tableExpression, \Change\Db\Query\Expressions\Join::RIGHT_OUTER_JOIN, $this->processJoinCondition($joinCondition));
		$this->query()->getFromClause()->addJoin($join);
		return $this;
	}
	
	/**
	 * @api
	 * @param \Change\Db\Query\Expressions\AbstractExpression $tableExpression
	 * @param \Change\Db\Query\Expressions\AbstractExpression $joinCondition
	 * @return \Change\Db\Query\Builder
	 */
	public function fullJoin(\Change\Db\Query\Expressions\AbstractExpression $tableExpression, $joinCondition = null)
	{
		$join = new \Change\Db\Query\Expressions\Join($tableExpression, \Change\Db\Query\Expressions\Join::FULL_OUTER_JOIN, $this->processJoinCondition($joinCondition));
		$this->query()->getFromClause()->addJoin($join);
		return $this;
	}
	
	/**
	 * @api
	 * @param \Change\Db\Query\Expressions\AbstractExpression $tableExpression
	 * @return \Change\Db\Query\Builder
	 */
	public function crossJoin(\Change\Db\Query\Expressions\AbstractExpression $tableExpression)
	{
		$join =  new \Change\Db\Query\Expressions\Join($tableExpression, \Change\Db\Query\Expressions\Join::CROSS_JOIN);
		$this->query()->getFromClause()->addJoin($join);
		return $this;
	}
	
	/**
	 * @param \Change\Db\Query\Expressions\AbstractExpression $joinCondition
	 * @return \Change\Db\Query\Expressions\UnaryOperation
	 */
	protected function processJoinCondition($joinCondition = null)
	{
		$joinExpr = null;
		if ($joinCondition instanceof \Change\Db\Query\Predicates\InterfacePredicate)
		{
			$joinExpr = new \Change\Db\Query\Expressions\UnaryOperation($joinCondition, 'ON');
		}
		elseif ($joinCondition instanceof \Change\Db\Query\Expressions\Column || $joinCondition instanceof \Change\Db\Query\Expressions\ExpressionList)
		{
			$p = new \Change\Db\Query\Expressions\Parentheses($joinCondition);
			$joinExpr = new \Change\Db\Query\Expressions\UnaryOperation($p, 'USING');
		}
		return $joinExpr;
	}
	
	/**
	 * @api
	 * @param \Change\Db\Query\Expressions\AbstractExpression $expression
	 * @return \Change\Db\Query\Builder
	 */
	public function orderAsc(\Change\Db\Query\Expressions\AbstractExpression $expression)
	{
		$this->addOrder($expression, \Change\Db\Query\Expressions\OrderingSpecification::ASC);
		return $this;
	}
	
	/**
	 * @api
	 * @param \Change\Db\Query\Expressions\AbstractExpression $expression
	 * @return \Change\Db\Query\Builder
	 */
	public function orderDesc(\Change\Db\Query\Expressions\AbstractExpression $expression)
	{
		$this->addOrder($expression, \Change\Db\Query\Expressions\OrderingSpecification::DESC);
		return $this;
	}

	/**
	 * @api
	 * @param \Change\Db\Query\Expressions\AbstractExpression $expression
	 * @param string $direction
	 */
	protected function addOrder(\Change\Db\Query\Expressions\AbstractExpression $expression, $direction)
	{
		$orderByClause = $this->query()->getOrderByClause();
		if ($orderByClause === null)
		{
			$orderByClause = new \Change\Db\Query\Clauses\OrderByClause();
			$this->query()->setOrderByClause($orderByClause);
		}
		$orderByClause->addExpression(new \Change\Db\Query\Expressions\OrderingSpecification($expression, $direction));
	}
	
	/**
	 * @api
	 * @param \Change\Db\Query\Expressions\AbstractExpression $expression
	 * @return \Change\Db\Query\Builder
	 */
	public function group(\Change\Db\Query\Expressions\AbstractExpression $expression)
	{
		$groupByClause = $this->query()->getGroupByClause();
		if ($groupByClause === null)
		{
			$groupByClause = new \Change\Db\Query\Clauses\GroupByClause();
			$this->query()->setGroupByClause($groupByClause);
		}
		$groupByClause->addExpression($expression);
		return $this;
	}
	
	/**
	 * Add a predicate to the existing where clause in "OR" mode
	 * 
	 * @api
	 * @param \Change\Db\Query\Predicates\InterfacePredicate $predicate
	 * @return \Change\Db\Query\Builder
	 */
	public function orWhere(\Change\Db\Query\Predicates\InterfacePredicate $predicate)
	{
		$existingWhereClause = $this->query()->getWhereClause();
		if ($existingWhereClause === null)
		{
			$this->where($predicate);
		}
		else
		{
			$existingWherePredicate = $existingWhereClause->getPredicate();
			$existingWhereClause->setPredicate(new \Change\Db\Query\Predicates\Disjunction($existingWherePredicate, $predicate));
		}
		return $this;
	}
	
	/**
	 * Add a predicate to the existing where clause in "AND" mode
	 * 
	 * @api
	 * @param \Change\Db\Query\Predicates\InterfacePredicate $predicate
	 * @return \Change\Db\Query\Builder
	 */
	public function andWhere(\Change\Db\Query\Predicates\InterfacePredicate $predicate)
	{
		$existingWhereClause = $this->query()->getWhereClause();
		if ($existingWhereClause === null)
		{
			$this->where($predicate);
		}
		else
		{
			$existingWherePredicate = $existingWhereClause->getPredicate();
			$existingWhereClause->setPredicate(new \Change\Db\Query\Predicates\Conjunction($existingWherePredicate, $predicate));
		}
		return $this;
	}
				
	/**
	 * @api
	 * @throws \InvalidArgumentException
	 * @throws \LogicException
	 * @param string|\Change\Db\Query\Expressions\Parameter $parameter
	 * @return \Change\Db\Query\Builder
	 */
	public function addParameter($parameter)
	{
		if (is_string($parameter))
		{
			$parameter = $this->fragmentBuilder->parameter($parameter);
		}
		if (!($parameter instanceof \Change\Db\Query\Expressions\Parameter))
		{
			throw new \InvalidArgumentException('argument must be a string or a \Change\Db\Query\Expressions\Parameter', 42004);
		}
		
		$this->query()->addParameter($parameter);
		return $this;
	}
	
	/**
	 * @api
	 * @throws \LogicException
	 * @return SelectQuery
	 */
	public function query()
	{
		if ($this->query === null)
		{
			throw new \LogicException('Call select() before', 42005);
		}

		if ($this->cacheKey)
		{
			$this->dbProvider->addBuilderQuery($this->query);
		}
		return $this->query;
	}
}