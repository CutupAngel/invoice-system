$(document).ready(function(){
	if($(window).width() > 975)
	{
		var parentWidth = $('#frmCheckout').width();
		var newWidth = parentWidth * 0.333;
		$('.total-sidebar').css('width',newWidth+'px').css('position','fixed').css('left',$('#frmCheckout').offset().left + parentWidth - newWidth + 'px');
	}
	$('select[name="billingAddress[country]"]').change();
});
$(window).resize(function(){
	if($(window).width() > 975)
	{
		var parentWidth = $('#frmCheckout').width();
		var newWidth = parentWidth * 0.333;
		$('.total-sidebar').css('width',newWidth+'px').css('position','fixed').css('left',$('#frmCheckout').offset().left + parentWidth - newWidth + 'px');
	}
});

$('#paymentMethodTabs a input').click(function (e) {
	$(this).parent().tab('show');
});
$('#paymentMethodTabs a').click(function (e) {
	$(this).tab('show');
	$(this).find('input').prop('checked', true);
});
$('select[name="billingAddress[country]"]').change(function() {
	var regionId = $('[name="billingAddress[region]"]').data('region');

	$.ajax({
		headers: { 'X-CSRF-Token': csrf_token},
		url: '/checkout/regions',
		type: 'POST',
		data: 'country='+$(this).val()
	})
	.done(function(data, textStatus, jqXHR) {
		var newHtml = '';
		$.each(data,function(key, item) {
			newHtml = newHtml + '<option value="' + item.id + '"' + (regionId == item.id ? ' selected' : '') + '>' + item.name + '</option>';
		});

		$('select[name="billingAddress[region]"]').html(newHtml);
		$('select[name="billingAddress[region]"]').change();
	})
	.fail(function(data) {

	});
});
$('#btnDiscountCode').click(function() {
	$('#btnDiscountCode').prop('disabled',true);
	$('#divDiscountCode .error').remove();
	$.ajax({
		headers: { 'X-CSRF-Token': csrf_token},
		url: '/checkout/discountCode',
		type: 'POST',
		data: 'code='+$('#discountCode').val(),
		dataType:'json'
	})
	.done(function(data, textStatus, jqXHR) {
		$('#btnDiscountCode').prop('disabled',false);
		if(data.discount > 0)
		{
			if(!$('#discountTotal').length)
			{
				$('#tax').prepend('<li class="subtotal clearfix" id="discountTotal"><div class="pull-left lab">Discount</div><div class="pull-right"><span class="amount"></span></div></li>');
			}
			$('#discountTotal .amount').html(data.formattedDiscount);
			$('#tax .amount').html(data.formattedTax);
			$('#grandTotal .amount').html(data.formattedTotal);
		}
		else
		{
			$('#divDiscountCode .form-group').append('<div class="error bg-danger">Invalid code</div>');
		}
		$('#grandTotal .amount').html(data.grandTotal.toFixed(2));
	})
	.fail(function(data) {
		$('#btnDiscountCode').prop('disabled',false);

	});
});
var updatedTax = 0;
$('select[name="billingAddress[region]"]').change(function() {
	getRegionData();
});
$('select[name="billingAddress[zip]"]').change(function() {
	getRegionData();
});

function getRegionData(callback)
{
	$('#frmCheckoutSubmit').prop('disabled',true);
	$.ajax({
		headers: { 'X-CSRF-Token': csrf_token},
		url: '/checkout/tax',
		type: 'POST',
		data: 'county='+$('select[name="billingAddress[region]"]').val()
	})
	.done(function(data, textStatus, jqXHR) {
		updatedTax = 1;
		$('#frmCheckoutSubmit').prop('disabled',false);
		data = JSON.parse(data);
		if(parseFloat(data.tax) > 0)
		{
			$('#tax').css('display','block');
			$('#tax .amount').html(data.currency + data.tax.toFixed(2));
		}
		else
		{
			//$('#tax').css('display','none');
			$('#tax .amount').html('');
		}
		// old
		// $('#grandTotal .amount').html(data.grandTotal.toFixed(2));
		if(callback !== undefined)
		{
			callback();
		}
	})
	.fail(function(data) {
		$('#frmCheckoutSubmit').prop('disabled',false);

	});
}
function submitData() {
	$('#frmCheckoutSubmit').addClass('disabled').removeClass('btn-danger');
	$('.has-error').removeClass('has-error');
	$('.errStatus.text-danger').removeClass('errStatus text-danger');
	$('#errMsgs').html('').addClass('has-error');

	var params = $('#frmCheckout input, #frmCheckout select').serialize();

	$.ajax({
		headers: { 'X-CSRF-Token': csrf_token},
		url: '/checkout',
		type: 'POST',
		data: params
	}).done(function(data, textStatus, jqXHR) {

		$('#frmCheckoutSubmit').removeClass('disabled');

		if (data.errors && data.errors[0] !== null) {

			$('#frmCheckoutSubmit').addClass('btn-danger');

			 $.each(data.errors,function(k, v) {
			 	$.each(v.inputs, function(k2, v2){
			 		$('[name="'+v2+'"]').parent().addClass('has-error');
			 		$('[name="'+v2+'"]').addClass('errStatus text-danger');
			 	});

			 	$('.errMsgs').append('<div class="alert alert-danger">'+v.error_message+'</div>');
			 });
		} else {
			$('#frmCheckoutSubmit').addClass('btn-success');
			if (data.redirect) {
				window.location.assign('/checkout/receipt/');
			}

			if (data.offsiteGateway) {
				$('body').append(data.offsiteGateway);
				$('#frmOffsiteGateway').submit();
			}
		}
	}).fail(function(data) { return 'fail';
		$('span.input-item-error').replaceWith();
		$('.has-error').removeClass('has-error');

		if (data.status == 400) {
            var errors = data.responseJSON.errors;

            $.each(errors, function (key, val) {
            	var field = key.split('.');

            	if (field !== undefined && field.length >= 3) {
            		$('[name="' + field[0] + '[' + field[1] + ']' + '[' + field[2] + ']"]').addClass('has-error');
	                $('[name="' + field[0] + '[' + field[1] + ']' + '[' + field[2] + ']"]').after('<span class="input-item-error">' + val[0] + '</span>');
            	} else {
	                $('[name="' + field[0] + '[' + field[1] + ']"]').addClass('has-error');
	                $('[name="' + field[0] + '[' + field[1] + ']"]').after('<span class="input-item-error">' + val[0] + '</span>');
	            }
            });
        }

		$('#frmCheckoutSubmit').removeClass('disabled').addClass('btn-danger');
	});
}
$('#frmCheckoutSubmit').click(frmCheckoutSubmit);
function frmCheckoutSubmit() {
	if (!$('input[name="term"]').prop('checked')) {
		$('.agree-error').html('<p>Agreements is required.</p>');
	} else {
		$('.agree-error').text('');
	}

	if(!updatedTax) {
		getRegionData(frmCheckoutSubmit);
	} else {
		if($('[name="paymentMethod[type]"]:checked').val() == '0'&& $('[name="chosenCC"]').length && $('[name="chosenCC"]:checked').val() > 0) {
			submitData();
		} else if($('[name="paymentMethod[type]"]:checked').val() == '1' && $('[name="chosenBank"]').length && $('[name="chosenBank"]:checked').val() > 0) {
			submitData();
		} else {
			startPayment();
		}
	}

};
