<?php
/********************************************************************
config.php handles setting of Global variables, GET, and POST requests
It also houses the functions for POST requests.

All other config functions are in ./include/config-functions.php

********************************************************************/

require('./include/global-vars.php');
require('./include/global-functions.php');
require('./include/menu.php');
require('./include/config-functions.php');

load_config();
ensure_active_session();

/************************************************
*Constants                                      *
************************************************/


/************************************************
*Global Variables                               *
************************************************/
$page = 1;
$searchbox = '';
$showblradio = false;
$blradio = 'all';
$db = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);

/************************************************
*Arrays                                         *
************************************************/
$DHCPConfig = array();
$list = array();                                 //Global array for all the Block Lists

/************************************************
*POST REQUESTS                                  *
************************************************/
//Deal with POST actions first, that way we can reload the page and remove POST requests from browser history.
if (isset($_POST['action'])) {
  switch($_POST['action']) {
    case 'advanced':
      if (update_advanced()) {                   //Are users settings valid?
        save_config();                           //If ok, then save the Config file        
        sleep(1);                                //Short pause to prevent race condition
        exec(NTRK_EXEC.'--parsing');             //Update ParsingTime value in Cron job
      }      
      header('Location: ?v=advanced');           //Reload page
      break;
    case 'blocklists':
      update_blocklist_config();
      save_config();
      exec(NTRK_EXEC.'--run-notrack');
      $mem->delete('SiteList');                  //Delete Site Blocked from Memcache
      sleep(1);                                  //Short pause to prevent race condition
      header('Location: ?v=blocks');             //Reload page
      break;
    case 'dhcp':
      update_dhcp();
      header('Location: ?v=dhcp');               //Reload to DHCP
      break;
    case 'webserver':
      update_webserver_config();
      save_config();
      header('Location: ?');
      break;
    case 'stats':
      if (update_stats_config()) {
        save_config();
        sleep(1);                                //Short pause to prevent race condition
        header('Location: ?v=general');
      }
      break;
    case 'tld':
      load_csv(TLD_FILE, 'CSVTld');              //Load tld.csv
      update_domain_list();      
      sleep(1);                                  //Prevent race condition
      header('Location: ?v=tld');                //Reload page
      break;
    default:
      die('Unknown POST action');
  }
}
//-------------------------------------------------------------------
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <link href="./css/master.css" rel="stylesheet" type="text/css">
  <link rel="icon" type="image/png" href="./favicon.png">
  <script src="./include/config.js"></script>
  <script src="./include/menu.js"></script>
  <title>NoTrack - Config</title>  
</head>

<body>
<?php
draw_topmenu('Config');
draw_sidemenu();
echo '<div id="main">';


/********************************************************************
 *  Update Advanced Config
 *    1. Make sure Suppress list is valid
 *    1a. Replace new line and space with commas
 *    1b. If string too short, set to '' then leave
 *    1c. Copy Valid URL's to a ValidList array
 *    1d. Write valid URL's to Config Suppress string seperated by commas 
 *  Params:
 *    None
 *  Return:
 *    None
 */
function update_advanced() {
  
  global $Config;
  
  $suppress = '';
  $suppresslist = array();
  $validlist = array();
  
  if (isset($_POST['parsing'])) {
    $Config['ParsingTime'] = filter_integer($_POST['parsing'], 1, 60, 7);
  }
  
  if (isset($_POST['suppress'])) {
    $suppress = preg_replace('#\s+#',',',trim($_POST['suppress'])); //Split array
    if (strlen($suppress) <= 2) {                //Is string too short?
      $Config['Suppress'] = '';
      return true;
    }
    
    $suppresslist = explode(',', $suppress);     //Split string into array
    foreach ($suppresslist as $site) {           //Check if each item is a valid URL
      if (filter_url($site)) {
        $validlist[] = strip_tags($site);
      }
    }
    if (sizeof($validlist) == 0) $Config['Suppress'] = '';
    else $Config['Suppress'] = implode(',', $validlist);
  }
  
  return true;
}


