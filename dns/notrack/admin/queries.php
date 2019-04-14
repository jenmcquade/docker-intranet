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
  <script src="./include/queries.js"></script>
  <title>NoTrack - DNS Queries</title>
</head>

<body>
<?php
draw_topmenu('DNS Queries');
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

$VIEWLIST = array('livegroup', 'livetime', 'historicgroup', 'historictime');

$COMMONSITESLIST = array('cloudfront.net',
                         'googleusercontent.com',
                         'googlevideo.com',
                         'cedexis-radar.net',
                         'gvt1.com',
                         'deviantart.net',
                         'deviantart.com',
                         'ampproject.net',
                         'steamcontent.com',
                         'userstorage.mega.co.nz',
                         'tumblr.com');
//CommonSites referres to websites that have a lot of subdomains which aren't necessarily relivent. In order to improve user experience we'll replace the subdomain of these sites with "*"

/************************************************
*Global Variables                               *
************************************************/
$page = 1;
$filter = DEF_FILTER;
$view = 'livegroup';
$sort = 'DESC';
$sys = DEF_SYSTEM;

$datestart = DEF_SDATE;
$dateend = DEF_EDATE;
$sqltable = 'live';

/************************************************
*Arrays                                         *
************************************************/
$syslist = array();
$TLDBlockList = array();
$CommonSites = array();                          //Merge Common sites list with Users Suppress list


/********************************************************************
 *  Add Date Vars to SQL Search
 *
 *  Params:
 *    None
 *  Return:
 *    SQL Query string
 */
function add_datestr() {
  global $sqltable, $filter, $sys, $datestart, $dateend;
  
  if ($sqltable == 'live') return '';
  
  $searchstr = ' WHERE ';
  if (($filter != DEF_FILTER) || ($sys != DEF_SYSTEM)) $searchstr = ' AND ';
  
  $searchstr .= 'log_time BETWEEN \''.$datestart.'\' AND \''.$dateend.' 23:59\'';
  
  return $searchstr;
}


/********************************************************************
 *  Add Filter Vars to SQL Search
 *
 *  Params:
 *    None
 *  Return:
 *    SQL Query string
 */
function add_filterstr() {
  global $filter, $sys;
  
  $searchstr = " WHERE ";
  
  if (($filter == DEF_FILTER) && ($sys == DEF_SYSTEM)) {   //Nothing to add
    return '';
  }
  
  if ($sys != DEF_SYSTEM) {
    $searchstr .= "sys = '$sys'";
  }
  if ($filter != DEF_FILTER) {
    if ($sys != DEF_SYSTEM) {
      $searchstr .= " AND dns_result=";
    }    
    else {
      $searchstr .= " dns_result=";
    }    
    
    switch($filter) {
      case 'allowed':
        $searchstr .= "'a'";
        break;
      case 'blocked':
        $searchstr .= "'b'";
        break;
      case 'local':
        $searchstr .= "'l'";
        break;
    }
  }
  return $searchstr;        
}


/********************************************************************
 *  Count rows in table and save result to memcache
 *  
 *  1. Attempt to load value from Memcache
 *  2. Check if same query is being run
 *  3. If that fails then run query
 *
 *  Params:
 *    Query String
 *  Return:
 *    Number of Rows
 */
function count_rows_save($query) {
  global $db, $mem;
  
  $rows = 0;
  
  if ($mem->get('rows')) {                       //Does rows exist in memcache?
    if ($query == $mem->get('oldquery')) {       //Is this query same as old query?
      $rows = $mem->get('rows');                 //Use stored value      
      return $rows;
    }
  }
  
  if(!$result = $db->query($query)){
    die('There was an error running the query '.$db->error);
  }
  
  $rows = $result->fetch_row()[0];               //Extract value from array
  $result->free();    
  $mem->set('oldquery', $query, 0, 600);         //Save for 10 Mins
      
  return $rows;
}


