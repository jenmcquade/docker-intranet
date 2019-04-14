<?php

/********************************************************************
 *  Add Search Box String to SQL Search
 *
 *  Params:
 *    None
 *  Return:
 *    SQL Query string
 */
function add_searches() {
  global $blradio, $searchbox;
  $searchstr = '';
  
  if (($blradio != 'all') && ($searchbox != '')) {
    $searchstr = ' WHERE site LIKE \'%'.$searchbox.'%\' AND bl_source = \''.$blradio.'\' ';
  }
  elseif ($blradio != 'all') {
    $searchstr = ' WHERE bl_source = \''.$blradio.'\' ';
  }
  elseif ($searchbox != '') {
    $searchstr = ' WHERE site LIKE \'%'.$searchbox.'%\' ';
  }
  
  return $searchstr;
}


/********************************************************************
 *  Draw Blocklist Row
 *
 *  Params:
 *    Block list, bl_name, Message
 *  Return:
 *    None
 */
function draw_blocklist_row($bl, $bl_name, $msg) {
  global $Config;
  //Txt File = Origniating download file
  //TLD Is a special case, and the Txt file used is TLD_FILE
  
  $txtfile = false;
  $txtfilename = '';
  $txtlines = 0;
  $filename = '';
  $totalmsg = '';  
  
  if ($Config[$bl] == 0) {
    echo '<tr><td>'.$bl_name.':</td><td><input type="checkbox" name="'.$bl.'"> '.$msg.'</td></tr>'.PHP_EOL;
  }
  else {    
    $filename = strtolower(substr($bl, 3));
    if ($bl == 'bl_tld') {
      $txtfilename = TLD_FILE;
    }
    else {
      $txtfilename = DIR_TMP.$filename.'.txt';
    }
    
    $rows = count_rows("SELECT COUNT(*) FROM blocklist WHERE bl_source = '$bl'");
        
    $txtfile = file_exists($txtfilename);
    
    if (($rows > 0) && ($txtfile)) {
      $txtlines = intval(exec('wc -l '.$txtfilename));
      if ($rows > $txtlines) $rows = $txtlines;  //Prevent stupid result
      $totalmsg = '<p class="light">'.$rows.' used of '.$txtlines.'</p>';
    }
    else {
      $totalmsg = '<p class="light">'.$rows.' used of ?</p>';
    }
    
   
    echo '<tr><td>'.$bl_name.':</td><td><input type="checkbox" name="'.$bl.'" checked="checked"> '.$msg.' '.$totalmsg.'</td></tr>'.PHP_EOL;    
  }
    
  return null;
}


/********************************************************************
 *  Draw Blocklist Radio Form
 *    Radio list is made up of the items in $BLOCKLISTNAMES array
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function draw_blradioform() {
  global $BLOCKLISTNAMES, $showblradio, $blradio, $page, $searchbox;
  
  if ($showblradio) {                            //Are we drawing Form or Show button?
    echo '<form name = "blradform" method="GET">'.PHP_EOL;   //Form for Radio List
    echo '<input type="hidden" name="v" value="full">'.PHP_EOL;
    echo '<input type="hidden" name="page" value="'.$page.'">'.PHP_EOL;
    if ($searchbox != '') {
      echo '<input type="hidden" name="s" value="'.$searchbox.'">'.PHP_EOL;
    }    
  
    if ($blradio == 'all') {
      echo '<span class="blradiolist"><input type="radio" name="blrad" value="all" checked="checked" onclick="document.blradform.submit()">All</span>'.PHP_EOL;
    }
    else {
      echo '<span class="blradiolist"><input type="radio" name="blrad" value="all" onclick="document.blradform.submit()">All</span>'.PHP_EOL;
    }
  
    foreach ($BLOCKLISTNAMES as $key => $value) { //Use BLOCKLISTNAMES for Radio items
      if ($key == $blradio) {                    //Should current item be checked?
        echo '<span class="blradiolist"><input type="radio" name="blrad" value="'.$key.'" checked="checked" onclick="document.blradform.submit()">'.$value.'</span>'.PHP_EOL;
      }
      else {
        echo '<span class="blradiolist"><input type="radio" name="blrad" value="'.$key.'" onclick="document.blradform.submit()">'.$value.'</span>'.PHP_EOL;
      }
    }
  }  
  else {                                         //Draw Show button instead
    echo '<form action="?v=full&amp;page='.$page.'" method="POST">'.PHP_EOL;
    echo '<input type="hidden" name="showblradio" value="1">'.PHP_EOL;
    echo '<input type="submit" class="button-blue" value="Select Block List">'.PHP_EOL;
  }
  
  echo '</form>'.PHP_EOL;                        //End of either form above
  echo '<br>'.PHP_EOL;
}


/********************************************************************
 *  Load CSV List
 *    Load TLD List CSV file into $list
 *  Params:
 *    listname - blacklist or whitelist, filename
 *  Return:
 *    true on completion
 */
function load_csv($filename, $listname) {
  global $list, $mem;
    
  $list = $mem->get($listname);
  if (empty($list)) {
    $fh = fopen($filename, 'r') or die('Error unable to open '.$filename);
    while (!feof($fh)) {
      $list[] = fgetcsv($fh);
    }
    
    fclose($fh);
    if (count($list) > 50) {                     //Only store decent size list in Memcache
      $mem->set($listname, $list, 0, 600);       //10 Minutes
    }
  }
  
  return true;
}

