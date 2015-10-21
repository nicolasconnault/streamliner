// JavaScript Document
/**
 * This was adapted from the news scroller on Bespoke Hotels.
 * The controls have been removed for the moment.
 */
var NewsScroller__animiationTime = 2000;
var NewsScroller__clickAnimationTime = 1000;
var NewsScroller__showTime = 5000;
var NewsScroller__lastAnimationTime = 0;
var NewsScroller__currentEffect = null;
var NewsScroller__nextEffect = null;

Event.observe(document, 'dom:loaded', NewsScroller__init);
function NewsScroller__init() {
	//alert('NewsScroller__init');
	try {
		NewsScroller__newsItems = $$('ul.offerScrollContent li');
		setTimeout('NewsScroller__cycle(+1, NewsScroller__animiationTime, false);', NewsScroller__showTime);
		/**
		 * Controls removed for now.
		var newsUp = $$('img.newsUp')[0];
		var newsDown = $$('img.newsDown')[0];
		newsUp.observe('click', NewsScroller__newsUp);
		newsDown.observe('click', NewsScroller__newsDown);
		*/
	}
	catch(e) {
		NewsScroller__handleException(e);
	}
}

function NewsScroller__handleException(e) {
	if(typeof console != 'undefined') console.log(e);
	else alert(e.message);
}

function NewsScroller__cycle(direction, overideAnimTime, user) {
	//alert('NewsScroller__cycle');
	//console.log('NewsScroller__cycle');
	var lastTimeDelta = new Date().getTime() - NewsScroller__lastAnimationTime;
	if(lastTimeDelta < NewsScroller__animiationTime && !user) {
		//Too early
		console.log('Too early ('+lastTimeDelta+' < '+NewsScroller__animiationTime+')');
		return;
	}

	NewsScroller__lastAnimationTime = new Date().getTime();

	try {
		var localAnimTime = overideAnimTime;

		var currentItemIndex = null;
		if(NewsScroller__currentEffect == null) {
			for(var i = 0; i < NewsScroller__newsItems.length && currentItemIndex == null; i++) {
				if(parseInt(NewsScroller__newsItems[i].style.top) == 0) currentItemIndex = i;
			}
		}
		else {
			for(var i = 0; i < NewsScroller__newsItems.length && currentItemIndex == null; i++) {
				if(NewsScroller__newsItems[i] == NewsScroller__currentEffect.element) currentItemIndex = i;
			}
			NewsScroller__currentEffect.cancel();
		}

		if(currentItemIndex == null) {
			//Nothing's on show???
			//Just default to the first.
			currentItemIndex = 0;
		}

		var nextItemIndex = currentItemIndex+direction;
		if(nextItemIndex >= NewsScroller__newsItems.length) nextItemIndex = 0;
		else if(nextItemIndex < 0) nextItemIndex = NewsScroller__newsItems.length-1;

		if(NewsScroller__nextEffect != null) {
			NewsScroller__nextEffect.cancel();
		}

		//console.log(NewsScroller__newsItems[currentItemIndex]);
		//console.log(NewsScroller__newsItems[nextItemIndex]);

		//targetPosition += parseFloat(NewsScroller__newsItems[currentItemIndex].style.top);
		if(NewsScroller__nextEffect != null && NewsScroller__nextEffect.element != NewsScroller__newsItems[nextItemIndex]) {
			NewsScroller__currentEffect = new Effect.Morph(NewsScroller__newsItems[currentItemIndex], {
				style:{top:"0px"},
				duration:localAnimTime/1000,
				afterFinish:NewsScroller__oldFinished
			});

			//Setup the animation to move the
			//"old" next item to wherever we need it
			var oldNextItem =  NewsScroller__nextEffect.element;
			var oldNextTarget = direction > 0 ? -($(oldNextItem.parentNode).getHeight()+1) : ($(oldNextItem.parentNode).getHeight()+1);
			NewsScroller__nextEffect = new Effect.Morph(oldNextItem, {
				style:{top:oldNextTarget+"px"},
				duration:localAnimTime/1000
			});
		}
		else {
			if(direction > 0) {
				//Next item needs to be below the container and animate "up".
				NewsScroller__newsItems[nextItemIndex].style.top = (($(NewsScroller__newsItems[nextItemIndex].parentNode).getHeight()+1) + parseFloat(NewsScroller__newsItems[currentItemIndex].style.top)) +"px";
				var targetPosition = (-($(NewsScroller__newsItems[currentItemIndex].parentNode).getHeight()+1));
			}
			else {
				//Next item needs to be above the container and animate "up".
				NewsScroller__newsItems[nextItemIndex].style.top = ((-($(NewsScroller__newsItems[nextItemIndex].parentNode).getHeight()+1)) + parseFloat(NewsScroller__newsItems[currentItemIndex].style.top)) +"px";
				var targetPosition = ( ($(NewsScroller__newsItems[currentItemIndex].parentNode).getHeight()+1));
			}

			NewsScroller__currentEffect = new Effect.Morph(NewsScroller__newsItems[currentItemIndex], {
				style:{top:targetPosition+"px"},
				duration:localAnimTime/1000,
				afterFinish:NewsScroller__oldFinished
			});

			NewsScroller__nextEffect = new Effect.Morph(NewsScroller__newsItems[nextItemIndex], {
				style:{top:"0px"},
				duration:localAnimTime/1000
			});
		}
	}
	catch(e) {
		NewsScroller__handleException(e);
	}
}

/**
* Move the "old" news item, now above the container,
* to the parking area below the container.
*/
function NewsScroller__oldFinished(effect) {
	//No longer necessary.
	//var newsItem = effect.element;
	//newsItem.style.top = (newsItem.parentNode.getHeight()+1)+"px";
	NewsScroller__currentEffect = null;
	NewsScroller__nextEffect = null;
	setTimeout('NewsScroller__cycle(+1, NewsScroller__animiationTime, false);', NewsScroller__showTime);
}

function NewsScroller__newsDown() {
	NewsScroller__cycle(-1, NewsScroller__clickAnimationTime, true);
}

function NewsScroller__newsUp() {
	NewsScroller__cycle(+1, NewsScroller__clickAnimationTime, true);
}