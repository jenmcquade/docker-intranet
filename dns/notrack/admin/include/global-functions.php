<?php
//Global Functions used in NoTrack Admin

/********************************************************************
 *  Draw Sys Table
 *    Start off a sys-group table
 *  Params:
 *    Title
 *  Return:
 *    None
 */ 
function draw_systable($title) {
  echo '<div class="sys-group"><div class="sys-title">'.PHP_EOL;
  echo '<h5>'.$title.'</h5></div>'.PHP_EOL;
  echo '<div class="sys-items"><table class="sys-table">'.PHP_EOL;
  
  return null;
}


/********************************************************************
 *  Draw Sys Table
 *    Start off a sys-group table
 *  Params:
 *    Description, Value
 *  Return:
 *    None
 */
function draw_sysrow($description, $value) {
  echo '<tr><td>'.$description.': </td><td>'.$value.'</td></tr>'.PHP_EOL;
  
  return null;
}


/********************************************************************
 *  Activate Session
 *    Create login session
 *  Params:
 *    None
 *  Return:
 *    None
 */
function activate_session() {  
  $_SESSION['session_expired'] = false;
  $_SESSION['session_start'] = time();  
}

function ensure_active_session() {
  if (is_password_protection_enabled()) {
    session_start();
    if (isset($_SESSION['session_start'])) {
      if (!is_active_session()) {
        $_SESSION['session_expired'] = true;
        header('Location: ./login.php');
        exit;
      }
    }
    else {
      header('Location: ./login.php');
      exit;
    }
  }
}


function is_active_session() {
  $session_duration = 1800;    
  if (isset($_SESSION['session_start'])) {    
    if ((time() - $_SESSION['session_start']) < $session_duration) {
      return true;
    }
  }
  return false;
}

function is_password_protection_enabled() {
  global $Config;
  
  if ($Config['Password'] != '') return true;
  return false;
}


/********************************************************************
 *  Check Version
 *    1. Split strings by '.'
 *    2. Combine back together and multiply with Units array
 *    e.g 1.0 - 1x10000 + 0x100 = 10,000
 *    e.g 0.8.0 - 0x10000 + 8x100 + 0x1 = 800
 *    e.g 0.7.10 - 0x10000 + 7x100 + 10x1 = 710
 *  Params:
 *    Version
 *  Return:
 *    true if latestversion >= currentversion, or false if latestversion < currentversion
 */
function check_version($latestversion) {
  //If LatestVersion is less than Current Version then function returns false
  
  $numversion = 0;
  $numlatest = 0;
  $units = array(10000,100,1);
  
  $splitversion = explode('.', VERSION);
  $splitlatest = explode('.', $latestversion);
  
  for ($i = 0; $i < count($splitversion); $i++) {
    $numversion += ($units[$i] * intval($splitversion[$i]));
  }
  for ($i = 0; $i < count($splitlatest); $i++) {
    $numlatest += ($units[$i] * intval($splitlatest[$i]));
  }
  
  if ($numlatest < $numversion) return false;
  
  return true;
}

/********************************************************************
 *  Count rows in table
 *
 *  Params:
 *    SQL Query
 *  Return:
 *    Number of Rows
 */
function count_rows($query) {
  global $db;
  
  $rows = 0;
  
  if(!$result = $db->query($query)){
    die('count_rows() error running the query '.$db->error);
  }
    
  $rows = $result->fetch_row()[0];                         //Extract value from array
  $result->free();
  
  return $rows;
}

/********************************************************************
 *  Extract Domain
 *    Extract domain with optional double-barrelled tld
 *  Params:
 *    URL to check
 *  Return:
 *    Filtered domain
 */
function extract_domain($url) {
  preg_match(REGEX_DOMAIN, $url, $matches);
  
  return $matches[0];
}



/********************************************************************
 *  Filter Boolean Value
 *    Checks if value given is 'true' or 'false'
 *  Params:
 *    Value to Check
 *  Return:
 *    true or false
 */
function filter_bool($value) {
  if ($value == 'true') {
    return true;
  }
  else {
    return false;
  }
}

/********************************************************************
 *  Filter Integer Value
 *    Checks if Integer value given is between min and max
 *  Params:
 *    Value to Check, Minimum, Maximum, Default Value
 *  Return:
 *    value on success, default value on fail
 */
function filter_integer($value, $min, $max, $defaultvalue=0) {
  if (is_numeric($value)) {
    if (($value >= $min) && ($value <= $max)) {
      return intval($value);
    }
  }
  
  return $defaultvalue;
}

