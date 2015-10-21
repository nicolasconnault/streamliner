/**
 * This was adapted from the script on Christian Hills Design.
 * Certain aspects such as the dynamic pre-loading were felt
 * as being unnecessary and disabled.
 */
var ri__animiationTime = 5000;
var ri__clickAnimationTime = 1000;
var ri__showTime = 4000;
var ri__lastAnimationTime = 0;
var ri__currentEffect = null
var ri__nextEffect = null;

Event.observe(document, 'dom:loaded', ri__init);
function ri__init() {
	//alert('ri__init');
	try {
		images = new Array();
		var imageDivs = $$('div.imageContainer');
		imageDivs.each(function(img) {
			var src = img.style.backgroundImage.match(/url\(([^)]+)\)/i)[1];
			var newImg = new BannerImage(img.id, src, img.getWidth(), img.getHeight(), 0, 0, img.getAttribute('alt'));
			newImg.element = img; //Setting the element manually prevents it from trying to pre-load later
			images.push(newImg);
		});
		//console.log(images);
		setTimeout('ri__cycle(+1, ri__animiationTime, false);', ri__showTime);
		/**
		* No controls at the mo'.
		var newsUp = $$('img.newsUp')[0];
		var newsDown = $$('img.newsDown')[0];
		newsUp.observe('click', ri__newsUp);
		newsDown.observe('click', ri__newsDown);
		
		$('controls').observe("mouseleave", ri__hideControls);
		$$('img.dot')[0].observe("mouseover", ri__showControls);
		$('pause').observe("click", ri__pause);
		$('resume').observe("click", ri__resume);
		$('fullscreen').observe("click", ri__fullscreen);
		*/
	}
	catch(e) {
		ri__handleException(e)
	}
}

function ri__handleException(e) {
	if(typeof console != 'undefined') console.log(e);
	else alert(e.message);
}

function ri__cycle(direction, overideAnimTime, user) {
	//alert('ri__cycle');
	//console.log('ri__cycle');
	if(new Date().getTime() - ri__lastAnimationTime < ri__animiationTime && !user) {
		//Too early
		return;
	}

	ri__lastAnimationTime = new Date().getTime();

	try {
		var localAnimTime = overideAnimTime;

		if(images.length < 2) return;
		var currentItemIndex = null;
		if(ri__currentEffect == null) {
			for(var i = 0; i < images.length && currentItemIndex == null; i++) {
				if(images[i].isVisible()) currentItemIndex = i;
			}
		}
		else {
			for(var i = 0; i < images.length && currentItemIndex == null; i++) {
				if(images[i].element != null && images[i].element == ri__currentEffect.element) currentItemIndex = i;
			}
			ri__currentEffect.cancel();
		}

		if(currentItemIndex == null) {
			//Nothing's on show???
			//Just default to the first.
			currentItemIndex = 0;
		}

		var nextItemIndex = currentItemIndex+direction;
		if(nextItemIndex >= images.length) nextItemIndex = 0;
		else if(nextItemIndex < 0) nextItemIndex = images.length-1;


		//Pre-load the next-next image so it's downloaded
		//ready for the fade-in.
		var nextNextItemIndex = nextItemIndex+direction;
		if(nextNextItemIndex >= images.length) nextNextItemIndex = 0;
		else if(nextNextItemIndex < 0) nextNextItemIndex = images.length-1;
		images[nextNextItemIndex].loadImage();

		if(ri__nextEffect != null) {
			ri__nextEffect.cancel();
		}

		images[currentItemIndex].loadImage(); //Just incase something wierd has happened.
		if(ri__nextEffect != null && ri__nextEffect.element != images[nextItemIndex].element) {
			ri__currentEffect = new Effect.Morph(images[currentItemIndex].element, {
				style:{opacity:"1"},
				duration:localAnimTime/1000,
				afterFinish:ri__oldFinished
			});

			//Setup the animation to move the
			//"old" next item to wherever we need it
			var oldNextItem =  ri__nextEffect.element;
			ri__nextEffect = new Effect.Morph(oldNextItem, {
				style:{opacity:"0"},
				duration:localAnimTime/1000
			});
		}
		else {
			ri__currentEffect = new Effect.Morph(images[currentItemIndex].element, {
				style:{opacity:"0"},
				duration:localAnimTime/1000,
				afterFinish:ri__oldFinished
			});

			images[nextItemIndex].loadImage(); //Just incase something wierd has happened.
			ri__nextEffect = new Effect.Morph(images[nextItemIndex].element, {
				style:{opacity:"1"},
				duration:localAnimTime/1000
			});
		}
	}
	catch(e) {
		ri__handleException(e)
	}
}

