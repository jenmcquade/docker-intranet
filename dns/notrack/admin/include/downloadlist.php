<?php
//Title : Download List
//Description : Takes a file from parameter ?v= and forces the browser to download the echoed out contents.
//Author : QuidsUp
//Date : 2015-03-25
//Usage : This page is called from a link generated in config.php > DisplayCustomList
//Further notes: Strict controls are needed on echo file_get_contents to prevent user from downloading system or config files.

require('./global-vars.php');
$list = '';
$file = '';

header('Content-type: text/plain');

if (isset($_GET['v'])) {
  switch($_GET['v']) {
    case 'black':
      $list = $FileBlackList;
      $file = 'blacklist.txt';
      break;
    case 'white':
      $list = $FileWhiteList;
      $file = 'whitelist.txt';
      break;    
    default:
      echo 'Error: No valid file selected';
      die();
  }
}
else {
  echo 'Error: No file selected';
  die();
}

header('Content-Disposition: attachment; filename="'.$file.'"');
echo file_get_contents($list);

?>
