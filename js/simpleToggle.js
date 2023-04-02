/**
 * superMicro CMS
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 22 March 2023 */

/* https://www.encodedna.com/javascript/practice-ground/default.htm?pg=how_to_show_hide_div_element_using_javascript */

function toggle(ele) {
	var cont = document.getElementById('cont'); /* The hidden content to display */
	if (cont.style.display == 'block') {
		cont.style.display = 'none'; /* Hidden by default */
		document.getElementById(ele.id).value = 'view more';
	} else {
		cont.style.display = 'block';
		document.getElementById(ele.id).value = 'view less';
	}
}
