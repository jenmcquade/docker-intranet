<?php
/********************************************************************
 *  Draw Side Menu
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function draw_sidemenu() {
  echo '<nav><div id="menu-side">'.PHP_EOL;
  echo '<ul>'.PHP_EOL;
  echo '<li><span><a href="../admin/"><img src="./svg/smenu_dashboard.svg" alt="" title="Dashboard">Dashboard</a></span></li>'.PHP_EOL;
  echo '<li><span><a href="../admin/queries.php"><img src="./svg/smenu_queries.svg" alt="" title="DNS Queries">DNS Queries</a></span></li>'.PHP_EOL;
  echo '<li><span><a href="../admin/investigate.php"><img src="./svg/smenu_investigate.svg" alt="" title="Investigate">Investigate</a></span></li>'.PHP_EOL;
  echo '<li><span><a href="../admin/blocked.php"><img src="./svg/smenu_blocked.svg" alt="" title="Sites Blocked">Sites Blocked</a></span></li>'.PHP_EOL;
  echo '<li><span><a href="../admin/dhcpleases.php"><img src="./svg/smenu_dhcp.svg" alt="" title="Network">Network</a></span></li>'.PHP_EOL;
  echo '<li><span><a href="../admin/config.php"><img src="./svg/smenu_config.svg" alt="" title="Config">Config</a></span></li>'.PHP_EOL;
  echo '<li><span><a href="../admin/help.php"><img src="./svg/smenu_help.svg" alt="" title="Help">Help</a></span></li>'.PHP_EOL;
  
  echo '</ul>'.PHP_EOL;
  sidemenu_sysstatus();
  
  echo '<span id="menu-side-bottom"><a href="https://quidsup.net/donate" target="_blank"><img src="./svg/smenu_don.svg" alt="Donate" title="Donate"></a></span>'.PHP_EOL;
  echo '</div></nav>'.PHP_EOL;
  echo PHP_EOL;
}


/********************************************************************
 *  Draw Help Menu
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function draw_helpmenu() {
  echo '<nav><div id="menu-side">'.PHP_EOL;
  echo '<ul>'.PHP_EOL;
  echo '<li><span><a href="../admin/"><img src="./svg/smenu_dashboard.svg" alt="" title="Dashboard">Dashboard</a></span></li>'.PHP_EOL;
  echo '<li><span><a href="../admin/help.php"><img src="./svg/smenu_help.svg" alt="">Help</a></span></li>'.PHP_EOL;
  echo '<li><span><a href="../admin/help.php?p=security">Security</a></span></li>'.PHP_EOL;
  echo '<li><span><a href="../admin/help.php?p=position" title="Where To Position NoTrack Device">Positioning Device</a></span></li>'.PHP_EOL;
  
  echo '</ul>'.PHP_EOL;
  echo '</div></nav>'.PHP_EOL;
  echo PHP_EOL;
}


/********************************************************************
 *  Draw Top Menu
 *    mobile-hide class is used to hide button text on mobile sized displays
 *
 *  Params:
 *    Current Page Title (optional)
 *  Return:
 *    None
 */