/********************************************************************
 *  Draw Filter Box
 *  
 *  Params:
 *    None
 *  Return:
 *    None
 */
function draw_filterbox() {
  global $FILTERLIST, $syslist, $filter, $page, $sqltable, $sort, $sys, $view;
  global $datestart, $dateend;
  
  $hidden_date_vars = '';
  $line = '';
  
  if ($sqltable == 'historic') {
    $hidden_date_vars = '<input type="hidden" name="datestart" value="'.$datestart.'"><input type="hidden" name="dateend" value="'.$dateend.'">'.PHP_EOL;
  }
  
  echo '<div class="sys-group">'.PHP_EOL;
  echo '<h5>DNS Queries</h5>'.PHP_EOL;
  echo '<div class="row"><div class="col-half">'.PHP_EOL;
  echo '<form method="get">'.PHP_EOL;
  echo '<input type="hidden" name="page" value="'.$page.'">'.PHP_EOL;
  echo '<input type="hidden" name="view" value="'.$view.'">'.PHP_EOL;
  echo '<input type="hidden" name="filter" value="'.$filter.'">'.PHP_EOL;
  echo '<input type="hidden" name="sort" value="'.strtolower($sort).'">'.PHP_EOL;
  echo $hidden_date_vars;
  echo '<span class="filter">System:</span><select name="sys" onchange="submit()">';
    
  if ($sys == DEF_SYSTEM) {
    echo '<option value="all">All</option>'.PHP_EOL;
  }
  else {
    echo '<option value="1">'.$sys.'</option>'.PHP_EOL;
    echo '<option value="all">All</option>'.PHP_EOL;
  }
  foreach ($syslist as $line) {
    if ($line != $sys) echo '<option value="'.$line.'">'.$line.'</option>'.PHP_EOL;
  }
  echo '</select></form>'.PHP_EOL;
  echo '</div>'.PHP_EOL;
  
  echo '<div class="col-half">'.PHP_EOL;
  echo '<form method="get">'.PHP_EOL;
  echo '<input type="hidden" name="page" value="'.$page.'">'.PHP_EOL;
  echo '<input type="hidden" name="view" value="'.$view.'">'.PHP_EOL;
  echo '<input type="hidden" name="sort" value="'.strtolower($sort).'">'.PHP_EOL;
  echo '<input type="hidden" name="sys" value="'.$sys.'">'.PHP_EOL;
  echo $hidden_date_vars;
  echo '<span class="filter">Filter:</span><select name="filter" onchange="submit()">';
  echo '<option value="'.$filter.'">'.$FILTERLIST[$filter].'</option>'.PHP_EOL;
  foreach ($FILTERLIST as $key => $line) {
    if ($key != $filter) echo '<option value="'.$key.'">'.$line.'</option>'.PHP_EOL;
  }
  echo '</select></form>'.PHP_EOL;
  echo '</div></div>'.PHP_EOL;
  
  if ($sqltable == 'historic') {
    echo '<div class="row">'.PHP_EOL;
    echo '<form method="get">'.PHP_EOL;
    echo '<input type="hidden" name="page" value="'.$page.'">'.PHP_EOL;
    echo '<input type="hidden" name="view" value="'.$view.'">'.PHP_EOL;
    echo '<input type="hidden" name="sort" value="'.strtolower($sort).'">'.PHP_EOL;
    echo '<input type="hidden" name="filter" value="'.$filter.'">'.PHP_EOL;
    echo '<input type="hidden" name="sys" value="'.$sys.'">'.PHP_EOL;
    echo '<div class="col-half">'.PHP_EOL;
    echo '<span class="filter">Start Date: </span><input name="datestart" type="date" value="'.$datestart.'" onchange="submit()"/>'.PHP_EOL;
    echo '</div>'.PHP_EOL;
    echo '<div class="col-half">'.PHP_EOL;
    echo '<span class="filter">End Date: </span><input name="dateend" type="date" value="'.$dateend.'" onchange="submit()"/>'.PHP_EOL;
    echo '</div>'.PHP_EOL;
    echo '</form>'.PHP_EOL;
    echo '</div>'.PHP_EOL;
  }
  
  echo '</div>'.PHP_EOL;
}


