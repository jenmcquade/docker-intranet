/********************************************************************
 *  Change Status
 *    Update pause-timer, pause-button, menu-side-blocking based on new status value
 *    TODO Home-Nav box to update
 *  Params:
 *    New Status (output from API $Config['status'])
 *  Return:
 *    None
 */
function changeStatus(newstatus) {
  const STATUS_ENABLED = 1;
  const STATUS_DISABLED = 2;
  const STATUS_PAUSED = 4;
  
  if (newstatus & STATUS_ENABLED) {
    document.getElementById("pause-timer").textContent = "";
    document.getElementById("pause-button").textContent = "\u2161";
    document.getElementById("pause-button").title = "Disable Blocking";
    document.getElementById("menu-side-blocking").innerHTML = "<img src='./svg/status_green.svg' alt=''>Blocking: Enabled";
  }
  
  else if (newstatus & STATUS_DISABLED) {
    document.getElementById("pause-timer").textContent = "----";
    document.getElementById("pause-button").textContent = "\u25b6";
    document.getElementById("pause-button").title = "Enable Blocking";
    document.getElementById("menu-side-blocking").innerHTML = "<img src='./svg/status_red.svg' alt=''>Blocking: Disabled";
  }
  
  else if (newstatus & STATUS_PAUSED) {
    document.getElementById("pause-button").textContent = "\u25b6";
    document.getElementById("pause-button").title = "Enable Blocking";
    document.getElementById("menu-side-blocking").innerHTML = "<img src='./svg/status_yellow.svg' alt=''>Blocking: Paused";
  }
}

/********************************************************************
 *  Menu incognito
 *    POST Incognito operation to API
 *    API returns new status value
 *    Set incognito elements to purple colours
 *  Params:
 *    None
 *  Return:
 *    None
 */

function menuIncognito() {
  var oReq = new XMLHttpRequest();
  var url = "./include/api.php";
  var params = "operation=incognito";
  
  oReq.open("POST", url, true);
  oReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  /*oReq.onload = function () {
    // do something to response
    console.log(this.responseText);
  };*/
  oReq.onreadystatechange = function() {
    if(this.readyState == 4 && this.status == 200) {
      var apiResponse = JSON.parse(this.responseText);
      if (apiResponse["status"] & 8) {                     //Bitwise check for STATUS_INCOGNITO
        //Change incognito elements to purple colour
        document.getElementById("incognito-button").src = "./svg/menu_incognito_active.svg";
        document.getElementById("incognito-text").classList.add("purple");
      }
      else {
        //Turning incognito off, change incognito elements back to grey
        document.getElementById("incognito-button").src = "./svg/menu_incognito.svg";
        document.getElementById("incognito-text").classList.remove("purple");
      }
    }
  }
  oReq.send(params);
}


/********************************************************************
 *  Enable NoTrack
 *    POST enable operation to API
 *    API returns new status value
 *    This is the same function for Enable and Disable. 
 *    Let the API work out what the request is meant to be.
 *  Params:
 *    None
 *  Return:
 *    None
 */

function enableNoTrack() {
  var oReq = new XMLHttpRequest();
  var url = "./include/api.php";
  var params = "operation=enable";
  
  oReq.open("POST", url, true);
  oReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  
  oReq.onreadystatechange = function() {
    if(this.readyState == 4 && this.status == 200) {
      var apiResponse = JSON.parse(this.responseText);
      changeStatus(apiResponse["status"]);
    }
  }
  oReq.send(params);
}


/********************************************************************
 *  Enable NoTrack
 *    POST enable operation to API
 *    API returns new status value
 *    This is the same function for Enable and Disable. 
 *    Let the API work out what the request is meant to be.
 *  Params:
 *    mins to pause for
 *  Return:
 *    None
 */

