if(typeof pvShowErrors == "undefined") pvShowErrors =
	function pvShowErrors(xmlhttp) {
		var emsg;
		if (xmlhttp.responseText.trim().length > 0) {
			emsg = document.createElement("div")
			emsg.setAttribute("id", "errorMessage")
			emsg.style.cssText = "width:600px;height:500px;position:fixed;top:0px;left:0px;font-size:12pt;color:black;background-color:white;display:none;overflow:auto;z-index:999999;border:solid 1pt red;"
			emsg.onclick = function () { emsg.style.display = "none" }
			document.getElementsByTagName("body")[0].appendChild(emsg)
			emsg.innerHTML = xmlhttp.responseText.trim();
			emsg.style.visibility = "visible"
			emsg.style.display = "inline"
		}
	}

function makeSubmitForm(buttonElement, actionItem) {
	var frm = document.createElement("form")
	frm.setAttribute("id", "submitForm")
	frm.setAttribute("method", "POST")
	frm.setAttribute("action", ajaxurl)

	var element = document.createElement("input")
	element.setAttribute("type", "hidden")
	element.setAttribute("name", "action")
	element.setAttribute("value", actionItem)
	frm.appendChild(element)

	element = document.createElement("input")
	element.setAttribute("type", "submit")
	element.setAttribute("value", "")

	buttonElement.parentNode.removeChild(buttonElement)
	frm.appendChild(buttonElement)
	frm.appendChild(element)

	document.getElementsByTagName("body")[0].appendChild(frm);

	return frm;
}

function hideMenu(buttonElement) {

	var frm = makeSubmitForm(buttonElement, "hideMenuOption")
	frm.submit()
}

// currentSetting = true: menu is hidden
function revealMenu(currentSetting) {
	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();

	xmlhttp.open("POST",ajaxurl,true);
	data.append("action", "pasVersions_revealMenu");
	data.append("hideMenu", currentSetting);
	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.status == 200) {
			location.reload();
		}
	}
	xmlhttp.send(data);
}

var pvItem = "";
var tID = "";
var tenSeconds = 10000

function pvShowItem(item, pwd) {
	var ndx;
	if (pvItem == "") {
		pvItem = item.innerHTML
	}
	tID = setTimeout(function () { item.innerHTML = pvItem; item.className = "pvItemValueHidden" }, tenSeconds);
	item.innerHTML = pwd
	item.className = "pvItemValueVisible"
}
function createBox(id = "id_" + (new Date()), nodename = "DIV", parent = document.getElementsByTagName("body")[0], className=null) {
	var box = document.getElementById(id);
	if (box == null) {
		box = document.createElement(nodename);
		box.setAttribute("id", id);
		parent.appendChild(box);
		if (className != null) {
			box.className = className;
		}
	} else {
		box.innerHTML = "";
	}
	return box;
}
function killElement(element) {
	element.parentNode.removeChild(element);
	element.remove();
}

var classElements = document.getElementsByClassName("categoryHeader");

for (var ndx = 0; ndx < classElements.length; ndx++) {
	classElements[ndx].addEventListener("click", function () {
		var content = this.nextElementSibling;
		if (content.style.maxHeight) {
			content.style.maxHeight = null;
			content.style.overflowY = "";
		} else {
			if (content.scrollHeight > 300) {
				content.style.maxHeight = "300px";
				content.style.overflowY = "scroll";
			} else {
				content.style.maxHeight = content.scrollHeight + "px";
				content.style.overflowY = "";
			}
		}
	});
}

classElements = document.getElementsByClassName("content");
for (ndx = 0; ndx < classElements.length; ndx++) {
	classElements[ndx].addEventListener("mouseover", function (event) {
		var name = event.target.getAttribute("data-name");
		var value = event.target.getAttribute("data-value");
		if (name == null) { return }
		var box = createBox("actionBox");
		box.innerHTML = name + ": " + value;
	});
	classElements[ndx].addEventListener("mouseout", function () {
		killElement(createBox("actionBox"));
	});
}