/********************************************************************
 *  Filter URL
 *    perform regex match to see if url is in the form of some-site.com, or some_site.co.uk
 *  Params:
 *    URL to check
 *  Return:
 *    True on success, False on failure
 */
function filter_url($url) {  
  if (preg_match('/[\d\w\-\_]\.[\d\w\-\_\.]/', $url) > 0) {
    return true;
  }
  else {
    return false;
  }
}
/********************************************************************
 *  Format Number
 *    Returns a number rounded to 3 significant figures
 *  Params:
 *    Number 
 *  Return:
 *    Number rounded to 3sf
 */
function formatnumber($number) {
  if ($number < 1000) return $number;
  elseif ($number < 10000) return number_format($number / 1000, 2).'k';
  elseif ($number < 100000) return number_format($number / 1000, 1).'k';
  elseif ($number < 1000000) return number_format($number / 1000, 0).'k';
  elseif ($number < 10000000) return number_format($number / 1000000, 2).'M';
  elseif ($number < 100000000) return number_format($number / 1000000, 1).'M';
  elseif ($number < 1000000000) return number_format($number / 1000000, 0).'M';
  elseif ($number < 10000000000) return number_format($number / 1000000000, 2).'G';
  elseif ($number < 100000000000) return number_format($number / 1000000000, 1).'G';
  elseif ($number < 1000000000000) return number_format($number / 1000000000, 0).'G';
  
  return number_format($number / 1000000000000, 0).'T';
}


/********************************************************************
 *  Is Active Class
 *    Used to allocate class="active" against li
 *  Params:
 *    Current View, Item
 *  Return:
 *    class='active' or '' when inactive
 */
function is_active_class($currentview, $item) {
  if ($currentview == $item) {
    return ' class="active"';
  }
  else {
    return '';
  }
}


/********************************************************************
 *  Is Checked
 *    Used to in forms to determine if tickbox should be checked
 *  Params:
 *    value
 *  Return:
 *    checked="checked" or nothing
 */
 function is_checked($value) {
  if ($value == 1 || $value == true) {
    return ' checked="checked"';
  }
  
  return '';
}
 

/********************************************************************
 *  Pagination
 *  
 *  Draw up to 7 buttons
 *  Main [<] [1] [x] [x+1] [L] [>]
 *  Or   [ ] [1] [2] [>]
 *
 *  Params:
 *    rows
 *    $linktext = text for a href
 *  Return:
 *    None
 */
function pagination($totalrows, $linktext) {
  global $page;

  $numpages = 0;
  $currentpage = 0;
  $startloop = 0;
  $endloop = 0;
  
  if ($totalrows > ROWSPERPAGE) {                     //Is Pagination needed?
    $numpages = ceil($totalrows / ROWSPERPAGE);       //Calculate List Size
    
    //<div class="sys-group">
    echo '<div class="float-left pag-nav"><ul>'.PHP_EOL;
  
    if ($page == 1) {                            // [ ] [1]
      echo '<li><span>&nbsp;&nbsp;</span></li>'.PHP_EOL;
      echo '<li class="active"><a href="?page=1&amp;'.$linktext.'">1</a></li>'.PHP_EOL;
      $startloop = 2;
      if ($numpages > 4)  $endloop = $page + 4;
      else $endloop = $numpages;
    }
    else {                                       // [<] [1]
      echo '<li><a href="?page='.($page-1).'&amp;'.$linktext.'">&#x00AB;</a></li>'.PHP_EOL;
      echo '<li><a href="?page=1&amp;'.$linktext.'">1</a></li>'.PHP_EOL;
      
      if ($numpages < 5) {
        $startloop = 2;                          // [1] [2] [3] [4] [L]
      }
      elseif (($page > 2) && ($page > $numpages -4)) {
        $startloop = ($numpages - 3);            //[1]  [x-1] [x] [L]
      }
      else {
        $startloop = $page;                      // [1] [x] [x+1] [L]
      }
      
      if (($numpages > 3) && ($page < $numpages - 2)) {
        $endloop = $page + 3;                    // [y] [y+1] [y+2] [y+3]
      }
      else {
        $endloop = $numpages;                    // [1] [x-2] [x-1] [y] [L]
      }      
    }    
    
    for ($i = $startloop; $i < $endloop; $i++) { //Loop to draw 3 buttons
      if ($i == $page) {
        echo '<li class="active"><a href="?page='.$i.'&amp;'.$linktext.'">'.$i.'</a></li>'.PHP_EOL;
      }
      else {
        echo '<li><a href="?page='.$i.'&amp;'.$linktext.'">'.$i.'</a></li>'.PHP_EOL;
      }
    }
    
    if ($page == $numpages) {                    // [Final] [ ]
      echo '<li class="active"><a href="?page='.$numpages.'&amp;'.$linktext.'">'.$numpages.'</a></li>'.PHP_EOL;
      echo '<li><span>&nbsp;&nbsp;</span></li>'.PHP_EOL;
    }    
    else {                                       // [Final] [>]
      echo '<li><a href="?page='.$numpages.'&amp;'.$linktext.'">'.$numpages.'</a></li>'.PHP_EOL;
      echo '<li><a href="?page='.($page+1).'&amp;'.$linktext.'">&#x00BB;</a></li>'.PHP_EOL;
    }	
    
  echo '</ul></div>'.PHP_EOL;
  //</div>
  }
}


