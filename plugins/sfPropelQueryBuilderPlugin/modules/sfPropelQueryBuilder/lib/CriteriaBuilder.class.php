<?php

class CriteriaBuilder
{
	public $cPrefix;
	public $crit;
	public $detailed;
	public $comments;
	public $concat;
	public $returnType;
	public $loop;
	public $resultSetType;

	private $ops = array('=' => null, '>' => 'GREATER_THAN', '>=' => 'GREATER_EQUAL',
			'<' => 'LESS_THAN', '<=' => 'LESS_EQUAL', '<>' => 'NOT_EQUAL',
			'!=' => 'ALT_NOT_EQUAL', 'LIKE' => 'LIKE', 'NOT LIKE' => 'NOT_LIKE',
			'IN' => 'IN', 'NOT IN' => 'NOT_IN', 'IS NOT NULL' => 'ISNOTNULL',
			'IS NULL' => 'ISNULL',
	);

	// Operators that we need to specifically deal with (usually for post-processing)
	const OP_IN = 'IN';
	const OP_NOT_IN = 'NOT IN';

	// Return types
	const RETURN_ARRAY = 'array';
	const RETURN_RESULTSET = 'resultset';

	// ResultSet index types
	const RESULTSET_NUM = 'FETCHMODE_NUM';
	const RESULTSET_ASSOC = 'FETCHMODE_ASSOC';

	public function __construct($cPrefix = 'crit', $crit = 'c', $detailed = false,
		$comments = true, $concat = false)
	{
		$this->cPrefix = $cPrefix;
		$this->crit = $crit;
		$this->detailed = $detailed;
		$this->comments = $comments;
		$this->concat = $concat;
	}

	public function build(ClauseGroup $group)
	{
		$cid = 0;
		$lines[0] = $this->makeLine('$' . $this->crit . " = new Criteria();\n", -1, false);
		$code = $this->buildRecursive($group, 0, $cid);
		$lines = array_merge($lines, $code);

		if ($this->detailed)
		{
			return $lines;
		}
		else
		{
			$result = '';
			foreach ($lines as $line)
			{
				$result .= $line['statement'];
			}
			return $result;
		}
	}

