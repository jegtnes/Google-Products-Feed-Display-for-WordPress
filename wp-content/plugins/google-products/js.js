/** DOMReady v2.0.1 - MIT license - https://github.com/freelancephp/DOMReady */
(function(a){a.DOMReady=function(){var b=[],c=false,d=null,e=function(a,b){try{a.apply(this,b||[])}catch(c){if(d)d.call(this,c)}},f=function(){c=true;for(var a=0;a<b.length;a++)e(b[a].fn,b[a].args||[]);b=[]};this.setOnError=function(a){d=a;return this};this.add=function(a,d){if(c){e(a,d)}else{b[b.length]={fn:a,args:d}}return this};if(a.addEventListener){a.document.addEventListener("DOMContentLoaded",function(){f()},false)}else{(function(){if(!a.document.uniqueID&&a.document.expando)return;var b=a.document.createElement("document:ready");try{b.doScroll("left");f()}catch(c){setTimeout(arguments.callee,0)}})()}return this}()})(window);


function cronControl() {
	//checks for the presence of elements
	if (document.getElementById && document.getElementById('goopro_cron_enabled') && document.getElementById('goopro_update_interval')) {
		
		//click handler for the cron checkbox
		getElementById('goopro_cron_enabled').onclick = function() {
			
			//the select box to control
			var cronTime = document.getElementById('goopro_update_interval');
			
			//disables or enables the time setting
			this.checked == true ? cronTime.disabled = false : cronTime.disabled = true;
		}
	}
}

//When the DOM has loaded
DOMReady.add(cronControl);