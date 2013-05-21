<?php
namespace Change\Db\Query;

/**
 * @api
 * @name \Change\Db\Query\InsertQuery
 */
class InsertQuery extends AbstractQuery
{
	/**
	 * @var \Change\Db\Query\Clauses\InsertClause
	 */
	protected $insertClause;

	/**
	 * @var \Change\Db\Query\Clauses\ValuesClause
	 */
	protected $valuesClause;

	/**
	 * @var SelectQuery
	 */
	protected $selectQuery;

	/**
	 * @api
	 * @return \Change\Db\Query\Clauses\InsertClause|null
	 */
	public function getInsertClause()
	{
		return $this->insertClause;
	}

	/**
	 * @api
	 * @return \Change\Db\Query\Clauses\ValuesClause|null
	 */
	public function getValuesClause()
	{
		return $this->valuesClause;
	}

	/**
	 * @api
	 * @return SelectQuery|null
	 */
	public function getSelectQuery()
	{
		return $this->selectQuery;
	}

	/**
	 * @api
	 * @param \Change\Db\Query\Clauses\InsertClause $insertClause
	 */
	public function setInsertClause(\Change\Db\Query\Clauses\InsertClause $insertClause)
	{
		$this->insertClause = $insertClause;
	}

	/**
	 * @api
	 * @param \Change\Db\Query\Clauses\ValuesClause $valuesClause
	 */
	public function setValuesClause(\Change\Db\Query\Clauses\ValuesClause $valuesClause)
	{
		$this->valuesClause = $valuesClause;
	}

	/**
	 * @api
	 * @param SelectQuery $selectQuery
	 */
	public function setSelectQuery(SelectQuery $selectQuery)
	{
		$this->selectQuery = $selectQuery;
	}

	/**
	 * @api
	 * @throws \RuntimeException
	 */
	public function checkCompile()
	{
		if ($this->insertClause === null)
		{
			throw new \RuntimeException('InsertClause can not be null', 42008);
		}
		if ($this->valuesClause === null && $this->selectQuery === null)
		{
			throw new \RuntimeException('ValuesClause or SelectQuery should be not null', 42009);
		}
		if ($this->valuesClause !== null && $this->selectQuery !== null)
		{
			throw new \RuntimeException('ValuesClause or SelectQuery should be null', 42010);
		}
	}

	/**
	 * @api
	 * @return string
	 */
	public function toSQL92String()
	{
		$this->checkCompile();
		$parts = array($this->insertClause->toSQL92String());
		if ($this->valuesClause !== null)
		{
			$parts[] = $this->valuesClause->toSQL92String();
		}
		else
		{
			$parts[] = $this->selectQuery->toSQL92String();
		}

		return implode(' ', $parts);
	}

	/**
	 * @api
	 * @return integer
	 */
	public function execute()
	{
		return $this->dbProvider->executeQuery($this);
	}
}