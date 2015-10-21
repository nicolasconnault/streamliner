$(document).observe("dom:loaded", function(evnt) {
	imageChecks = $$('.imageItem input');
	imageChecks.each(function(check) {
		$(check).observe('click', pd__imageChecked);
	});
	pd__checkLimit();
})

function pd__imageChecked(evnt) {
	//evnt.stop();
	//this.checked = !this.checked;

	if(this.checked) {
		pd__checkLimit();
	}
	else {
		for(var i = 0; i < imageChecks.length; i++) {
			imageChecks[i].disabled = false;
		}
	}
}

function pd__checkLimit() {
	var checked = 0;
	for(var i = 0; i < imageChecks.length; i++) {
		if(imageChecks[i].checked) {
			checked++;
		}
	}

	if(checked >= 4) {
		this.checked = "";
		for(var i = 0; i < imageChecks.length; i++) {
			if(!imageChecks[i].checked) {
				imageChecks[i].disabled = true;
			}
		}
	}
}