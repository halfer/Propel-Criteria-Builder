<?php if (isset($tree)) { ?>
<fieldset class="tree">
  <legend>Parse tree</legend>
  <pre><?php echo $tree ?></pre>
</fieldset>
<?php } ?>

<?php if (isset($code)) { ?>
<fieldset class="code">
  <legend>Propel code</legend>
  <pre><?php echo $code ?></pre>
</fieldset>
<?php } ?>
