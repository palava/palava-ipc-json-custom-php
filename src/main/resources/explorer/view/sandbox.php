<?php if (!defined("EXPLORER_NAME")) die("Must be run within the Command-Explorer."); ?>

<?php

if (!$cmd) $cmd = Package::getCommand(COMMAND);

if (empty($_POST['parameters'])) {
    $params = $cmd->getParams();

    if (!empty($params)) {
        $box_params = '{' . NL;
        foreach ($cmd->getParams() as $param) {
            $box_params .= TAB . '"' . $param->getName() . '" : ,' . NL;
        }
        $box_params = rtrim($box_params, ',' . NL) . NL . '}';
    }
    
} else {
    $box_params = $_POST['parameters'];

    if (!empty($_POST['submit'])) {
        $result = Ajax::runCommand(COMMAND, json_decode($_POST['parameters']));

        if (is_array($result))
            $box_returns = print_r($result, true);
        else
            $box_returns = $result;
    }
}


?>

<div id="ex__sandbox" class="<? if (!empty($_COOKIE['sandbox_expanded'])) echo 'expanded'; ?>">
  <div class="wrap">
    <form method="post" action="<?php echo $_REQUEST_URI; ?>">
      <h2>Sandbox</h2>
      <input type="submit" class="submit" name="run" value="Run" />
      <p class="message"></p>
      <div class="parameters clearfix">
        <label>Parameters:</label>
        <textarea name="parameters"><?php echo isset($box_params) ? $box_params : ''; ?></textarea>
      </div>
      <div class="returns clearfix">
        <label>Returns:</label>
        <textarea name="returns"><?php echo isset($box_returns) ? $box_returns : ''; ?></textarea>
      </div>
    </form>
  </div>
</div>