/********************************************************************
 *  Save Config
 *    1. Check if Latest Version is less than Current Version
 *    2. Open Temp Config file for writing
 *    3. Loop through Config Array
 *    4. Write all values, except for "Status = Enabled"
 *    5. Close Config File
 *    6. Delete Config Array out of Memcache, in order to force reload
 *    7. Onward process is to Display appropriate config view
 *  Params:
 *    None
 *  Return:
 *    SQL Query string
 */
function save_config() {
  global $Config, $mem;
  
  $key = '';
  $value = '';
  
  //Prevent wrong version being written to config file if user has just upgraded and old LatestVersion is still stored in Memcache
  if (check_version($Config['LatestVersion'])) {
    $Config['LatestVersion'] = VERSION;
  }
  
  $fh = fopen(CONFIGTEMP, 'w');                  //Open temp config for writing
  
  foreach ($Config as $key => $value) {          //Loop through Config array
    if ($key == 'Status') {
      if ($value != 'Enabled') {
        fwrite($fh, $key.' = '.$value.PHP_EOL);  //Write Key & Value
      }
    }
    else {
      fwrite($fh, $key.' = '.$value.PHP_EOL);    //Write Key & Value
    }
  }
  fclose($fh);                                   //Close file
  
  $mem->delete('Config');                        //Delete config from Memcache
  
  exec(NTRK_EXEC.'--save-conf');
}


/********************************************************************
 *  Check SQL Table Exists
 *    Uses LIKE to check for table name in order to avoid error message.
 *  Params:
 *    SQL Table
 *  Return:
 *    True if table exists
 *    False if table does not exist
 */
function table_exists($table) {
  global $db;
  $exists = false;
  
  $result = $db->query("SHOW TABLES LIKE '$table'");
  
  if ($result->num_rows == 1) { 
    $exists = true;
  }
  
  $result->free();
  return $exists;
}


/********************************************************************
 *  Load Config File
 *    1. Attempt to load Config from Memcache
 *    2. Write DefaultConfig to Config, incase any variables are missing
 *    3. Read Config File
 *    4. Split Line between: (Var = Value)
 *    5. Certain values need filtering to prevent XSS
 *    6. For other values, check if key exists, then replace with new value
 *    7. Setup SearchUrl
 *    8. Write Config to Memcache
 *  Params:
 *    Description, Value
 *  Return:
 *    None
 */