/********************************************************************
 *  Draw View Buttons
 *    [Today][Historic][Group][Time]
 *  Params:
 *    None
 *  Return:
 *    None
 */
function draw_viewbuttons() {
  global $sqltable, $view;
    
  echo '<div class="pag-nav float-right"><ul>'.PHP_EOL;
  if ($sqltable == 'live') {
    echo '<li class="active"><a class="pag-wide" href="?view=livegroup">Today</a></li>'.PHP_EOL;
    echo '<li><a class="pag-wide" href="?view=historicgroup">Historic</a></li>'.PHP_EOL;
  }
  else {
    echo '<li><a class="pag-wide" href="?view=livegroup">Today</a></li>'.PHP_EOL;
    echo '<li class="active"><a class="pag-wide" href="?view=historicgroup">Historic</a></li>'.PHP_EOL;
  }  
  if (($view == 'livetime') || ($view == 'historictime')) {
    echo '<li><a class="pag-wide" href="?view='.$sqltable.'group">Group</a></li>'.PHP_EOL;
    echo '<li class="active"><a class="pag-wide" href="?view='.$sqltable.'time">Time</a></li>'.PHP_EOL;    
  }
  elseif (($view == 'livegroup') || ($view == 'historicgroup')) {
    echo '<li class="active pag-wide"><a class="pag-wide" href="?view='.$sqltable.'group">Group</a></li>'.PHP_EOL;
    echo '<li><a class="pag-wide" href="?view='.$sqltable.'time">Time</a></li>'.PHP_EOL;    
  }
  echo '</ul></div>'.PHP_EOL; 
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

//Need to ammend for historic view TODO
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
      die('There was an error running the query'.$db->error);
    }
    while($row = $result->fetch_assoc()) {       //Read each row of results
      $syslist[] = $row['sys'];                  //Add row value to $syslist
    }
    $result->free();
    $mem->set('syslist', $syslist, 0, 600);      //Save for 10 Mins
  }    
}


/********************************************************************
 *  Show Group View
 *    Show results from either Live or Historic table in Group order
 *
 *  Params:
 *    None
 *  Return:
 *    false when nothing found, true on success
 */
