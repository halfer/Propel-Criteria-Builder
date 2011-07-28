<?php
require_once(dirname(__FILE__) . '/../bootstrap/unit.php');

// Symfony classes
$path = realpath(dirname(__FILE__) . '/../../lib');
require_once($path . '/symfony/exception/sfException.class.php');
require_once($path . '/symfony/config/sfConfig.class.php');

$path = dirname(__FILE__) . '/../../plugins/sfPropelQueryBuilderPlugin/modules/sfPropelQueryBuilder/lib';
require_once($path . '/QuotedStringParser.class.php');

$a = new sfException();

$t = new lime_test(6, new lime_output_color());
$t->diag('QuotedStringParser::replace');

$subject = 'person.age = 18';
$result = QuotedStringParser::replace($subject, 'Hello', 'Goodbye');
$t->ok($subject === $result, "Search for something that doesn't exist in a non-quoted string");

$result = QuotedStringParser::replace($subject, '=', '!=');
$ok = $result === 'person.age != 18';
$t->ok($ok, "Search for something that exists in a non-quoted string");

$subject = "rule.cond = 'LIKE'";
$result = QuotedStringParser::replace($subject, 'LIKE', 'HATE');
$t->ok($subject === $result, "Search for something that exists only within a quoted string");

$result = QuotedStringParser::replace($subject, 'WIBBLE', 'BLAH');
$t->ok($subject === $result, "Search for something that does not exist at all in a quoted string");

$result = QuotedStringParser::replace($subject, '=', '<>');
$ok = ($result === "rule.cond <> 'LIKE'");
$t->ok($ok, "Search for something that exists outside of a quoted string");

$subject = "(person.name > 21 AND name='(not sure)')";
$result = QuotedStringParser::replace($subject, '\(', '');
$result = QuotedStringParser::replace($result, '\)', '');
$ok = ($result === "person.name > 21 AND name='(not sure)'");
$t->ok($ok, "Remove brackets, but not in string");

$t = new lime_test(3, new lime_output_color());
$t->diag('QuotedStringParser::extractStrings');

$subject = "person.location IN ('Brum','Coventry')";
$strs = array();
QuotedStringParser::extractStrings($subject, $strs);
$t->ok($subject == "person.location IN (%%0,%%1)", 'Extract strings from an IN clause');

$subject = "person.name = '%%0'";
$strs = array();
QuotedStringParser::extractStrings($subject, $strs);
$t->ok($subject == "person.name = %%0", "Check that tokens don't confuse string extraction");
$t->ok($strs[0] == "'%%0'", "Check that strings are correctly saved");

