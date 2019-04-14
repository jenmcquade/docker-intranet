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
  <title>NoTrack - Upgrade</title>
</head>

<body>
<?php
draw_topmenu('Config');
draw_sidemenu();

//There are two views for upgrade:
//1. Carrying out upgrade (dependant on POST['doupgrade']) which shows the result of ntrk-upgrade
//2. Version info, upgrade button, and curl output

echo '<div id="main">'.PHP_EOL;

if (isset($_POST['doupgrade'])) {    //Check if we are running upgrade or displaying status
  echo '<div class="sys-group">'.PHP_EOL;
  echo '<h5>NoTrack Upgrade</h5></div>'.PHP_EOL;
    
  echo '<pre>';
  passthru(NTRK_EXEC.'--upgrade');
  echo '</pre>'.PHP_EOL;
    
  echo '<div class="sys-group">'.PHP_EOL;
  echo '<div class="centered">'.PHP_EOL;         //Center div for button
  echo '<button class="button-blue" onclick="window.location=\'./\'">Back</button>'.PHP_EOL;    
  echo '</div></div>'.PHP_EOL;
  $mem->flush();                                 //Delete config from Memcache
  sleep(1);
}

else {                                           //Just displaying status
  echo '<form method="post">'.PHP_EOL;
  echo '<input type="hidden" name="doupgrade">'.PHP_EOL;
  if (VERSION == $Config['LatestVersion']) {     //See if upgrade Needed
    draw_systable('NoTrack Upgrade');
    draw_sysrow('Status', 'Running the latest version v'.VERSION);
    draw_sysrow('Force Upgrade', 'Force upgrade to Development version of NoTrack<br><input type="submit" class="button-danger" value="Upgrade">');
    echo '</table>'.PHP_EOL;
    echo '</div></div>'.PHP_EOL;
    echo '</form>'.PHP_EOL;
  }
  else {
    draw_systable('NoTrack Upgrade');
    draw_sysrow('Status', 'Running version v'.VERSION.'<br>Latest version available: v'.$Config['LatestVersion']);
    draw_sysrow('Commence Upgrade', '<input type="submit" class="button-blue" value="Upgrade">');
    echo '</table>'.PHP_EOL;
    echo '</div></div>'.PHP_EOL;
    echo '</form>'.PHP_EOL;
  }
   
  //Display changelog
  if (extension_loaded('curl')) {                //Check if user has Curl installed
    $ch = curl_init();                           //Initiate curl
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_setopt($ch, CURLOPT_URL,'https://raw.githubusercontent.com/quidsup/notrack/master/changelog.txt');
    $data = curl_exec($ch);                      //Download Changelog
    curl_close($ch);                             //Close curl
    echo '<pre>'.PHP_EOL;
    echo $data;                                  //Display Data
    echo '</pre>'.PHP_EOL;
  }  
}
?> 
</div>
</body>
</html>
