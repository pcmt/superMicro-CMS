/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 29 July 2021 */

/* https://www.encodedna.com/javascript/practice-ground/default.htm?pg=how_to_show_hide_div_element_using_javascript */

function toggle(ele) {
	var cont = document.getElementById('cont');
	if (cont.style.display == 'block') {
		cont.style.display = 'none';
		document.getElementById(ele.id).value = 'view more';
	} else {
		 cont.style.display = 'block';
		document.getElementById(ele.id).value = 'view less';
	}
}

/*
<input type="button" value="Show" id="bt" onclick="toggle(this)"></p>

<div style="display:none;" id="content">

Content

</div>
*/
