<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
<head style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
    <!-- If you delete this meta tag, Half Life 3 will never be released. -->
    <meta name="viewport" content="width=device-width" style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" />

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" />
    <title style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >Invoice</title>
</head>

<body bgcolor="#e8e8e8" style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;-webkit-font-smoothing:antialiased;-webkit-text-size-adjust:none;width:100%!important;height:100%;" >

    <!-- HEADER -->
    <table class="head-wrap" style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;width:100%;" >
        <tr style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
            <td style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" ></td>
            <td class="header container" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;display:block!important;max-width:600px!important;margin-top:0 !important;margin-bottom:0 !important;margin-right:auto !important;margin-left:auto !important;clear:both!important;" >
                <div class="content" style="font-family:'Raleway', sans-serif;background-color:#06afc3;padding-top:15px;padding-bottom:15px;padding-right:15px;padding-left:15px;max-width:600px;margin-top:0;margin-bottom:0;margin-right:auto;margin-left:auto;display:block;" >
                    <table class="header-padding" style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;width:100%;" >
                        <tr style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
                            <td style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
                                @if ($site('logo'))
        <b><img src="{{config('app.CDN')}}{{ $site('logo') }}" width="250" alt="{{$site('name')}}"></b>
      @else
        <b>{{ $site('name') }}</b>
      @endif

                            </td>
                            <td align="right" style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
                                <h6 class="collapse" style="font-family:Raleway;line-height:1.1;font-weight:900;font-size:14px;text-transform:uppercase;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;margin-top:0 !important;margin-bottom:0 !important;margin-right:0 !important;margin-left:0 !important;color:#fff;" >
									{{$user->mailingContact->address->address_1}}
									@if($user->mailingContact->address->address_2)
										<br>{{$user->mailingContact->address->address_2}}
									@endif
									@if($user->mailingContact->address->address_3)
										<br>{{$user->mailingContact->address->address_3}}
									@endif
									@if($user->mailingContact->address->address_4)
										<br>{{$user->mailingContact->address->address_4}}
									@endif
									@if($user->mailingContact->address->city)
										<br>{{$user->mailingContact->address->city}}
									@endif
									@if($user->mailingContact->address->county->name)
										<br>{{$user->mailingContact->address->county->name}}
									@endif
									@if($user->mailingContact->address->country->name)
										<br>{{$user->mailingContact->address->country->name}}
									@endif
									@if($user->mailingContact->address->postal_code)
										<br>{{$user->mailingContact->address->postal_code}}
									@endif
								</h6>
                            </td>
                        </tr>
                    </table>
                </div>

            </td>
            <td style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" ></td>
        </tr>
    </table>
    <!-- /HEADER -->

	<h2>This is a renewal invoice</h2>	
	<div>The invoice listed below has come to its renewal term. You can log in to your account to make regular payments or update your information. The payment is due no later than the {{ date('d/m/Y', strtotime($invoice->due_at)) }}.</div>
	
	<br>
	<br>
	
    <table class="body-wrap" style="margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;margin-top:-3px;width:100%;" >
        <tr style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
            <td style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" ></td>
            <td class="container" bgcolor="#FFFFFF" style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;display:block!important;max-width:600px!important;margin-top:0 !important;margin-bottom:0 !important;margin-right:auto !important;margin-left:auto !important;clear:both!important;" >

                <div class="content" style="font-family:'Raleway', sans-serif;padding-top:15px;padding-bottom:15px;padding-right:15px;padding-left:15px;max-width:600px;margin-top:0;margin-bottom:0;margin-right:auto;margin-left:auto;display:block;" >
                    <table style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;width:100%;" >
                        <tr style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
                            <td style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
                                <table class="invoice-info" style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;width:100%;" >
                                    <tr style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
                                        <td style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;padding-top:2px;padding-bottom:2px;padding-right:2px;padding-left:2px;font-size:14px;color:#757575;" >Client
                                        </td>
                                        <td style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;padding-top:2px;padding-bottom:2px;padding-right:2px;padding-left:2px;font-size:14px;color:#757575;" >@if($customer->mailingContact->address->contact_name)
											{{$customer->mailingContact->address->contact_name}}
										@else
											{{$customer->name}}
										@endif
                                        </td>
                                    </tr>
                                    <tr style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
                                        <td style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;padding-top:2px;padding-bottom:2px;padding-right:2px;padding-left:2px;font-size:14px;color:#757575;" >Invoice #
                                        </td>
                                        <td style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;padding-top:2px;padding-bottom:2px;padding-right:2px;padding-left:2px;font-size:14px;color:#757575;" >{{$user->getSetting('invoice.prefix','')}}{{$invoice->invoice_number}}
                                        </td>
                                    </tr>
                                    <tr style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
                                        <td style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;padding-top:2px;padding-bottom:2px;padding-right:2px;padding-left:2px;font-size:14px;color:#757575;" >Created
                                        </td>
                                        <td style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;padding-top:2px;padding-bottom:2px;padding-right:2px;padding-left:2px;font-size:14px;color:#757575;" >{{ date('d/m/Y', strtotime($invoice->created_at)) }}
                                        </td>
                                    </tr>
                                    <tr style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
                                        <td style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;padding-top:2px;padding-bottom:2px;padding-right:2px;padding-left:2px;font-size:14px;color:#757575;" >Due
                                        </td>
                                        <td style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;padding-top:2px;padding-bottom:2px;padding-right:2px;padding-left:2px;font-size:14px;color:#757575;" >{{ date('d/m/Y', strtotime($invoice->due_at)) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
                                <table class="invoice-info" style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;width:100%;" >
                                    <tr style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
                                        <td align="right" style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;padding-top:2px;padding-bottom:2px;padding-right:2px;padding-left:2px;font-size:14px;color:#757575;" >
                                            <p style="margin-top:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;margin-bottom:10px;font-weight:normal;font-size:14px;line-height:1.6;" >
                                                {{$customer->mailingContact->address->address_1}}
												@if($customer->mailingContact->address->address_2)
													<br>{{$customer->mailingContact->address->address_2}}
												@endif
												@if($customer->mailingContact->address->address_3)
													<br>{{$customer->mailingContact->address->address_3}}
												@endif
												@if($customer->mailingContact->address->address_4)
													<br>{{$customer->mailingContact->address->address_4}}
												@endif
												@if($customer->mailingContact->address->city)
													<br>{{$customer->mailingContact->address->city}}
												@endif
												@if($customer->mailingContact->address->county->name)
													<br>{{$customer->mailingContact->address->county->name}}
												@endif
												@if($customer->mailingContact->address->country->name)
													<br>{{$customer->mailingContact->address->country->name}}
												@endif
												@if($customer->mailingContact->address->postal_code)
													<br>{{$customer->mailingContact->address->postal_code}}
												@endif
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <br/>
                    <table class="heading-style" style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;border-spacing:0;width:100%;" >
                        <tr style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
                            <th style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;background-color:#06afc3;padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;color:#fff;" >Item
                            </th>
                            <th style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;background-color:#06afc3;padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;color:#fff;" >Description
                            </th>
                            <th style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;background-color:#06afc3;padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;color:#fff;" >Qty
                            </th>
                            <th style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;background-color:#06afc3;padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;color:#fff;" >Price
                            </th>
                            <th style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;background-color:#06afc3;padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;color:#fff;" >Total
                            </th>
                        </tr>
						@foreach($invoice->items as $item)
                        <tr style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
                            <td style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CEC7C7;font-size:15px;padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >{{$item->item}}
                            </td>
                            <td style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CEC7C7;font-size:15px;padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >{{$item->description}}
                            </td>
                            <td style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CEC7C7;font-size:15px;padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >{{$item->quantity}}
                            </td>
                            <td style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CEC7C7;font-size:15px;padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >{{$currency->symbol}}{{number_format($item->price,2)}}
                            </td>
                            <td style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CEC7C7;font-size:15px;padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >{{$currency->symbol}}{{number_format($item->tax,2)}}
                            </td>
                            <td style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CEC7C7;font-size:15px;padding-top:10px;padding-bottom:10px;padding-right:10px;padding-left:10px;" >{{$currency->symbol}}{{number_format($item->price * $item->quantity,2)}}
                            </td>
                        </tr>
						@endforeach
                        <tr class="no-border" style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
                            <td colspan="3" style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CEC7C7;font-size:15px;border-width:0px;padding-top:3px;padding-bottom:3px;padding-right:3px;padding-left:3px;" > 
                            </td>
                             
                            <td style="margin-top:0;text-align:center;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CEC7C7;font-size:15px;border-width:0px;padding-top:3px;padding-bottom:3px;padding-right:3px;padding-left:3px;" >Subtotal
                            </td>
                            <td style="margin-top:0;text-align:center;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CEC7C7;font-size:15px;border-width:0px;padding-top:3px;padding-bottom:3px;padding-right:3px;padding-left:3px;" >{{$currency->symbol}}{{number_format($subTotal,2)}}
                            </td>
                        </tr>
						@foreach($invoice->totals as $total)
                        <tr class="no-border" style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
                            <td colspan="3" style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CEC7C7;font-size:15px;border-width:0px;padding-top:3px;padding-bottom:3px;padding-right:3px;padding-left:3px;" > 
                            </td>
                             
                            <td style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CEC7C7;font-size:15px;border-width:0px;padding-top:3px;padding-bottom:3px;padding-right:3px;padding-left:3px;" >{{$total->item}}
                            </td>
                            <td style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CEC7C7;font-size:15px;border-width:0px;padding-top:3px;padding-bottom:3px;padding-right:3px;padding-left:3px;" >{{$currency->symbol}}{{number_format($total->price,2)}}
                            </td>
                        </tr>
						@endforeach
                        <tr class="no-border" style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" >
                            <td colspan="3" style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CEC7C7;font-size:15px;border-width:0px;padding-top:3px;padding-bottom:3px;padding-right:3px;padding-left:3px;" > 
                            </td>
                             
                            <td style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CEC7C7;font-size:15px;border-width:0px;padding-top:3px;padding-bottom:3px;padding-right:3px;padding-left:3px;" >Total
                            </td>
                            <td style="text-align:center;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Raleway', sans-serif;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CEC7C7;font-size:15px;border-width:0px;padding-top:3px;padding-bottom:3px;padding-right:3px;padding-left:3px;" >{{$currency->symbol}}{{number_format($invoice->total,2)}}
                            </td>
                        </tr>
                    
                    </table>
                    <br/><br/>
                     <h4 style="margin-top:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;text-align:center;font-family:Raleway;line-height:1.1;margin-bottom:15px;color:#2BA6CB;font-weight:500;font-size:23px;" ><a href="https://{{Config('app.site')->domain}}/invoices/{{$invoice->id}}/pay/{{$validationHash}}">Pay Now</a></h4><br/>
                    <h4 style="margin-top:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;text-align:center;font-family:Raleway;line-height:1.1;margin-bottom:15px;color:#2BA6CB;font-weight:500;font-size:23px;" >Thank You</h4>
                </div>
                <!-- /content -->

            </td>
            <td style="margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;" ></td>
        </tr>
    </table>
    <!-- /BODY -->

    <!-- FOOTER -->
    <table class="footer-wrap" style="margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:'Raleway', sans-serif;width:100%;clear:both!important;margin-top:-5px;" >
    </table>


    <!-- /FOOTER -->

</body>
</html>
