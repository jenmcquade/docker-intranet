function getUrlVars() {
  //Used to find URL arguments
  var vars = {};
  var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi,    
  function(m,key,value) {
    vars[key] = value;
  });
  return vars;
}

function confirmLogDelete() {
  if (confirm("Are you sure you want to delete all History?")) window.open("?action=delete-history", "_self");
}
function addSite(rownumber) {  
  var sitename = document.getElementsByName('site'+rownumber)[0].value;
  var comment = document.getElementsByName('comment'+rownumber)[0].value;
  window.open('?v='+getUrlVars()["v"]+'&action='+getUrlVars()["v"]+'&do=add&site='+sitename+'&comment='+comment, "_self");
}
function deleteSite(rownumber) {
  window.open('?v='+getUrlVars()["v"]+'&action='+getUrlVars()["v"]+'&do=del&row='+rownumber, "_self");
}
function changeSite(Item) {
  window.open('?v='+getUrlVars()["v"]+'&action='+getUrlVars()["v"]+'&do=cng&row='+Item.name.substring(1)+'&status='+Item.checked, "_self");  
}
