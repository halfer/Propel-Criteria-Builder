<?php
class QuotedStringParser
{
	const QUOTE_OPENING = "'";
	const QUOTE_CLOSING = "'";
	const ESCAPE = '\\';
	const TOKEN = '%%';
	const DEBUG = false;

	/**
	 * Splits a string outside of quotes
	 */
	public function split($subject, $regexp)
	{
	}

	/**
	 * Replaces something outside of quotes
	 *
	 * Quotes can be escaped, like \' so, and this function needs to respect this
	 */
	public static function replace($subject, $regexp, $replace)
	{
		$strings = array();

		self::extractStrings($subject, $strings);
		$subject = preg_replace("/$regexp/", $replace, $subject);
		self::insertStrings($subject, $strings);

		return $subject;
	}

	public static function extractStrings(&$subject, &$strings)
	{
		$strings = array();
		$pos = 0;
		$inStr = false;
		$strId = 0;

		while ($pos < ($len = strlen($subject)))
		{
			$char = substr($subject, $pos, 1);
			switch ($char)
			{
				case self::QUOTE_OPENING:
				case self::QUOTE_CLOSING:
					$inStr = !$inStr;
					if ($inStr)
					{
						$openPos = $pos;
					}
					else
					{
						$cut = substr($subject, $openPos, $pos - $openPos + 1);
						$strings[] = $cut;
						$token = self::TOKEN . $strId;
						$strId++;
						$subject = substr($subject, 0, $openPos) . $token . substr($subject, $pos + 1);

						// Now the string has changed length, so adjust pointer
						$pos -= ($len - strlen($subject));
					}
					break;
				case self::ESCAPE:
					if ($inStr)
					{
						$pos++;
					}
					break;
			}
			$pos++;
		}
	}

	/**
	 * Replaces tokens with real strings
	 *
	 * @todo Need unit tests for this - don't quite trust it yet
	 */
	public static function insertStrings(&$subject, $strings)
	{
		// OK, now replace the strings
		$pos = 0;
		for ($i = 0; $i < count($strings); $i++)
		{
			// Get position of token
			$token = self::TOKEN . $i;
			$tempPos = strpos($subject, $token, $pos);

			// Replace token manually, so we don't so search-n-replace on previously inserted strings
			if ($tempPos)
			{
				$pos = $tempPos;
				$subject = substr($subject, 0, $pos) . $strings[$i] . substr($subject, $pos + strlen($token));
				$pos += strlen($strings[$i]);
			}
		}
	}
}