	/**
	 * Recursive function to build the code from nested ClauseGroup objects
	 */
	private function buildRecursive(ClauseGroup $group, $level, &$cid, $simplifyOpts = null)
	{
		$lines = array();

		$i = 0;
		foreach ($group->getSubclauses() as $subClause)
		{
			if ($subClause instanceof ClauseGroup)
			{
				// Pass the simplification options to the ClauseGroup
				$subClause->simplify($simplifyOpts);

				$block = $this->buildRecursive($subClause, $level + 1, $cid, $simplifyOpts);
				$type = $subClause->getType();
				switch ($type)
				{
					case ClauseGroup::BOOLEAN_AND:
						break;
					case ClauseGroup::BOOLEAN_OR:
						break;
					default:
						throw new Exception('Unrecognised boolean operator found');
				}

				// Copy code into lines
				foreach ($block as $pieces)
				{
					array_push($lines, $pieces);
				}
			}
			else
			{
				// Build a criterion statement
				SubclauseParser::parse($subClause, $col, $op, $value);

				// This is the name of the criterion object
				$crit = '$' . $this->cPrefix . $cid;

				// Modify the value if required
				$value = $this->valuePostProcess($op, $value);

				// Loop through the operators we presently know about
				$line = null;
				foreach ($this->ops as $thatOp => $syntax)
				{
					if ($op == $thatOp)
					{
						$cid++;

						// For the equality operator...
						if (is_null($syntax))
						{
							$statement = "$crit = \$c->getNewCriterion($col, $value);\n";
						}
						elseif (is_null($value))
						// ... the null operators ...
						{
							$statement = "$crit = \$c->getNewCriterion($col, null, Criteria::$syntax);\n";
						}
						else
						// ... all other operators
						{
							$statement = "$crit = \$c->getNewCriterion($col, $value, Criteria::$syntax);\n";
						}

						$line = $this->makeLine($statement, $level, ($i == 0), $crit);
						array_push($lines, $line);
						break;
					}
				}

				// Moan if the operator is not found, as it was detected by SubclauseParser!
				if (is_null($line))
				{
					throw new Exception("Unrecognised comparison operator ('$op') found");
				}
			}

			$i++;
		}

		// OK, link up the criterions at our current level
		$crits = '';
		$first = null;
		$code = '';
		foreach ($lines as $line)
		{
			if ($line['level'] == $level)
			{
				$crit = $line['crit'];
				if (!$first)
				{
					$first = $crit;
				}
				else
				{
					$op = $this->boolOpToPropelOp($group->getType());
					$code .= "${first}->${op}($crit);\n";
				}
				$crits .= $line['crit'] . ' ';
			}
		}

		// Trim off the last \n we've given the code block
		$code = substr($code, 0, strlen($code) - 1);

		$type = $group->getType();
		$str = "$code\n";
		if ($this->comments)
		{
			$str = "// Perform $type at level $level ($crits)\n$str";
		}
		$str = "\n$str";
		$line = $this->makeLine($str, $level - 1, false, $first);
		array_push($lines, $line);

		// Link into $c if last item
		if ($level == 0)
		{
			// Determine the right select method and loop to use
			if ($this->returnType == self::RETURN_ARRAY)
			{
				$method = 'doSelect';
				$loop = "foreach (\$result as \$obj)\n{\n\t//\$val = \$obj->getValue();\n}";
				$postOp = "";
			}
			else
			{
				// Determine fetch mode
				$fetchMode = $this->resultSetType;
				if ($fetchMode == self::RESULTSET_NUM)
				{
					$col1 = '1';
					$col2 = '2';
				}
				else
				{
					$col1 = "'col1'";
					$col2 = "'col2'";
				}

				$method = 'doSelectRS';
				$loop = "while (\$result->next())\n{\n\t//\$str = \$result->getString($col1);\n\t//\$int = \$result->getInt($col2);\n}";
				$postOp = "\$result->setFetchMode(ResultSet::$fetchMode);\n";
			}

			// OK, add the select part...
			$statement = "\n";
			if ($this->comments)
			{
				$statement .= "// Remember to change the peer class here for the correct one in your model\n";
			}
			$statement .= "\$c->add($first);\n\$result = TablePeer::${method}(\$c);\n$postOp\n";

			// ... now add a demo loop
			if ($this->loop)
			{
				if ($this->comments)
				{
					$statement .= "// This loop will of course need to be edited to work\n";
				}
				$statement .= $loop;
			}
			$line = $this->makeLine($statement, -1);
			array_push($lines, $line);
		}

		return $lines;
	}

	private function makeLine($statement, $level, $first = false, $critName = null)
	{
		$line = array();
		$line['statement'] = $statement;
		$line['level'] = $level;
		$line['first'] = $first;
		$line['crit'] = $critName;

		return $line;
	}

	private function boolOpToPropelOp($boolOp)
	{
		$result = null;

		switch ($boolOp)
		{
			case ClauseGroup::BOOLEAN_AND:
				$result = 'addAnd';
				break;
			case ClauseGroup::BOOLEAN_OR:
				$result = 'addOr';
				break;
			default:
				throw new Exception("Unrecognised boolean operator '$boolOp'");
		}

		return $result;
	}

	/**
	 * Optionally modify the value syntax depending on the operation
	 */
	private function valuePostProcess($op, $value)
	{
		// We should be able to guarantee here that the list syntax is OK, else
		// it would not get through SubclauseParser. However as of 28 June, unmatched
		// single-quotes can sneak through!
		if (($op == self::OP_IN) || ($op == self::OP_NOT_IN))
		{
			$value = str_replace('[', 'array(', $value);
			$value = str_replace(']', ')', $value);
		}

		return $value;
	}
}

