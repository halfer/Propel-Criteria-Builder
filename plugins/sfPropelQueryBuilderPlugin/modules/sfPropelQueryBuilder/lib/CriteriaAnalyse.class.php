<?php
class CriteriaAnalyse
{
	const BRACKET_OPEN = '(';
	const BRACKET_CLOSE = ')';
	const STATEMENT_OR = ' OR ';
	const STATEMENT_AND = ' AND ';

	const RECURSE_LIMIT = 10;

	var $strings = array();

	public function analyse($strLogic)
	{
		// Remove CRs
		$strLogic = $this->removeReturns($strLogic);
		
		// Parse out strings as #1 priority
		$this->strings = array();
		QuotedStringParser::extractStrings($strLogic, $this->strings);

		// Check brackets are ok first
		if (!$this->checkBracketSensibility($strLogic))
		{
			throw new BadBracketNesting('Brackets are incorrectly nested');
		}

		// Preprocessing (eg switch IN () to IN [] to avoid splitting in the wrong places)
		$strLogic = $this->preProcess($strLogic);

		// Split the string on brackets first, needs to be recursive
		$group = $this->deBracket($strLogic);

		return $group;
	}

	private function checkBracketSensibility($input)
	{
		$ok = true;
		$count = 0;
		for($i = 0; $i < strlen($input); $i++)
		{
			$char = substr($input, $i, 1);
			switch ($char)
			{
				case self::BRACKET_OPEN: $count++; break;
				case self::BRACKET_CLOSE: $count--; break;
			}

			if ($count < 0)
			{
				$ok = false;
				break;
			}
		}

		// If the brackets aren't closed, that's a problem too
		if ($ok)
		{
			$ok = ($count == 0);
		}

		return $ok;
	}

	/**
	 * Replaces carriage returns with spaces
	 *
	 * @param string $str
	 * @return string
	 */
	private function removeReturns($str)
	{
		$pieces = explode("\n", $str);
		return implode(' ', $pieces);
	}

	private function preProcess($input)
	{
		// Regexp: switch a round bracket pair after " IN" to square brackets
		$needle = "/(\s+IN)\s*\(([^\)]+)\)/";
		$newNeedle = '$1 [$2]';
		$newHaystack = preg_replace($needle, $newNeedle, $input);

		// @todo Compress multiple whitespace chars outside quotes to just one space
		//echo "Tidying spaces<br/>";
		//$newHaystack = QuotedStringParser::replace($newHaystack, '\s{2,}', ' ');
		//echo "Finished tidying spaces<br/>";

		return $newHaystack;
	}

	private function postProcess($group, $strings)
	{
		return $group;
	}

	/**
	 * Function to generate nested ClauseGroup
	 *
	 * @return ClauseGroup
	 */
	private function deBracket($input)
	{
		$level = 0;
		return $this->deBracketRecurse($input, $level);
	}

	/**
	 * Recursive function to generate nested ClauseGroup
	 *
	 * @return ClauseGroup
	 */
	private function deBracketRecurse($section, $level)
	{
		// Initially, check the process hasn't run away with itself!
		if ($level > self::RECURSE_LIMIT)
		{
			return null;
			throw new Exception("Passed maximum recursion depth of $level");
		}

		// Search for first bracket
		$intFirst = strpos($section, self::BRACKET_OPEN);

		// Run this loop whilst opening brackets are found
		$group = new ClauseGroup();
		while ($intFirst !== false)
		{
			// Get matching bracket
			$intMatching = $this->findMatching($section, $intFirst);

			$strFirst = substr($section, 0, $intFirst);
			$strMiddle = substr($section, $intFirst + 1, $intMatching - $intFirst - 1);
			$strLast = substr($section, $intMatching + 1);
			/* echo "#$level: Analysing: $section<br/>";
			echo "#$level: Before bracket: <strong>$strFirst</strong><br/>";
			echo "#$level: Within brackets: <strong>$strMiddle</strong><br/>";
			echo "#$level: After brackets: <strong>$strLast</strong><br/>";
			echo "<br/>"; */

			// Parse the first, non-bracketed section
			$g = $this->parse($strFirst);
			if (!is_null($g))
			{
				// If the clause group had just 1 item, then note how the
				// type would not get carried across
				// if ($g->getCount() == 1)
				//{
				//	$group->addSubclause($g->getFirstSubclause());
				//}
				//else
				//{
					$group = $this->groupMerge($group, $g);
				//}
			}

			$g = $this->deBracketRecurse($strMiddle, $level + 1);
			if ($g->getCount() == 1)
			{
				$group->addSubclause($g->getFirstSubclause());
			}
			else
			{
				$group->addClauseGroup($g);
			}

			// Now treat the section as the unprocessed bit
			$section = $strLast;
			$intFirst = strpos($section, self::BRACKET_OPEN);
		}

		$g = $this->parse($section);

		$group = $this->groupMerge($group, $g);
		if (($group->getCount() == 1) && ($group->getFirstSubclause() instanceof ClauseGroup))
		{
			$group = $group->getFirstSubclause();
		}

		return $group;
	}

