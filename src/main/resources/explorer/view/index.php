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
  <div id="ex__left">
      <?php
          // helper method to recursiv generate the html
          function generateUl($list, $level) {
              if (empty($list)) return;
              
              echo '<ul class="level_' . $level++ . '">';
              foreach ($list as $li) {
                  if ($li instanceof Package) {
                      echo '<li><span>'.$li->getName().'</span>';
                      generateUl($li->getPackages(), $level);
                      generateUl($li->getCommands(), $level);
                      echo '</li>';
                  } else {
                      echo '<li><a href="?cmd='.$li->getFullName().'">'.$li->getName().'</a></li>';
                  }
              }
              echo '</ul>';
          }

          // now generate everything 
          generateUl(Package::getRoots(), 0);
        ?>
  </div>
  <div id="ex__content">
    <?php require ROOT . 'view' . DS . VIEW;?>
  </div>
</body>
</html>