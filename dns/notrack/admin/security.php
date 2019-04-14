<?php
require('./include/global-vars.php');
require('./include/global-functions.php');
require('./include/menu.php');

load_config();
ensure_active_session();

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
  <title>NoTrack - Security</title>  
</head>

<body>
<?php
draw_topmenu('Config');
draw_sidemenu();
echo '<div id="main">';

/************************************************
*Constants                                      *
************************************************/
define ('DEF_DELAY', 30);

/********************************************************************
 *  Disable Password Protection
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function disable_password_protection() {
  global $Config;
  
  $Config['Username'] = '';
  $Config['Password'] = '';
  save_config();  
}

/********************************************************************
 *  Change Password Form
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function change_password_form() {
  global $Config;
  
  echo '<form name="security" method="post">'.PHP_EOL;
  echo '<input type="hidden" name="change_password">'.PHP_EOL;
  echo '<table class="sys-table">'.PHP_EOL;
  
  draw_sysrow('Old Password', '<input type="password" name="old_password" id="password" placeholder="Old Password">');
  
  draw_sysrow('New Password', '<input type="password" name="password" id="password" placeholder="Password" onkeyup="checkPassword();" required>');
  draw_sysrow('Confirm Password', '<input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" onkeyup="checkPassword();">');
  
  echo '<tr><td colspan="2"><div class="centered"><input type="submit" class="button-blue" value="Change Password"></div></td></tr>';
  
  echo '</table></form>'.PHP_EOL;
  
  echo '<table class="sys-table">'.PHP_EOL;
  echo '<tr><td colspan="2"><div class="centered"><form method="post"><input type="hidden" name="disable_password"><input type="submit" class="button-danger" value="Turn off password protection"></form></div></td></tr>'.PHP_EOL;
  echo '</table>'.PHP_EOL;
}

/********************************************************************
 *  New Password Input Form
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */
function new_password_input_form() {
  global $Config;
  
  echo '<form name="security" method="post">';
  echo '<table class="sys-table">'.PHP_EOL;
    
  draw_sysrow('NoTrack Username', '<input type="text" name="username" value="'.$Config['Username'].'" placeholder="Username"><p><i>Optional authentication username</i></p>');
  
  draw_sysrow('NoTrack Password', '<input type="password" name="password" id="password" placeholder="Password" onkeyup="checkPassword();" required><p><i>Authentication password</i></p>');
  draw_sysrow('Confirm Password', '<input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" onkeyup="checkPassword();">');
  
  draw_sysrow('Delay', '<input type="number" class="input-conf" name="delay" min="5" max="2400" value="'.$Config['Delay'].'"><p><i>Delay in seconds between attempts</i></p>');
  echo '<tr><td colspan="2"><div class="centered"><input type="submit" class="button-blue" value="Save Changes"></div></td></tr>';
  echo '</table></form>'.PHP_EOL;
}

/********************************************************************
 *  Update Password Config
 *
 *  Params:
 *    Username, either from POST or Existing
 *  Return:
 *    true on success, false on fail
 */
function update_password_config($username) {
  global $Config, $message;
  
  $confirm_password = '';
  $password = $_POST['password'];
  if (isset($_POST['confirm_password'])) $confirm_password = $_POST['confirm_password'];
  
  
  //Is username valid?
  if (preg_match('/[!\"£\$%\^&\*\(\)\[\]+=<>:\,\|\/\\\\]/', $username) != 0) {
    $message = 'Invalid Username';
    return false;
  }
  
  if ($password != $confirm_password) {                              //Does validate password match?
    $message = 'Passwords don\'t match';
    return false;
  }
  
  if (($username == '') && ($password == '')) {                      //Removing password
    $Config['Username'] = '';
    $Config['Password'] = '';
  }
  else {  
    $Config['Username'] = $username;
    if (function_exists('password_hash')) {                          //Newer version of PHP with password_hash function
      $Config['Password'] = password_hash($password, PASSWORD_DEFAULT);
    }
    else {                                                           //Fallback for older versions of PHP 
      $Config['Password'] = hash('sha256', $password);
    }
    
    if (isset($_POST['delay'])) {                                    //Set Delay
      $Config['Delay'] = filter_integer($_POST['delay'], 5, 2401, DEF_DELAY);
    }
    else {                                                           //Fallback if Delay not posted
      $Config['Delay'] = DEF_DELAY;
    }
  }
  
  return true;
}

/********************************************************************
 *  Validate Old Password
 *
 *  Params:
 *    None
 *  Return:
 *    true on success, false on fail
 */
 
function validate_oldpassword() {
  global $Config;
  if (! isset($_POST['old_password'])) return false;                 //Has old password been entered?
  
  if (function_exists('password_hash')) {                            //Is PHP version new enough?
    //Use built in password_verify function to compare with $Config['Password'] hash
    if (password_verify($_POST['old_password'], $Config['Password'])) {
      return true;
    }
  }
  else {                                                             //Fallback to SHA256 for older versions of PHP
    if (hash('sha256', $_POST['old_password']) == $Config['Password']) {
      return true;
    }  
  }
  
  return false;
}
//-------------------------------------------------------------------
$show_password_input_form = false;
$show_button_on = true;
$message = '';

if (isset($_POST['enable_password'])) {
  $show_password_input_form = true;
  $show_button_on = false;
}
elseif (isset($_POST['change_password']) && (isset($_POST['password']))) {
  if (validate_oldpassword()) {
    if (update_password_config($Config['Username'])) {
      save_config();
      $message = 'Password Changed';
    }
  }
  else {
    $message = 'Old password invalid';
  }
  $show_button_on = false;
}
elseif (isset($_POST['disable_password'])) {
  disable_password_protection();
  $show_password_input_form = false;
  $message = 'Password Protection Removed';
  if (session_status() == PHP_SESSION_ACTIVE) session_destroy();
}
elseif ((isset($_POST['username']) && (isset($_POST['password'])))) {
  if (update_password_config($_POST['username'])) {
    save_config();
    if (session_status() == PHP_SESSION_ACTIVE) session_destroy();   //Force logout
    $message = 'Password Protection Enabled';
    $show_button_on = false;
  }  
}

echo '<div class="sys-group"><div class="sys-title">'.PHP_EOL;
echo '<h5>Security&nbsp;<a href="./help.php?p=security"><img class="btn" src="./svg/button_help.svg" alt="help"></a></h5></div>'.PHP_EOL;
echo '<div class="sys-items">'.PHP_EOL;

if (is_password_protection_enabled()) {
  change_password_form();
  
  $show_password_input_form = false;
  $show_button_on = false;
}

if ($show_button_on) {
  echo '<form method="post"><input type="hidden" name="enable_password"><input type="submit" class="button-blue" value="Turn on password protection"></form>'.PHP_EOL;
}

if ($show_password_input_form) { 
  new_password_input_form();
}

      
if ($message != '') {
  echo '<br>'.PHP_EOL;
  echo '<h3>'.$message.'</h3>'.PHP_EOL;
}
  
echo '</div></div>'.PHP_EOL;
echo '</div>'.PHP_EOL;
?>

<script>
function checkPassword() {
  if (document.getElementById('password').value == document.getElementById('confirm_password').value) {
    document.getElementById('confirm_password').style.background='#00BB00';
  } 
  else {
    document.getElementById('confirm_password').style.background='#B1244A';
  }
}
</script>
</body>
</html>
