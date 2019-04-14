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
  <title>NoTrack - Sites Blocked</title>  
</head>

<body>
<?php
draw_topmenu('Sites Blocked');
draw_sidemenu();

/************************************************
*Constants                                      *
************************************************/
//Chart colours from: http://godsnotwheregodsnot.blogspot.co.uk/2013/11/kmeans-color-quantization-seeding.html
$CHARTCOLOURS = array('#FFFF00', '#1CE6FF', '#FF34FF', '#FF4A46', '#008941', '#006FA6', '#A30059', '#FFDBE5', '#7A4900', '#0000A6', '#63FFAC', '#B79762', '#004D43', '#8FB0FF', '#997D87', '#5A0007', '#809693', '#FEFFE6', '#1B4400', '#4FC601', '#3B5DFF', '#4A3B53',  '#DDEFFF', '#000035', '#7B4F4B', '#A1C299', '#300018', '#0AA6D8', '#013349', '#00846F');


/************************************************
*Global Variables                               *
************************************************/
$page = 1;
$view = 'group';
$sort = 'DESC';
$last = 1;                                       //SQL Interval Time
$unit = 'DAY';                                   //SQL Interval Unit

$db = new mysqli(SERVERNAME, USERNAME, PASSWORD, DBNAME);


/************************************************
*Arrays                                         *
************************************************/



/********************************************************************
 *  Add Date Vars to SQL Search
 *    Draw Sub Navigation menu
 *  Params:
 *    None
 *  Return:
 *    None
 */
function draw_subnav() {
  global $view;
  
  echo '<div class="sys-group">'.PHP_EOL;
  echo '<h5>Sites Blocked</h5>'.PHP_EOL;
  echo '<div class="pag-nav">'.PHP_EOL;
  echo '<ul>'.PHP_EOL;
  echo '<li'.is_active_class($view, 'group').'><a class="pag-exwide" href="?view=group">Group</a></li>'.PHP_EOL;
  echo '<li'.is_active_class($view, 'time').'><a class="pag-exwide" href="?view=time">Time</a></li>'.PHP_EOL;
  //echo '<li><a'.is_active_class($view, 'ref').' href="?view=ref">Referrer</a></li>'.PHP_EOL;
  echo '<li'.is_active_class($view, 'visualisation').'><a class="pag-exwide" href="?view=vis">Visualisation</a></li>'.PHP_EOL;
  echo '</ul>'.PHP_EOL;
  echo '</div>'.PHP_EOL;
  echo '</div>'.PHP_EOL;
}

/********************************************************************
 *  Get User Agent 
 *    Identifies OS and Browser
 *  Params:
 *    UserAgent String
 *  Return:
 *    OS and Browser
 
1st Capturing Group (Mozilla|Dalvik|Opera)
2nd Capturing Group (Linux|X11|Android|Windows|compatible|iPad|iPhone|Macintosh|IE 11\.0)
3rd Capturing Group (MSIE|Android|Windows)?
Non-capturing group (?:KHTML|Gecko|AppleWebKit)?
4th Capturing Group (Firefox|Iceweasel|PaleMoon|SeaMonkey|\(KHTML\,\slike\sGecko\)\s)?
5th Capturing Group (Chrome|Version|min|brave)?
6th Capturing Group (Mobile|Safari)?
7th Capturing Group (Edge|OPR|Vivaldi)?
 */
