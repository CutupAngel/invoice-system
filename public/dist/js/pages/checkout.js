$(document).ready(function(){
	if($(window).width() > 975)
	{
		var parentWidth = $('#frmCheckout').width();
		var newWidth = parentWidth * 0.333;
		$('.total-sidebar').css('width',newWidth+'px').css('position','fixed').css('left',$('#frmCheckout').offset().left + parentWidth - newWidth + 'px');
	}
	$('select[name="billingAddress[country]"]').change();

	amountCustomOriginal = $('#amountCustom').html();
});
$(window).resize(function(){
	if($(window).width() > 975)
	{
		var parentWidth = $('#frmCheckout').width();
		var newWidth = parentWidth * 0.333;
		$('.total-sidebar').css('width',newWidth+'px').css('position','fixed').css('left',$('#frmCheckout').offset().left + parentWidth - newWidth + 'px');
	}
});

//var PaReq = document.getElementById('PaReq');
//var TermUrl = document.getElementById('TermUrl');
//var MD = document.getElementById('MD');
var form3ds = document.forms['ThreeDForm'];
var creq = document.getElementById('creq');
var btnCheckout = $('#frmCheckoutSubmit');

$('#paymentMethodTabs a input').click(function (e) {
	$(this).parent().tab('show');
});
$('#paymentMethodTabs a').click(function (e) {
	$(this).tab('show');
	$(this).find('input').prop('checked', true);
	
	console.log($(this).find('input').val());
	
	$('#valuePaymentMethod').val($(this).find('input').val());
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
		var newHtml = '<option value="">Region</option>';
		$.each(data,function(key, item) {
			newHtml = newHtml + '<option value="' + item.id + '"' + (regionId == item.id ? ' selected' : '') + '>' + item.name + '</option>';
		});

		$('select[name="billingAddress[region]"]').html(newHtml);
		$('select[name="billingAddress[region]"]').change();
	})
	.fail(function(data) {

	});
});

var amountCustomOriginal = '';
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
			//$('#discountTotal .amount').html(data.formattedDiscount);
			//$('#tax .amount').html(data.formattedTax);
			//$('#grandTotal .amount').html(data.formattedTotal);

			$('#amountCustom').html(data.formattedDiscountedTotal);
			$('#totaldueCustom').html(data.formattedDiscountedTotal);
			$('#divDiscountCodeMessage').html('<div class="alert alert-success">Discount Applied: ' + data.formattedDiscount + '</div>');
		}
		else
		{
			$('#divDiscountCodeMessage').html('<div class="alert alert-danger">Invalid code</div>');
			$('#amountCustom').html(amountCustomOriginal);
			$('#totaldueCustom').html(amountCustomOriginal);
		}

		//$('#grandTotal .amount').html(data.grandTotal.toFixed(2));
	})
	.fail(function(data) {
		$('#btnDiscountCode').prop('disabled',false);
	});
});

$('#btnRemoveDiscountCode').click(function() {
		$('#divDiscountCodeMessage').html('');
		$('#discountCode').val('');
		$('#amountCustom').html(amountCustomOriginal);
		$('#totaldueCustom').html(amountCustomOriginal);
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
		if(parseFloat(data.tax) > 0 && data.taxName != 0)
		{
			$('#taxName').html(data.taxName);
			$('#taxAmount').html(data.currency + data.tax.toFixed(2));
			$('#taxAmountHidden').html(data.currency + data.tax.toFixed(2));
			$('#taxAmountHidden').val(data.tax.toFixed(2));
			$('#taxAmount_1').html(data.currency + data.tax.toFixed(2));
			$('#totaldueCustom').html(data.currency + data.grandTotal.toFixed(2));
			$('#grandTotalBasket').html($('#totaldueCustom').html());

			$('#tax').css('display','block');
		}
		else
		{
			//$('#tax').css('display','none');
			$('#taxName').html(data.taxName);
			$('#taxAmount').html(data.currency + '0.00');
			$('#taxAmountHidden').html(data.currency + '0.00');
			$('#taxAmount_1').html(data.currency + '0.00');
			var total = data.grandTotal - data.tax;
			$('#totaldueCustom').html(data.currency + total.toFixed(2));
			$('#grandTotalBasket').html($('#totaldueCustom').html());
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
	btnCheckout.removeClass('btn-danger');
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
		if (jqXHR.status === 202) {
			filledAuthorized(data);
			$('#3dsConfirm').modal({
                escapeClose: false,
                clickClose: false,
                showClose: false
			});
		}

		if (data.errors && data.errors[0] !== null) {

			$('.errMsgs').html('');

			btnCheckout.addClass('btn-danger');

			 $.each(data.errors,function(k, v) {
			 	$.each(v.inputs, function(k2, v2){
			 		$('[name="'+v2+'"]').parent().addClass('has-error');
			 		$('[name="'+v2+'"]').addClass('errStatus text-danger');
			 	});
			 	$('.errMsgs').append('<div class="alert alert-danger">'+v.error_message+'</div>');
			 });
		} else {
			btnCheckout.addClass('btn-success');
			if (data.redirect) {
				window.location.assign('/checkout/receipt/');
			}

			if (data.offsiteGateway) {
				$('body').append(data.offsiteGateway);
				$('#frmOffsiteGateway').submit();
			}
		}
	}).fail(function(data) {
		if (data.status === 402) {
            window.location.replace("/fraud");
		}

		$('span.input-item-error').replaceWith();
		$('.has-error').removeClass('has-error');

		if (data.status == 403) {
            var errors = data.responseJSON.errors;

            $.each(errors, function (key, val) {
            	var field = key.split('.');
            	if (field !== undefined && field.length >= 3) {
            		$('[name="' + field[0] + '[' + field[1] + ']' + '[' + field[2] + ']"]').addClass('has-error');
	                $('[name="' + field[0] + '[' + field[1] + ']' + '[' + field[2] + ']"]').after('<span class="input-item-error">' + val[0] + '</span>');
            	} else if(field.length >= 2) {
	                $('[name="' + field[0] + '[' + field[1] + ']"]').addClass('has-error');
	                $('[name="' + field[0] + '[' + field[1] + ']"]').after('<span class="input-item-error">' + val[0] + '</span>');
	            } else {
	                /* $('[name="' + field[0] + ']"]').addClass('has-error');
	                $('[name="' + field[0] + ']"]').after('<span class="input-item-error">' + val[0] + '</span>'); */
									$('#' + field[0]).addClass('has-error');
									$('#' + field[0]).after('<span class="input-item-error">' + val[0] + '</span>');
	            }
            });
        }

		btnCheckout.addClass('btn-danger');
	}).always(function () {
		btnLoading(false);
    });
}

