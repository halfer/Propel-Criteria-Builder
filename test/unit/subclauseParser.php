<?php
require_once(dirname(__FILE__) . '/../bootstrap/unit.php');

// Symfony classes
$path = realpath(dirname(__FILE__) . '/../../lib');
require_once($path . '/symfony/exception/sfException.class.php');
require_once($path . '/symfony/config/sfConfig.class.php');
require_once($path . '/symfony/util/sfToolkit.class.php');
require_once($path . '/symfony/util/sfInflector.class.php');

$path = dirname(__FILE__) . '/../../plugins/sfPropelQueryBuilderPlugin/modules/sfPropelQueryBuilder/lib';
require_once($path . '/SubclauseParser.class.php');
require_once($path  .'/BadSubclause.class.php');

$a = new sfException();

$t = new lime_test(3, new lime_output_color());
$t->diag('SubclauseParser::parse');

// Test numeric equality comparison
$col = $op = $value = null;
SubclauseParser::parse('table.column = 1', $col, $op, $value);
$ok = (($col == 'TablePeer::COLUMN') && ($op == '=') && ($value == '1'));
$t->ok($ok, 'decode a column equality comparison');

// Test LIKE
$col = $op = $value = null;
SubclauseParser::parse("table.column LIKE 'A%'", $col, $op, $value);
$ok = (($col == 'TablePeer::COLUMN') && ($op == 'LIKE') && ($value == "'A%'"));
$t->ok($ok, 'decode a column string LIKE comparison');

// Test numeric IN
$col = $op = $value = null;
SubclauseParser::parse("table.column IN (1, 2)", $col, $op, $value);
echo "$col\n$op\n$value\n";
$ok = (($col == 'TablePeer::COLUMN') && ($op == 'IN') && ($value == "(1, 2)"));
$t->ok($ok, 'decode a column numeric IN test');
