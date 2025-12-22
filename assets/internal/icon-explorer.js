/**
 * v3 of the icon explorer feature from the "AmadeusWeb Smithy"
 * 
 * DO NOTE: This is proprietary software by Imran Ali Namazi.
 * It cannot be reused, distributed or derived without
 * prior written consent after paying a royalty for it.
 */

if (typeof($) === 'undefined') $ = jQuery.noConflict();

$(document).ready(function() {
	const pageVars = {
		'itemCount': 0,
		'itemsShown': 0,
	}

	initializeExplorer();
	$('#prefix').on('change', initializeExplorer);
	$('#search').on('change', filterItems);

	function updateCounts() {
		$('#counts').val(pageVars.itemsShown + ' of ' + pageVars.itemCount);
	}

	function initializeExplorer() {
		const div = $('#icons');
		let prefix = $('#prefix').val();
		const magnify = $('#magnify').val();
		const items = getAllSelectors(prefix);
		
		pageVars.itemsShown = pageVars.itemCount = items.length;
		updateCounts();
		
		items.forEach(function (item) {
			$('<div class="col-md-3 col-sm-6" />')
				.append('<span class="' + magnify + ' ' + prefix + ' ' + item + '"></span>')
				.append('<br />' + item.replaceAll(prefix, '').replaceAll('-', ' '))
				.appendTo(div);
		});
	};

	function filterItems() {
		const searchVal = $('#search').val();
		let shown = 0;

		$('#icons div').each(function() {
			const div = $(this);

			if (searchVal == '' || div.text().includes(searchVal)) {
				shown += 1;
				div.show();
			} else {
				div.hide();
			} 
		});

		pageVars.itemsShown = shown;
		updateCounts();
	}

	function getAllSelectors(prefixesCsv) {
		const result = [];
		const prefixes = prefixesCsv.split(', ').map(function(itm) { return '.' + (itm == 'fab' ? 'fa' : itm); });

		for(var i = 0; i < document.styleSheets.length; i++) {
			const sheet = document.styleSheets[i];
			if (!sheet.href || !sheet.href.includes('icons')) continue;

			const rules = sheet.rules || sheet.cssRules;

			for(var x in rules) {
				let found = false;
				const selector = rules[x].selectorText;
				if(typeof selector == 'string'){
					prefixes.forEach(function(prefix) {
						if (found || !selector.startsWith(prefix))
							return;
						found = true;
						result.push(selector.replaceAll('.', '').replaceAll('::before', '').replaceAll('::after', ''));
					});
				}
			}
		}

		return result;
	}
});
