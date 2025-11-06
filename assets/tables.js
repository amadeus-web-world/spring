/***
 * This code is part of the php framework "" and is Proprietary, Source-available software!
 * Author: Imran Ali Namazi <imran@amadeusweb.world>
 * You MUST agree to and adhere to all "courtesies" required by:
 *     https://github.com/amadeus-web-world/spring#License-1-ov-file
***/

if (typeof ($) === 'undefined') $ = jQuery.noConflict();

$(document).ready(function () {
	const sansTH = $('.table-sans-th');
	if (sansTH.length) {
		sansTH.each(function (ix, el) {
			const th = $('tr:first', $(this));
			$(this).find('thead').append(th);
			$(this).addClass('amadeus-data-table');
		});
	}

	if ($('.amadeus-data-table').length == 0) return;

	function thisTable(el) {
		return el.closest('.amadeus-data-table');
	}

	$('.amadeus-data-table').each(function (ix, tbl) {
		const sth = $(tbl).hasClass('table-sans-th');
		$(tbl).DataTable(getDTParams(sth));
	});

	function getDTParams(slim) {
		return {
			amadeusTable: undefined, id: undefined, cardView: undefined,

			//https://datatables.net/reference/option/layout
			layout: {
				top: slim ? null : 'info',
				topStart: null,
				topEnd: {
					search: {
						placeholder: 'Search'
					}
				},
				bottom: slim ? null : { buttons: ['copy', 'pdf', 'print'] },
				bottomStart: null,
				bottomEnd: null,
			},

			responsive: true,
			paging: false,
			'order': [], //off by default

			initComplete: initAWBTComplete,
		};
	}

	function initAWBTComplete(settings, json) {
		const amadeusTable = thisTable($(this)),
			tableId = amadeusTable.attr('id'),
			cardView = tableId + '-card-view';

		// Setup - add a text input to each header cell with header name
		$('thead th, thead td', amadeusTable).each(function (ix, el) {
			var title = $(this).text();
			$(this).html(title + '<br><input class="filter filter-' + title.toLowerCase().replaceAll(' ', '-') + '" type="text" placeholder="Search ' + title + '" />');
		});

		const table = this.api();

		// Apply the search
		table.columns().every(function () {
			var that = this;
			//TODO: responsive shows as undefined
			$('input', this.header()).on('keyup change clear', function () {
				if (that.search() !== this.value) {
					that
						.search(this.value)
						.draw();
				}
			});
		});
	}
});