function get_useragent($user_agent) {
  $matches = array();
  $ua = array('unknown', 'unknown');
  $pattern = '/^(Mozilla|Dalvik|Opera)\/\d\.\d\.?\d?\s\((Linux|X11|Android|Windows|compatible|iPad|iPhone|Macintosh|IE 11\.0).\s?(MSIE|Android|Windows)?[^\)]+\)\s?(?:KHTML|Gecko|AppleWebKit)?[\/\d\.\+]*\s?(Firefox|Iceweasel|PaleMoon|SeaMonkey|\(KHTML\,\slike\sGecko\)\s)?(Chrome|Version|min|brave)?[\/\d\.\s]*(Mobile|Safari)?[\/\d\.\s]*(Edge|OPR|Vivaldi)?/';
  
  if (preg_match($pattern, $user_agent, $matches) > 0) {
    switch($matches[1]) {                        //Usually Mozilla
      case 'Dalvik': $ua[1] = 'android'; break;  //Android apps
      case 'Opera': $ua[1] = 'opera'; break;     //Opera prior to Blink
    }    
  
    switch($matches[2]) {                        //Most OS's or IE 11
      case 'Linux':
      case 'X11':
        $ua[0] = 'linux';
        break;
      case 'Android':
        $ua[0] = 'android';
        break;
      case 'Windows':
      case 'compatible':
        $ua[0] = 'windows';
        break;
      case 'iPad':
      case 'iPhone':
      case 'Macintosh':
        $ua[0] = 'apple';
        break;
      case 'IE 11.0':
        $ua[0] = 'windows';
        $ua[1] = 'internet-explorer';
        break;
    }
   
    if (isset($matches[3])) {                    //Android or IE
      switch($matches[3]) {
        case 'MSIE': $ua[1] = 'internet-explorer'; break;
        case 'Android': $ua[0] = 'android'; break;
        case 'Windows': $ua[0] = 'windows'; break;
      }      
    }
    
    if (isset($matches[4])) {                    //Gecko rendered Mozilla browsers
      switch($matches[4]) {
        case 'Firefox': $ua[1] = 'firefox'; break;
        case '(KHTML, like Gecko):': $ua[1] = 'chrome'; break;
        case 'Iceweasel': $ua[1] = 'iceweasel'; break;
        case 'PaleMoon': $ua[1] = 'palemoon'; break;
        case 'SeaMonkey': $ua[1] = 'seamonkey'; break;
      }
    }
    
    if (isset($matches[5])) {
      switch($matches[5]) {
        case 'Chrome': $ua[1] = 'chrome'; break;
        case 'min': $ua[1] = 'min'; break;
        case 'brave': $ua[1] = 'brave'; break;
      }
    }
    
    if (isset($matches[6])) {                    //Safari or Safari compliant
      if ($matches[5] == 'Version') {            //Backtrack to Group5 to check if actually Safari
        $ua[1] = 'safari';
      }
    }
    
    if (isset($matches[7])) {
      switch($matches[7]) {
        case 'Edge': $ua[1] = 'edge'; break;
        case 'OPR': $ua[1] = 'opera'; break;
        case 'Vivaldi': $ua[1] = 'vivaldi'; break;
      }
    }    
  }
  //Subsequent regex statements are too dificult to implement above
  elseif(preg_match('/^Python\-urllib\/\d\.\d\d?/', $user_agent, $matches) > 0) {
    $ua = array('unknown', 'python');
  }    
  
  return $ua;
}

/********************************************************************
 *  Hightlight URL
 *    Highlight site, similar to browser behaviour
 *    Full Group 1: http / https / ftp
 *    Non-capture group to remove www.
 *    Full Group 2: Domain
 *    Full Group 3: URI Path
 *    Domain Group 1: Site
 *    Domain Group 2: Optional .gov, .org, .co, .com
 *    Domain Group 3: Top Level Domain
 *
 *    Merge final string together with Full Group 1, Full Group 2 - Length Domain, Domain (highlighted black), Full Group 3
 *  Params:
 *    URL
 *  Return:
 *    html formatted string 
 */
function highlight_url($url) {
  $highlighted =  $url;
  $full = array();
  $domain = array();
    
  if (preg_match('/^(https?:\/\/|ftp:\/\/)?(?:www\.)?([^\/]+)?(.*)$/', $url, $full) > 0) {    
    if (preg_match('/([\w\d\-\_]+)\.(co\.|com\.|gov\.|org\.)?([\w\d\-\_]+)$/', $full[2], $domain) > 0) {      
      $highlighted = '<span class="gray">'.$full[1].substr($full[2], 0, 0 -strlen($domain[0])).'</span>'.$domain[0].'<span class="gray">'.$full[3].'</span>';
    }
  }  
  return $highlighted;
}

/********************************************************************
 *  Show Access Table
 *    
 *  Params:
 *    None
 *  Return:
 *    True on results found
 */
