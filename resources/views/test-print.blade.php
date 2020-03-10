<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel Broadcast Redis Socket io Tutorial</title>
    <style type="text/css">
        body {
          margin: 0;
          padding: 0;
        }
        section#invoice-sales {
            font-family: Roboto, "Google Sans", "Open Sans", BoonHome, "Phetsarath OT",
            arial, serif, sans-serif !important;
        }

        table#table-sales * {
            font-family: Roboto, "Google Sans", "Open Sans", BoonHome, "Phetsarath OT",
            arial, serif, sans-serif !important;
        }

        #cart-print-area {
            padding: 10px 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div>
    <div id="cart-print-area"><span>
      <div style="text-align: center; font-family:  sans-serif;">
        <p></p>
        <div>
          <img class="invoice-logo" style="max-width: 140px; height: auto; margin: 0 auto;"
               src="http://sabsungdai.com/uploads/logo/logo_37106859.jpeg" alt="Logo">
        </div>
        <p></p>
        <h1 style="font-weight: lighter; margin-bottom: 0;">Sab Sung DAi</h1>
        <br>
        <small>Sales Receipt</small>
        <br>
        <h3 style="text-align:center;">INVOICE</h3>
      </div>
            <!--header bottom start-->
      <div>
        <div style="float:left; width: 50%;">
          <p style="font-weight:bold;">Invoice ID: <span></span></p>
          <p style="font-weight:bold;">Sold To: <span>Walk-in customer</span></p>
          <p style="font-weight:bold;">Sold By: <span>Sab Sab Sung Dai</span></p>
          <p style="font-weight:bold;">Phone: <span></span></p>
          <p style="font-weight:bold;">Address: <span></span></p>
        </div>
        <div style="float:right; width: 45%;">
          <p style="font-weight:bold; text-align: right;">Date : <span>10/03/2020</span></p>
          <p style="font-weight:bold; text-align: right;">Time : <span>01:43 PM</span></p>
        </div>
      </div>
      <br>
      <table id="table-sales"
             style="border-top: 1px solid #bfbfbf; border-bottom: 1px solid #bfbfbf; border-collapse: collapse; font-weight:500; width: 100%; max-width: 100%; margin-bottom: 0; background-color: transparent;">
        <tbody>
          <tr>
            <th style="text-align: left; padding: 7px 0; border-bottom: 1px solid #bfbfbf; width: 40%;">Items</th>
            <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Qty</th>
            <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Price</th>
            <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Discount</th>
            <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Total</th>
          </tr>
          <tr>
            <td style="padding: 7px 0;" class="text-center" colspan="5"></td>
          </tr>
          <tr>
            <td style="padding: 7px 0; text-align: left; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">ໝູປີ້ງ
            </td>
            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">1</td>
            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">₭5,000
            </td>
            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">0.00%
            </td>
            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">₭5,000
            </td>
          </tr>

          <tr>
            <th style="text-align: left; padding: 7px 0;">Sub Total</th>
            <th></th>
            <th></th>
            <th></th>
            <td style="text-align: right; padding: 7px 0;">₭5,000</td>
          </tr>
          <tr>
            <th style="text-align: left; padding: 7px 0;">Tax</th>
            <th></th>
            <th></th>
            <th></th>
            <td style="text-align: right; padding: 7px 0;">₭0</td>
          </tr>
          <tr>
            <th style="text-align: left; padding: 7px 0;">Discount</th>
            <th></th>
            <th></th>
            <th></th>
            <td style="text-align: right; padding: 7px 0;">₭0</td>
          </tr>
          <tr>
            <th style="text-align: left;">Total</th>
            <th></th>
            <th></th>
            <th></th>
            <td style="text-align: right; padding: 7px 0;">₭5,000</td>
          </tr>
          <tr>
            <td style="padding: 7px 0;" class="text-center" colspan="5"></td>
          </tr>
          <tr>
            <th style="text-align: left; padding: 7px 0;">Exchange</th>
            <th></th>
            <th></th>
            <th></th>
            <td style="text-align: right; padding: 7px 0;"></td>
          </tr>
        </tbody>
      </table>
    </span></div>
</div>
</body>
</html>