<?php
require('./include/global-vars.php');
require('./include/global-functions.php');
require('./include/menu.php');

load_config();
ensure_active_session();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <link href="./css/master.css" rel="stylesheet" type="text/css">  
  <link rel="icon" type="image/png" href="./favicon.png">
  <script src="./include/menu.js"></script>
  <title>NoTrack Help</title>
</head>

<body>
<?php
//-------------------------------------------------------------------
function LoadHelpPage($Page) {  
  if (file_exists('./help/'.$Page.'.html')) {
    echo file_get_contents('./help/'.$Page.'.html');
  }
  else {
    echo 'Error: File not found'.PHP_EOL;
  }
}
//-------------------------------------------------------------------
draw_topmenu();
draw_helpmenu();
echo '<div id="main">'.PHP_EOL;

if (isset($_GET['p'])) {
  switch($_GET['p']) {
    case 'position':
      LoadHelpPage('position');
      break;
    case 'newblocklist':
      LoadHelpPage('newblocklist');
      break;
    case 'security':
      LoadHelpPage('security');
      break;
    default:
      LoadHelpPage('list');
  }
}
else {
  LoadHelpPage('list');
}
?>
</div>
</body>
</html>
