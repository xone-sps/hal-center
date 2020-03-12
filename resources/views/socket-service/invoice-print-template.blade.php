<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice Print</title>
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            font-family: Roboto, BoonHome, serif, sans-serif !important;
        }

        section#invoice-sales {
            font-family: Roboto, BoonHome, serif, sans-serif !important;
        }

        table#table-sales * {
            font-family: Roboto, BoonHome, serif, sans-serif !important;
        }

        #cart-print-area {
            padding-left: 18px;
            font-size: 18px;
        }
    </style>
</head>
<body>
<div>
    <div id="cart-print-area">
        {!! $html !!}
    </div>
</div>
</body>
</html>