/********************************************************************
 *  Load Custom Block List
 *    Loads a Black or White List from File into $list Array
 *    Saves $list into respective Memcache array  
 *  Params:
 *    listname - blacklist or whitelist, filename
 *  Return:
 *    true on completion
 */
function load_customlist($listname, $filename) { 
  global $list, $mem;
    
  $list = $mem->get($listname);
  
  if (empty($list)) {
    $fh = fopen($filename, 'r') or die('Error unable to open '.$filename);
    while (!feof($fh)) {
      $Line = trim(fgets($fh));
      
      if (filter_url($Line)) {
        $seg = explode('#', $Line);
        if ($seg[0] == '') {
          $list[] = array(trim($seg[1]), $seg[2], false);
        }
        else {
          $list[] = array(trim($seg[0]), $seg[1], true);
        }        
      }
    }  
    fclose($fh);  
    $mem->set($listname, $list, 0, 60);
  }
  
  return true;  
}


/********************************************************************
 *  Load List
 *    Loads a a List from File and returns it in Array form
 *    Saves $list into respective Memcache array  
 *  Params:
 *    listname - blacklist or whitelist, filename
 *  Return:
 *    array of file
 */
function load_list($filename, $listname) {
  global $mem;
  
  $filearray = array();
  
  $filearray = $mem->get($listname);
  if (empty($filearray)) {
    if (file_exists($filename)) {                //Check if File Exists
      $fh = fopen($filename, 'r') or die('Error unable to open '.$filename);
      while (!feof($fh)) {
        $filearray[] = trim(fgets($fh));
      }
      fclose($fh);
      $mem->set($listname, $filearray, 0, 600);  //Change to 1800
    }
  }
  
  return $filearray;
}
/********************************************************************
 *  Show Advanced Page
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function show_advanced() {
  global $Config;
  echo '<form action="?v=advanced" method="post">'.PHP_EOL;
  echo '<input type="hidden" name="action" value="advanced">';
  draw_systable('Advanced Settings');
  draw_sysrow('DNS Log Parsing Interval', '<input type="number" name="parsing" min="1" max="60" value="'.$Config['ParsingTime'].'" title="Time between updates in Minutes">');
  draw_sysrow('Suppress Domains <img class="btn" src="./svg/button_help.svg" alt="help" title="Group together certain domains on the Stats page">', '<textarea rows="5" name="suppress">'.str_replace(',', PHP_EOL, $Config['Suppress']).'</textarea>');
  echo '<tr><td colspan="2"><div class="centered"><input type="submit" class="button-grey" value="Save Changes"></div></td></tr>'.PHP_EOL;
  echo '</table>'.PHP_EOL;
  echo '</div></div>'.PHP_EOL;
  echo '</form>'.PHP_EOL;
}


/********************************************************************
 *  Show Block List Page
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function show_blocklists() {
  global $Config;

  echo '<form action="?v=blocks" method="post">';         //Block Lists
  echo '<input type="hidden" name="action" value="blocklists">';
  draw_systable('NoTrack Block Lists');
  draw_blocklist_row('bl_notrack', 'NoTrack List', 'NoTrack Block List contains mixture of Tracking and Advertising sites');
  draw_blocklist_row('bl_notrack_malware', 'NoTrack Malware', 'NoTrack Malware List contains malicious and dodgy sites that aren&rsquo;t really considered tracking or advertising');
  draw_blocklist_row('bl_tld', 'Top Level Domains', 'Whole country and generic top level domains');
  echo '</table></div></div>'.PHP_EOL;
  
  //Advert Blocking
  draw_systable('Advert Blocking');
  draw_blocklist_row('bl_easylist', 'EasyList', 'EasyList without element hiding rules‎ <a href="https://forums.lanik.us/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_pglyoyo', 'Peter Lowe&rsquo;s Ad server list‎', 'Some of this list is already in NoTrack <a href="https://pgl.yoyo.org/adservers/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>'); 
  echo '</table></div></div>'.PHP_EOL;
  
  //Privacy
  draw_systable('Privacy');
  draw_blocklist_row('bl_easyprivacy', 'EasyPrivacy', 'Supplementary list from AdBlock Plus <a href="https://forums.lanik.us/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_fbenhanced', 'Fanboy&rsquo;s Enhanced Tracking List', 'Blocks common tracking scripts <a href="https://www.fanboy.co.nz/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  echo '</table></div></div>'.PHP_EOL;
  
  //Malware
  draw_systable('Malware');
  draw_blocklist_row('bl_hexxium', 'Hexxium Creations Threat List', 'Hexxium Creations are a small independent team running a community based malware and scam domain database <a href="https://www.hexxiumcreations.com/projects/malicious-domain-blocking" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_cedia', 'CEDIA Malware List', 'National network investigation and education of Ecuador - Malware List <a href="https://cedia.org.ec/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_cedia_immortal', 'CEDIA Immortal Malware List', 'CEDIA Long-lived &#8220;immortal&#8221; Malware sites <a href="https://cedia.org.ec/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_disconnectmalvertising', 'Malvertising list by Disconnect', '<a href="https://disconnect.me/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_malwaredomainlist', 'Malware Domain List', '<a href="http://www.malwaredomainlist.com/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_malwaredomains', 'Malware Domains', 'A good list to add <a href="http://www.malwaredomains.com/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_spam404', 'Spam404', '<a href="http://www.spam404.com/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_swissransom', 'Swiss Security - Ransomware Tracker', 'Protects against downloads of several variants of Ransomware, including Cryptowall and TeslaCrypt <a href="https://ransomwaretracker.abuse.ch/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_swisszeus', 'Swiss Security - ZeuS Tracker', 'Protects systems infected with ZeuS malware from accessing Command & Control servers <a href="https://zeustracker.abuse.ch/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  echo '</table></div></div>'.PHP_EOL;
  
  //Crypto Coin
  draw_systable('Crypto Coin Mining');
    
  draw_blocklist_row('bl_cbl_all', 'Coin Blocker Lists - All', 'This list contains all crypto mining domains - A list for administrators to prevent mining in networks. <a href="https://gitlab.com/ZeroDot1/CoinBlockerLists" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  
  draw_blocklist_row('bl_cbl_opt', 'Coin Blocker Lists - Optional', 'This list contains all optional mining domains - An additional list for administrators. <a href="https://gitlab.com/ZeroDot1/CoinBlockerLists" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  
  draw_blocklist_row('bl_cbl_browser', 'Coin Blocker Lists - Browser', 'This list contains all browser mining domains - A list to prevent browser mining only. <a href="https://gitlab.com/ZeroDot1/CoinBlockerLists" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');    
  
  echo '</table></div></div>'.PHP_EOL;                     //End Crypto Coin
  
  //Social
  draw_systable('Social');
  draw_blocklist_row('bl_fbannoyance', 'Fanboy&rsquo;s Annoyance List', 'Block Pop-Ups and other annoyances. <a href="https://www.fanboy.co.nz/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_fbsocial', 'Fanboy&rsquo;s Social Blocking List', 'Block social content, widgets, scripts and icons. <a href="https://www.fanboy.co.nz" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  echo '</table></div></div>'.PHP_EOL;
  
  //Multipurpose
  draw_systable('Multipurpose');
  draw_blocklist_row('bl_someonewhocares', 'Dan Pollock&rsquo;s hosts file', 'Mixture of Shock and Ad sites. <a href="http://someonewhocares.org/hosts" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_hphosts', 'hpHosts', 'Inefficient list <a href="http://hosts-file.net" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  //draw_blocklist_row('bl_securemecca', 'Secure Mecca', 'Mixture of Adult, Gambling and Advertising sites <a href="http://securemecca.com/" target="_blank">(securemecca.com)</a>');
  draw_blocklist_row('bl_winhelp2002', 'MVPS Hosts‎', 'Very inefficient list <a href="http://winhelp2002.mvps.org/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  echo '</table></div></div>'.PHP_EOL;
  
  //Region Specific
  draw_systable('Region Specific');
  draw_blocklist_row('bl_fblatin', 'Latin EasyList', 'Spanish/Portuguese Adblock List <a href="https://www.fanboy.co.nz/regional.html" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_areasy', 'AR EasyList', 'عربي EasyList (Arab) ‎ <a href="https://forums.lanik.us/viewforum.php?f=98" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_chneasy', 'CHN EasyList', '中文 EasyList (China)‎ <a href="http://abpchina.org/forum/forum.php" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_yhosts', 'CHN Yhosts', 'YHosts 中文‎ focused on Chinese advert sites (China) <a href="https://github.com/vokins/yhosts" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');

  draw_blocklist_row('bl_deueasy', 'DEU EasyList', 'Deutschland EasyList (Germany) <a href="https://forums.lanik.us/viewforum.php?f=90" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_dnkeasy', 'DNK EasyList', 'Danmark Schacks Adblock Plus liste‎ (Denmark) <a href="https://henrik.schack.dk/adblock/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');  
  draw_blocklist_row('bl_fraeasy', 'FRA EasyList', 'France EasyList <a href="https://forums.lanik.us/viewforum.php?f=91" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_grceasy', 'GRC EasyList', 'Ελλάδα EasyList (Greece) <a href="https://github.com/kargig/greek-adblockplus-filter" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_huneasy', 'HUN hufilter', 'Magyar Adblock szűrőlista (Hungary) <a href="https://github.com/szpeter80/hufilter" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_idneasy', 'IDN EasyList', 'ABPindo (Indonesia) <a href="https://github.com/ABPindo/indonesianadblockrules" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_isleasy', 'ISL EasyList', 'Adblock Plus listi fyrir íslenskar vefsíður (Iceland) <a href="https://adblock.gardar.net" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_itaeasy', 'ITA EasyList', 'Italia EasyList (Italy) <a href="https://forums.lanik.us/viewforum.php?f=96" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_jpneasy', 'JPN EasyList', '日本用フィルタ (Japan) <a href="https://github.com/k2jp/abp-japanese-filters" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_koreasy', 'KOR EasyList', '대한민국 EasyList (Korea) <a href="https://github.com/gfmaster/adblock-korea-contrib" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_korfb', 'KOR Fanboy', '대한민국 Fanboy&rsquo;s list (Korea) <a href="https://forums.lanik.us/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_koryous', 'KOR YousList', '대한민국 YousList (Korea) <a href="https://github.com/yous/YousList" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_ltueasy', 'LTU EasyList', 'Lietuva EasyList (Lithuania) <a href="http://margevicius.lt/easylist_lithuania" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_lvaeasy', 'LVA EasyList', 'Latvija List (Latvia) <a href="https://notabug.org/latvian-list/adblock-latvian" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_nldeasy', 'NLD EasyList', 'Nederland EasyList (Dutch) <a href="https://forums.lanik.us/viewforum.php?f=100" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_poleasy', 'POL EasyList', 'Polskie filtry do Adblocka (Poland) <a href="https://www.certyficate.it/adblock-ublock-polish-filters/" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_ruseasy', 'RUS EasyList', 'Россия RuAdList+EasyList (Russia) <a href="https://forums.lanik.us/viewforum.php?f=102" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_spaeasy', 'SPA EasyList', 'España EasyList (Spain) <a href="https://forums.lanik.us/viewforum.php?f=103" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  draw_blocklist_row('bl_svneasy', 'SVN EasyList', 'Slovenska lista (Slovenia) <a href="https://github.com/betterwebleon/slovenian-list" target="_blank"><img class="btn" alt="Link" src="./svg/icon_home.svg"></a>');
  
  echo '</table></div></div>'.PHP_EOL;
  
  draw_systable('Custom Block Lists');
  draw_sysrow('Custom', '<p>Use either Downloadable or Localy stored Block Lists</p><textarea rows="5" name="bl_custom">'.str_replace(',', PHP_EOL,$Config['bl_custom']).'</textarea>');
  
  echo '</table><br>'.PHP_EOL;
  
  echo '<div class="centered"><input type="submit" class="button-grey" value="Save Changes"></div>'.PHP_EOL;
  echo '</div></div></form>'.PHP_EOL;
  
  return null;
}


/********************************************************************
 *  Show Custom List
 *    Follows on from Black List or White List being loaded
 *
 *  Params:
 *    $view - Current Config Page
 *  Return:
 *    None
 */
