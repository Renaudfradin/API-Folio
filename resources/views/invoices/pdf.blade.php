<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->type->documentTitle() }} {{ $invoice->number }}</title>
    <style>
        @page {
            margin: 28px 36px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #1c1c1c;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.45;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .title {
            margin: 0;
            font-size: 42px;
            letter-spacing: 1px;
            font-weight: 700;
            line-height: 1;
        }

        .seal {
            text-align: right;
        }

        .pills {
            margin-top: 18px;
            margin-bottom: 16px;
        }

        .pill {
            display: inline-block;
            border: 1.5px solid #222;
            border-radius: 16px;
            padding: 4px 12px;
            margin-right: 8px;
            font-size: 12px;
        }

        .rule {
            border: 0;
            border-top: 1px solid #222;
            margin: 0 0 22px;
        }

        .parties td {
            vertical-align: top;
            width: 50%;
        }

        .parties .right {
            text-align: right;
        }

        .issuer-name,
        .client-name {
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 0 0 8px;
        }

        .attention {
            margin: 0 0 8px;
            font-size: 10px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .muted {
            margin: 0;
            color: #333;
        }

        .lines {
            margin-top: 28px;
        }

        .lines th {
            background: #2b2b2b;
            color: #fff;
            font-size: 11px;
            letter-spacing: 0.6px;
            font-weight: 700;
            text-align: center;
            padding: 9px 8px;
        }

        .lines th.desc,
        .lines td.desc {
            text-align: left;
            padding-left: 14px;
        }

        .lines td {
            border: 1px solid #ececec;
            padding: 9px 8px;
            text-align: center;
        }

        .totals {
            width: 280px;
            margin-left: auto;
            margin-top: 18px;
        }

        .totals td {
            padding: 3px 0;
        }

        .totals .label {
            text-align: right;
            padding-right: 18px;
            font-weight: 700;
        }

        .totals .amount {
            text-align: right;
            width: 110px;
        }

        .grand td {
            background: #2b2b2b;
            color: #fff;
            font-weight: 700;
            padding: 8px 10px;
        }

        .footer {
            margin-top: 36px;
        }

        .footer td {
            vertical-align: top;
            width: 50%;
        }

        .footer .right {
            text-align: right;
        }

        .footer h2 {
            margin: 0 0 4px;
            font-size: 12px;
        }

        .thanks {
            margin-top: 28px;
            text-align: center;
            letter-spacing: 1.4px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td>
                <h1 class="title">{{ $invoice->type->documentTitle() }}</h1>
            </td>
            <td class="seal">
                <svg xmlns="http://www.w3.org/2000/svg" width="58" height="58" viewBox="0 0 64 64">
                    <polygon fill="none" stroke="#222" stroke-width="1.3" points="32,4 36,20 50,8 42,24 60,22 46,32 60,42 42,40 50,56 36,44 32,60 28,44 14,56 22,40 4,42 18,32 4,22 22,24 14,8 28,20"/>
                </svg>
            </td>
        </tr>
    </table>

    <div class="pills">
        <span class="pill">{{ $invoice->numberLabel() }}</span>
        <span class="pill">{{ $invoice->formattedIssuedOn() }}</span>
    </div>

    <hr class="rule">

    <table class="parties">
        <tr>
            <td>
                <p class="issuer-name">{{ $invoice->issuer_name }}</p>
                @if ($invoice->issuer_phone)
                    <p class="muted">{{ $invoice->issuer_phone }}</p>
                @endif
                @if ($invoice->issuer_email)
                    <p class="muted">{{ $invoice->issuer_email }}</p>
                @endif
                @if ($invoice->issuer_website)
                    <p class="muted">{{ $invoice->issuer_website }}</p>
                @endif
                @if ($invoice->issuer_address)
                    <p class="muted">{!! nl2br(e($invoice->issuer_address)) !!}</p>
                @endif
            </td>
            <td class="right">
                <p class="attention">À l'attention de</p>
                <p class="client-name">{{ $invoice->client_name }}</p>
                @if ($invoice->client_phone)
                    <p class="muted">{{ $invoice->client_phone }}</p>
                @endif
                @if ($invoice->client_email)
                    <p class="muted">{{ $invoice->client_email }}</p>
                @endif
                @if ($invoice->client_address)
                    <p class="muted">{!! nl2br(e($invoice->client_address)) !!}</p>
                @endif
            </td>
        </tr>
    </table>

    <table class="lines">
        <thead>
            <tr>
                <th class="desc">DESCRIPTION</th>
                <th>PRIX</th>
                <th>QUANTITÉ</th>
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->lines as $line)
                <tr>
                    <td class="desc">{{ $line->description }}</td>
                    <td>{{ $line->formattedUnitPrice() }}</td>
                    <td>{{ $line->formattedQuantity() }}</td>
                    <td>{{ $line->formattedLineTotal() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">Sous total :</td>
            <td class="amount">{{ $invoice->formattedSubtotal() }}</td>
        </tr>
        <tr>
            <td class="label">TVA ({{ $invoice->formattedVatRate() }}%) :</td>
            <td class="amount">{{ $invoice->formattedVatAmount() }}</td>
        </tr>
        <tr class="grand">
            <td class="label">TOTAL :</td>
            <td class="amount">{{ $invoice->formattedTotal() }}</td>
        </tr>
    </table>

    <table class="footer">
        <tr>
            <td>
                <h2>Paiement à l'ordre de {{ $invoice->paymentOrderName() }}</h2>
                @if ($invoice->account_number)
                    <p class="muted">N° de compte {{ $invoice->account_number }}</p>
                @endif
            </td>
            <td class="right">
                <h2>Conditions de paiement</h2>
                <p class="muted">{{ $invoice->payment_terms ?: 'Paiement sous 30 jours' }}</p>
            </td>
        </tr>
    </table>

    <p class="thanks">{{ $invoice->thank_you ?: 'MERCI DE VOTRE CONFIANCE' }}</p>
</body>
</html>
