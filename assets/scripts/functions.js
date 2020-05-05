/**
 * Function to add customized classes to the <h1> page title.
 * @param  {string} tagClass  The class to be added to container.
 */
var bannerAddClassToTitle = function (selector, tagClass){
	jQuery(selector).addClass(tagClass); 
};

/**
 * Function to put title after the first super banner ad.
 * @param  {string} selector  The container title selector.
 * @param  {string} container The banner container.
 * @param  {string} tagClass  The class to be added to container.
 * @return {string}           The container title.
 */
var bannerMoveTitleDown = function (selector, container){
	var title = jQuery(selector); 
	jQuery(selector).remove(); 
	jQuery(container).after(
		function(){
			return title; 
	});
};