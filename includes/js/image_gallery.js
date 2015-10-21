// JavaScript Document
function handleException(e)
	{
		alert("Exception line: "+e.lineNumber+"\n"+e);	
	}
	
var imageCalleryInstance = new ImageGallery();
	
//Image gallery class
function ImageGallery() {
	
	this.init = function ()
	{
		new Ajax.Request('/admin/image_gallery/ajax_all_images/'+galleryID+'.htm', {
				method:"get",
				onSuccess:this.getAllCB
		});
	}
	
	this.getAllCB = function(transport) 
	{
		try {
			//console.log("init callback");
			var xml = transport.responseXML;	
			var imageElements = xml.getElementsByTagName("image");
			//console.log(imageElements.length+" images");
			for(var  i = 0; i < imageElements.length; i++) {
					//console.log("processing image "+i);
					var imageHolder = imageCalleryInstance.createImageHolder(imageElements[i]);
					imageHolder.dispOrder =  imageElements[i].getElementsByTagName("display_order")[0].firstChild.data;
					var tDiv = document.getElementById("used_images");
					if(imageHolder.dispOrder < 0) tDiv = document.getElementById("unused_images");
								
					imageCalleryInstance.insertImage(tDiv ,imageHolder);
			}
			
			//set up our sortables
			Sortable.create('used_images', 		{tag:'div', overlap:'horizontal', containment:['used_images', 'unused_images'], constraint:'', dropOnEmpty:true, onUpdate:function(){ imageCalleryInstance.updateUsedOrder();} });
			Sortable.create('unused_images', 	{tag:'div', overlap:'horizontal', containment:['used_images', 'unused_images'], constraint:'', dropOnEmpty:true, onUpdate:function(){ imageCalleryInstance.updateUnUsedOrder();} });
		}
		catch(e) {
			handleException(e);	
		}
	}
	
	this.insertImage = function(targetDiv, image)
	{
		//console.log("insertImage()");
		/* targetDiv.getElementsByTagName("div");
		*  is getting all divs under this node, not just 
		*  first generation children.
		*  need to iterate through children manually :-/
		*/
		var existingDivs = new Array(); //targetDiv.getElementsByTagName("div");
		var child = targetDiv.firstChild;
		while(child != null) {
			if(child.nodeType == 1 && child.nodeName == "DIV") existingDivs.push(child);
			child = child.nextSibling;
		}
		
		if(existingDivs.length == 0) {
			targetDiv.appendChild(image);
			return;	
		}
		
		//very simple linear search for insertion point.
		//should be a binary-search as ordered data.
		//cba for anything that complex.
		var fg = 0;
		while(fg < existingDivs.length && existingDivs[fg].dispOrder < image.dispOrder) 
		{
			fg++;	
		}
		
		if(fg < existingDivs.length){
			try {
				targetDiv.insertBefore(image, existingDivs[fg]);
			}
			catch(e) {
				alert(existingDivs[fg]);
				existingDivs[fg].style.border = "1px dashed #00FF00";
			}
		}
		else targetDiv.appendChild(image);
	}
	
	this.createImageHolder = function(image)
	{
		//console.log("createImageHolder()");
		var imageHolder = document.createElement("div");
		imageHolder.className = "galleryImageHolder";
		imageHolder.id = "image_"+image.getElementsByTagName("id")[0].firstChild.data;
		
		var delSpan = document.createElement("span");
		imageHolder.appendChild(delSpan);
		delSpan.style.cursor = "pointer";
		//delSpan.style.verticalAlign = "middle";
		delSpan.onclick = function() { imageCalleryInstance.deleteImage(this); };
		
		var delImg = document.createElement("img");
		delSpan.appendChild(delImg);
		delImg.src = "/images/icons/delete.png";
		delImg.alt = "Delete";
		delImg.title = "Delete";
		delImg.style.verticalAlign = "middle";
		
		delSpan.appendChild(document.createTextNode(" Delete"));
		
		var imageDiv = document.createElement("div");
		imageDiv.className = "galleryImage";
		imageDiv.style.background = "url("+image.getElementsByTagName("url")[0].firstChild.data+") no-repeat";
		imageHolder.appendChild(imageDiv);
		return imageHolder;
	}
	
	this.deleteImage = function(span)  
	{
		if(confirm("Do you really want to delete this image?"))
		{
			var holderDiv = span.parentNode;
			var imgID = holderDiv.id.substr(6); //image_X
			new Ajax.Request('/admin/image_gallery/ajax_delete_image.htm', {
				method:"post",
				parameters: {image_id:imgID},
				onSuccess:this.deleteImageCB
			});
		}
	}
	
	this.deleteImageCB = function (transport)
	{
		var xml = transport.responseXML;
		var id = xml.getElementsByTagName("image_id")[0].firstChild.data;
		var image = document.getElementById("image_"+id);
		image.parentNode.removeChild(image);
	}
	
	this.updateUsedOrder = function ()
	{
		//console.log("update used");	
		var paramStr = Sortable.serialize('used_images');
		paramStr+="&gallery_id="+galleryID;
		
		new Ajax.Request('/admin/image_gallery/ajax_update_order.htm', {
			method:"post",
			parameters: paramStr/*,
			onSuccess:this.alertCB*/
		});
	}
	
	this.updateUnUsedOrder = function ()
	{
		//console.log("update un used");	
		var paramStr = Sortable.serialize('unused_images');
		paramStr+="&gallery_id="+galleryID;
		
		new Ajax.Request('/admin/image_gallery/ajax_update_order.htm', {
			method:"post",
			parameters: paramStr/*,
			onSuccess:this.alertCB*/
		});
	}
	
	this.alertCB = function(transport)
	{
		alert(transport.responseText);	
	}
}