/**
 * Qwwwik
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 28 June 2024 */

/* https://verpex.com/blog/website-tips/how-to-build-a-css-only-accordion */

let details = document.querySelectorAll('.accordion details')

details.forEach(function (d, index) {
	d.onclick = () => {
		details.forEach(function(c, i) {
			index === i ?'':c.removeAttribute('open')
		});
	};
});
