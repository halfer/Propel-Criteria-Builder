<?php
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

// Symfony classes
$path = realpath(dirname(__FILE__) . '/../../lib');
require_once($path . '/symfony/exception/sfException.class.php');
require_once($path . '/symfony/config/sfConfig.class.php');
require_once($path . '/symfony/util/sfToolkit.class.php');
require_once($path . '/symfony/util/sfInflector.class.php');

// Analyser classes
$path = dirname(__FILE__) . '/../../plugins/sfPropelQueryBuilderPlugin/modules/sfPropelQueryBuilder/lib';
require_once($path . '/CriteriaAnalyse.class.php');
require_once($path . '/ClauseGroup.class.php');
require_once($path . '/BadSubclause.class.php');
require_once($path . '/QuotedStringParser.class.php');
require_once($path . '/SubclauseParser.class.php');

$a = new sfException();

$t = new lime_test(12, new lime_output_color());
$t->diag('CriteriaAnalyse::analyse');
$ca = new CriteriaAnalyse();

// Simple statement with no brackets
$sql1 = "user.enabled = ''";
$result = $ca->analyse($sql1);
$g1 = new ClauseGroup();
$g1->addSubclause($sql1);
$t->ok($result->equal($g1), 'decode a valid single subclause, non-bracketed string');

// Negative numbers
$sql = 'user.score < -10';
$result = $ca->analyse($sql);
$g = new ClauseGroup();
$g->addSubclause($sql);
$t->ok($result->equal($g), 'decode a valid single subclause containing a -ve number');

// Compound AND statement
$clause1 = 'user.enabled = 1';
$clause2 = 'org.enabled = 1';
$sql = "$clause1 AND $clause2";
$result = $ca->analyse($sql);
$g = new ClauseGroup();
$g->addSubclause($clause1);
$g->addSubclause($clause2);
$g->setType(ClauseGroup::BOOLEAN_AND);
$t->ok($result->equal($g), "decode a valid non-bracketed string containing an AND");

// Compound OR
$clause1 = "user.type = 'admin'";
$clause2 = "user.type = 'super'";
$clause3 = "user.type = 'normal'";
$sql = "$clause1 OR $clause2 OR $clause3";
$result = $ca->analyse($sql);
$g = new ClauseGroup();
$g->addSubclause($clause1);
$g->addSubclause($clause2);
$g->addSubclause($clause3);
$g->setType(ClauseGroup::BOOLEAN_OR);
$t->ok($result->equal($g), "decode a valid non-bracketed string containing two ORs");

$sql = "person.age > 18 AND person.gender = 'M'";
$result = $ca->analyse($sql);
$g = new ClauseGroup();
$g->addSubclause("person.age > 18");
$g->addSubclause("person.gender = 'M'");
$g->setType(ClauseGroup::BOOLEAN_AND);
$t->ok($result->equal($g), "decode a valid non-bracketed string containing an AND");

// Redundant bracket removal
$sql = "((($sql1)))";
$result = $ca->analyse($sql);
$t->ok($result->equal($g1), "decode a valid single subclause with redundant brackets");

// Catch errors in subclause
$sql = 'user.enabled = ';
try
{
	$result = $ca->analyse($sql);
	$caught = false;
}
catch (Exception $e)
{
	$caught = true;
}
$t->ok($caught, "throw an error for an incomplete subclause");

// Catch bad operator
$sql = "user.enabled ABITLIKE 'hello'";
try
{
	$result = $ca->analyse($sql);
	$caught = false;
}
catch (Exception $e)
{
	$caught = true;
}
$t->ok($caught, 'throw an error for an unrecognised operator');

$clause1 = "user.enabled = ''";
$clause2 = "user.cat = 'admin'";
$clause3 = "user.cat = 'super'";
$clause4 = "org.enabled = 1";
$sql = "$clause1 AND ($clause2 OR $clause3) AND ($clause4)";
$result = $ca->analyse($sql);
$g1 = new ClauseGroup();
$g1->addSubclause($clause1);
$g2 = new ClauseGroup();
$g2->addSubclause($clause2);
$g2->addSubclause($clause3);
$g2->setType(ClauseGroup::BOOLEAN_OR);
$g1->addClauseGroup($g2);
$g1->addSubclause($clause4);
$g1->setType(ClauseGroup::BOOLEAN_AND);
$t->ok($result->equal($g1), 'decode a valid bracketed string containing ANDs and ORs');

$clause1 = "person.age IN (17,18,19,20)";
$clause2 = "person.height IN (100, 101)";
$result = $ca->analyse($clause1);
$rClause1 = $result->getFirstSubclause();
$g = new ClauseGroup();
$g->addSubclause($rClause1);
$t->ok($result->equal($g), 'decode a simple IN statement');

try
{
	$clause = $clause1 . ' OR ' . $clause2;
	$result = $ca->analyse($clause);
	$clauses = $result->getSubclauses();
	$g->addSubclause($clauses[1]);
	$g->setType(ClauseGroup::BOOLEAN_OR);
	$ok = $result->equal($g);
}
catch(Exception $e)
{
	$ok = false;
}
$t->ok($ok, 'decode a two IN statements');

$clause = "person.name IN ('person.age IN (1, 2, 3)')";
try
{
	$result = $ca->analyse($clause);
	$rClause = $result->getFirstSubclause();
	$g = new ClauseGroup();
	$g->addSubclause($rClause);
	$ok = $result->equal($g);
}
catch (Exception $e)
{
	$ok = false;
}
$t->ok($ok, 'decode a simple IN statement containing a misleading IN string');
