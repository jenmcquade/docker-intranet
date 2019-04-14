//Supporting JavaScript for NoTrack queries.php
function ValidateIPaddress(ipaddress) {
 if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ipaddress)) {
    return true;
  }
  return false;
}
/********************************************************************
 *  Report Site
 *
 *  Params:
 *    site, if blocked, show report button
 *  Return:
 *    None
 */
function reportSite(site, blocked, showreport) {  
  var Msg = "";                                  //Message to show user
  var button1 = "";                               //Block button and message
  var button2 = "";                               //Block button and message with subdomain
  var report = "";                               //report button and message
  var domain = "";
  
  if (/^\*/.test(site)) {                        //Is it a *common site?
    Msg = "<p>Domains starting with * are known to utilise a large number of subdomains</p>";
  }
  else if (! /^[\w\d\-\_]+\.[\w\d\-\.\_]+/.test(site)) {  //Is there a dot . present?
    Msg = "<p>Invalid site</p>";
  }  
  else if (/.*\.akamai\.net$|akamaiedge\.net$/.test(site)) {  //Is it an Akami site?
    Msg = "<p>Akami is a Content Delivery Network (CDN) providing media delivery for a wide range of websites.</p><p>It is more efficient to block the originating website, rather than an Akami subdomain.</p>";
  }
  else if (ValidateIPaddress(site)) {            //Is it an IP Address
    Msg = "<p>Unable to Block IP addresses.<br>You could add it to your Firewall instead</p>";
  }  
  
  else {                                         //Valid site    
    if (showreport) {                            //Show report button?
      if (blocked) {                             //Remove blocked site
        report = 'remove--'+site;
      }
      else {                                     //Report site for blocking
        report = site;
      }
    }
        
    //Is it a single domain with optional double-barrelled tld?
    if (/^[\w\d\-\_]+\.(org\.|co\.|com\.|gov\.)?[\w\d\-\_]+$/.test(site)) {
      if (blocked) {
        button1 = '<p><a class="button-blue" href=" ./config.php?v=white&action=white&do=add&site='+site+'&comment=" target="_blank">Whitelist Domain</a> Add domain to your White List</p>';
      }
      else {
        button1 = '<p><a class="button-blue" href=" ./config.php?v=black&action=black&do=add&site='+site+'&comment=" target="_blank">Block Domain</a> Add domain to your Black List</p>';
      }      
    }
    else {                                       //No, it has one or more sub-domains
      domain = site.match(/[\w\d\-\_]+\.(org\.|co\.|com\.|gov\.)?[\w\d\-\_]+$/)[0];     //Extract domain with optional double-barrelled tld
      if (blocked) {
        button1 = '<p><a class="button-blue" href=" ./config.php?v=white&action=white&do=add&site='+domain+'&comment=" target="_blank">Whitelist Domain</a> Whitelist entire domain '+domain+'</p>';
	      button2 = '<p><a class="button-blue" href=" ./config.php?v=white&action=white&do=add&site='+site+'&comment=" target="_blank">Whitelist  Subdomain</a> Add subdomain to your White List</p>';
      }
      else {
	      button1 = '<p><a class="button-blue" href=" ./config.php?v=black&action=black&do=add&site='+domain+'&comment=" target="_blank">Block Domain</a> Block entire domain '+domain+'</p>';
	      button2 = '<p><a class="button-blue" href=" ./config.php?v=black&action=black&do=add&site='+site+'&comment=" target="_blank">Block Subdomain</a> Add subdomain to your Black List</p>';
      }
    }
  }
  
  //Modify DOM elements depending on whether a string has been set.
  document.getElementById("sitename").innerHTML = "<h2>"+site+"</h2>";
  
  if (Msg == "") {
    document.getElementById("statsmsg").style.display = "none";
    document.getElementById("statsmsg").innerHTML = "";
  }
  else {
    document.getElementById("statsmsg").style.display = "block";
    document.getElementById("statsmsg").innerHTML = Msg;
  }
  
  if (button1 == "") {
    document.getElementById("statsblock1").style.display = "none";
    document.getElementById("statsblock1").innerHTML = "";
  }
  else {
    document.getElementById("statsblock1").style.display = "block";
    document.getElementById("statsblock1").innerHTML = button1;
  }
  
  if (button2 == "") {
    document.getElementById("statsblock2").style.display = "none";
    document.getElementById("statsblock2").innerHTML = "";
  }
  else {
    document.getElementById("statsblock2").style.display = "block";
    document.getElementById("statsblock2").innerHTML = button2;
  }
  
  if (report == "") {
    document.getElementById("statsreport").style.display = "none";    
  }
  else {
    document.getElementById("statsreport").style.display = "block";    
    document.getElementById("siterep").value = report;
  }
  
  //Position Fade and Stats box
  document.getElementById('fade').style.top=window.pageYOffset+"px";
  document.getElementById('fade').style.display = "block";
    
  document.getElementById('stats-box').style.top = (window.pageYOffset + (window.innerHeight / 2))+"px";
  document.getElementById('stats-box').style.left = (window.innerWidth / 2)+"px";
  document.getElementById('stats-box').style.display = "block";  
}
//-------------------------------------------------------------------
function HideStatsBox() {
  document.getElementById('stats-box').style.display = "none";
  document.getElementById('fade').style.display = "none";
}
//-------------------------------------------------------------------
function ScrollToBottom() {  
  window.scrollTo(0, document.body.scrollHeight);  
  //Animated http://jsfiddle.net/forestrf/tPQSv/2/
}  
//-------------------------------------------------------------------
function ScrollToTop() {
  window.scrollTo(0, 0);
}  
//-------------------------------------------------------------------
window.onscroll = function() {Scroll()};         //OnScroll Event

