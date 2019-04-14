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
  <link href="./css/chart.css" rel="stylesheet" type="text/css">
  <link rel="icon" type="image/png" href="./favicon.png">
  <script src="./include/menu.js"></script>
  <script src="./include/queries.js"></script>
  <title>NoTrack - Investigate</title>
</head>

<body>
<?php
draw_topmenu('Investigate');
draw_sidemenu();
echo '<div id="main">'.PHP_EOL;

/************************************************
*Constants                                      *
************************************************/
DEFINE('DEF_FILTER', 'all');
DEFINE('DEF_SYSTEM', 'all');
DEFINE('DEF_SDATE', date("Y-m-d", time() - 172800));  //Start Date of Historic -2d
DEFINE('DEF_EDATE', date("Y-m-d", time() - 86400));   //End Date of Historic   -1d

$FILTERLIST = array('all' => 'All Requests',
                    'allowed' => 'Allowed Only',
                    'blocked' => 'Blocked Only',
                    'local' => 'Local Only');


/************************************************
*Global Variables                               *
************************************************/
$datetime = '';
$site = '';

$whois_date = '';
$whois_record = '';

/************************************************
*Arrays                                         *
************************************************/
$syslist = array();
$TLDBlockList = array();


/********************************************************************
 *  Create Who Is Table
 *    Run sql query to create whois table
 *    TODO Causes unknown error - Should be fixed now removing error check
 *  Params:
 *    None
 *  Return:
 *    None
 */
function create_whoistable() {
  global $db;
    
  $query = "CREATE TABLE whois (id BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, save_time DATETIME, site TINYTEXT, record MEDIUMTEXT)";
  
  $db->query($query);    
}


/********************************************************************
 *  Draw Search Bar
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function draw_searchbar() {
  global $site;
  
  echo '<div class="sys-group">'.PHP_EOL;
  echo '<form method="GET">'.PHP_EOL;
  echo '<input type="text" name="site" class="input-conf" placeholder="Search domain" value="'.$site.'">&nbsp;'.PHP_EOL;
  echo '<input type="submit" class="button-blue" value="Investigate">'.PHP_EOL;
  echo '</form>'.PHP_EOL;
  echo '</div>'.PHP_EOL;
}



/********************************************************************
 *  Get Block List Name
 *    Returns the name of block list if it exists in the names array
 *  Params:
 *    $bl - bl_name
 *  Return:
 *    Full block list name
 */

function get_blocklistname($bl) {
  global $BLOCKLISTNAMES;
  
  if (array_key_exists($bl, $BLOCKLISTNAMES)) {
    return $BLOCKLISTNAMES[$bl];
  }
  
  return $bl;
}
/********************************************************************
 *  Search Block Reason
 *    1. Search $site in bl_source for Blocklist name
 *    2. Use regex match to extract (site).(tld)
 *    3. Search site.tld in bl_source
 *    4. On fail search for .tld in bl_source
 *    5. On fail return ''
 *
 *  Params:
 *    $site - Site to search
 *  Return:
 *    blocklist name
 */
function search_blockreason($site) {
  global $db;
  
  $result = $db->query('SELECT bl_source site FROM blocklist WHERE site = \''.$site.'\'');
  if ($result->num_rows > 0) {
    return $result->fetch_row()[0];
  }
  
    
  //Try to find LIKE site ending with site.tld
  if (preg_match('/([\w\d\-\_]+)\.([\w\d\-\_]+)$/', $site,  $matches) > 0) {
    $result = $db->query('SELECT bl_source site FROM blocklist WHERE site LIKE \'%'.$matches[1].'.'.$matches[2].'\'');

    if ($result->num_rows > 0) {
      return $result->fetch_row()[0];
    }    
    else {                                      //On fail try for site = .tld
      $result = $db->query('SELECT bl_source site FROM blocklist WHERE site = \'.'.$matches[2].'\'');
      if ($result->num_rows > 0) {
        return $result->fetch_row()[0];
      }
    }
  }
  
  return '';                                     //Don't know at this point    
}