function show_custom_list($view) {
  global $list, $searchbox;
  
  echo '<div class="sys-group">'.PHP_EOL;
  echo '<h5>'.ucfirst($view).' List</h5>'.PHP_EOL;  
  echo '<form action="?" method="get">';
  echo '<input type="hidden" name="v" value="'.$view.'">';
  echo '<input type="text" name="s" id="searchbox" value="'.$searchbox.'">&nbsp;&nbsp;';
  echo '<input type="submit" class="button-blue" value="Search">'.PHP_EOL;
  echo '</form>'.PHP_EOL;
  echo '</div>'.PHP_EOL;
  
  echo '<div class="sys-group">';
  echo '<table id="cfg-custom-table">'.PHP_EOL;            //Start custom list table
  $i = 1;

  if ($searchbox == '') {
    foreach ($list as $site) {
      if ($site[2] == true) {
        echo '<tr><td>'.$i.'</td><td>'.$site[0].'</td><td>'.$site[1].'<td><input type="checkbox" name="r'.$i.'" onclick="changeSite(this)" checked="checked"><button class="button-small"  onclick="deleteSite('.$i.')"><span><img src="./images/icon_trash.png" class="btn" alt="-"></span></button></td></tr>'.PHP_EOL;
      }
      else {
        echo '<tr class="dark"><td>'.$i.'</td><td>'.$site[0].'</td><td>'.$site[1].'<td><input type="checkbox" name="r'.$i.'" onclick="changeSite(this)"><button class="button-small"  onclick="deleteSite('.$i.')"><span><img src="./images/icon_trash.png" class="btn" alt="-"></span></button></td></tr>'.PHP_EOL;
      }
      $i++;
    }
  }
  else {
    foreach ($list as $site) {
      if (strpos($site[0], $searchbox) !== false) {
        if ($site[2] == true) {
          echo '<tr><td>'.$i.'</td><td>'.$site[0].'</td><td>'.$site[1].'<td><input type="checkbox" name="r'.$i.'" onclick="changeSite(this)" checked="checked"><button class="button-small"  onclick="deleteSite('.$i.')"><span><img src="./images/icon_trash.png" class="btn" alt="-"></span></button></td></tr>'.PHP_EOL;
        }
        else {
          echo '<tr class="dark"><td>'.$i.'</td><td>'.$site[0].'</td><td>'.$site[1].'<td><input type="checkbox" name="r'.$i.'" onclick="changeSite(this)"><button class="button-small"  onclick="deleteSite('.$i.')"><span><img src="./images/icon_trash.png" class="btn" alt="-"></span></button></td></tr>'.PHP_EOL;
        }
      }
      $i++;
    }
  }
  
  echo '<tr><td>'.$i.'</td><td><input type="text" class="ninty" name="site'.$i.'" placeholder="site.com"></td><td>';   //Add new site row
  echo '<input type="text" class="ninty" name="comment'.$i.'" placeholder="comment"></td>';
  echo '<td><button class="button-grey" onclick="addSite('.$i.')">Save</button></td></tr>';                            //End add new site row
        
  echo '</table>'.PHP_EOL;                                 //End custom list table
  
  echo '<div class="centered"><br>'.PHP_EOL;  
  echo '<a href="?v='.$view.'&amp;action='.$view.'&amp;do=update" class="button-blue">Update Blocklists</a>&nbsp;&nbsp;';
  echo '<a href="./include/downloadlist.php?v='.$view.'" class="button-grey">Download List</a>';
  echo '</div></div>'.PHP_EOL;  
}


