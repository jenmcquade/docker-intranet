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
  <title>NoTrack - DHCP Leases</title>
</head>

<body>
<?php
draw_topmenu('Network');
draw_sidemenu();
echo '<div id="main">'.PHP_EOL;

echo '<div class="sys-group"><div class="sys-title">'.PHP_EOL;
echo '<h5>DHCP Leases</h5>'.PHP_EOL;
echo '</div>'.PHP_EOL;
echo '<div class="sys-items">'.PHP_EOL;

//Is DHCP Active?
if (file_exists('/var/lib/misc/dnsmasq.leases')) {
  $FileHandle= fopen('/var/lib/misc/dnsmasq.leases', 'r') or die('Error unable to open /var/lib/misc/dnsmasq.leases');

  echo '<table id="dhcp-table">'.PHP_EOL;
  echo '<tr><th>IP Allocated</th><th>Device Name</th><th>MAC Address</th><th>Valid Until</th>'.PHP_EOL;
  
  while (!feof($FileHandle)) {
    $Line = trim(fgets($FileHandle));            //Read Line of LogFile
    if ($Line != '') {                           //Sometimes a blank line appears in log file
      $Seg = explode(' ', $Line);
      //0 - Time Requested in Unix Time
      //1 - MAC Address
      //2 - IP Allocated
      //3 - Device Name
      //4 - '*' or MAC address
      echo '<tr><td>'.$Seg[2].'</td><td>'.$Seg[3].'</td><td>'.$Seg[1].'</td><td>'.date("d M Y \- H:i:s", $Seg[0]).'</td></tr>'.PHP_EOL;
    }    
  }
  echo '</table>'.PHP_EOL;
}

//No, display tutorial on how to set it up.
else {
  echo '<p>DHCP is not currently being handled by NoTrack.</p>'.PHP_EOL;
  echo '<p>In order to enable it, you need to edit Dnsmasq config file.<br>See this video tutorial: <a href="https://www.youtube.com/watch?v=a5dUJ0SlGP0">DHCP Server Setup with Dnsmasq</a></p><br>'.PHP_EOL;
  echo '<iframe width="640" height="360" src="https://www.youtube.com/embed/a5dUJ0SlGP0" frameborder="0" allowfullscreen></iframe>'.PHP_EOL;  
}
?>
</div></div>
</div>
</body>
</html>
