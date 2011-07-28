<?php

class SubclauseParser
{
	/**
	 * Checks to see if subclause looks OK
	 */
	public static function verifySubclause($subClause)
	{
		$column = $op = $value = null;
		self::parse($subClause, $column, $op, $value);

		return (!is_null($column));
	}

	/**
	 * Parse column, operator and value given a subclause string
	 *
	 * Allowed subclause regexs:
	 *
	 * 	alphadot (=|<>|!=|<|>|<=|>=) number|'string'
	 * 	alphadot LIKE \((any,)*any\)
	 *
	 * @todo Replace hardwired strings with consts
	 */
	public static function parse($subClause, &$column, &$op, &$value)
	{
		// Regex for "column" or "table.column" (?: prevents subpattern capturing)
		$col = "\w+(?:\.\w+)?";
		// Actually let's enforce use of table.column
		$col = "\w+\.\w+";

		// Possible operators (LIKE must have at least one space before it)
		$ops = "(?:=|<>|!=|<|>|<=|>=)";
		$nulls = "(?:IS\s+NOT|IS)\s+NULL\s*";

		// Possible values
		$num = '\-*\d+';
		$str = "'.*'";
		//$str = "%%\d+"; // String placeholder
		$val = "(?:${num}|${str})";

		// List types (temporarily using [] rather than ())
		$numSpc = "\s*${num}\s*";
		$strSpc = "\s*${str}\s*";
		$nList = "${numSpc}(?:,${numSpc})*";
		$sList = "${strSpc}(?:,${strSpc})*";
		$list = "\[($nList|$sList)\]";

		// Try a variety of regexps
		$regexps = array("(${col})\s*(${ops})\s*(${val})",
				"(${col})\s+(LIKE)\s*(${str})",
				"(${col})\s+(NOT\s+LIKE)\s*(${str})",
				"(${col})\s+(IN)\s*(${list})",
				"(${col})\s+(NOT\s+IN)\s*(${list})",
				"(${col})\s+(${nulls})",
				);
		foreach ($regexps as $regexp)
		{
			$count = preg_match("/$regexp/i", trim($subClause), $matches);
			if ($count == 1)
			{
				break;
			}
		}

		if ($count == 1)
		{
			$column = self::formatCompleteColumn($matches[1]);
			$op = $matches[2];
			if (array_key_exists(3, $matches))
			{
				$value = $matches[3];
			}
			else
			// Useful for IS (NOT) NULL, which does not have a value
			{
				$value = null;
			}
		}
		else
		{
			$column = $op = $value = null;
		}
	}

	private static function formatCompleteColumn($col)
	{
		$regexp = "(\w+)\.(\w+)";
		$count = preg_match("/$regexp/i", trim($col), $matches);
		$peer = self::formatTableName($matches[1]) . 'Peer';

		return "${peer}::" . self::formatColumnName($matches[2]);
	}

	private static function formatTableName($name)
	{
		return sfInflector::camelize($name);
	}

	private static function formatColumnName($name)
	{
		return strtoupper($name);
	}
}