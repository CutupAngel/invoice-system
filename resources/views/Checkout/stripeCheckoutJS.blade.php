<script type="text/javascript" src="https://js.stripe.com/v3/"></script>
<script type="text/javascript">
	function startPayment()
	{
		 doPayment();
	}

	//new method with 3D Secure SCA (Strong Customers Authentication)
	// Create a Stripe client.
	var stripe = Stripe("{{ $userAdmin->getSetting('stripe.publishablekey') }}");

	// Collect payment method details on the client
	var elements = stripe.elements();
	var cardElement = elements.create('card');
	cardElement.mount('#card-element');

	function doPayment()
	{
			if($('#term').is(":checked") == false) {
				return false;
			}

			if($('input[name="paymentMethod[type]"]').val() == 0) {
				// Submit the payment to Stripe from the client
				var cardholderName = document.getElementById('card-name');
				var cardButton = document.getElementById('frmCheckoutSubmit');
				var cardMessage = document.getElementById('card-message');  // for testing (to remove)

				//cardMessage.textContent = "Calling handleCardPayment..."; // for testing (to remove)
				//$('#frmCheckoutSubmit').html('<i class="fas fa-circle-notch fa-spin"></i>');
				//$('#frmCheckoutSubmit').prop('disabled', true);

				//for using saved credit card
				if($('input[name="select_card"]').length && $('input[name="select_card"]:checked').val() == 'existing') {
					if($('#saved_stripe_card').val() == '') {
						$('#report_message').html('{{ trans("frontend.chk-selectsavedcardstripenull") }}');
						$('#report_message').removeClass('alert-success');
						$('#report_message').addClass('alert-danger');
						$('#report_message').attr('style', '');
						return;
					}

					$('#report_message').html('');
					$('#report_message').removeClass('alert-success');
					$('#report_message').addClass('alert-danger');
					$('#report_message').attr('style', 'display:none;');

					@if($user)
						grandTotal = parseFloat('{{ $basketGrendTotal }}');
						if($('#companyVat').val() == 'yes' && $('#useTax').val() == 'no')
						{
								grandTotal = grandTotal - parseFloat($('#taxAmountHidden').val());
						}
						$.ajax({
											url: "/checkout/getPaymentIntentSavedCardStripe",
											async: false,
											data: {
													    amount: grandTotal, //parseFloat('{{ $basketGrendTotal }}'),
													    currency: '{{ $currency->short_name }}',
													    customer: '{{ $user->stripeId }}',
													    payment_method: $('#saved_stripe_card').val()
										  },
											success: function(result){
												if(result.error_code) { //error authentication_required
													$('#report_message').html(result.payment_intent.last_payment_error.message);
													$('#report_message').removeClass('alert-success');
													$('#report_message').addClass('alert-danger');
													$('#report_message').attr('style', '');

													stripe.confirmCardPayment(result.payment_intent.client_secret, {
														payment_method: result.payment_intent.last_payment_error.payment_method.id
														}).then(function(result) {
															if (result.error) {
																// Show error to your customer
																$('#report_message').html(result.error.message);
																$('#report_message').removeClass('alert-success');
																$('#report_message').addClass('alert-danger');
																$('#report_message').attr('style', '');
															}
															else {
																if (result.paymentIntent.status === 'succeeded') {
																	$('#transaction_id').val(result.paymentIntent.id);
																	$('#transaction_json').val(JSON.stringify(result));
																	submitData();
																}
															}
														});
												}
												else {
																$('#transaction_id').val(result.id);
																$('#transaction_json').val(JSON.stringify(result));
																submitData();
														 }
											}
									});
						@endif
				}

				//for save card
				if($('#cc_save').is(":checked") == true) {
					//unlogged user

					//get add customer from stripe
					$.ajax({
										url: "/checkout/getOrAddCustomerStripe",
										async: false,
										success: function(result){
				    								$('#customer_stripe_id').val(result.id);
														clientSecret = result.client_secret;
				  					}
								});

					//create setup intent stripe
					$.ajax({
										url: "/checkout/createSetupIntentStripe",
										async: false,
										data: {
												    stripeId: $('#customer_stripe_id').val(),
									  },
										success: function(result){
														clientSecret = result.client_secret;
										}
								});

					stripe.confirmCardSetup(
				    clientSecret,
				    {
				      payment_method: {
				        card: cardElement,
				        billing_details: {
				          name: cardholderName.value,
				        },
				      },
				    }
				  ).then(function(result) {
					    if (result.error) {
								$('#report_message').html(result.error.message);
								$('#report_message').removeClass('alert-success');
								$('#report_message').addClass('alert-danger');
								$('#report_message').attr('style', '');
                            btnLoading(false);
								//$('#frmCheckoutSubmit').html('{{ trans("frontend.chk-confirm") }}');
								//$('#frmCheckoutSubmit').prop('disabled', false);
								return;
					    } else {
								// Pass the PaymentIntent’s client secret to the client
								var paymentIntent;
								fetch('/checkout/payment_intents').then(function (r) {
										return r.json();
								}).then(function (response) {
										paymentIntent = response;
										//console.log("Fetched PI: ", response);
										$('#transaction_id').val(response.id);
										$('#transaction_json').val(JSON.stringify(response));

										stripe.handleCardPayment(
												paymentIntent.client_secret, cardElement, {
														payment_method_data: {
																billing_details: {name: cardholderName.value}
														}
												}
										).then(function (result) {
												if (result.error) {
													$('#report_message').html(result.error.message);
													$('#report_message').removeClass('alert-success');
													$('#report_message').addClass('alert-danger');
													$('#report_message').attr('style', '');
													//$('#frmCheckoutSubmit').html('{{ trans("frontend.chk-confirm") }}');
													//$('#frmCheckoutSubmit').prop('disabled', false);
                                                    btnLoading(false);
													return;
												}
												$('#transaction_id').val(result.paymentIntent.id);
												$('#transaction_json').val(JSON.stringify(result));
												submitData();
												//cardMessage.textContent = JSON.stringify(result, null, 2); // for testing (to remove)
										});
								});
					    }
					  });
				}
				else if(($('input[name="select_card"]').length && $('input[name="select_card"]:checked').val() == 'new') || $('input[name="select_card"]').length == false) { //not save card

					// Pass the PaymentIntent’s client secret to the client
					var paymentIntent;
					fetch('/checkout/payment_intents').then(function (r) {
							return r.json();
					}).then(function (response) {
							paymentIntent = response;
							//console.log("Fetched PI: ", response);
							$('#transaction_id').val(response.id);
							$('#transaction_json').val(JSON.stringify(response));

							stripe.handleCardPayment(
									paymentIntent.client_secret, cardElement, {
											payment_method_data: {
													billing_details: {name: cardholderName.value}
											}
									}
							).then(function (result) {
									if (result.error) {
										$('#report_message').html(result.error.message);
										$('#report_message').removeClass('alert-success');
										$('#report_message').addClass('alert-danger');
										$('#report_message').attr('style', '');
										//$('#frmCheckoutSubmit').html('{{ trans("frontend.chk-confirm") }}');
										//$('#frmCheckoutSubmit').prop('disabled', false);
                                        btnLoading(false);
										return;
									}
									$('#transaction_id').val(result.paymentIntent.id);
									$('#transaction_json').val(JSON.stringify(result));
									submitData();
									//cardMessage.textContent = JSON.stringify(result, null, 2); // for testing (to remove)
							});
						});
					}
				}
		}

		function SelectCard()
		{
				$("#select_card").prop("checked", true);
		}
</script>
