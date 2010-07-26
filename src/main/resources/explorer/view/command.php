<?php if (!defined("EXPLORER_NAME")) die("Must be run within the Command-Explorer."); ?>

<?php $cmd = Package::getCommand(COMMAND); ?>

<h2><?php echo $cmd->getPackage()->getFullName();?></h2>
<h1><?php echo $cmd->getName();?></h1>

<?php if ($cmd->getDescription() != null): ?>
  <p class="description"><?php echo $cmd->getDescription() ?></p>
<?php endif; ?>

<h3>Parameters</h3>
<?php if (count($cmd->getParams())): ?>
<ul>
  <?php foreach ($cmd->getParams() as $param): ?>
    <li>
      <b><?php echo $param->getName(); ?></b>
      <span class="type"><?php echo (count($param->getType()) > 1 ? '('.$param->getType().')' : '' ); ?></span>
      <?php echo ($param->getOptional() ? '<span class="default">default: ' . $param->getDefaultValue() . '</span>' : ''); ?> <br />
      <span class="description"><?php echo $param->getDescription(); ?></span>
    </li>
  <?php endforeach; ?>
</ul>
<?php else: ?>
  <p class="none"><i>no parameters</i></p>
<?php endif; ?>

<h3>Returns</h3>
<?php if (count($cmd->getReturns())): ?>
<ul>
  <?php foreach ($cmd->getReturns() as $return): ?>
    <li>
      <b><?php echo $return->getName(); ?></b> <br />
      <span class="description"><?php echo $return->getDescription(); ?></span>
    </li>
  <?php endforeach; ?>
</ul>
<?php else: ?>
  <p class="none"><i>no return-values</i></p>
<?php endif; ?>

<h3>Throws</h3>
<?php if (count($cmd->getThrows())): ?>
<ul>
  <?php foreach ($cmd->getThrows() as $throws): ?>
    <li>
      <b><?php echo $throws->getName(); ?></b> <br />
      <span class="description"><?php echo $throws->getDescription(); ?></span>
    </li>
  <?php endforeach; ?>
</ul>
<?php else: ?>
  <p class="none"><i>nothing thrown</i></p>
<?php endif; ?>

<h3>Annotations</h3>
<?php if (count($cmd->getAnnotations())): ?>
<ul>
  <?php foreach ($cmd->getAnnotations() as $annotation): ?>
    <li><?php echo $annotation->getName(); ?></li>
  <?php endforeach; ?>
</ul>
<?php else: ?>
  <p class="none"><i>no annotations</i></p>
<?php endif; ?>

<?php //require ROOT . 'view' . DS . 'sandbox.php'; ?>