/********************************************************************
 *  Load DHCP Values from SQL
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function load_dhcp() {
  global $DHCPConfig, $db;
  
  $query = "SELECT * FROM config WHERE config_type = 'dhcp'";
  $DHCPConfig['static_hosts'] = '';
  
  if (table_exists('config')) {
    if (count_rows("SELECT COUNT(*) FROM config WHERE config_type = 'dhcp'") == 0) {
      exec(NTRK_EXEC.'--read dhcp');
    }    
    if (count_rows("SELECT COUNT(*) FROM config WHERE config_type = 'dnsmasq'") == 0) {
      exec(NTRK_EXEC.'--read dnsmasq');
    }    
  }
  else {
    exec(NTRK_EXEC.'--read dhcp');
    exec(NTRK_EXEC.'--read dnsmasq');
  }
  
  if(!$result = $db->query($query)) {            //Run the Query
    die('There was an error running the query'.$db->error);
  }
  
  while($row = $result->fetch_assoc()) {         //Read each row of results
    switch($row['option_name']) {
      case 'dhcp-host':
        $DHCPConfig['static_hosts'] .= $row['option_value'].PHP_EOL;
        break;
      case 'dhcp_enabled':
      case 'dhcp-authoritative':
      case 'log-dhcp':
        $DHCPConfig[$row['option_name']] = $row['option_enabled'];
        break;
      default:
        $DHCPConfig[$row['option_name']] = $row['option_value'];
        break;
    }    
  }
  
  $result->free();
}
/********************************************************************
 *  Show Full Block List
 *    1: DHCPConfig has been loaded from SQL table into Array
 *    2: Draw form
 * 
 *  Params:
 *    None
 *  Return:
 *    None
 */
