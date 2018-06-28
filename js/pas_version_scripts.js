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

function revealMenu() {
	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();

	xmlhttp.open("POST",ajaxurl,true);
	data.append("action", "pas_version_reveal_menu");
	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.status == 200) {
			location.reload();
		}
	}
	xmlhttp.send(data);
}

var pvItem = "";
var tID = 0;

function pvShowItem(item, pwd) {
	var ndx;
	if (pvItem == "") {
		pvItem = item.innerHTML
	}
	tID = setTimeout(function () { item.innerHTML = pvItem }, 10000);
	item.innerHTML = pwd
}