	/**
	 * Given an opening bracket position and a string, find the matching closing bracket
	 */
	public function findMatching($clause, $bracketPos)
	{
		// Since we skip over the first bracket, set the count to 1
		$count = 1;

		do
		{
			if ($count > 0)
			{
				$bracketPos++;
			}

			$char = substr($clause, $bracketPos, 1);
			switch ($char)
			{
				case self::BRACKET_OPEN: $count++; break;
				case self::BRACKET_CLOSE: $count--; break;
			}

		}
		while (($bracketPos < strlen($clause)) && ($count > 0));

		// Another sense check
		if ($count > 0)
		{
			//throw new Exception("Mismatching brackets found when searching for closing bracket in <$clause>");
			echo "Mismatching brackets found in <$clause>";
		}

		return $bracketPos;
	}

	/**
	 * Parses a non-bracketed expression
	 *
	 * Returns a ClauseGroup for a multi-part expression, or returns a string for an
	 * expression containing just one (<col> <test> <value>) part
	 */
	private function parse($clause)
	{
		// If the input string is essentially empty then return null
		if (trim($clause) == '')
		{
			return null;
		}

		// Ensure that ANDs and ORs are not mixed
		$strAnd = ClauseGroup::BOOLEAN_AND;
		$strOr = ClauseGroup::BOOLEAN_OR;
		$boolAnd = (strpos($clause, " $strAnd ") !== false);
		$boolOr = (strpos($clause, " $strOr ") !== false);
		if ($boolAnd && $boolOr)
		{
			throw new SyntaxError("Need to bracket ANDs and ORs to indicate precedence in clause <$clause>");
		}

		if ($boolOr)
		{
			$split = $strOr;
		}
		elseif ($boolAnd)
		{
			$split = $strAnd;
		}
		else
		{
			$split = null;
		}

		// If applicable, split the clause into subclauses and decide the type
		if ($split)
		{
			$subClauses = explode(" $split ", $clause);
		}
		else
		{
			$subClauses = array($clause);
		}
		$group = new ClauseGroup();
		$group->setType($split);

		// Then add all the subclauses in
		foreach ($subClauses as $subClause)
		{
			if (trim($subClause) != '')
			{
				QuotedStringParser::insertStrings($subClause, $this->strings);
				$group->addSubclause($subClause);
			}
		}

		// If there is only one subclause, return a string rather than a ClauseGroup
		/*
		$subClauseArray = $group->getSubclauses();
		if (count($subClauseArray) == 1)
		{
			return $subClauseArray[0];
		}
		else
		{
			return $group;
		}*/

		return $group;
	}

	/**
	 * Merge two ClauseGroups
	 *
	 * NB: first param must be a ClauseGroup (ie not a null)
	 * Second param can be either a ClauseGroup, a string clause, or null
	 */
	private function groupMerge(ClauseGroup $g1, $g2)
	{
		if (is_null($g2))
		{
			return $g1;
		}
		elseif (is_string($g2))
		{
			$clone = clone $g1;
			$clone->addSubclause($g2);
			return $clone;
		}
		else
		{
			// If one of the boolean types is null, it can be treated as empty
			$t1 = $g1->getType();
			$t2 = $g2->getType();
			if (is_null($t1))
			{
				$t1 = $t2;
			}
			elseif (is_null($t2))
			{
				$t2 = $t1;
			}

			// Can't merge two groups of differing bool operators
			if ($t1 != $t2)
			{
				throw new Exception("Can't merge two groups of differing boolean operators ($t1, $t2)");
			}
			else
			{
				$clone = clone $g1;
				$subClauses = $g2->getSubclauses();
				foreach ($subClauses as $subClause)
				{
					//QuotedStringParser::insertStrings($subClause, $this->strings);
					$clone->addSubclause($subClause);
				}

				// We've made the two types the same, so use one on the clone
				$clone->setType($t1);
				return $clone;
			}
		}
	}
}

?>