function pauseNoTrack(mins) {
  var oReq = new XMLHttpRequest();
  var url = "./include/api.php";
  var params = "operation=pause&mins="+mins;
  
  oReq.open("POST", url, true);
  oReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  
  oReq.onreadystatechange = function() {
    if(this.readyState == 4 && this.status == 200) {
      var apiResponse = JSON.parse(this.responseText);
      changeStatus(apiResponse["status"]);
      document.getElementById("pause-timer").textContent = apiResponse["unpausetime"];
    }
  }
  oReq.send(params);
}


/********************************************************************
 *  Restart System
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */

function restartSystem() {
  var oReq = new XMLHttpRequest();
  var url = "./include/api.php";
  var params = "operation=restart";
  
  document.getElementById("options-box").style.display = "none";
  
  oReq.open("POST", url, true);
  oReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  
  /*oReq.onreadystatechange = function() {
    if(this.readyState == 4 && this.status == 200) {
      var apiResponse = JSON.parse(this.responseText);
      document.getElementById("menu-top-logo").textContent = "Updating Blocklists";
    }
  }*/
  oReq.send(params);
}


/********************************************************************
 *  Shutdown System
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */

function shutdownSystem() {
  var oReq = new XMLHttpRequest();
  var url = "./include/api.php";
  var params = "operation=shutdown";
  
  document.getElementById("options-box").style.display = "none";
  
  oReq.open("POST", url, true);
  oReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  
  /*oReq.onreadystatechange = function() {
    if(this.readyState == 4 && this.status == 200) {
      var apiResponse = JSON.parse(this.responseText);
      document.getElementById("menu-top-logo").textContent = "Updating Blocklists";
    }
  }*/
  oReq.send(params);
}


/********************************************************************
 *  Update Blocklists
 *
 *  Params:
 *    None
 *  Return:
 *    None
 */

function updateBlocklist() {
  var oReq = new XMLHttpRequest();
  var url = "./include/api.php";
  var params = "operation=updateblocklist";
  
  oReq.open("POST", url, true);
  oReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  
  oReq.onreadystatechange = function() {
    if(this.readyState == 4 && this.status == 200) {
      var apiResponse = JSON.parse(this.responseText);
      document.getElementById("menu-top-logo").textContent = "Updating Blocklists";
      //TODO Show countdown ... then revert to original logo
    }
  }
  oReq.send(params);
  
  hideOptions();
}


/********************************************************************
 *  Show Options Box
 *    Set top position of fade, then set display as block
 *    Set screen centre position of options box, then set display as block
 *  Params:
 *    None
 *  Return:
 *    None
 */
function showOptions() {
  document.getElementById("fade").style.top=window.pageYOffset+"px";
  document.getElementById("fade").style.display = "block";
    
  document.getElementById("options-box").style.top = (window.pageYOffset + (window.innerHeight / 2))+"px";
  document.getElementById("options-box").style.left = (window.innerWidth / 2)+"px";
  document.getElementById("options-box").style.display = "block";
}


/********************************************************************
 *  Hide Options Box
 *    Check if stats-box is visible, then hide
 *    Hide options-box
 *    Hide fade
 *  Params:
 *    None
 *  Return:
 *    None
 */
function hideOptions() {
  if (document.getElementById("stats-box")) {
    document.getElementById("stats-box").style.display = "none";
  }

  document.getElementById("options-box").style.display = "none";
  document.getElementById("fade").style.display = "none";
}


/********************************************************************
 *  Open Side Menu
 *    Check width of #menu-side
 *    Expand to 14rem if zero
 *    Reduce to zero for any other size
 *  Params:
 *    None
 *  Return:
 *    None
 */

function openNav() {
  if (document.getElementById("menu-side").style.width == "0rem" || document.getElementById("menu-side").style.width == "") {
    document.getElementById("menu-side").style.width = "14rem";
    document.getElementById("main").style.marginLeft = "14rem";
  }
  else {
    document.getElementById("menu-side").style.width = "0rem";
    document.getElementById("main").style.marginLeft= "0rem"; 
  }  
}
