<?php
require('./global-vars.php');
require('./global-functions.php');
load_config();
ensure_active_session();

header('Content-Type: application/json; charset=UTF-8');

/************************************************
*Global Variables                               *
************************************************/
$response = array();


/********************************************************************
 *  Enable NoTrack
 *    Enable or Disable NoTrack Blocking
 *    Calls NTRK_EXEC with Play or Stop based on current status
 *    Race condition isn't being prevented, however we are assuming the user can't get config to load quicker than ntrk-pause can change the file
 *     
 *  Params:
 *    None
 *  Return:
 *    None
 */
function api_enable_notrack() {
  global $Config, $mem, $response;

  if ($Config['status'] & STATUS_ENABLED) {
    exec(NTRK_EXEC.'-s');
    $Config['status'] -= STATUS_ENABLED;
    $Config['status'] += STATUS_DISABLED;
  }
  elseif ($Config['status'] & STATUS_PAUSED) {
    exec(NTRK_EXEC.'-p');
    $Config['status'] -= STATUS_PAUSED;
    $Config['status'] += STATUS_ENABLED;
  }
  elseif ($Config['status'] & STATUS_DISABLED) {
    exec(NTRK_EXEC.'-p');
    $Config['status'] -= STATUS_DISABLED;
    $Config['status'] += STATUS_ENABLED;
  }
  //sleep(1);                                  //Prevent race condition
  $mem->delete('Config');                      //Force reload of config
  //load_config();
  $response['status'] = $Config['status'];
}


/********************************************************************
 *  Pause NoTrack
 *    Pause NoTrack with time parsed in POST mins
 *
 *  Params:
 *    None
 *  Return:
 *    false on error
 *    true on success
 */
function api_pause_notrack() {
  global $Config, $mem, $response;
  
  $mins = 0;

  if (! isset($_POST['mins'])) {
    $response['error'] = 'api_pause_notrack: Mins not specified';
    return false;
  }
  
  $mins = filter_integer($_POST['mins'], 1, 1440, 5);      //1440 = 24 hours in mins
  
  exec(NTRK_EXEC.'--pause '.$mins);
  
  if ($Config['status'] & STATUS_INCOGNITO) {
    $Config['status'] = STATUS_INCOGNITO + STATUS_PAUSED;
  }
  else {
    $Config['status'] = STATUS_PAUSED;    
  }
  //sleep(1);
  $mem->delete('Config');                      //Force reload of config
  //load_config();
  $response['status'] = $Config['status'];
  $response['unpausetime'] = date('H:i', (time() + ($mins * 60)));
  
  return true;
}


/********************************************************************
 *  API Incognito
 *    Switch incognito status based on bitwise value of Config[status]
 *  Params:
 *    None
 *  Return:
 *    None
 */
function api_incognito() {
  global $Config, $response;
  
  if ($Config['status'] & STATUS_INCOGNITO) $Config['status'] -= STATUS_INCOGNITO;
  else $Config['status'] += STATUS_INCOGNITO;
  $response['status'] = $Config['status'];
  
  save_config();
}


/********************************************************************
 *  API Restart
 *    Restart the system
 *    Delay execution of the command for a couple of seconds to finish off any disk writes
 *  Params:
 *    None
 *  Return:
 *    None
 */
function api_restart() {
  sleep(2);
  exec(NTRK_EXEC.'--restart');
  exit(0);
}


/********************************************************************
 *  API Shutdown
 *    Shutdown the system
 *    Delay execution of the command for a couple of seconds to finish off any disk writes
 *  Params:
 *    None
 *  Return:
 *    None
 */
function api_shutdown() {
  sleep(2);
  exec(NTRK_EXEC.'--shutdown');
  exit(0);
}


//Main---------------------------------------------------------------

/************************************************
*POST REQUESTS                                  *
************************************************/

if (isset($_POST['operation'])) {
  switch ($_POST['operation']) {
      case 'force-notrack':
        exec(NTRK_EXEC.'--force');
        sleep(3);                                //Prevent race condition
        header("Location: ?");
        break;
      case 'disable': api_enable_notrack(); break;
      case 'enable': api_enable_notrack(); break;
      case 'pause': api_pause_notrack(); break;
      case 'incognito': api_incognito(); break;
      case 'restart': api_restart(); break;
      case 'shutdown': api_shutdown(); break;
      case 'updateblocklist': exec(NTRK_EXEC.'--run-notrack'); break;
  }
}

echo json_encode($response);
?>
