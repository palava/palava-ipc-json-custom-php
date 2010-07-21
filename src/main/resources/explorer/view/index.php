<?php if (!defined("EXPLORER_NAME")) die("Must be run within the Command-Explorer."); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title><?php echo EXPLORER_NAME; ?></title>
</head>
<body>
  <div id="ex__left">
   <ul>
     <?php foreach ($Explorer->getCommands() as $command):?>
       <li><?php echo $command['class'] ?></li>
     <?php endforeach; ?>
   </ul>
  </div>
  <div id="ex__content">
    <?php require VIEW;?>
  </div>
</body>
</html>