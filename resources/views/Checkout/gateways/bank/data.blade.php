<div role="tabpanel" class="tab-pane {!! $paymentMethods['checked'] == 'bank' ? 'active' : '' !!}" id="bank">
					<div class="row">
						<div class="col-xs-12 directions">
							<p>{{ trans('frontend.chk-banksub') }}</p>
						</div>
						@if($savePaymentsAllowed)
							<div class="col-xs-6">
								<label class="check">
								<input type="checkbox" name="paymentMethod[banksave]" value="1"/> {{ trans('frontend.chk-banksave') }}
								<span class="checkmark">
							</label>
							</div>
							<div class="col-xs-6">
								<input type="checkbox" name="paymentMethod[bankautocharge]" value="1"/> {{ trans('frontend.chk-bankautocharge') }}
							</div>
						@endif
						@if($paymentMethods['bankGateway'] == 'gocardless')
							<div class="col-xs-12 col-md-4 center-block">
								<input class="form-control tokenSkip" type="text" name="paymentMethod[account]" placeholder="{{ trans('frontend.chk-accountnum') }}"/>
							</div>
							<div class="col-xs-12 col-md-4 center-block">
								<input class="form-control tokenSkip" type="text" name="paymentMethod[iban]" placeholder="IBAN"/>
							</div>
							<div class="col-xs-12 col-md-4 center-block">
								<input class="form-control tokenSkip" type="text" name="paymentMethod[bankCode]" placeholder="bank code"/>
							</div>
							<div class="col-xs-12 col-md-4 center-block">
								<input class="form-control tokenSkip" type="text" name="paymentMethod[branchCode]" placeholder="branch code"/>
							</div>
						@else
							<div class="col-xs-12 center-block">
								<select class="" ="form-control tokenSkip" name="paymentMethod[bank_type]">
									<option value="0">{{ trans('frontend.chk-checking') }}</option>
									<option value="1">{{ trans('frontend.chk-savings') }}</option>
								</select>
							</div>
							<div class="col-xs-12 center-block"><input class="form-control tokenSkip" type="text" name="paymentMethod[account]" placeholder="{{ trans('frontend.chk-accountnum') }}"/></div>
							<div class="col-xs-12 center-block"><input class="form-control tokenSkip" type="text" name="paymentMethod[routing]" placeholder="{{ trans('frontend.chk-routingnum') }}"/></div>
						@endif
					</div>
				</div>