function load_config() {
  global $Config, $mem, $DEFAULTCONFIG, $SEARCHENGINELIST, $WHOISLIST;
  $line = '';
  $splitline = array();
  
  $Config=$mem->get('Config');                   //Load Config array from Memcache
  if (! empty($Config)) {
    return null;                                 //Did it load from memory?
  }
  
  $Config = $DEFAULTCONFIG;                      //Firstly Set Default Config
  
  if (file_exists(CONFIGFILE)) {                 //Check file exists
    $fh= fopen(CONFIGFILE, 'r');
    while (!feof($fh)) {
      $line = trim(fgets($fh));                  //Read Line of LogFile
      $splitline = explode('=', $line);
      if (count($splitline) == 2) {
        $splitline[0] = trim($splitline[0]);
        $splitline[1] = trim($splitline[1]);
        switch (trim($splitline[0])) {
          case 'Delay':
            $Config['Delay'] = filter_integer($splitline[1], 0, 3600, 30);
            break;
          case 'ParsingTime':
            $Config['ParsingTime'] = filter_integer($splitline[1], 1, 60, 7);
            break;            
          default:
            if (array_key_exists($splitline[0], $Config)) {
              $Config[$splitline[0]] = strip_tags($splitline[1]);
            }
            break;
        }
      }
    }
    
    fclose($fh);
  }
  
  //Set SearchUrl if User hasn't configured a custom string via notrack.conf
  if ($Config['SearchUrl'] == '') {      
    if (array_key_exists($Config['Search'], $SEARCHENGINELIST)) {
      $Config['SearchUrl'] = $SEARCHENGINELIST[$Config['Search']];
    } 
    else {
      $Config['SearchUrl'] = $SEARCHENGINELIST['DuckDuckGo'];       
    }
  }
   
  //Set WhoIsUrl if User hasn't configured a custom string via notrack.conf
  if ($Config['WhoIsUrl'] == '') {      
    if (array_key_exists($Config['WhoIs'], $WHOISLIST)) {
      $Config['WhoIsUrl'] = $WHOISLIST[$Config['WhoIs']];
    } 
    else {
      $Config['WhoIsUrl'] = $WHOISLIST['Who.is'];       
    }
  }
  
  $mem->set('Config', $Config, 0, 1200);
  
  return null;
}


/********************************************************************
 *  Draw Line Chart
 *    Draws background line chart using SVG
 *    1. Calulate maximum values of input data for $ymax
 *    2. Draw grid lines
 *    3. Draw axis labels
 *    4. Draw coloured graph lines
 *    5. Draw coloured circles to reduce sharpness of graph line
 *
 *  Params:
 *    $values1 - array 1, $values2 array 2, $xlabels array
 *  Return:
 *    None
 */
function linechart($values1, $values2, $xlabels) {  
  $max_value = 0;
  $ymax = 0;
  $xstep = 0;
  $pathout = '';
  $numvalues = 0;
  $x = 0;
  $y = 0;

//Prepare chart
  $max_value = max(array(max($values1), max($values2)));
  $numvalues = count($values1);
  $values1[] = 0;                                          //Ensure line returns to 0
  $values2[] = 0;                                          //Ensure line returns to 0
  $xlabels[] = $xlabels[$numvalues-1] + 1;                 //Increment xlables
  
  $xstep = 1900 / 24;                                      //Calculate x axis increment
  if ($max_value < 200) {                                  //Calculate y axis maximum
    $ymax = (ceil($max_value / 10) * 10) + 10;             //Change offset for low values
  }
  elseif ($max_value < 10000) {
    $ymax = ceil($max_value / 100) * 100;
  }
  else {
    $ymax = ceil($max_value / 1000) * 1000;
  }
  
  echo '<div class="linechart-container">'.PHP_EOL;        //Start Chart container
  echo '<svg width="100%" height="90%" viewbox="0 0 2000 910" class="shadow">'.PHP_EOL;
  echo '<rect x="1" y="1" width="1998" height="908" rx="5" ry="5" fill-opacity="0.95" fill="#F7F7F7" stroke="#B3B3B3" stroke-width="2px" opacity="1" />'.PHP_EOL;
    
  for ($i = 0.25; $i < 1; $i += 0.25) {                    //Draw Y Axis lables and horizontal lines
    echo '<path class="gridline" d="M100,'.($i*850).' H2000" />'.PHP_EOL;
    echo '<text class="axistext" x="8" y="'.(18+($i*850)).'">'.formatnumber((1-$i)*$ymax).'</text>'.PHP_EOL;
  }
  echo '<text x="8" y="855" class="axistext">0</text>';
  echo '<text x="8" y="38" class="axistext">'.formatnumber($ymax).'</text>';
  
  
  for ($i = 0; $i < $numvalues; $i += 2) {                 //Draw X Axis labels skipping every other value
    echo '<text x="'.(55+($i * $xstep)).'" y="898" class="axistext">'.$xlabels[$i].':00</text>'.PHP_EOL;
  }  
  
  for ($i = 2; $i < 24; $i += 2) {                         //Verticle Grid lines
    echo '<path class="gridline" d="M'.(100+($i*$xstep)).',2 V850" />'.PHP_EOL;
  }
  
  draw_graphline($values1, $xstep, $ymax, '#008CD1');
  draw_circles($values1, $xstep, $ymax, '#008CD1');
  draw_graphline($values2, $xstep, $ymax, '#B1244A');
  draw_circles($values2, $xstep, $ymax, '#B1244A');

  echo '<path class="axisline" d="M100,0 V850 H2000 " />'; //X and Y Axis line
  echo '</svg>'.PHP_EOL;                                   //End SVG
  echo '</div>'.PHP_EOL;                                   //End Chart container

}