function show_accesstable() {
  global $db, $page, $sort, $view;
  
  $rows = 0;
  $http_method = '';
  $referrer = '';
  $query = '';
  $remote_host = '';
  $table_row = '';
  $user_agent = '';
  $user_agent_array = array();
    
  echo '<div class="sys-group">'.PHP_EOL;
  if ($view == 'group') {                                  //Group view
    echo '<h6>Sorted by Unique Site</h6>'.PHP_EOL;
    $rows = count_rows('SELECT COUNT(DISTINCT site) FROM lightyaccess');
    if ((($page-1) * ROWSPERPAGE) > $rows) $page = 1;
    
    $query = 'SELECT * FROM lightyaccess GROUP BY site ORDER BY UNIX_TIMESTAMP(log_time) '.$sort.' LIMIT '.ROWSPERPAGE.' OFFSET '.(($page-1) * ROWSPERPAGE);
  }
  elseif ($view == 'time') {                               //Time View
    echo '<h6>Sorted by Time last seen</h6>'.PHP_EOL;
    $rows = count_rows('SELECT COUNT(*) FROM lightyaccess');
    if ((($page-1) * ROWSPERPAGE) > $rows) $page = 1;
    
    $query = 'SELECT * FROM lightyaccess ORDER BY UNIX_TIMESTAMP(log_time) '.$sort.' LIMIT '.ROWSPERPAGE.' OFFSET '.(($page-1) * ROWSPERPAGE);
  }  
      
  if(!$result = $db->query($query)){
    die('There was an error running the query'.$db->error);
  }
  
    
  if ($result->num_rows == 0) {                            //Leave if nothing found
    $result->free();
    echo 'No sites found in Access List'.PHP_EOL;
    echo '</div>';
    return false;
  }
  
  pagination($rows, 'view='.$view);                        //Draw pagination buttons
  
  echo '<table id="access-table">'.PHP_EOL;                //Start table
  echo '<tr><th>Date Time</th><th>Method</th><th>User Agent</th><th>Site</th></tr>'.PHP_EOL;
  
  while($row = $result->fetch_assoc()) {                   //Read each row of results
    if ($row['http_method'] == 'GET') {                    //Colour HTTP Method
      $http_method = '<span class="green">GET</span>';
    }
    else {
      $http_method = '<span class="violet">POST</span>';
    }
    
        
    //Temporary situation until v0.8.3
    if (array_key_exists('referrer', $row)) {
      $referrer = $row['referrer'];
    }
    else {
      $referrer = '';
    }
    
    if (array_key_exists('user_agent', $row)) {
      $user_agent = $row['user_agent'];
    }
    else {
      $user_agent = '';
    }
    
    if (array_key_exists('remote_host', $row)) {
      $remote_host = $row['remote_host'];
    }
    else {
      $remote_host = '';
    }
        
    $user_agent_array = get_useragent($user_agent);        //Get OS and Browser from UserAgent
    
    //Build up the table row
    $table_row = '<tr><td>'.$row['log_time'].'</td><td>'.$http_method.'</td>';
    
    $table_row .='<td title="'.$user_agent.'"><div class="centered"><img src="./images/useragent/'.$user_agent_array[0].'.png" alt=""><img src="./images/useragent/'.$user_agent_array[1].'.png" alt=""></div></td>';
    
    $table_row .= '<td>'.highlight_url(htmlentities($row['site'].$row['uri_path'])).'<br>Referrer: '.highlight_url(htmlentities($referrer)).'<br>Requested By: '.$remote_host.'</td></tr>';
    
    echo $table_row.PHP_EOL;                               //Echo the table row
  }
  
  echo '</table><br>'.PHP_EOL;                             //End of table
  pagination($rows, 'view='.$view);                        //Draw pagination buttons
  echo '</div>'.PHP_EOL;                                   //End Sys-group div
  
  $result->free();

  return true;
}


/********************************************************************
 *  Show Visualisation
 *    
 *  Params:
 *    None
 *  Return:
 *    True on results found
 */