/********************************************************************
 *  Search Systems
 *  
 *  1. Find unique sys values in table
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function search_systems() {
  global $db, $mem, $syslist;
  
  $syslist = $mem->get('syslist');
  
  if (empty($syslist)) {
    if (! $result = $db->query("SELECT DISTINCT sys FROM live ORDER BY sys")) {
      die('search_systems(): There was an error running the query'.$db->error);
    }
    while($row = $result->fetch_assoc()) {                 //Read each row of results
      $syslist[] = $row['sys'];                            //Add row value to $syslist
    }
    $result->free();
    $mem->set('syslist', $syslist, 0, 600);                //Save for 10 Mins
  }    
}


/********************************************************************
 *  Show Time View
 *    Show results in Time order
 *
 *  Params:
 *    None
 *  Return:
 *    false when nothing found, true on success
 */
function show_time_view() {
  global $db, $datetime, $site, $sys, $Config, $TLDBlockList;
    
  $rows = 0;
  $row_class = '';
  $query = '';
  $action = '';
  $blockreason = '';
  
  $query = "SELECT *, DATE_FORMAT(log_time, '%H:%i:%s') AS formatted_time FROM live WHERE sys = '$sys' AND log_time > SUBTIME('$datetime', '00:00:05') AND log_time < ADDTIME('$datetime', '00:00:03') ORDER BY UNIX_TIMESTAMP(log_time)";
  
    
  if(!$result = $db->query($query)){
    die('There was an error running the query'.$db->error);
  }
  
  if ($result->num_rows == 0) {                  //Leave if nothing found
    $result->free();
    echo "Nothing found for the selected dates";
    return false;
  }
  
  echo '<div class="sys-group">'.PHP_EOL;
  //draw_viewbuttons();
  
  echo '<table id="query-time-table">'.PHP_EOL;
  echo '<tr><th>Time</th><th>System</th><th>Site</th><th>Action</th></tr>'.PHP_EOL;  
  
  while($row = $result->fetch_assoc()) {         //Read each row of results
    $action = '<a target="_blank" href="'.$Config['SearchUrl'].$row['dns_request'].'"><img class="icon" src="./images/search_icon.png" alt="G" title="Search"></a>&nbsp;<a target="_blank" href="'.$Config['WhoIsUrl'].$row['dns_request'].'"><img class="icon" src="./images/whois_icon.png" alt="W" title="Whois"></a>&nbsp;';
    if ($row['dns_result'] == 'A') {             //Allowed
      $row_class='';
      $action .= '<span class="pointer"><img src="./images/report_icon.png" alt="Rep" title="Report Site" onclick="reportSite(\''.$row['dns_request'].'\', false, true)"></span>';
    }
    elseif ($row['dns_result'] == 'B') {         //Blocked
      $row_class = ' class="blocked"';      
      $blockreason = search_blockreason($row['dns_request']);      
      if ($blockreason == 'bl_notrack') {        //Show Report icon on NoTrack list
        $action .= '<span class="pointer"><img src="./images/report_icon.png" alt="Rep" title="Report Site" onclick="reportSite(\''.$row['dns_request'].'\', true, true)"></span>';
        $blockreason = '<p class="small">Blocked by NoTrack list</p>';
      }
      elseif ($blockreason == 'custom') {        //Users blacklist, show report icon
        $action .= '<span class="pointer"><img src="./images/report_icon.png" alt="Rep" title="Report Site" onclick="reportSite(\''.$row['dns_request'].'\', true, true)"></span>';
        $blockreason = '<p class="small">Blocked by Black list</p>';
      }
      elseif ($blockreason == '') {              //No reason is probably IP or Search request
        $row_class = ' class="invalid"';
        $blockreason = '<p class="small">Invalid request</p>';
      }
      else {
        $blockreason = '<p class="small">Blocked by '.get_blocklistname($blockreason).'</p>';
        $action .= '<span class="pointer"><img src="./images/report_icon.png" alt="Rep" title="Report Site" onclick="reportSite(\''.$row['dns_request'].'\', true, false)"></span>';
      }    
    }
    elseif ($row['dns_result'] == 'L') {         //Local
      $row_class = ' class="local"';
      $action = '&nbsp;';
    }
    
    if ($site == $row['dns_request']) {
      $row_class = ' class="cyan"';
    }
    
    echo '<tr'.$row_class.'><td>'.$row['formatted_time'].'</td><td>'.$row['sys'].'</td><td>'.$row['dns_request'].$blockreason.'</td><td>'.$action.'</td></tr>'.PHP_EOL;
    $blockreason = '';
  }
  
  echo '</table>'.PHP_EOL;
  echo '<br>'.PHP_EOL;
  echo '</div>'.PHP_EOL;
  
  $result->free();
  return true;
}