/********************************************************************
 *  Update Block List Config
 *    1: Search through Config array for bl_? (excluding bl_custom)
 *    2: Check if bl_? appears in POST[bl_?]
 *    3: Set bl_custom by splitting and filtering values from POST[bl_custom]
 *    4: After this function save_config is run
 *  Params:
 *    None
 *  Return:
 *    None
 */
function update_blocklist_config() {  
  global $Config;
  $customstr = '';
  $customlist = array();
  $validlist = array();
  $key = '';
  $value = '';
  
  foreach($Config as $key => $value) {           //Read entire Config array
    if (preg_match('/^bl\_(?!custom)/', $key) > 0) { //Look for values starting bl_
      if (isset($_POST[$key])) {                 //Is there an equivilent POST value?
        if ($_POST[$key] == 'on') {              //Is it set to on (ticked)?
          $Config[$key] = 1;                     //Yes - enable block list
        }
      }
      else {                                     //No POST value
        $Config[$key] = 0;                       //Block list is unticked
      }
    }
  }
  
  if (isset($_POST['bl_custom'])) {              //bl_custom requires extra processing
    $customstr = preg_replace('#\s+#',',',trim($_POST['bl_custom'])); //Split array
    $customlist = explode(',', $customstr);      //Split string into array
    foreach ($customlist as $site) {             //Check if each item is a valid URL
      if (filter_url($site)) {
        $validlist[] = strip_tags($site);
      }
    }
    if (sizeof($validlist) == 0) $Config['bl_custom'] = '';
    else $Config['bl_custom'] = implode(',', $validlist);
  }
  else {
    $Config['bl_custom'] = "";
  }
    
  return null;
}


/********************************************************************
 *  Update Custom List
 *    Works for either BlackList or WhiteList
 *    1. Appropriate list should have already have been loaded into $list Array
 *    2. Find out what value has been requested on GET &do=
 *    2a. Add Site using site name, and comment to end of $list array
 *    2b. Change whether Site is enabled or disabled using Row number of $list array
 *    2c. Delete Site from $list array by using Row number
 *    2d. Change whether Site is enabled or disabled using name
 *    2e. Error and leave function if any other value is given
 *    3. Open File for writing in /tmp/"listname".txt
 *    4. Write $list array to File
 *    5. Delete $list array from Memcache
 *    6. Write $list array with changes from #2 to Memcache
 *    7. Run ntrk-exec as Forked process
 *    8. Onward process is to show_custom_list function
 *
 *  Params:
 *    Actual Name, List name
 *  Return:
 *    True when action carried out
 */
function update_custom_list($actualname, $listname) {
  global $list, $mem;
  $row = 0;
  
  if (isset($_GET['do'])) {
    switch ($_GET['do']) {
      case 'add':                                //Add Site
        if ((isset($_GET['site'])) && (isset($_GET['comment']))) {
          if (filter_url($_GET['site'])) {
            $list[] = array($_GET['site'], strip_tags($_GET['comment']), true);
          }
        }
        break;
      case 'cng':
        if ((isset($_GET['row'])) && (isset($_GET['status']))) {
          $row = filter_integer($_GET['row'], 1, count($list)+1);
          if ($row > 0) {                        //Is integer valid?
            $row--;                              //Compensate table value to array position
            $list[$row][2] = filter_bool($_GET['status']);
          }
        }      
        break;
      case 'del':
        if (isset($_GET['row'])) {
          $row = filter_integer($_GET['row'], 1, count($list)+1);
          if ($row > 0) {                        //Is integer valid?
            array_splice($list, $row-1, 1);      //Remove one line from List array
          }
        }             
       break;
      case 'update':
        echo '<pre>Updating Custom blocklists in background</pre>'.PHP_EOL;
        exec(NTRK_EXEC.'--run-notrack');        
        return true;
        break;    
      default:
        echo 'Invalid request in update_custom_list()'.PHP_EOL;
        return false;
    }
  }
  else {
    echo 'No request specified in update_custom_list()'.PHP_EOL;
    return false;
  }
  
  //Open file /tmp/listname.txt for writing
  $fh = fopen(DIR_TMP.strtolower($actualname).'.txt', 'w') or die('Unable to open '.DIR_TMP.$actualname.'.txt for writing');
  
  //Write Usage Instructions to top of File
  fwrite($fh, "#Use this file to create your own custom ".$actualname.PHP_EOL);
  fwrite($fh, '#Run notrack script (sudo notrack) after you make any changes to this file'.PHP_EOL);
  
  foreach ($list as $line) {                     //Write list array to temp
    if ($line[2] == true) {                      //Is site enabled?
      fwrite($fh, $line[0].' #'.$line[1].PHP_EOL);
    }
    else {                                       //Site disabled, comment it out by preceding Line with #
      fwrite($fh, '# '.$line[0].' #'.$line[1].PHP_EOL);
    }    
  }
  fclose($fh);                                   //Close file
  
  $mem->delete($listname);
  $mem->set($listname, $list, 0, 60);
  
  exec(NTRK_EXEC.'--copy '.$listname);
  
  return true;
}