function show_visualisation() {
  global $CHARTCOLOURS, $db, $last, $unit;
  
  $site_names = array();
  $site_count = array();
  $total = 0;
  $other = 0;
  $numsites = 0;
  
  echo '<div class="sys-group">'.PHP_EOL;
  echo '<h6>Visualisation</h6>'.PHP_EOL;
  
  echo '<div class="pag-nav"><ul>'.PHP_EOL;
  echo '<li'.is_active_class($last.$unit, '1HOUR').'><a href="?view=vis&amp;last=1hour">1 Hour</a></li>'.PHP_EOL;
  echo '<li'.is_active_class($last.$unit, '4HOUR').'><a href="?view=vis&amp;last=4hour">4 Hours</a></li>'.PHP_EOL;
  echo '<li'.is_active_class($last.$unit, '8HOUR').'><a href="?view=vis&amp;last=8hour">8 Hours</a></li>'.PHP_EOL;
  echo '<li'.is_active_class($last.$unit, '1DAY').'><a href="?view=vis&amp;last=1day">1 Day</a></li>'.PHP_EOL;
  echo '<li'.is_active_class($last.$unit, '7DAY').'><a href="?view=vis&amp;last=7day">7 Days</a></li>'.PHP_EOL;
  echo '</ul></div>'.PHP_EOL;
  
  
  $total = count_rows('SELECT COUNT(*) FROM lightyaccess WHERE log_time >= (NOW() - INTERVAL '.$last.' '.$unit.')');
  
  $query = 'SELECT site, COUNT(*) AS count FROM lightyaccess WHERE log_time >= (NOW() - INTERVAL '.$last.' '.$unit.') GROUP BY site ORDER BY count DESC LIMIT 20';
  
  if(!$result = $db->query($query)){
    die('There was an error running the query'.$db->error);
  }
  
  if ($result->num_rows == 0) {                            //Leave if nothing found
    $result->free();
    echo 'No sites found in Access List'.PHP_EOL;
    echo '</div>';
    return false;
  }

  while($row = $result->fetch_assoc()) {                   //Read each row of results
    $site_names[] = $row['site'];
    $site_count[] = $row['count'];
    $other += $row['count'];
  }
  
  $other = $total - $other;
  
  if ($other > 10) {                                       //Is it worth adding other?
    $site_names[] = 'Other';
    $site_count[] = $other;
  }
  
  $numsites = count($site_names);
  
  echo '<svg width="100%" height="90%" viewbox="0 0 1500 1100">'.PHP_EOL;
  echo piechart($site_count, 500, 540, 490, $CHARTCOLOURS);
  echo '<circle cx="500" cy="540" r="120" stroke="#00000A" stroke-width="2" fill="#f7f7f7" />'.PHP_EOL;
  
  for ($i = 0; $i < $numsites; $i++) {
    echo '<rect x="1015" y="'.(($i*43)+90).'" rx="5" ry="5" width="38" height="38" style="fill:'.$CHARTCOLOURS[$i].'; stroke:#00000A; stroke-width=3" />';
    echo '<text x="1063" y="'.(($i*43)+118).'" style="font-family: Arial; font-size: 26px; fill:#00000A">'.$site_names[$i].': '.number_format(floatval($site_count[$i])).'</text>'.PHP_EOL;
  }
  
  echo '</svg>'.PHP_EOL;
    
  echo '</div>'.PHP_EOL;                                   //End Sys-group div
  
  $result->free();

  return true;
}

//Main---------------------------------------------------------------

/************************************************
*GET REQUESTS                                   *
************************************************/
if (isset($_GET['view'])) {
  switch($_GET['view']) {
    case 'group': $view = 'group'; break;
    case 'time': $view = 'time'; break;
    case 'vis': $view = 'visualisation'; break;
  }
}

if (isset($_GET['page'])) {
  $page = filter_integer($_GET['page'], 1, PHP_INT_MAX, 1);
}

if (isset($_GET['last'])) {
  $lastmatches = array();
  
  if (preg_match('/^(\d\d?)(hour|day|week)$/', $_GET['last'], $lastmatches) > 0) {
    $last = intval($lastmatches[1]);
    $unit = strtoupper($lastmatches[2]);
  }
  unset($lastmatches);
}
//Start of page------------------------------------------------------
echo '<div id="main">';

draw_subnav();

if (($view == 'group') || ($view == 'time'))  {
  show_accesstable();
}
elseif ($view == 'visualisation') {
  show_visualisation();
}

?>
</div>
<div id="scrollup" class="button-scroll" onclick="ScrollToTop()"><img src="./svg/arrow-up.svg" alt="up"></div>
<div id="scrolldown" class="button-scroll" onclick="ScrollToBottom()"><img src="./svg/arrow-down.svg" alt="down"></div>
</body>
</html>
