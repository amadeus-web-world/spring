if (typeof($) === 'undefined') $ = jQuery.noConflict();

$(document).ready(function() {
	$('textarea.autofit').on('change, input', textAreaAutoHeight).trigger('input');

	function textAreaAutoHeight(ev) {
		this.style.height = '1px';
		this.style.height = this.scrollHeight + 'px';
	}
});