/********************************************************************
 *  Get Who Is Data
 *    Downloads whois data from jsonwhois.com
 *    Checks cURL return value
 *    Save data to whois table
 *
 *  Params:
 *    URL to Query, Users API Key to jsonwhois
 *  Return:
 *    True on success
 *    False on lookup failed or other HTTP error
 */
function get_whoisdata($site, $apikey) {
  global $db, $whois_date, $whois_record;

  $headers[] = 'Accept: application/json';
  $headers[] = 'Content-Type: application/json';
  $headers[] = 'Authorization: Token token='.$apikey;
  $url = 'https://jsonwhois.com/api/v1/whois/?domain='.$site;
  $whois_date = date('Y-m-d H:i:s');

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $json_response = curl_exec($ch);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  
  if ($status == 400) {                                    //Bad request domain doesn't exist
    echo '<div class="sys-group"><div class="sys-title">'.PHP_EOL;
    echo '<h5>Domain Information</h5></div>'.PHP_EOL;
    echo '<div class="sys-items">'.PHP_EOL;
    echo $site.' does not exist'.PHP_EOL;
    echo '</div></div>'.PHP_EOL;
    curl_close($ch);
    return false;
  }
  
  if ($status >= 300) {                                    //Other HTTP Error
    echo "Error: call to URL $url failed with status $status, response $json_response";
    curl_close($ch);
    return false;
  }
  
  curl_close($ch);

  
  //Save whois record into whois table
  $cmd = "INSERT INTO whois (id, save_time, site, record) VALUES ('NULL', '$whois_date', '$site', '".$db->real_escape_string($json_response)."')";
  if ($db->query($cmd) === false) {
    echo 'get_whoisdata() Error adding data to whois table: '.$db->error;
  }
  
  $whois_record = json_decode($json_response, true);
  
  return true;  
}


/********************************************************************
 *  Search Who Is Record
 *    Attempts to find $site from whois table in order to prevent overuse of JsonWhois API
 *
 *  Params:
 *    URL to search
 *  Return:
 *    True on record found
 *    False if no record found
 */