function draw_topmenu($currentpage='') {
  global $Config, $mem;
  
  echo '<div id="menu-top">'.PHP_EOL;
  echo '<span class="menu-top-item float-left pointer mobile-show" onclick="openNav()">&#9776;</span>'.PHP_EOL;   //Hamburger menu to show #menu-side
  
  if ($currentpage == '') {                                //Display version number when $currentpage has not been set
    echo '<a href="./"><span id="menu-top-logo" class="logo"><b>No</b>Track <small>v'.VERSION.'</small></span></a>'.PHP_EOL;
  }
  else {                                                   //$currentpage set, display that next to NoTrack logo
    echo '<a href="./"><span id="menu-top-logo" class="logo"><b>No</b>Track <small> - '.$currentpage.'</small></span></a>'.PHP_EOL;
  }
  
  if (is_password_protection_enabled()) {                  //Show Logout button if there is a password
    echo '<a href="../admin/logout.php"><span class="menu-top-item float-right"><img src="./svg/menu_logout.svg" alt=""><span class="mobile-hide">Logout</span></span></a>'.PHP_EOL;
  }
  echo '<span class="menu-top-item float-right pointer" onclick="showOptions()"><img src="./svg/menu_option.svg" alt=""><span class="mobile-hide">Options</span></span>'.PHP_EOL;
  
  if ($Config['status'] & STATUS_INCOGNITO) {              //Is Incognito set? Draw purple button and text
    echo '<span class="menu-top-item float-right pointer" onclick="menuIncognito()"><img id="incognito-button" src="./svg/menu_incognito_active.svg" alt=""><span id="incognito-text" class="mobile-hide purple">Incognito</span></span>'.PHP_EOL;
  }
  else {                                                   //No, draw white button and text
    echo '<span class="menu-top-item float-right pointer" onclick="menuIncognito()"><img id="incognito-button" src="./svg/menu_incognito.svg" alt=""><span id="incognito-text" class="mobile-hide">Incognito</span></span>'.PHP_EOL;
  }
  
  //If Status = Paused AND UnpauseTime < Now plus a few seconds then force reload of Config
  if (($Config['status'] & STATUS_PAUSED) && ($Config['unpausetime'] < (time()+10))) {
    $mem->delete('Config');
    load_config();
  }

  echo '<div id="pause">'.PHP_EOL;
  echo '<input type="hidden" name="pause-time" id="pause-time" value="">'.PHP_EOL;
  if ($Config['status'] & STATUS_PAUSED) {
    echo '<span id="pause-timer" class="timer" title="Paused until">'.date('H:i', $Config['unpausetime']).'</span>'.PHP_EOL;
    echo '<span id="pause-button" class="pause-btn pointer" title="Enable Blocking" onclick="enableNoTrack()">&#9654;</span>'.PHP_EOL;
  }
  elseif ($Config['status'] & STATUS_DISABLED) {
    echo '<span id="pause-timer" class="timer" title="NoTrack Disabled">----</span>'.PHP_EOL;
    echo '<span id="pause-button" class="pause-btn pointer" title="Enable Blocking" onclick="enableNoTrack()">&#9654;</span>'.PHP_EOL;
  }
  else {
    echo '<span id="pause-timer"></span>'.PHP_EOL;
    echo '<span id="pause-button" class="pause-btn pointer" title="Disable Blocking" onclick="enableNoTrack()">&#8545;</span>'.PHP_EOL;
  }
  
  //Dropdown menu for default pause times
  echo '<div tabindex="1" id="dropbutton" title="Pause for..."><span class="pointer">&#x25BC;</span>'.PHP_EOL;
  echo '<div id="pause-menu">'.PHP_EOL;  
  echo '<span class="pointer" onclick="pauseNoTrack(5)">Pause for 5 minutes</span>'.PHP_EOL;
  echo '<span class="pointer" onclick="pauseNoTrack(15)">Pause for 15 minutes</span>'.PHP_EOL;
  echo '<span class="pointer" onclick="pauseNoTrack(30)">Pause for 30 minutes</span>'.PHP_EOL;
  echo '<span class="pointer" onclick="pauseNoTrack(60)">Pause for 1 Hour</span>'.PHP_EOL;
  echo '<span class="pointer" onclick="pauseNoTrack(120)">Pause for 2 Hours</span>'.PHP_EOL;
  echo '</div></div>'.PHP_EOL;
  echo '</div></div>'.PHP_EOL;
  //echo '</nav>'.PHP_EOL;

  
  //Dialogs----------------------------------------------------------
  echo '<div id="dialog-box">'.PHP_EOL;
  echo '<div class="dialog-bar">NoTrack</div>'.PHP_EOL;
  echo '<span id="dialogmsg">Doing something</span>'.PHP_EOL;
  echo '<div class="centered"><img src="./images/progress.gif" alt=""></div>'.PHP_EOL;
  echo '</div>'.PHP_EOL;

  //Operations 
  echo '<div id="options-box">'.PHP_EOL;
  echo '<div class="dialog-bar">Options</div>'.PHP_EOL;
  echo '<div class="centered">'.PHP_EOL;
  
  //echo '<form id="operation-form" action="?" method="post">'.PHP_EOL;
  //echo '<input type="hidden" name="operation" id="operation" value="">'.PHP_EOL;
  echo '<span onclick="updateBlocklist()" title="Force Download and Update Blocklist" class="button-grey button-options pointer">Update Blocklist</span>'.PHP_EOL;
  echo '<span onclick="restartSystem()" class="button-grey button-options pointer">Restart System</span>'.PHP_EOL;
  echo '<span onclick="shutdownSystem()" class="button-danger button-options pointer">Shutdown System</span>'.PHP_EOL;
  //echo '</form>'.PHP_EOL;
  
  echo '<div class="close-button"><img src="./svg/button_close.svg" onmouseover="this.src=\'./svg/button_close_over.svg\'" onmouseout="this.src=\'./svg/button_close.svg\'" alt="Close" onclick="hideOptions()"></div>'.PHP_EOL;
  echo '</div></div>'.PHP_EOL;

  echo '<div id="fade" onclick="hideOptions()"></div>'.PHP_EOL;
}