/********************************************************************
 *  Update Stats Config
 *
 *  Params:
 *    None
 *  Return:
 *    True if change has been made or False if nothing changed
 */
function update_stats_config() {
  global $Config, $SEARCHENGINELIST, $WHOISLIST;
  
  $updated = false;
  print_r($_POST);
  if (isset($_POST['search'])) {
    if (array_key_exists($_POST['search'], $SEARCHENGINELIST)) {      
      $Config['Search'] = $_POST['search'];
      $Config['SearchUrl'] = $SEARCHENGINELIST[$Config['Search']];
      $updated = true;
    }
  }
  
  if (isset($_POST['whois'])) {    
    if (array_key_exists($_POST['whois'], $WHOISLIST)) {
      $Config['WhoIs'] = $_POST['whois'];
      $Config['WhoIsUrl'] = $WHOISLIST[$Config['WhoIs']];
      $updated = true;
    }
  }
  
  if (isset($_POST['whoisapi'])) {                         //Validate whoisapi
    if (strlen($_POST['whoisapi']) < 50) {                 //Limit input length
      if (ctype_xdigit($_POST['whoisapi'])) {              //Is input hexadecimal?
        $Config['whoisapi'] = $_POST['whoisapi'];
        $updated = true;
      }
      else {
        $Config['whoisapi'] = '';
      }
    }
  }  
  
  return $updated;
}


/********************************************************************
 *  Add Config Record
 *    Add new record to config table
 *  Params:
 *    type, name, value, enabled
 *  Return:
 *    None
 */
function add_config_record($config_type, $option_name, $option_value, $option_enabled) {
  global $db;
  
  $query = "INSERT INTO config (config_id, config_type, option_name, option_value, option_enabled) VALUES(null, '$config_type', '$option_name', '$option_value', '$option_enabled')";
    
  if (! $db->query($query)) {
    die('add_config_record Error: '.$db->error);
  }
  
  return null;
}

/********************************************************************
 *  Delete Config Record
 *    Delete records from config table
 *  Params:
 *    type, name
 *  Return:
 *    None
 */
function delete_config_record($config_type, $option_name) {
  global $db;
  
  $query = "DELETE FROM config WHERE config_type = '$config_type' AND option_name = '$option_name'";
    
  if (! $db->query($query)) {
    die('delete_config_record Error: '.$db->error);
  }
    
  return null;
}


/********************************************************************
 *  Update Config Record
 *    1: Search for the ID of option_name
 *    2: If record can't be found then set query to add value
 *  Params:
 *    type, name, value, enabled
 *  Return:
 *    None
 */
