var timeInSecs;
var ticker;

function startTimer(secs){
timeInSecs = parseInt(secs)-1;
ticker = setInterval("tick()",1000);   // every second
}

function tick() {
var secs = timeInSecs;
if (secs>0) {
timeInSecs--;
}
else {
clearInterval(ticker); // stop counting at zero
// startTimer(60);  // remove forward slashes in front of startTimer to repeat if required
}

document.getElementById("countdown").innerHTML = secs;
}

startTimer(60);  // 60 seconds 

function showDebug() 
{
	document.getElementById("debug").style.display = 'block';
}

function showVod() 
	{
	document.getElementById('voddrive').readonly=false;
	}

function showUpload() 
	{
	document.getElementById('voduploaddir').readonly=false;
	}
	
function showInspect() 
	{
	document.getElementById('pathtoinspect').readonly=false;
	}
	
function showQueue() 
	{
	document.getElementById('pathtoqueue').readonly=false;
	}
	
function showXml() 
	{
	document.getElementById('pathtoxml').readonly=false;
	}
	
function showVideotypes() 
	{
	document.getElementById('allowedvideotypes').readonly=false;
	}
	
function showXmlTypes() 
	{
	document.getElementById('allowedxmltypes').readonly=false;
	}
	
function showMins() 
	{
	document.getElementById('minstowait').readonly=false;
	}