/********************************************************************
 *  Side Menu Status
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function sidemenu_sysstatus() {
  global $Config;
  
  $sysload = sys_getloadavg();
  $freemem = preg_split('/\s+/', exec('free -m | grep Mem'));
  
  $mempercentage = round(($freemem[2]/$freemem[1])*100);

  echo '<div id="menu-side-status">'.PHP_EOL;              //Start menu-side-status
  echo '<div><img src="./svg/status_screen.svg" alt="">System Status</div>';
  
  if ($Config['status'] & STATUS_ENABLED) {
    if (file_exists(NOTRACK_LIST)) {
      echo '<div id="menu-side-blocking"><img src="./svg/status_green.svg" alt="">Blocking: Enabled</div>'.PHP_EOL;
    }
    else {
      if (file_exists(NOTRACK_LIST)) {
        echo '<div id="menu-side-blocking"><img src="./svg/status_red.svg" alt="">Blocklist Missing</div>'.PHP_EOL;
      }
    }
  }
  elseif ($Config['status'] & STATUS_PAUSED) {
    echo '<div id="menu-side-blocking"><img src="./svg/status_yellow.svg" alt="">Blocking: Paused</div>'.PHP_EOL;
  }
  elseif ($Config['status'] & STATUS_DISABLED) {
    echo '<div id="menu-side-blocking"><img src="./svg/status_red.svg" alt="">Blocking: Disabled</div>'.PHP_EOL;
  }
  
  if ($mempercentage > 85) echo '<div><img src="./svg/status_red.svg" alt="">Memory Used: '.$mempercentage.'%</div>'.PHP_EOL;
  elseif ($mempercentage > 60) echo '<div><img src="./svg/status_yellow.svg" alt="">Memory Used: '.$mempercentage.'%</div>'.PHP_EOL;
  else echo '<div><img src="./svg/status_green.svg" alt="">Memory Used: '.$mempercentage.'%</div>'.PHP_EOL;
  
  if ($sysload[0] > 0.85) echo '<div><img src="./svg/status_red.svg" alt="">Load: ', $sysload[0].' | '.$sysload[1].' | '.$sysload[2].'</div>'.PHP_EOL;
  elseif ($sysload[0] > 0.60) echo '<div><img src="./svg/status_yellow.svg" alt="">Load: ', $sysload[0].' | '.$sysload[1].' | '.$sysload[2].'</div>'.PHP_EOL;
  else echo '<div><img src="./svg/status_green.svg" alt="">Load: ', $sysload[0].' | '.$sysload[1].' | '.$sysload[2].'</div>'.PHP_EOL;
  
  echo '</div>'.PHP_EOL;                                   //End menu-side-status
}
