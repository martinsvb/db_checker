
function zobrazSkryj()
{
    function toggle(id)
    {
        var el = document.getElementById(id);
	el.className = (el.className == 'skryj') ? '' : 'skryj';
	
	var sl = id.split(" ");
	var odr =  document.getElementById(sl[0] + " odr " + sl[sl.length-1]);
	odr.className = (odr.className == 'icon-plus') ? 'icon-minus' : 'icon-plus';
    }
    
    for(var i=0; i<arguments.length; ++i) {
        toggle(arguments[i]);
    }
}

function parseHexColor(c) {
  var j = {};

  var s = c.replace(/^#([0-9A-Fa-f]{2})([0-9A-Fa-f]{2})([0-9A-Fa-f]{2})$/, function(_, r, g, b) {
    j.red = parseInt(r, 16);
    j.green = parseInt(g, 16);
    j.blue = parseInt(b, 16);

    return "";
  });

  if(s.length == 0) {
    return j;
  }
}

function colorDifference() {
  var a = parseHexColor(document.formA.b1.value);
  var b = parseHexColor(document.formA.b2.value);
  
  if(typeof(a) != 'undefined' && typeof(b) != 'undefined') {
    document.formA.rozdil.value = "#" + (a.red - b.red).toString(16) + (a.green - b.green).toString(16) + (a.blue - b.blue).toString(16);
  }
}