function show_group_view() {
  global $db, $sqltable, $page, $sort, $filter, $sys, $view, $Config, $TLDBlockList;
  global $datestart, $dateend;
  
  $i = (($page - 1) * ROWSPERPAGE) + 1;
  $rows = 0;
  $row_class = '';
  $action = '';
  $blockreason = '';
  $query = '';
  $site_cell = '';
  
  $linkstr = "&amp;filter=$filter&amp;sys=$sys"; //Default link string
  
  if ($sqltable == 'historic') {                 //Add date search to link in histroic view
    $linkstr .= "&amp;datestart=$datestart&amp;dateend=$dateend";
  }
  
  $rows = count_rows_save("SELECT COUNT(DISTINCT dns_request) FROM $sqltable " .add_filterstr().add_datestr());
  $query = "SELECT sys, dns_request, dns_result, COUNT(*) AS count FROM $sqltable" .add_filterstr().add_datestr()." GROUP BY dns_request ORDER BY count $sort LIMIT ".ROWSPERPAGE." OFFSET ".(($page-1) * ROWSPERPAGE);
  
  if(!$result = $db->query($query)){
    die('There was an error running the query'.$db->error);
  } 
  
  if ($result->num_rows == 0) {                 //Leave if nothing found
    $result->free();
    echo 'Nothing Found';
    return false;
  }
  
  if ((($page-1) * ROWSPERPAGE) > $rows) $page = 1;
  
  echo '<div class="sys-group">'.PHP_EOL;
  pagination($rows, 'view='.$view.'&amp;sort='.strtolower($sort).$linkstr);
  draw_viewbuttons();
  
  echo '<table id="query-group-table">'.PHP_EOL;
  
  echo '<tr><th>#</th><th>Site</th><th>Action</th><th>Requests<a class="blue" href="?page='.$page.'&amp;view='.$view.'&amp;sort=desc'.$linkstr.'">&#x25BE;</a><a class="blue" href="?page='.$page.'&amp;view='.$view.'&amp;sort=asc'.$linkstr.'">&#x25B4;</a></th></tr>'.PHP_EOL;  
  
  while($row = $result->fetch_assoc()) {         //Read each row of results
    $action = '<a target="_blank" href="'.$Config['SearchUrl'].$row['dns_request'].'"><img class="icon" src="./images/search_icon.png" alt="G" title="Search"></a>&nbsp;<a target="_blank" href="'.$Config['WhoIsUrl'].$row['dns_request'].'"><img class="icon" src="./images/whois_icon.png" alt="W" title="Whois"></a>&nbsp;';
    
    if ($row['dns_result'] == 'A') {             //Row colouring
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
    elseif ($row['dns_result'] == 'L') {
      $row_class = ' class="local"';
      $action = '&nbsp;';
    }
    
    //Make entire site cell clickable with link going to Investigate
    $site_cell = '<td class="pointer" onclick="window.open(\'./investigate.php?site='.$row['dns_request'].'\', \'_blank\')"><a href="./investigate.php?site='.$row['dns_request'].'" class="black" target="_blank">'.$row['dns_request'].$blockreason.'</a></td>';
        
    echo '<tr'.$row_class.'><td>'.$i.'</td>'.$site_cell.'<td>'.$action.'</td><td>'.$row['count'].'</td></tr>'.PHP_EOL;
    $blockreason = '';
    $i++;
  }
  
  echo '</table>'.PHP_EOL;
  echo '<br>'.PHP_EOL;
  pagination($rows, 'view='.$view.'&amp;sort='.strtolower($sort).$linkstr);
  
  echo '</div>'.PHP_EOL;
  $result->free();

  return true;
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
  global $db, $page, $sort, $filter, $sys, $view, $datestart, $dateend, $Config, $TLDBlockList;
  global $datestart, $dateend;
  
  $rows = 0;
  $row_class = '';
  $pagination_link = '';
  $query = '';
  $action = '';
  $blockreason = '';
  $investigate = '';
  $site_cell = '';
  
  if ($view == 'livetime') {
    $rows = count_rows_save('SELECT COUNT(*) FROM live'.add_filterstr());
    if ((($page-1) * ROWSPERPAGE) > $rows) {
      $page = 1;    
    }
    $query = "SELECT *, DATE_FORMAT(log_time, '%H:%i:%s') AS formatted_time FROM live ".add_filterstr(). " ORDER BY UNIX_TIMESTAMP(log_time) $sort LIMIT ".ROWSPERPAGE." OFFSET ".(($page-1) * ROWSPERPAGE);
    $pagination_link = "view=$view&amp;sort=".strtolower($sort)."&amp;filter=$filter&amp;sys=$sys";
  }
  else {    
    $rows = count_rows_save("SELECT COUNT(*) FROM historic".add_filterstr().add_datestr());
    if ((($page-1) * ROWSPERPAGE) > $rows) {
      $page = 1;
    }
    $query = "SELECT *, DATE_FORMAT(log_time, '%Y-%m-%d %H:%i:%s') AS formatted_time FROM historic".add_filterstr().add_datestr(). " ORDER BY UNIX_TIMESTAMP(log_time) $sort LIMIT ".ROWSPERPAGE." OFFSET ".(($page-1) * ROWSPERPAGE);
    $pagination_link = "view=$view&amp;sort=".strtolower($sort)."&amp;filter=$filter&amp;sys=$sys&amp;datestart=$datestart&amp;dateend=$dateend";
  }
  
  if(!$result = $db->query($query)){
    die('There was an error running the query'.$db->error);
  }
  
  if ($result->num_rows == 0) {                  //Leave if nothing found
    $result->free();
    echo "Nothing found for the selected dates";
    return false;
  }
  
  echo '<div class="sys-group">'.PHP_EOL;
  pagination($rows, $pagination_link);
  draw_viewbuttons();
  
  echo '<table id="query-time-table">'.PHP_EOL;
  echo '<tr><th>Time<a class="blue" href="?'.htmlspecialchars('page='.$page.'&view='.$view.'&sort=desc&filter='.$filter.'&sys='.$sys.'&datestart='.$datestart.'&dateend='.$dateend).'">&#x25BE;</a><a class="blue" href="?'.htmlspecialchars('page='.$page.'&view='.$view.'&sort=asc&filter='.$filter.'&sys='.$sys.'&datestart='.$datestart.'&dateend='.$dateend).'">&#x25B4;</a></th><th>System</th><th>Site</th><th>Action</th></tr>'.PHP_EOL;
  
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
    
        
    //Make entire site cell clickable with link going to Investigate
    //Add in datetime and system into investigate link
    $site_cell = '<td class="pointer" onclick="window.open(\'./investigate.php?datetime='.$row['formatted_time'].'&amp;site='.$row['dns_request'].'&amp;sys='.$row['sys'].'\', \'_blank\')"><a href="./investigate.php?datetime='.$row['formatted_time'].'&amp;site='.$row['dns_request'].'&amp;sys='.$row['sys'].'" class="black" target="_blank">'.$row['dns_request'].$blockreason.'</a></td>';
        
    echo '<tr'.$row_class.'><td>'.$row['formatted_time'].'</td><td>'.$row['sys'].'</td>'.$site_cell.'<td>'.$action.$investigate.'</td></tr>'.PHP_EOL;
    $blockreason = '';
  }
  
  echo '</table>'.PHP_EOL;
  echo '<br>'.PHP_EOL;
  pagination($rows,  $pagination_link);
  echo '</div>'.PHP_EOL;
  
  $result->free();
  return true;
}