function search_whois($site) {
  global $db, $whois_date, $whois_record;
  
  $query = "SELECT * FROM whois WHERE site = '$site'";
      
  if(!$result = $db->query($query)){
    die('search_whois() There was an error running the query: '.$db->error);
  }
  
  if ($result->num_rows == 0) {                            //Leave if nothing found
    $result->free();
    return false;
  }
    
  $row = $result->fetch_assoc();                           //Read one row of results
  
  $whois_date = $row['save_time'];
  $whois_record = json_decode($row['record'], true);

  $result->free();
  
  return true;
}
  
 
/********************************************************************
 *  Show Who Is Data
 *    Displays data from $whois_record
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function show_whoisdata() {
  global $whois_date, $whois_record;
  
  if ($whois_record == null) return null;                  //Any data in the array?
  
  //TODO give user a chance to reload data
  if (isset($whois_record['error'])) {
    echo '<div class="sys-group"><div class="sys-title">'.PHP_EOL;
    echo '<h5>Domain Information</h5></div>'.PHP_EOL;
    echo '<div class="sys-items">'.PHP_EOL;
    echo $whois_record['error'].PHP_EOL;
    echo '</div></div>'.PHP_EOL;
    return null;
  }
  
  draw_systable('Domain Information');
  draw_sysrow('Domain Name', $whois_record['domain']);
  draw_sysrow('Name', $whois_record['registrar']['name']);
  draw_sysrow('Status', ucfirst($whois_record['status']));
  draw_sysrow('Created On', substr($whois_record['created_on'], 0, 10));
  draw_sysrow('Updated On', substr($whois_record['updated_on'], 0, 10));
  draw_sysrow('Expires On', substr($whois_record['expires_on'], 0, 10));
  if (isset($whois_record['nameservers'][0])) draw_sysrow('Name Servers', $whois_record['nameservers']['0']['name']);
  if (isset($whois_record['nameservers'][1])) draw_sysrow('', $whois_record['nameservers']['1']['name']);
  if (isset($whois_record['nameservers'][2])) draw_sysrow('', $whois_record['nameservers']['2']['name']);
  if (isset($whois_record['nameservers'][3])) draw_sysrow('', $whois_record['nameservers']['3']['name']);
  draw_sysrow('Last Retrieved', $whois_date);
  echo '</table></div></div>'.PHP_EOL;
  
  if (isset($whois_record['registrant_contacts'][0])) {
    draw_systable('Registrant Contact');
    draw_sysrow('Name', $whois_record['registrant_contacts']['0']['name']);
    draw_sysrow('Organisation', $whois_record['registrant_contacts']['0']['organization']);
    draw_sysrow('Address', $whois_record['registrant_contacts']['0']['address']);
    draw_sysrow('City', $whois_record['registrant_contacts']['0']['city']);
    draw_sysrow('Postcode', $whois_record['registrant_contacts']['0']['zip']);
    if (isset($whois_record['registrant_contacts'][0]['state'])) draw_sysrow('State', $whois_record['registrant_contacts']['0']['state']);
    draw_sysrow('Country', $whois_record['registrant_contacts']['0']['country']);
    if (isset($whois_record['registrant_contacts'][0]['phone'])) draw_sysrow('Phone', $whois_record['registrant_contacts']['0']['phone']);
    if (isset($whois_record['registrant_contacts'][0]['fax'])) draw_sysrow('Fax', $whois_record['registrant_contacts']['0']['fax']);
    if (isset($whois_record['registrant_contacts'][0]['email'])) draw_sysrow('Email', strtolower($whois_record['registrant_contacts']['0']['email']));
    echo '</table></div></div>'.PHP_EOL;
  }
  
  //print_r($whois_record);
}

/********************************************************************
 *  Show Who Is Error when no API is set
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function show_whoiserror() {
  echo '<div class="sys-group"><div class="sys-title">'.PHP_EOL;
  echo '<h5>Domain Information</h5></div>'.PHP_EOL;
  echo '<div class="sys-items">'.PHP_EOL;
  echo '<p>Error: No WhoIs API key set. In order to use this feature you will need to add a valid JsonWhois API key to NoTrack config</p>'.PHP_EOL;
  echo '<p>Instructions:</p>'.PHP_EOL;
  echo '<ol>'.PHP_EOL;
  echo '<li>Sign up to <a href="https://jsonwhois.com/">JsonWhois</a></li>'.PHP_EOL;
  echo '<li> Add your API key to NoTrack <a href="./config.php?v=general">config</a></li>'.PHP_EOL;
  echo '</ol>'.PHP_EOL;
  echo '</div></div>'.PHP_EOL;
}

/********************************************************************
 *  Traffic Graph
 *    Live Table runs from 04:00 to 03:59
 *    1. Adjust values for today and tomorrow depending if time is (04:00 to 23:59) or (00:00 to 03:59)
 *    2. Create xlabels
 *    3. Load allowed 'a' results from live table for values per hour
 *    4. Load blocked 'b' results from live table for values per hour
 *    5. Send data to linechart()
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function trafficgraph() {
  global $site;
    
  $allowed_values = array();
  $blocked_values = array();
  $xlabels = array();
  
    
  if ((date('H') >= 0) && (date('H') < 4)) {               //Is 'today' yesterday in terms of log data?
    $today = date("Y-m-d", strtotime('yesterday'));
    $tomorrow = date('Y-m-d');    
  }
  else {                                                   //No, 'today' is today in terms of log data
    $today = date('Y-m-d');
    $tomorrow = date("Y-m-d", strtotime('+1 day'));
  }
  
  $xlabels[] = '04';                                       //Create xlabels
  $xlabels[] = '05';
  $xlabels[] = '06';
  $xlabels[] = '07';
  $xlabels[] = '08';
  $xlabels[] = '09';
  $xlabels[] = '10';
  $xlabels[] = '11';
  $xlabels[] = '12';
  $xlabels[] = '13';
  $xlabels[] = '14';
  $xlabels[] = '15';
  $xlabels[] = '16';
  $xlabels[] = '17';
  $xlabels[] = '18';
  $xlabels[] = '19';
  $xlabels[] = '20';
  $xlabels[] = '21';
  $xlabels[] = '22';
  $xlabels[] = '23';
  $xlabels[] = '00';
  $xlabels[] = '01';
  $xlabels[] = '02';
  $xlabels[] = '03';
  
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 04:00:00' AND log_time < '$today 05:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 05:00:00' AND log_time < '$today 06:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 06:00:00' AND log_time < '$today 07:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 07:00:00' AND log_time < '$today 08:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 08:00:00' AND log_time < '$today 09:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 09:00:00' AND log_time < '$today 10:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 10:00:00' AND log_time < '$today 11:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 11:00:00' AND log_time < '$today 12:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 12:00:00' AND log_time < '$today 13:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 13:00:00' AND log_time < '$today 14:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 14:00:00' AND log_time < '$today 15:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 15:00:00' AND log_time < '$today 16:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 16:00:00' AND log_time < '$today 17:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 17:00:00' AND log_time < '$today 18:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 18:00:00' AND log_time < '$today 19:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 19:00:00' AND log_time < '$today 20:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 20:00:00' AND log_time < '$today 21:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 21:00:00' AND log_time < '$today 22:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 22:00:00' AND log_time < '$today 23:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$today 23:00:00' AND log_time < '$tomorrow 00:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$tomorrow 00:00:00' AND log_time < '$tomorrow 01:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$tomorrow 01:00:00' AND log_time < '$tomorrow 02:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$tomorrow 02:00:00' AND log_time < '$tomorrow 03:00:00'");
  $allowed_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'a' AND log_time >= '$tomorrow 03:00:00' AND log_time < '$tomorrow 04:00:00'");
  
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 04:00:00' AND log_time < '$today 05:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 05:00:00' AND log_time < '$today 06:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 06:00:00' AND log_time < '$today 07:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 07:00:00' AND log_time < '$today 08:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 08:00:00' AND log_time < '$today 09:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 09:00:00' AND log_time < '$today 10:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 10:00:00' AND log_time < '$today 11:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 11:00:00' AND log_time < '$today 12:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 12:00:00' AND log_time < '$today 13:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 13:00:00' AND log_time < '$today 14:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 14:00:00' AND log_time < '$today 15:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 15:00:00' AND log_time < '$today 16:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 16:00:00' AND log_time < '$today 17:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 17:00:00' AND log_time < '$today 18:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 18:00:00' AND log_time < '$today 19:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 19:00:00' AND log_time < '$today 20:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 20:00:00' AND log_time < '$today 21:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 21:00:00' AND log_time < '$today 22:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 22:00:00' AND log_time < '$today 23:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$today 23:00:00' AND log_time < '$tomorrow 00:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$tomorrow 00:00:00' AND log_time < '$tomorrow 01:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$tomorrow 01:00:00' AND log_time < '$tomorrow 02:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$tomorrow 02:00:00' AND log_time < '$tomorrow 03:00:00'");
  $blocked_values[] = count_rows("SELECT COUNT(*) FROM live WHERE dns_request LIKE '%$site' AND dns_result = 'b' AND log_time >= '$tomorrow 03:00:00' AND log_time < '$tomorrow 04:00:00'");
  
    
  /*print_r($allowed_values);                              //For debugging
  echo '<br>';
  print_r($blocked_values);*/
  echo '<div class="home-nav-container">'.PHP_EOL;
  linechart($allowed_values, $blocked_values, $xlabels);   //Draw the line chart
  echo '</div>'.PHP_EOL;
}  


