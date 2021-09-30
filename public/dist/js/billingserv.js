$.BillingServ = {};

if ($.BillingServ.url === undefined) {
	$.BillingServ.url = window.location.origin;
}

$.BillingServ.modal = function(title, content) {
	var $modal = $('<div></div>').addClass('modal'),
		$modalHeader = $('<div></div>').addClass('modal-header'),
		$modalBody = $('<div></div>').addClass('modal-body');

	$modalHeader.append($('<button></button>').addClass('close').attr('data-dismiss', 'modal').html('&times;'))
		.append($('<h4></h4>').addClass('modal-title').text(title));

	$modalBody.html(content);

	$modal.html(
		$('<div></div>').addClass('modal-dialog')
			.html(
				$('<div></div>').addClass('modal-content')
					.html($modalHeader)
					.append($modalBody)
			)
	);

	$($modal).modal();
};

$.BillingServ.urlModal = function(title, url) {
	$.ajax({
		url: $.BillingServ.url + url,
		type: 'GET',
		dataType: 'html',
	})
	.done(function(data) {
		$.BillingServ.modal(title, data);
	})
	.fail(function() {

	});
};