function Scroll() {
  //Show Scroll button depending on certain conditions:
  //1: Under 100 pixels from Top - None
  //2: Over 100 pixels from Top and Under 60% - Scroll Down
  //3: Over 60% - Scroll Up
  
  var Y = document.body.scrollHeight / 10;
  
  if (window.pageYOffset > 100 && window.pageYOffset < Y * 6) {
    document.getElementById('scrollup').style.display = "none";
    document.getElementById('scrolldown').style.display = "block";
  }
  else if (window.pageYOffset >= (Y * 6)) {
    document.getElementById('scrollup').style.display = "block";
    document.getElementById('scrolldown').style.display = "none";
  }
  else {
    document.getElementById('scrollup').style.display = "none";
    document.getElementById('scrolldown').style.display = "none";
  }
  
  //Lock Stats box and Fade in place if visible
  if (document.getElementById('stats-box').style.display == "block") {
    document.getElementById('fade').style.top=window.pageYOffset+"px";
      
    document.getElementById('stats-box').style.top = (window.pageYOffset + (window.innerHeight / 2))+"px";
    document.getElementById('stats-box').style.left = (window.innerWidth / 2)+"px";
  }
  //Lock Options box in place if visible
  if (document.getElementById('options-box').style.display == "block") {
    document.getElementById('fade').style.top=window.pageYOffset+"px";
      
    document.getElementById('options-box').style.top = (window.pageYOffset + (window.innerHeight / 2))+"px";
    document.getElementById('options-box').style.left = (window.innerWidth / 2)+"px";
  }
}
//-------------------------------------------------------------------
/*function ShowFull(Row) {
  //Function to show "smallhidden" p element on blocked.php
  if (document.getElementById('r'+Row).style.display == "block") {
    document.getElementById('r'+Row).style.display = "none";
    document.getElementById('b'+Row).innerHTML = "+";
  }
  else {
    document.getElementById('r'+Row).style.display = "block";
    document.getElementById('b'+Row).innerHTML = "-";
  }
}
*/