/********************************************************************
 *Main
 */

$db = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);  //Open MariaDB connection

search_systems();                                          //Need to find out systems are on live table

if (isset($_GET['sys'])) {                                 //Any system set?
  if (in_array($_GET['sys'], $syslist)) $sys = $_GET['sys'];
}

if (isset($_GET['datetime'])) {                            //Filter for hh:mm:ss
  if (preg_match(REGEX_TIME, $_GET['datetime']) > 0) {
    $datetime = date('Y-m-d ').$_GET['datetime'];
  }
}

if (isset($_GET['site'])) {
  if (filter_url($_GET['site'])) {
    $site = $_GET['site'];
  }
}

if (!table_exists('whois')) {                              //Does whois sql table exist?
  create_whoistable();                                     //If not then create it
  sleep(2);                                                //Delay to wait for MariaDB to create the table
}

if ($Config['whoisapi'] == '') {                           //Has user set an API key?              
  show_whoiserror();                                       //No - Don't go any further
  $db->close();
  exit;
}

draw_searchbar();

if ($datetime != '') show_time_view();                     //Show time view if datetime in parameters


if ($site != '') {                                         //Load whois data?
  $site = extract_domain($site);                           //Can only search for TLD
  if (! search_whois($site)) {                             //Attempt to search whois table
    get_whoisdata($site, $Config['whoisapi']);             //No record found - download it from JsonWhois
  }
  show_whoisdata();                                        //Display data from table / JsonWhois
  
  trafficgraph();                                          //Draw traffic graph
}

