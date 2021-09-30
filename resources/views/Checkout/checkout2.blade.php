@extends('Common.frontendLayout')
@section('title', 'Checkout')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://v2.b-cdn.uk/dist/css/checkout2.css"/>

    <style>
        .has-error {
            border: 1px solid red !important;
        }
    </style>
@endsection

@php
    if(Auth::user()) {
        $getMyCredit = \App\MiscStorage::where('user_id', Auth::user()->id)->where('name','account-credit')->first();
        if($getMyCredit) {
            $credit = $getMyCredit->value;
        } else {
            $credit = 0;
        }
    } else {
        $credit = 0;
    }
@endphp

@section('content')

    <div class="jumbotron">
        <div class="container">
            <div class="content-header">
                Checkout
            </div><!-- content-header -->

            @if(session()->has('error_payment'))
                <div class="alert alert-danger">
                    <ul>
                        @if(is_array(session()->get('error_payment')))
                            @foreach(session()->get('error_payment') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        @else
                            <li>{{ session()->get('error_payment') }}</li>
                        @endif

                        @if(session()->has('cardinity_errors'))
                            @if(is_array(session()->get('cardinity_errors')))
                                @foreach(session()->get('cardinity_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            @else
                                <li>{{ session()->get('cardinity_errors') }}</li>
                            @endif
                        @endif
                    </ul>
                </div>
            @endif

            <div id="errMsgs" class="alert alert-danger" style="display:none;"></div>

            <form id="frmCheckout" autocomplete="off" class="form-horizontal">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-4 hidden-xs"></div>

                                <div class="col-md-4 hidden-xs"></div>
                            </div>
                            @if (!$user)
                                <div id="accountInformation">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h2>{{ trans('frontend.chk-new') }}</h2>
                                        </div>
                                        <div class="col-md-8 hidden-xs">

                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 directions">
                                            <span>{{ trans('frontend.chk-newsub') }}</span>
                                        </div>
                                        <div class="col-md-6 alreadyHaveAccount">
                                            <a href="/auth/login?url=/checkout">{{ trans('frontend.chk-account') }}</a>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input class="form-control" type="text" name="account[email]"
                                                   placeholder="{{ trans('frontend.chk-email') }}"
                                                   value="{{ old('email') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <input class="form-control" type="text" name="account[businessname]"
                                                   placeholder="{{ trans('frontend.chk-businessname') }}"
                                                   value="{{ old('businessname') }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input class="form-control" type="password" name="account[password]"
                                                   placeholder="{{ trans('frontend.chk-password') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <input class="form-control" type="password"
                                                   name="account[password_confirmation]"
                                                   placeholder="{{ trans('frontend.chk-cnfpassword') }}"/>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @include('Checkout.formBillingAddress')

                            @if ((isset($paymentMethods['card']) && $paymentMethods['card']) || (isset($paymentMethods['stripe']) && $paymentMethods['stripe']) || (isset($paymentMethods['bank']) && $paymentMethods['bank']) ||
                            (isset($paymentMethods['banktransfer']) && $paymentMethods['banktransfer']) || (isset($paymentMethods['offsite']) && !empty($paymentMethods['offsite'])))
                                @include('Checkout.payments')
                            @else
                                <div class="alert alert-danger">
                                    {{ trans('frontend.chk-no-paymentmethods') }}
                                </div>
                            @endif
                        </div>
                        @include('Checkout.sidebar')
                    </div>
                </div>
                <input type="hidden" name="transaction_id" id="transaction_id"/>
                <input type="hidden" name="transaction_json" id="transaction_json"/>
                <input type="hidden" name="customer_stripe_id" id="customer_stripe_id"/>
                <input type="hidden" name="package_id" id="package_id" value="{{ $package_id }}"/>

                <input type='hidden' id='screen_width' name='screen_width' value=''/>
                <input type='hidden' id='screen_height' name='screen_height' value=''/>
                <input type='hidden' id='browser_language' name='browser_language' value=''/>
                <input type='hidden' id='color_depth' name='color_depth' value=''/>
                <input type='hidden' id='time_zone' name='time_zone' value=''/>

            </form>
            <div id="3dsConfirm" class="modal">
                <p>
                    If your browser does not start loading the page,
                    press the button below.
                    You will be sent back to this site after you
                    authorize the transaction.
                </p>
                <!--
                <form name="ThreeDForm" method="POST" action="">
                    <button class="btn btn-success" type=submit>Authorize</button>
                    <input type="hidden" name="PaReq" value="" id="PaReq" />
                    <input type="hidden" name="TermUrl" value="" id="TermUrl" />
                    <input type="hidden" name="MD" value="" id="MD" />
                </form>
            -->
                <form name="ThreeDForm" method="POST" action="">
                    <button class="btn btn-success" type=submit>Authorize</button>
                    <input type="hidden" name="creq" id="creq" value=""/>
                    <input type="hidden" name="threeDSSessionData" id="threeDSSessionData" value=""/>
                </form>
            </div>
        </div><!-- center --></div>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
    <script type="text/javascript">
        var csrf_token = '{{csrf_token()}}';
    </script>
    @if($tokenPayment && $userAdmin->getSetting('site.defaultGateway') && $basketGrendTotal > 0)
        @include('Checkout.'.$userAdmin->getSetting('site.defaultGateway').'CheckoutJS')
    @else
        <script type="text/javascript">
            function startPayment() {
                submitData();
            }

            document.addEventListener("DOMContentLoaded", function () {
                document.getElementById("screen_width").value = screen.availWidth;
                document.getElementById("screen_height").value = screen.availHeight;
                document.getElementById("browser_language").value = navigator.language;
                document.getElementById("color_depth").value = screen.colorDepth;
                document.getElementById("time_zone").value = new Date().getTimezoneOffset();
            });
        </script>
    @endif
    <script type="text/javascript" src="/dist/js/pages/checkout.js"></script>
    <!-- <script type="text/javascript" src="https://v2.b-cdn.uk/new-theme/js/checkout.js"></script> -->
    <script type="text/javascript">

        $(function () {
            $('#companyVat').on('change', showHideVat);
            if ($('#vatNumber').val() != '') {
                showHideVat();
                validateVat();
            }

            function showHideVat() {
                var companyVat = $('#companyVat').val();
                if (companyVat == 'no') {
                    $("#vatNumber").attr('style', 'display:none;');
                    $('#spanVatNumber').remove();
                    $('#vatNumber').val('');
                    $('#vatNumberValidated').val('no');

                    //display TAX
                    $('#taxtotal').attr('style', '');
                    $('#totaldueCustom').html($('#currencySymbolHidden').val() + $('#totalDueHidden').val());
                } else if (companyVat == 'yes') {
                    $("#vatNumber").attr('style', '');
                }
            }

            $('#vatNumber').on('blur', validateVat);

            function validateVat() {
                $.ajax({
                    url: '/checkout/validate_vat',
                    type: 'GET',
                    data: {
                        vatNumber: $('#vatNumber').val(),
                        country_id: $('select[name="billingAddress[country]"]').val()
                    },
                    success: function (result) {
                        if (result.success) {
                            $('#spanVatNumber').remove();
                            $('#vatNumber').removeClass('has-error');
                            $('#vatNumber').addClass('has-success');
                            $('#vatNumber').after('<span id="spanVatNumber" class="input-item-success">' + result.message + '</span>');
                            $('#vatNumberValidated').val('yes');

                            if (result.use_vat) {
                                $('#useTax').val('no');

                                //hide TAX
                                $('#taxtotal').attr('style', 'display:none;');
                                var totalDue = $('#totalDueHidden').val() - $('#taxAmountHidden').val();
                                var totalDue = totalDue.toFixed(2);
                                $('#totaldueCustom').html($('#currencySymbolHidden').val() + totalDue);
                            } else {
                                $('#useTax').val('yes');

                                //display TAX
                                $('#taxtotal').attr('style', '');
                                $('#totaldueCustom').html($('#currencySymbolHidden').val() + $('#totalDueHidden').val());
                            }
                        } else {
                            $.each(result.errors, function (key, val) {
                                $('#spanVatNumber').remove();
                                $('#vatNumber').removeClass('has-success');
                                $('#vatNumber').addClass('has-error');
                                $('#vatNumber').after('<span id="spanVatNumber" class="input-item-error">' + val[0] + '</span>');
                            });
                            $('#vatNumberValidated').val('no');
                            $('#useTax').val('yes');

                            //display TAX
                            $('#taxtotal').attr('style', '');
                            $('#totaldueCustom').html($('#currencySymbolHidden').val() + $('#totalDueHidden').val());
                        }
                    }
                });
            }

        });

        var basket = JSON.parse('{!! json_encode($basketItems) !!}');
        var taxes = JSON.parse('{!! json_encode($taxes) !!}');
        var useCredit = document.getElementById('useCredit');
        var credit = "{{ floatval($credit) / $default_currency->conversion * $currency->conversion }}";
        var totalPayment = "{{ ($basketTotal / $default_currency->conversion * $currency->conversion) }}";
        var totalDuePayment = "{{ ($basketGrendTotal / $default_currency->conversion * $currency->conversion) }}";
        var defaultCurrency = "{{ $default_currency->conversion }}";
        var currencyConverter = "{{ $currency->conversion }}";

        $('#useCredit').click(function () {
            var tax = 0;
            $.each(basket, function (key, val) {
                var tmp = val.price;

                if (useCredit.checked === true) {
                    if (credit >= val.price) {
                        totalPayment -= credit;
                        credit = credit - val.price;
                        $('#taxAmount_' + val.id).html(`{!! $currency->symbol !!}` + parseFloat("0").toFixed(2));
                        $('#creditCustom').html(`{!! $currency->symbol !!}` + parseFloat(credit).toFixed(2));
                        tmp = 0;
                    } else {
                        totalPayment -= credit;
                        tmp = val.price - credit;
                        tax += taxes[val.id].name / 100 * tmp;
                        credit = 0;
                        $('#taxAmount_' + val.id).html(`{!! $currency->symbol !!}` + parseFloat(taxes[val.id].name / 100 * tmp).toFixed(2));
                        $('#creditCustom').html(`{!! $currency->symbol !!}` + parseFloat(credit).toFixed(2));
                        $('#totaldueCustom').html(`{!! $currency->symbol !!}` + parseFloat(
                            totalPayment + tax
                        ).toFixed(2));
                    }
                } else {
                    tax += taxes[val.id].tax;
                    totalPayment = "{{ ($basketTotal / $default_currency->conversion * $currency->conversion) }}";
                    tmp = val.price / defaultCurrency * currencyConverter;
                    $('#taxAmount_' + val.id).html(`{!! $currency->symbol !!}` + parseFloat(taxes[val.id].tax).toFixed(2));
                    credit = "{{ floatval($credit) / $default_currency->conversion * $currency->conversion }}";
                    $('#creditCustom').html(`{!! $currency->symbol !!}` + parseFloat(credit).toFixed(2));
                    $('#totaldueCustom').html(`{!! $currency->symbol !!}` + parseFloat(
                        "{{ ($basketGrendTotal / $default_currency->conversion * $currency->conversion) }}"
                    ).toFixed(2));
                }
            });

            $('#amountCustom').html(`{!! $currency->symbol !!}` + parseFloat(totalPayment).toFixed(2));
        });
    </script>

@stop
