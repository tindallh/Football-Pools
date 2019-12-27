var NS=(document.layers) ? true : false; 
var IE=(document.all) ? true : false; 
function Mouse(evnt){
}
(NS)?window.onMouseMove=Mouse:document.onmousemove=Mouse;
function showPortinfo(text, eventObj){
	var posx = 0;
	var posy = 0;
	if (!e) var e = window.event;
	if (e.pageX || e.pageY)
	{
		posx = e.pageX;
		posy = e.pageY;
	}
	else if (e.clientX || e.clientY)
	{
		posx = e.clientX + document.body.scrollLeft;
		posy = e.clientY + document.body.scrollTop;
	}
  nnLayer = 'document.layers[\\'popup\\']';
  ieLayer = 'document.all[\\'popup\\']';
  if (!(document.all||document.layers)) return;
  if (document.all) document.popup=eval(ieLayer);
  else document.popup=eval(nnLayer);
  var table = "";
  if (document.all){
    table += "<table cellpadding='3' bgcolor='#000000' border='0' cellspacing='1'>" + text + "</table>";
    document.popup.innerHTML = table;
    if (posy + 2 < 475) {
      document.popup.style.top = posy + 2 ;
    }
    else {
      document.popup.style.top = posy - 275 ;
    }
    if (posx +2 < 310 ) {
      document.popup.style.left = posx +2 ;
    }
    else {
      document.popup.style.left = posx - 310 ;
    }
    document.popup.style.visibility = "visible";
  }
  else {
    table += "<table cellpadding='3' bgcolor='#000000' border='0' cellspacing='1'>" + text + "</table>";
    document.popup.document.open();
    document.popup.document.write(table);
    document.popup.document.close();
    document.popup.top = posy;
    if (posy + 2 < 475) {
      document.popup.top = posy + 2 ;
    }
    else {
      document.popup.top = posy - 275 ;
    }
    if (posx +2 < 310 ) {
      document.popup.left = posx +2 ;
    }
    else {
      document.popup.left = posx - 310 ;
    }
  }
}
function hidePortinfo(){ 
  if (!(document.all || document.layers)) return;
  if (document.popup == null){ }
  else if (document.all) document.popup.style.visibility = "hidden";
  else document.popup.visibility = "hidden";
  document.popup = null;
}