$db->close();

?>

</div>

<div id="scrollup" class="button-scroll" onclick="ScrollToTop()"><img src="./svg/arrow-up.svg" alt="up"></div>
<div id="scrolldown" class="button-scroll" onclick="ScrollToBottom()"><img src="./svg/arrow-down.svg" alt="down"></div>

<div id="stats-box">
<div class="dialog-bar">Report</div>
<span id="sitename">site</span>
<span id="statsmsg">something</span>
<span id="statsblock1"><a class="button-blue" href="#">Block Whole</a> Block whole domain</span>
<span id="statsblock2"><a class="button-blue" href="#">Block Sub</a> Block just the subdomain</span>
<form name="reportform" action="https://quidsup.net/notrack/report.php" method="post" target="_blank">
<input type="hidden" name="site" id="siterep" value="none">
<span id="statsreport"><input type="submit" class="button-blue" value="Report">&nbsp;<input type="text" name="comment" class="textbox-small" placeholder="Optional comment"></span>
</form>

<br>
<div class="centered"><h6 class="button-grey" onclick="HideStatsBox()">Cancel</h6></div>
<div class="close-button" onclick="HideStatsBox()"><img src="./svg/button_close.svg" onmouseover="this.src='./svg/button_close_over.svg'" onmouseout="this.src='./svg/button_close.svg'" alt="close"></div>
</div>

</body>
</html>