/********************************************************************
 *  Draw Graph Line
 *    Calulates and draws the graph line using straight point-to-point notes
 *
 *  Params:
 *    $values array, x step, y maximum value, line colour
 *  Return:
 *    svg path nodes with bezier curve points
 */

function draw_graphline($values, $xstep, $ymax, $colour) {
  $path = '';
  $x = 0;                                                  //Node X
  $y = 0;                                                  //Node Y
  $numvalues = count($values);
  
  $path = "<path d=\"M 100,850 ";                          //Path start point
  for ($i = 1; $i < $numvalues; $i++) {
    $x = 100 + (($i) * $xstep);
    $y = 850 - (($values[$i] / $ymax) * 850);
    $path .= "L $x $y";
  }
  $path .= 'V850 " stroke="'.$colour.'" stroke-width="5px" fill="'.$colour.'" fill-opacity="0.15" />'.PHP_EOL;
  echo $path;  
}


/********************************************************************
 *  Draw Circle Points
 *    Draws circle shapes where line node points are, in order to reduce sharpness of graph line
 *
 *  Params:
 *    $values array, x step, y maximum value, line colour
 *  Return:
 *    svg path nodes with bezier curve points
 */
function draw_circles($values, $xstep, $ymax, $colour) {
  $path = '';
  $x = 0;                                        //Node X
  $y = 0;                                        //Node Y
  $numvalues = count($values);
    
  for ($i = 1; $i < $numvalues; $i++) {
    if ($values[$i] > 0) {
      $x = 100 + (($i) * $xstep);
      $y = 850 - (($values[$i] / $ymax) * 850);    
    
      echo '<circle cx="'.$x.'" cy="'.(850-($values[$i]/$ymax)*850).'" r="10px" fill="'.$colour.'" fill-opacity="1" stroke="#EAEEEE" stroke-width="5px" />'.PHP_EOL;
    }    
  }  
}

/********************************************************************
 *  Draw Pie Chart
 *    Credit to Branko: http://www.tekstadventure.nl/branko/blog/2008/04/php-generator-for-svg-pie-charts
 *  Params:
 *    aray of values, the centre coordinates x and y, radius of the piechart, colours
 *  Return:
 *    svg data for path nodes
 */
function piechart($data, $cx, $cy, $radius, $colours) {
  $chartelem = "";
  $sum = 0;

  $max = count($data);
  
  if (max($data) == 0) return;                             //Prevent divide by zero warning

  foreach ($data as $key=>$val) {
    $sum += $val;
  }
  $deg = $sum/360;                               // one degree
  $jung = $sum/2;                                // necessary to test for arc type

  //Data for grid, circle, and slices
  $dx = $radius;                                 // Starting point:  
  $dy = 0;                                       // first slice starts in the East
  $oldangle = 0;

  for ($i = 0; $i<$max; $i++) {                  // Loop through the slices
    $angle = $oldangle + $data[$i]/$deg;         // cumulative angle
    $x = cos(deg2rad($angle)) * $radius;         // x of arc's end point
    $y = sin(deg2rad($angle)) * $radius;         // y of arc's end point

    $colour = $colours[$i];

    if ($data[$i] > $jung) {                     // arc spans more than 180 degrees
      $laf = 1;
    }
    else {
      $laf = 0;
    }

    $ax = $cx + $x;                              // absolute $x
    $ay = $cy + $y;                              // absolute $y
    $adx = $cx + $dx;                            // absolute $dx
    $ady = $cy + $dy;                            // absolute $dy
    $chartelem .= "<path d=\"M$cx,$cy ";         // move cursor to center
    $chartelem .= " L$adx,$ady ";                // draw line away away from cursor
    $chartelem .= " A$radius,$radius 0 $laf,1 $ax,$ay "; // draw arc
    $chartelem .= " z\" ";                       // z = close path
    $chartelem .= " fill=\"$colour\" stroke=\"#202020\" stroke-width=\"2\" ";
    $chartelem .= " fill-opacity=\"0.95\" stroke-linejoin=\"round\" />";
    $chartelem .= PHP_EOL;
    $dx = $x;      // old end points become new starting point
    $dy = $y;      // id.
    $oldangle = $angle;
  }
  
  return $chartelem; 
}
?>
