(function ($) {
	'use strict';

	var $searchForm = $('#searchForm');
	var $searchBox = $('#searchForm input');
	var $searchResults = $('#searchResults');
	var timeout;

	$searchForm.on('submit', function() {
		$('.searchResult').slideUp(400, function() {
			$('.searchResult').not('.header').remove();
			loadResults($searchBox.val());
		});

		return false;
	});

	function loadResults(query) {
		clearTimeout(timeout);
		timeout = setTimeout(function() {
			$.get('/helper/search', {'q': query}, populateResults);
		}, 300);
	}

	function populateResults(results) {
		results = JSON.parse(results);
		console.log(results);

		if (results.length !== 0) {
			$.each(results, function(index, val) {
				var result = $('<li>').addClass('searchResult').html($('<a>').attr('href', val.url).html(val.text));
				$(result).insertAfter('.searchResult');
			});
		} else {
			var result = $('<li>').addClass('searchResult').html($('<a>').html('No Results Found.'));
			$(result).insertAfter('.searchResult');
		}


		$('.searchResult').slideDown();
	}
})(jQuery);