/**
* Move the "old" news item, now above the container,
* to the parking area below the container.
*/
function ri__oldFinished(effect) {
	//No longer necessary.
	//var newsItem = effect.element;
	//newsItem.style.top = (newsItem.parentNode.getHeight()+1)+"px";
	ri__currentEffect = null;
	ri__nextEffect = null;
	setTimeout('ri__cycle(+1, ri__animiationTime, false);', ri__showTime);
}

function ri__newsDown() {
	ri__cycle(-1, ri__clickAnimationTime, true);
}

function ri__newsUp() {
	ri__cycle(+1, ri__clickAnimationTime, true);
}

function ri__pause() {
	//Bit of a hack to stop the next one running.
	ri__lastAnimationTime += ri__showTime;

	if(ri__currentEffect != null) {
		ri__currentEffect.cancel();
		//Reverse
		new Effect.Morph(ri__currentEffect.element, {
			style:{opacity:"1"},
			duration:ri__clickAnimationTime/1000
		});
		ri__currentEffect = null;
	}

	if(ri__nextEffect != null) {
		ri__nextEffect.cancel();
		//Reverse
		new Effect.Morph(ri__nextEffect.element, {
			style:{opacity:"0"},
			duration:ri__clickAnimationTime/1000
		});
		ri__nextEffect = null;
	}
	$$('img.dot')[0].src = "/images/pause.png";
}

function ri__resume() {
	$$('img.dot')[0].src = "/images/dot.png";
	if(ri__currentEffect != null || ri__nextEffect != null) return;
	ri__lastAnimationTime -= ri__showTime;
	setTimeout('ri__cycle(+1, ri__animiationTime, false);', 1);
}

function ri__fullscreen() {
	try {
		var img = null;
		if(ri__currentEffect != null) {
			var imageID = ri__currentEffect.element.id.match(/^image([0-9]+)$/)[1];
			for(var i = 0; i < images.length && img == null; i++) {
				if(images[i].imageID == imageID) img = images[i];
			}
		}
		else {
			for(var i = 0; i < images.length && img == null; i++) {
				if(images[i].isVisible()) img = images[i];
			}
		}
		window.open('/home/index/'+img.imageID+'.htm', 'ChristianHillsDesignFullscreen', 'channelmode=yes,fullscreen=yes,top=0,left=0,width='+screen.availWidth+',height='+screen.availHeight);
	}
	catch(e) {
		ri__handleException(e)
	}
}

function ri__hideControls(evnt) {
	var controls = $('controls');
	controls.style.display = "none";
}

function ri__showControls() {
	$('controls').style.display = "block";
}

var BannerImage = function(imgID, imgURL, w, h, x, y, a) {
	this.imageID = imgID;
	this.url  = imgURL;
	this.width = w;
	this.height = h;
	this.transX = x;
	this.transY = y;
	this.alt = a;
};
BannerImage.prototype.imageID;
BannerImage.prototype.url;
BannerImage.prototype.alt;
BannerImage.prototype.transX;
BannerImage.prototype.transY;
BannerImage.prototype.width;
BannerImage.prototype.height;
BannerImage.prototype.element = null;
BannerImage.prototype.loadImage = function() {
	if(this.element != null) return;
	if($('image'+this.imageID) != null) {
		//This happens to the first two images that
		//are written into the document ready.
		this.element = $('image'+this.imageID);
		return;
	}
	var styleStr = "position:absolute; opacity:0.0; filter:alpha(opacity=0); top:"+this.transY+"px; left:"+this.transX+"px;";
	this.element = new Element("img", {id:"image"+this.imageID, src:this.url, alt:this.alt, style:styleStr, width:this.width, height:this.height});
	$$('div.contentContainer')[0].appendChild(this.element);
}

BannerImage.prototype.isVisible = function() {
	if(this.element == null && $('image'+this.imageID) != null) {
		//This happens to the first two images that
		//are written into the document ready.
		this.element = $('image'+this.imageID);
	}

	if(this.element == null) return false;
	/*@cc_on
		@if (@_jscript)
		if(this.element.style.filter == '' || this.element.style.filter == 'alpha(opacity=100)') return true;
		@else @*/
		if(this.element.style.opacity == '' || parseFloat(this.element.style.opacity) == 1) return true;
		/*@end
	@*/
	return false;
}