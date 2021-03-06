<style>
<!--

h1, h2
{
  margin: 3px 0 5px 0;
}

h1 a
{
  color: black;
}

fieldset
{
  margin-top: 2px;
}

.formSpace
{
  margin: 4px;
}

.code
{
  border: 1px solid green;
}

.tree
{
  border: 1px solid red;
}

.code, .tree
{
  margin: 6px;
  padding: 6px;
  font-family: monospace;
}

.error
{
  border: 1px solid red;
  margin: 6px;
  padding: 6px;
  background-color: #ee8888;
}

p
{
  margin: 6px 4px;
}

-->
</style>

<h1><a href="/">Criteria demo</a></h1>

<?php if (!$sf_request->hasErrors()) { ?>
	<?php include_partial('result', array('code' => $code, 'tree' => $tree)) ?>
<?php } else { ?>
	<?php include_partial('error') ?>
	<?php include_partial('result', array('tree' => $tree)) ?>
<?php } ?>

<p>This is an alpha-release of a utility that converts pseudo-SQL into PHP Propel code. Give it a try &mdash; feedback is
welcome on the <a href="http://www.symfony-project.org/forum/index.php/m/32692/">symfony forum</a>!</p>
<fieldset>
<legend>Enter pseudo-sql</legend>
<p>Columns must be in the form table.field and strings must be in single quotes. Supported comparison operators are =, &lt;&gt;, !=, &lt;, &gt;, &lt;= and &gt;=. Also allowed are LIKE, NOT LIKE, IN, NOT IN, IS NULL and IS NOT NULL. In strings, the apostrophe character can be escaped with the use of a backslash.
Brackets  to any depth are fine, and clauses can be joined with AND and OR. No subclause may contain both
AND and OR - use brackets to indicate precedence.</p>
<form method="post" action="criteria/analyse">
<?php echo textarea_tag('logic', $input, 'size=90x8 class=formSpace') ?>
<div class="formSpace">
  <div><?php echo checkbox_tag('code_comments', 'yes', true) ?> Generate code comments</div>
  <div><?php echo checkbox_tag('demo_loop', 'yes', true) ?> Include skeleton iteration loop</div>
  <div><?php echo checkbox_tag('show_tree', 'yes', false) ?> Show parse tree</div>
  <div>
    <?php echo radiobutton_tag('return_type', CriteriaBuilder::RETURN_ARRAY, true) ?>Propel object array
    <fieldset>
    <legend><?php echo radiobutton_tag('return_type', CriteriaBuilder::RETURN_RESULTSET) ?>Propel ResultSet</legend>
    <div><?php echo radiobutton_tag('resultset_type', CriteriaBuilder::RESULTSET_NUM, true) ?>Numerically indexed array (slightly faster)
    <?php echo radiobutton_tag('resultset_type', CriteriaBuilder::RESULTSET_ASSOC) ?>Associative array (more maintainable)</div>
    </fieldset>
  </div>
  <div>
  Criterion variable prefix: <?php echo input_tag('crit_prefix', 'crit') ?>
  </div>
  <?php echo submit_tag('Generate') ?>
</div>
</form>
</fieldset>

<h2>To-do list</h2>
<ul>
<li><s>Unit tests for parser</s></li>
<li><s>Round brackets within quoted strings causes parse error</s></li>
<li><s>Accidental token markers in strings are dealt with incorrectly</s></li>
<li><s>Ensure semi-quoted strings in IN clauses throw an error</s></li>
<li><s>Convert table to peer name correctly (map_zone is parsed Map_zonePeer instead of MapZonePeer) - thanks Piwa&iuml;</s></li>
<li>Support Propel 1.3 PropelPDO clauses</li>
<li><s>Support comparisons with negative numbers - thanks Piwa&iuml;</s></li>
<li>Subclause parses incorrectly when joined by something other than AND or OR</li>
<li><s>Support IS NULL and IS NOT NULL</s></li>
<li>Use $c->addAnd() or $c->addOr() rather than Criterions for boolean expressions at the first level of the expression hierarchy</li>
<li><s>Allow backslash to act as escape character in quoted strings</s></li>
<li><s>IN and NOT IN arrays should be allowed to contain one element</s></li>
<li><s>Correctly interpret NOT LIKE and NOT IN if they contain more than one space character</s></li>
<li><s>Radio buttons to choose Propel arrays or ResultSet result</s></li>
<li><s>Tickbox to include demo iterative loop</s></li>
<li><s>Remove redundant carriage returns in input</s></li>
<li>Preserve options upon query submit</li>
<li>Collapse multiple ORs on the same field to IN</li>
<li>Allow spec of ASC and DESC sort columns</li>
<li>Improve the grouping of criterion creation and merging - thanks Piwa&iuml;</li>
<li>Determine peer class from a FROM clause, or allow it to be entered</li>
<li>Database-sensitive understanding of the wildcards in LIKE?</li>
<li>Allow a limit clause</li>
<li>Option to concatenate Criterion object ops (eg: $crit0->addAnd($crit1)->addAnd($crit3);)</li>
<li>Complain if the same field appears in more than one equality comparison in an AND sub-clause</li>
<li>If a nested op is the same as the op in the clause above, flatten the structure</li>
<li>Suppress comments for AND/OR when processing just a single subclause
<li>Support schema-based joins</li>
</ul>
