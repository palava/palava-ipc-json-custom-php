<?php if (!defined("EXPLORER_NAME")) die("Must be run within the Command-Explorer."); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?php echo EXPLORER_NAME; ?></title>
  <link href="css/explorer.css" rel="stylesheet" type="text/css" />
  <script src="js/jquery-1.4.2.min.js" type="text/javascript"></script>
  <script src="js/script.js" type="text/javascript"></script>
</head>
<body>
  <div id="ex__wrapper" class="clearfix">
    <div id="ex__left">
        <a href="#" class="toggle">show all</a>
        <h2>Commands</h2>
        <ul class="level_0">
        <?php
          $open = array();
          if (!empty($_COOKIE['open'])) {
            $open = explode(' ', $_COOKIE['open']);
          }
          
          // helper method to recursiv generate the html
          function generatePackage($package, $level, $visible = true) {
              global $open;
              $css_name = str_replace('.', '_', $package->getFullName());
              $style = 'display: none;';
              if ($visible && in_array($css_name, $open)) {
                  $style = 'display: block;';
              } else {
                  $visible = false;
              }
              
              echo '<li><span>'.$package->getName().'</span>';
              echo '<ul id="' . $css_name . '" class="level_' . $level . '" style="' . $style . '">';
              
              foreach ($package->getPackages() as $pkg) {
                  generatePackage($pkg, $level + 1, $visible);
              }
              foreach ($package->getCommands() as $command) {
                  generateCommand($command);
              }
              
              echo '</ul></li>';
          }
          
          function generateCommand($command) {
              echo '<li><a href="?cmd='.$command->getFullName().'">'.$command->getName().'</a></li>';
          }

          // now generate everything           
          foreach (Package::getRoots() as $root) {
              if ($root instanceof Package) {
                generatePackage($root, 1);
              } else {
                generateCommand($root);
              }
          }
        ?>
        </ul>
    </div>
    <div id="ex__content">
        <?php require ROOT . 'view' . DS . VIEW;?>
    </div>
  </div>
</body>
</html>