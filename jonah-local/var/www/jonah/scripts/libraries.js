var timeInSecs;
var ticker;

function startTimer(secs)
	{
	timeInSecs = parseInt(secs)-1;
	ticker = setInterval("tick()",1000);   // every second
	}

function tick() 
{
	var secs = timeInSecs;
	if (secs>0) {
			timeInSecs--;
			}
	else 		{
			clearInterval(ticker); // stop counting at zero
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

function processProfileData()
{
// var oForm = document.getElementById('line1');
// var testEl = document.oForm.getElementById('container');
// var testVal = testEl.value;
// alert ('form 1, format = '+testVal);	
}
