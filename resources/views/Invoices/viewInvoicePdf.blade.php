
    <style>
    .invoice-box {
        max-width: 800px;
        margin: auto;
        padding: 30px;
        border: 1px solid #eee;
        box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        font-size: 16px;
        line-height: 24px;
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        color: #555;
    }

    .invoice-box table {
        width: 100%;

        text-align: left;
    }

    .invoice-box table td {
        padding: 5px;
        vertical-align: top;
    }

    .invoice-box table tr.right td:nth-child(2) {
        text-align: right;
    }

    .invoice-box table tr td table.right td:nth-child(5) {
        text-align: right;
    }

    .invoice-box table tr.top table td {
        padding-bottom: 20px;
    }

    .invoice-box table tr.top table td.title {
        font-size: 45px;
        line-height: 45px;
        color: #333;
    }

    .invoice-box table tr.information table td {
        padding-bottom: 40px;
    }

    .invoice-box table tr.heading td.selected-color {
        background: #eee;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
    }

    .invoice-box table tr.details td {
        padding-bottom: 20px;
    }

    .invoice-box table tr.item td{
        border-bottom: 1px solid #eee;
    }

    .invoice-box table tr.item.last td {
        border-bottom: none;
    }

    .invoice-box table tr.total td:nth-child(2) {
        border-top: 2px solid #eee;
        font-weight: bold;
    }

    @media only screen and (max-width: 600px) {
        .invoice-box table tr.top table td {
            width: 100%;
            display: block;
            text-align: center;
        }

        .invoice-box table tr.information table td {
            width: 100%;
            display: block;
            text-align: center;
        }
    }

    /** RTL **/
    .rtl {
        direction: rtl;
        font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
    }

    .rtl table {
        text-align: right;
    }

    .rtl table tr td:nth-child(2) {
        text-align: left;
    }
    </style>

    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="{{ $logo }}" width="200">
                            </td>

                            <td>
                                <span style="font-weight: bold;">{{ $invoice->status == 1 ? 'Paid ' : '' }}Invoice</span> #: {{ $invoice->id }}<br>
                                Created: {{ date('d/m/Y', strtotime($invoice->created_at)) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <strong>{{ @$invoice->user->mailingContact->address->contact_name }}</strong><br>
                                <div>{{ $invoice->user->mailingContact->address->address_1 }}</div>
                                <div>{{ $invoice->user->mailingContact->address->address_2 }}</div>
                                <div>{{ $invoice->user->mailingContact->address->address_3 }}</div>
                                <div>{{ $invoice->user->mailingContact->address->address_4 }}</div>
                                <div>
                                    {{ $invoice->user->mailingContact->address->city }},
                                    {{ @$invoice->user->mailingContact->address->county->name }}
                                    {{ $invoice->user->mailingContact->address->postal_code }}
                                </div>
                                <div>{{ @$invoice->user->mailingContact->address->country->name }}</div>
                                <div>{{ $invoice->user->mailingContact->address->phone }}</div>
                                <div>{{ $invoice->user->mailingContact->address->fax }}</div>
                                <div>{{ $invoice->user->mailingContact->address->email }}</div>
                            </td>

                            <td>
                                <strong>{{ @$invoice->address->contact_name }}</strong><br>
                                <div>{{ $invoice->address->address_1 }}</div>
                                <div>{{ $invoice->address->address_2 }}</div>
                                <div>{{ $invoice->address->address_3 }}</div>
                                <div>{{ $invoice->address->address_4 }}</div>
                                <div>
                                    {{ $invoice->address->city }},
                                    {{ @$invoice->address->county->name }}
                                    {{ $invoice->address->postal_code }}
                                </div>
                                <div>{{ @$invoice->address->country->name }}</div>
                                <div>{{ $invoice->address->phone }}</div>
                                <div>{{ $invoice->address->fax }}</div>
                                <div>{{ $invoice->address->email }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- <tr class="heading right">
                <td>
                    Payment Due
                </td>

                <td>
                    Check #
                </td>
            </tr> -->

            <tr class="details right">
                <td>
                    Payment Due
                </td>

                <td>
                    {{ date('d/m/Y', strtotime($invoice->due_at)) }}
                </td>
            </tr>


            <tr class="heading">
                <td colspan="2">
                <table style="border-bottom: 2px solid #eee; margin-bottom: 10px">
                    <tr >
                        <td class="selected-color" style="text-align: left;">Item</td>
                        <td class="selected-color" style="text-align: center;">Description</td>
                        <td class="selected-color" style="text-align: center;">Price</td>
                        <td class="selected-color" style="text-align: center;">Qty</td>
                        <td class="selected-color" style="text-align: right;">Subtotal</td>
                    </tr>
                    @forelse ($invoice->items as $item)
                        <tr>
                            <td style="text-align: left;">{{ $item->item }}</td>
                            <td style="text-align: center;">{{ $item->description }}</td>
                            <td style="text-align: center;">{!! isset($currency) ? $currency->symbol : '£' !!} {{ number_format($item->price,2) }}</td>
                            <td style="text-align: center;">{{ $item->quantity }}</td>
                            <td  style="text-align: right;">{!! isset($currency) ? $currency->symbol : '£' !!} {{ number_format($item->price * $item->quantity, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No items were invoiced.</td>
                        </tr>
                    @endforelse
                </table>
                </td>
            </tr>

            <tr class="details right">
                <td>
                    Subtotal
                </td>
                <td>
                    {!! isset($currency) ? $currency->symbol : '£' !!} {{ number_format($invoice->items->sum('price'), 2) }}
                </td>
            </tr>

            @if ((int)$invoice->credit !== 0)
                <tr class="details right">
                    <td>
                        Credit
                    </td>
                    <td>
                        {!! isset($currency) ? $currency->symbol : '£' !!} - {{ number_format($invoice->credit, 2) }}
                    </td>
                </tr>
            @endif

            <tr class="item last details right">
                <td>
                    Tax
                </td>

                <td>
                    {!! isset($currency) ? $currency->symbol : '£' !!}  {{ number_format($invoice->tax, 2) }}
                </td>
            </tr>

            <tr class="total details right">
                <td></td>

                <td>
                   Total: {!! isset($currency) ? $currency->symbol : '£' !!} {{ number_format($invoice->total + $invoice->tax, 2) }}
                </td>
            </tr>

            <tr class="heading" colspan="2">
                <td class="selected-color">
                    Comment
                </td>
                <td class="selected-color"></td>
            </tr>
            <tr colspan="2">
                <td colspan="2">
                    {{ $invoice->comments }}
                </td>
            </tr>
        </table>
    </div>