function update_config_record($config_type, $option_name, $option_value, $option_enabled) {
  global $db;
  
  $config_id = 0;
  $query = '';
  
  $result = $db->query("SELECT * FROM config WHERE config_type = '$config_type' AND option_name = '$option_name'");
  
  if ($result->num_rows > 0) {                       #Has anything been found?
    $config_id = $result->fetch_object()->config_id; #Get the ID number
  }
  
  if ($config_id > 0) {                          #ID > 0 means an existing record was found
    $query = "UPDATE config SET option_value = '$option_value', option_enabled = '$option_enabled' WHERE config_id = '$config_id'";
  }
  else {                                         #Nothing found, add new record
    $query = "INSERT INTO config (config_id, config_type, option_name, option_value, option_enabled) VALUES(null, '$config_type', '$option_name', '$option_value', '$option_enabled')";
  }
  
  if (! $db->query($query)) {
    die('update_config_record Error: '.$db->error);
  }
  
  $result->free();
  return null;
}
/********************************************************************
 *  Update DHCP
 *    dhcp-enabled, and dhcp-authoritative are tick boxes
 *    router_ip, start_ip, end_ip are all IP addresses, use filter_var to validate
 *    Its not easy to update the dhcp-host's, so we delete them and then re-add
 *
 *  Params:
 *    None
 *  Return:
 *    None
 *  Regex:
 *    Group 1: anthing up to first comma ,
 *    Group 2: MAC Address
 *    Group 3: IPv4 or IPv6 address
 */
function update_dhcp() {
  global $db;
  
  
  $hosts = array();
  $matches = array();
  $host = '';
  
  update_config_record('dhcp', 'dhcp_enabled', '', isset($_POST['enabled']));
  update_config_record('dhcp', 'dhcp-authoritative', '', isset($_POST['authoritative']));
  
  if (isset($_POST['router_ip'])) {
    if (filter_var($_POST['router_ip'], FILTER_VALIDATE_IP) !== false) {
      update_config_record('dhcp', 'router_ip', $_POST['router_ip'], true);
    }    
  }
  if (isset($_POST['start_ip'])) {
    if (filter_var($_POST['start_ip'], FILTER_VALIDATE_IP) !== false) {
      update_config_record('dhcp', 'start_ip', $_POST['start_ip'], true);
    }    
  }
  if (isset($_POST['end_ip'])) {
    if (filter_var($_POST['end_ip'], FILTER_VALIDATE_IP) !== false) {
      update_config_record('dhcp', 'end_ip', $_POST['end_ip'], true);
    }    
  }
  
  delete_config_record('dhcp', 'dhcp-host');
  if (isset($_POST['static'])) {                 //Need to split textbox into seperate lines
    $hosts = explode(PHP_EOL, strip_tags($_POST['static'])); #Prevent XSS
    
    foreach($hosts as $host) {                   //Read each line
      //Check for Name,MAC,IP or MAC,IP
      //Add record if it is valid
      if (preg_match('/^([^,]+),([a-f\d]{2}:[a-f\d]{2}:[a-f\d]{2}:[a-f\d]{2}:[a-f\d]{2}:[a-f\d]{2}),([a-f\d:\.]+)/', $host, $matches) > 0) {
        add_config_record('dhcp', 'dhcp-host', $matches[0], true);
      }
      elseif (preg_match('/([a-f\d]{2}:[a-f\d]{2}:[a-f\d]{2}:[a-f\d]{2}:[a-f\d]{2}:[a-f\d]{2}),([a-f\d:\.]+)/', $host, $matches) > 0) {
        add_config_record('dhcp', 'dhcp-host', $matches[0], true);
      }
    }
  }
  
  return null;
}
/********************************************************************
 *  Update Domian List
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function update_domain_list() {
  global $list, $mem;
  
  //Start with White List
  $fh = fopen(DIR_TMP.'domain-whitelist.txt', 'w') or die('Unable to open '.DIR_TMP.'domain-whitelist.txt for writing');
  
  fwrite($fh, '#Domain White list generated by config.php'.PHP_EOL);
  
  foreach ($list as $site) {                     //Generate White list based on unticked Risk 1 items
    if ($site[2] == 1) {
      if (! isset($_POST[substr($site[0], 1)])) { //Check POST for domain minus preceding .
        fwrite($fh, $site[0].PHP_EOL);           //Add domain to White list
      }
    }
  }
  fclose($fh);                                   //Close White List
  
  //Write Black List
  $fh = fopen(DIR_TMP.'domain-blacklist.txt', 'w') or die('Unable to open '.DIR_TMP.'domain-blacklist.txt for writing');
    
  fwrite($fh, '#Domain Block list generated by config.php'.PHP_EOL);
  fwrite($fh, '#Do not make any changes to this file'.PHP_EOL);
  
  foreach ($_POST as $Key => $Value) {           //Generate Black list based on ticked items in $_POST
    if ($Value == 'on') fwrite($fh, '.'.$Key.PHP_EOL); //Add each item of POST of value is "on"
  }
  fclose($fh);                                   //Close Black List
  
  exec(NTRK_EXEC.'--copy tld');
      
  $mem->delete('TLDBlackList');                  //Delete Black List from Memcache
  $mem->delete('TLDWhiteList');                  //Delete White List from Memcache
  
  return null;
}


/********************************************************************
 *  Update Webserver Config
 *    Run ntrk-exec with appropriate change to Webserver setting
 *    Onward process is save_config function
 *  Params:
 *    None
 *  Return:
 *    None
 */
