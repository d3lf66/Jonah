
/*author Philip M. 2010*/

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