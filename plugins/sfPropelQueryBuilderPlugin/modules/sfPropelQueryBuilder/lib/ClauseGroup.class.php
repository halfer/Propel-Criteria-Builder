<?php
class ClauseGroup
{
	const BOOLEAN_AND = 'AND';
	const BOOLEAN_OR = 'OR';
	const OP_EQUAL = '=';
	const OP_NOTEQUAL = '<>';

	private $items = array();
	private $type = null;

	public function getType()
	{
		return $this->type;
	}

	public function setType($type)
	{
		$this->type = $type;
	}

	public function getCount()
	{
		return count($this->items);
	}

	public function getFirstSubclause()
	{
		return $this->items[0];
	}

	public function getSubclauses()
	{
		return $this->items;
	}

	/**
	 * Verifies and adds a simple string subclause eg (user.cat = 'admin')
	 */
	public function addSubclause($test)
	{
		if (SubclauseParser::verifySubclause($test))
		{
			array_push($this->items, $test);
		}
		else
		{
			throw new BadSubclause("The subclause '$test' is not valid");
		}
	}

	/**
	 * Adds an object of clauses
	 */
	public function addClauseGroup(ClauseGroup $group)
	{
		array_push($this->items, $group);
	}

	/**
	 * Compares string subclauses in this group for potential simplification
	 *
	 * Eg: a.b = 1 OR a.b = 2 OR a.b = 3  =>  a.b IN (1,2,3)
	 *     a.b != 'a' AND a.b != 'b'  =>  a.b NOT IN ('a','b') 
	 */
	public function simplify($opts)
	{
		// For the first item, get the comparison type
		$cType = null;

		foreach ($this->items as $subClause)
		{
			if (is_string($subClause))
			{
				$column = $op = $value = null;
				SubclauseParser::parse($subClause, $column, $op, $value);
				if (!$cType)
				{
					$cType = $op;
				}
				else
				{
					if ($cType != $op)
					{
						break;
					}
				}
			}
		}
	}

	/**
	 * Compares one ClauseGroup with another
	 */
	public function equal(ClauseGroup $g)
	{
		return $this->equalRecurse($g, $this, 0);
	}

	private function equalRecurse(ClauseGroup $g1, ClauseGroup $g2, $level)
	{
		if (($g1->getType() != $g2->getType()) || ($g1->getCount() != $g2->getCount()))
		{
			return false;
		}

		$subClauses1 = $g1->getSubclauses();
		$subClauses2 = $g2->getSubclauses();
		$ok = true;
		for ($i = 0; $i < count($subClauses1); $i++)
		{
			$item1 = $subClauses1[$i];
			$item2 = $subClauses2[$i];
			if ($item1 instanceof ClauseGroup)
			{
				$ok = $this->equalRecurse($item1, $item2, $level + 1);
			}
			else
			{
				$ok = ($item1 == $item2);
			}

			if (!$ok)
			{
				break;
			}
		}

		return $ok;
	}
}