//Main---------------------------------------------------------------

$db = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);

search_systems();                                //Need to find out systems on live table

if (isset($_GET['page'])) {
  $page = filter_integer($_GET['page'], 1, PHP_INT_MAX, 1);
}

if (isset($_GET['filter'])) {
  if (array_key_exists($_GET['filter'], $FILTERLIST)) $filter = $_GET['filter'];
}

if (isset($_GET['sort'])) {
  if ($_GET['sort'] == 'asc') $sort = 'ASC';
}

if (isset($_GET['sys'])) {
  if (in_array($_GET['sys'], $syslist)) $sys = $_GET['sys'];
}

if (isset($_GET['view'])) {  
  if (in_array($_GET['view'], $VIEWLIST)) $view = $_GET['view'];
  if (($view == 'historicgroup') || ($view == 'historictime')) $sqltable = 'historic';
}

if (isset($_GET['datestart'])) {                 //Filter for yyyy-mm-dd
  if (preg_match(REGEX_DATE, $_GET['datestart']) > 0) $datestart = $_GET['datestart'];
}
if (isset($_GET['dateend'])) {                   //Filter for yyyy-mm-dd
  if (preg_match(REGEX_DATE, $_GET['dateend']) > 0) $dateend = $_GET['dateend'];  
}

if ($sqltable == 'historic') {                   //Check to see if dates are valid
  if (strtotime($dateend) > time()) $dateend = DEF_EDATE;
  if (strtotime($datestart) > strtotime($dateend)) {
    $datestart = DEF_SDATE;
    $dateend = DEF_EDATE;
  }
}


draw_filterbox();                                //Draw filters

if ($view == 'livetime') {
  show_time_view();
}
elseif ($view == 'livegroup') {
  show_group_view();
}
elseif ($view == 'historictime') {
  show_time_view();
}
elseif ($view == 'historicgroup') {
  show_group_view();
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