btnCheckout.on('click', frmCheckoutSubmit);

function frmCheckoutSubmit() {
    if (!$('input[name="term"]').prop('checked')) {
        $('.agree-error').html('<p style="color: red!important;">Agreements is required.</p>');
        return;
    } else {
        $('.agree-error').text('');
    }

    if ($('#companyVat').val() == 'yes' && $('#vatNumberValidated').val() == 'no')
    {
        $('#spanVatNumber').remove();
        $('#vatNumber').removeClass('has-success');
        $('#vatNumber').addClass('has-error');
        $('#vatNumber').after('<span id="spanVatNumber" class="input-item-error">Please validate VAT Number</span>');
        return;
    }

    btnLoading(true);
		$('#errMsgs').html('');
		$('#errMsgs').attr('style', 'display:none');

		//check if stock or not
		$.ajax({
							url: "/checkout/check_outofstock",
							type: 'GET',
							async: false,
							data: {
											package_id : $("#package_id").val(),
							},
							success: function(result){
														if(result.action == 'out_of_stock')
														{
																$('#errMsgs').attr('style', 'display:1');
																$('#errMsgs').html(result.message);
																window.scrollTo(0, 0);
																btnLoading(false);
														}
														else
														{
																checkFraud();
														}
												}
							});
}

function executePayment()
{
		if(!updatedTax) {
			getRegionData(frmCheckoutSubmit);
		} else {
			var valuePaymentMethod = $('#valuePaymentMethod').val();
			if(valuePaymentMethod == '0' && $('[name="chosenCC"]').length && $('[name="chosenCC"]:checked').val() > 0) {
				//console.log('submitData 0 '+valuePaymentMethod);
				submitData();
			} else if(valuePaymentMethod == '1' && $('[name="chosenBank"]').length && $('[name="chosenBank"]:checked').val() > 0) {
				//console.log('submitData 1 '+valuePaymentMethod);
				submitData();
			} else if(valuePaymentMethod != '') {
				//console.log('submitData '+valuePaymentMethod);
				submitData();
			} else {
				//console.log('startPayment '+valuePaymentMethod);
				startPayment();
			}
		}
}

function checkFraud()
{
	$.ajax({
						url: "/checkout/check_fraud",
						type: 'GET',
						async: false,
						data: {
										firstname : $("input[name='billingInfo[firstname]']").val(),
										lastname : $("input[name='billingInfo[lastname]']").val(),
										email : $("input[name='billingInfo[email]']").val(),
										phone : $("input[name='billingInfo[phone]']").val(),
										address : $("input[name='billingAddress[address1]']").val(),
										city : $("input[name='billingAddress[city]']").val(),
										state : $("select[name='billingAddress[region]']").val(),
										postcode : $("input[name='billingAddress[zip]']").val(),
										country : $("select[name='billingAddress[country]']").val()
						},
						success: function(result){
													if(result.action == 'fraudlabs')
													{
															if(result.fraudlabs_status == "APPROVE")
															{
																	executePayment();
															}
															else
															{
																	window.location.replace("/fraud");
															}
													}
													else if(result.action == 'execute_payment')
													{
														
															executePayment();
													}
											}
						});
}

function filledAuthorized(data) {
	form3ds.action = data.acsUrl;
	creq.value = data.creq;
}

function btnLoading(disabled) {
    btnCheckout.prop('disabled', disabled);
    btnCheckout.text(disabled ? 'LOADING...' : 'CONFIRM PAYMENT');
    if (disabled) btnCheckout.addClass('disabled');
    else btnCheckout.removeClass('disabled');
}
