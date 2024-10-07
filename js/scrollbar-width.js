/**
 * Qwwwik
 * ==============
 * COPYRIGHT Patrick Taylor https://patricktaylor.com/
 */

/* Last updated 13 August 2024 */
// https://stackoverflow.com/questions/39392423/calculating-width-of-scrollbar-and-using-result-in-calc-css

document.body.style.setProperty(
    "--scrollbar-width",
    `${window.innerWidth - document.body.clientWidth}px` // Backticks required
);