function show_dhcp() {
  global $DHCPConfig;
    
  echo '<form method="POST">'.PHP_EOL;
  echo '<input type="hidden" name="action" value="dhcp">';
  
  draw_systable('<strike>DHCP</strike> Work in progress');
  draw_sysrow('Enabled', '<input type="checkbox" name="enabled" '.is_checked($DHCPConfig['dhcp_enabled']).'>');
  draw_sysrow('Gateway IP', '<input type="text" name="router_ip" value="'.$DHCPConfig['router_ip'].'"><p>Usually the IP address of your Router</p>');
  draw_sysrow('Range - Start IP', '<input type="text" name="start_ip" value="'.$DHCPConfig['start_ip'].'">');
  draw_sysrow('Range - End IP', '<input type="text" name="end_ip" value="'.$DHCPConfig['end_ip'].'">');
  draw_sysrow('Authoritative', '<input type="checkbox" name="authoritative"'.is_checked($DHCPConfig['dhcp-authoritative']).'><p>Set the DHCP server to authoritative mode. In this mode it will barge in and take over the lease for any client which broadcasts on the network. This avoids long timeouts
  when a machine wakes up on a new network. http://www.isc.org/files/auth.html</p>');
  echo '<tr><td>Static Hosts:</td><td><p><code>System.name,MAC Address,IP to allocate</code><br>e.g. <code>nas.local,11:22:33:aa:bb:cc,192.168.0.5</code></p>';
  echo '<textarea rows="10" name="static">'.$DHCPConfig['static_hosts'].'</textarea></td></tr>'.PHP_EOL;
  echo '<tr><td colspan="2"><div class="centered"><input type="submit" class="button-blue" value="Save Changes">&nbsp;<input type="reset" class="button-blue" value="Reset"></div></td></tr>'.PHP_EOL;
  echo '</table></div>'.PHP_EOL;
  echo '</div></form>'.PHP_EOL;
}
 

/********************************************************************
 *  Show Domain List
 *    1. Load Users Domain Black list and convert into associative array
 *    2. Load Users Domain White list and convert into associative array
 *    3. Display list
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function show_domain_list() {
  global $list, $FileTLDBlackList, $FileTLDWhiteList;
    
  $KeyBlack = array_flip(load_list($FileTLDBlackList, 'TLDBlackList'));
  $KeyWhite = array_flip(load_list($FileTLDWhiteList, 'TLDWhiteList'));
  $listsize = count($list);
  
  if ($list[$listsize-1][0] == '') {             //Last line is sometimes blank
    array_splice($list, $listsize-1);            //Cut last line out
  }

  $flag_image = '';  
  $flag_filename = '';

  echo '<div class="sys-group"><div class="sys-title">'.PHP_EOL;
  echo '<h5>Domain Blocking</h5>'.PHP_EOL;
  echo '</div>'.PHP_EOL;
  echo '<div class="sys-items">'.PHP_EOL;
  echo '<span class="key key-red">High</span>'.PHP_EOL;
  echo '<p>High risk domains are home to a high percentage of malicious sites compared to legitimate sites. Often they are cheap / free to buy and are not well policed.<br>'.PHP_EOL;
  echo 'High risk domains are automatically blocked, unless you specifically untick them.</p>'.PHP_EOL;
  echo '<br>'.PHP_EOL;

  echo '<span class="key key-orange">Medium</span>'.PHP_EOL;
  echo '<p>Medium risk domains are home to a significant number of malicious sites, but are outnumbered by legitimate sites. You may want to consider blocking these, unless you live in, or utilise the websites of the affected country.</p>'.PHP_EOL;  
  echo '<br>'.PHP_EOL;

  echo '<span class="key">Low</span>'.PHP_EOL;
  echo '<p>Low risk may still house some malicious sites, but they are vastly outnumbered by legitimate sites.</p>'.PHP_EOL;
  echo '<br>'.PHP_EOL;

  echo '<span class="key key-green">Negligible</span>'.PHP_EOL;
  echo '<p>These domains are not open to the public, therefore extremely unlikely to contain malicious sites.</p>'.PHP_EOL;
  echo '<br>'.PHP_EOL;

  echo '</div></div>'.PHP_EOL;

  //Tables
  echo '<div class="sys-group">'.PHP_EOL;
  if ($listsize == 0) {                          //Is List blank?
    echo 'No sites found in Block List'.PHP_EOL; //Yes, display error, then leave
    echo '</div>';
    return;
  }

  echo '<form name="tld" action="?" method="post">'.PHP_EOL;
  echo '<input type="hidden" name="action" value="tld">'.PHP_EOL;

  echo '<p><b>Old Generic Domains</b></p>'.PHP_EOL;
  echo '<table class="tld-table">'.PHP_EOL;                //Start TLD Table

  foreach ($list as $site) {
    if ($site[2] == 0) {                                   //Zero means draw new table
      echo '</table>'.PHP_EOL;                             //End current TLD table
      echo '<br>'.PHP_EOL;
      echo '<p><b>'.$site[1].'</b></p>'.PHP_EOL;           //Title of new TLD Table
      echo '<table class="tld-table">'.PHP_EOL;            //Start new TLD Table
      continue;                                            //Jump to end of loop
    }

    switch ($site[2]) {                                    //Row colour based on risk
      case 1: echo '<tr class="invalid">'; break;
      case 2: echo '<tr class="orange">'; break;
      case 3: echo '<tr>'; break;                //Use default colour for low risk
      case 5: echo '<tr class="green">'; break;
    }

    //Flag names are seperated by underscore and converted to ASCII, dropping any UTF-8 Characters
    $flag_filename = iconv('UTF-8', 'ASCII//IGNORE', str_replace(' ', '_', $site[1])); 

    //Does a Flag image exist?
    if (file_exists('./images/flags/Flag_of_'.$flag_filename.'.png')) {
      $flag_image = '<img src="./images/flags/Flag_of_'.$flag_filename.'.png" alt=""> ';
    }
    else {
      $flag_image = '';
      //$flag_image = iconv('UTF-8', 'ASCII//IGNORE', $flag_filename); Debugging UTF-8 filenames
    }

    //(Risk 1 & NOT in White List) OR (in Black List)
    if ((($site[2] == 1) && (! array_key_exists($site[0], $KeyWhite))) || (array_key_exists($site[0], $KeyBlack))) {
      echo '<td><b>'.$site[0].'</b></td><td><b>'.$flag_image.$site[1].'</b></td><td>'.$site[3].'</td><td><input type="checkbox" name="'.substr($site[0], 1).'" checked="checked"></td></tr>'.PHP_EOL;
    }
    else {
      echo '<td>'.$site[0].'</td><td>'.$flag_image.$site[1].'</td><td>'.$site[3].'</td><td><input type="checkbox" name="'.substr($site[0], 1).'"></td></tr>'.PHP_EOL;
    }
  }

  echo '</table>'.PHP_EOL;                                 //End TLD table
  echo '<div class="centered"><br>'.PHP_EOL;
  echo '<input type="submit" class="button-grey" value="Save Changes">'.PHP_EOL;
  echo '</div>'.PHP_EOL;
  echo '</form></div>'.PHP_EOL;                            //End Form

  return null;
}


/********************************************************************
 *  Show Full Block List
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function show_full_blocklist() {
  global $db, $page, $searchbox, $blradio, $showblradio;
  global $BLOCKLISTNAMES;
  
  $key = '';
  $value ='';
  $rows = 0;
  $row_class = '';
  $bl_source = '';
  $linkstr = '';
  $i = 0;
  
  echo '<div class="sys-group">'.PHP_EOL;
  echo '<h5>Sites Blocked</h5>'.PHP_EOL;
    
  $rows = count_rows('SELECT COUNT(*) FROM blocklist'.add_searches());
    
  if ((($page-1) * ROWSPERPAGE) > $rows) $page = 1;
  $i = (($page-1) * ROWSPERPAGE) + 1;                      //Calculate count position
    
  $query = 'SELECT * FROM blocklist '.add_searches().'ORDER BY id LIMIT '.ROWSPERPAGE.' OFFSET '.(($page-1) * ROWSPERPAGE);
  
  if(!$result = $db->query($query)){                       //Run the Query
    die('There was an error running the query'.$db->error);
  }
  
  draw_blradioform();                                      //Block List selector form
  
  echo '<form method="GET">'.PHP_EOL;                      //Form for Text Search
  echo '<input type="hidden" name="page" value="'.$page.'">'.PHP_EOL;
  echo '<input type="hidden" name="v" value="full">'.PHP_EOL;
  echo '<input type="hidden" name="blrad" value="'.$blradio.'">'.PHP_EOL;
  echo '<input type="text" name="s" id="search" value="'.$searchbox.'">&nbsp;&nbsp;';
  echo '<input type="Submit" class="button-blue" value="Search">'.PHP_EOL;
  echo '</form></div>'.PHP_EOL;                            //End form for Text Search
  
  
  if ($result->num_rows == 0) {                            //Leave if nothing found
    $result->free();
    echo 'No sites found in Block List';
    return false;
  }
  
  if ($showblradio) {                                      //Add selected blocklist to pagination link string
    $linkstr .= '&amp;blrad='.$blradio;
  }  
  
  echo '<div class="sys-group">';                          //Now for the results
  
  pagination($rows, 'v=full'.$linkstr);                    //Draw Pagination box
    
  echo '<table id="block-table">'.PHP_EOL;
  echo '<tr><th>#</th><th>Block List</th><th>Site</th><th>Comment</th></tr>'.PHP_EOL;
   
  while($row = $result->fetch_assoc()) {                   //Read each row of results
    if ($row['site_status'] == 0) {                        //Is site enabled or disabled?
      $row_class = ' class="dark"';
    }
    else {
      $row_class = '';
    }
    
    if (array_key_exists($row['bl_source'], $BLOCKLISTNAMES)) { //Convert bl_name to Actual Name
      $bl_source = $BLOCKLISTNAMES[$row['bl_source']];
    }
    else {
      $bl_source = $row['bl_source'];
    }
    echo '<tr'.$row_class.'><td>'.$i.'</td><td>'.$bl_source.'</td><td>'.$row['site'].'</td><td>'.$row['comment'].'</td></tr>'.PHP_EOL;
    $i++;
  }
  echo '</table>'.PHP_EOL;                                 //End of table
  
  echo '<br>'.PHP_EOL;
  pagination($rows, 'v=full'.$linkstr);                    //Draw second Pagination box
  echo '</div>'.PHP_EOL; 
  
  $result->free();

  return true;
}


/********************************************************************
 *  Show General View
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function show_general() {
  global $Config, $SEARCHENGINELIST, $WHOISLIST;
  
  $key = '';
  $value = '';
  
  $sysload = sys_getloadavg();
  $freemem = preg_split('/\s+/', exec('free -m | grep Mem'));

  $pid_dnsmasq = preg_split('/\s+/', exec('ps -eo fname,pid,stime,pmem | grep dnsmasq'));

  $pid_lighttpd = preg_split('/\s+/', exec('ps -eo fname,pid,stime,pmem | grep lighttpd'));

  $Uptime = explode(',', exec('uptime'))[0];
  if (preg_match('/\d\d\:\d\d\:\d\d\040up\040/', $Uptime) > 0) $Uptime = substr($Uptime, 13);  //Cut time from string if it exists
  
  draw_systable('Server');
  draw_sysrow('Name', gethostname());
  draw_sysrow('Network Device', $Config['NetDev']);
  if (($Config['IPVersion'] == 'IPv4') || ($Config['IPVersion'] == 'IPv6')) {
    draw_sysrow('Internet Protocol', $Config['IPVersion']);
    draw_sysrow('IP Address', $_SERVER['SERVER_ADDR']);
  }
  else {
    draw_sysrow('IP Address', $Config['IPVersion']);
  }
  
  draw_sysrow('Sysload', $sysload[0].' | '.$sysload[1].' | '.$sysload[2]);
  draw_sysrow('Memory Used', $freemem[2].' MB');
  draw_sysrow('Free Memory', $freemem[3].' MB');
  draw_sysrow('Uptime', $Uptime);
  draw_sysrow('NoTrack Version', VERSION); 
  echo '</table></div></div>'.PHP_EOL;
  
  draw_systable('Dnsmasq');
  if ($pid_dnsmasq[0] != null) draw_sysrow('Status','Dnsmasq is running');
  else draw_sysrow('Status','Inactive');
  draw_sysrow('Pid', $pid_dnsmasq[1]);
  draw_sysrow('Started On', $pid_dnsmasq[2]);
  //draw_sysrow('Cpu', $pid_dnsmasq[3]);
  draw_sysrow('Memory Used', $pid_dnsmasq[3].' MB');
  draw_sysrow('Historical Logs', count_rows('SELECT COUNT(DISTINCT(DATE(log_time))) FROM historic').' Days');
  draw_sysrow('Delete All History', '<button class="button-danger" onclick="confirmLogDelete();">Purge</button>');
  echo '</table></div></div>'.PHP_EOL;

  
  //Web Server
  echo '<form name="blockmsg" action="?" method="post">';
  echo '<input type="hidden" name="action" value="webserver">';
  draw_systable('Lighttpd');
  if ($pid_lighttpd[0] != null) draw_sysrow('Status','Lighttpd is running');
  else draw_sysrow('Status','Inactive');
  draw_sysrow('Pid', $pid_lighttpd[1]);
  draw_sysrow('Started On', $pid_lighttpd[2]);
  //draw_sysrow('Cpu', $pid_lighttpd[3]);
  draw_sysrow('Memory Used', $pid_lighttpd[3].' MB');
  if ($Config['BlockMessage'] == 'pixel') draw_sysrow('Block Message', '<input type="radio" name="block" value="pixel" checked onclick="document.blockmsg.submit()">1x1 Blank Pixel (default)<br><input type="radio" name="block" value="message" onclick="document.blockmsg.submit()">Message - Blocked by NoTrack<br>');
  else draw_sysrow('Block Message', '<input type="radio" name="block" value="pixel" onclick="document.blockmsg.submit()">1x1 Blank Pixel (default)<br><input type="radio" name="block" value="messge" checked onclick="document.blockmsg.submit()">Message - Blocked by NoTrack<br>');  
  echo '</table></div></div></form>'.PHP_EOL;

  
  //Stats
  echo '<form name="stats" method="post">';
  echo '<input type="hidden" name="action" value="stats">';
  
  draw_systable('Domain Stats');
  echo '<tr><td>Search Engine: </td>'.PHP_EOL;
  echo '<td><select name="search" class="input-conf" onchange="submit()">'.PHP_EOL;
  echo '<option value="'.$Config['Search'].'">'.$Config['Search'].'</option>'.PHP_EOL;
  foreach ($SEARCHENGINELIST as $key => $value) {
    if ($key != $Config['Search']) {
      echo '<option value="'.$key.'">'.$key.'</option>'.PHP_EOL;
    }
  }
  echo '</select></td></tr>'.PHP_EOL;
  
  echo '<tr><td>Who Is Lookup: </td>'.PHP_EOL;
  echo '<td><select name="whois" class="input-conf" onchange="submit()">'.PHP_EOL;
  echo '<option value="'.$Config['WhoIs'].'">'.$Config['WhoIs'].'</option>'.PHP_EOL;
  foreach ($WHOISLIST as $key => $value) {
    if ($key != $Config['WhoIs']) {
      echo '<option value="'.$key.'">'.$key.'</option>'.PHP_EOL;
    }
  }
  echo '</select></td></tr>'.PHP_EOL;
  draw_sysrow('JsonWhois API <a href="https://jsonwhois.com/"><img class="btn" src="./svg/button_help.svg"></a>', '<input type="text" name="whoisapi" class="input-conf" value="'.$Config['whoisapi'].'">');
  echo '</table></div></div></form>'.PHP_EOL;    //End Stats
  
  return null;
}


/********************************************************************
 *  Show Menu
 *    Control panel style menu
 *  Params:
 *    None
 *  Return:
 *    None
 */
function show_menu() {
  echo '<div class="sys-group">'.PHP_EOL;        //System
  echo '<h5>System</h5>'.PHP_EOL;
  echo '<a href="../admin/config.php?v=general"><div class="conf-nav"><img src="./svg/menu_config.svg"><span>General</span></div></a>'.PHP_EOL;
  echo '<a href="../admin/config.php?v=status"><div class="conf-nav"><img src="./svg/menu_status.svg"><span>Back-end Status</span></div></a>'.PHP_EOL;
  echo '<a href="../admin/security.php"><div class="conf-nav"><img src="./svg/menu_security.svg"><span>Security</span></div></a>'.PHP_EOL;
  echo '<a href="../admin/upgrade.php"><div class="conf-nav"><img src="./svg/menu_upgrade.svg"><span>Upgrade</span></div></a>'.PHP_EOL;
  echo '<a href="../admin/config.php?v=dhcp"><div class="conf-nav"><img src="./svg/menu_config.svg"><span>Work in progress</span></div></a>'.PHP_EOL;
  echo '<a href="../admin/config.php?v=dnsmasq"><div class="conf-nav"><img src="./svg/menu_config.svg"><span>Work in progress</span></div></a>'.PHP_EOL;
  echo '</div>'.PHP_EOL;
  
  echo '<div class="sys-group">'.PHP_EOL;        //Block lists
  echo '<h5>Block Lists</h5>'.PHP_EOL;
  echo '<a href="../admin/config.php?v=blocks"><div class="conf-nav"><img src="./svg/menu_blocklists.svg"><span>Select Block Lists</span></div></a>'.PHP_EOL;
  echo '<a href="../admin/config.php?v=tld"><div class="conf-nav"><img src="./svg/menu_domain.svg"><span>Top Level Domains</span></div></a>'.PHP_EOL;
  echo '<a href="../admin/config.php?v=black"><div class="conf-nav"><img src="./svg/menu_white.svg"><span>Custom Black List</span></div></a>'.PHP_EOL;
  echo '<a href="../admin/config.php?v=white"><div class="conf-nav"><img src="./svg/menu_black.svg"><span>Custom White List</span></div></a>'.PHP_EOL;
  echo '<a href="../admin/config.php?v=full"><div class="conf-nav"><img src="./svg/menu_sites.svg"><span>View Sites Blocked</span></div></a>'.PHP_EOL;
  echo '</div>'.PHP_EOL;
  
  echo '<div class="sys-group">'.PHP_EOL;        //Advanced
  echo '<h5>Advanced</h5>'.PHP_EOL;
  echo '<a href="../admin/config.php?v=advanced"><div class="conf-nav"><img src="./svg/menu_advanced.svg"><span>Advanced Options</span></div></a>'.PHP_EOL;
  echo '</div>'.PHP_EOL;
}

  
/********************************************************************
 *  Show Back End Status
 *    Display output of notrack --test
 *  Params:
 *    None
 *  Return:
 *    None
 */
function show_status() {
  echo '<pre>'.PHP_EOL;
  system('/usr/local/sbin/notrack --test');
  echo '</pre>'.PHP_EOL;
}

?>