function update_webserver_config() {
  global $Config;  
  
  if (isset($_POST['block'])) {
    switch ($_POST['block']) {
      case 'message':
        $Config['BlockMessage'] = 'message';
        exec(NTRK_EXEC.'--bm-msg');
        break;
      case 'pixel':
        $Config['BlockMessage'] = 'pixel';
        exec(NTRK_EXEC.'--bm-pxl');
        break;      
    }
  }
}

//Main---------------------------------------------------------------

/************************************************
*GET REQUESTS                                   *
************************************************/
if (isset($_GET['s'])) {                         //Search box
  //Allow only characters a-z A-Z 0-9 ( ) . _ - and \whitespace
  $searchbox = preg_replace('/[^a-zA-Z0-9\(\)\.\s\_\-]/', '', $_GET['s']);
  $searchbox = strtolower($searchbox);  
}

if (isset($_GET['page'])) {
  $page = filter_integer($_GET['page'], 1, PHP_INT_MAX, 1);
}

if (isset($_POST['showblradio'])) {
  if ($_POST['showblradio'] == 1) {
    $showblradio = true;
  }
}

if (isset($_GET['blrad'])) {
  if ($_GET['blrad'] == 'all') {
    $blradio = 'all';
    $showblradio = true;
  }
  elseif (array_key_exists($_GET['blrad'], $BLOCKLISTNAMES)) {
    $blradio = $_GET['blrad'];
    $showblradio = true;
  }
}

if (isset($_GET['action'])) {
  switch($_GET['action']) {
    case 'delete-history':
      exec(NTRK_EXEC.'--delete-history');
      show_general();
      break;
    case 'black':
      load_customlist('black', $FileBlackList);
      update_custom_list('BlackList', 'black');      
      break;
    case 'white':
      load_customlist('white', $FileWhiteList);
      update_custom_list('WhiteList', 'white');
      break;    
  }
}

if (isset($_GET['v'])) {                         //What view to show?
  switch($_GET['v']) {
    case 'config':
      show_general();
      break;
    case 'blocks':
      show_blocklists();
      break;
    case 'black':
      load_customlist('black', $FileBlackList);
      show_custom_list('black');
      break;
    case 'white':
      load_customlist('white', $FileWhiteList);
      show_custom_list('white');
      break;
    case 'dhcp':
      load_dhcp();
      show_dhcp();
      break;
    case 'full':      
      show_full_blocklist();
      break;
    case 'advanced':
      show_advanced();
      break;
    case 'status':
      show_status();
      break;
    case 'tld':
      load_csv(TLD_FILE, 'csv_tld');
      show_domain_list();     
      break;
    default:
      show_general();
      break;
  }
}
else {                                           //No View set
  show_menu();
}

$db->close();
?> 
</div>
</body>
</html>
