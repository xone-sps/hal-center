<?php

namespace App\Http\Controllers\API;

use App\Libraries\AllSettingFormat;
use App\Models\Branch;
use App\Models\InvoiceTemplate;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Payments;
use App\Models\PaymentType;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Config;

class InvoiceTemplateController extends Controller
{
    public function index(Request $request)
    {
        $data = InvoiceTemplate::getInvoiceTemplate($request);
        $totalCount = InvoiceTemplate::countData();

        return ['datarows' => $data, 'count' => $totalCount];
    }

    public function getAllInvoiceTemplate()
    {
        return ['invoice_template' => InvoiceTemplate::allData(), 'offline_mode' => Setting::getOneSetting('offline_mode')->setting_value];
    }

    public function store(Request $request)
    {
        $defaultInvoiceTemplate = InvoiceTemplate::getDefaultTemplate();

        $this->validate($request, [
            'title' => 'required',
            'template_type' => 'required'
        ]);

        if ($request->input('is_default_template') == 1) {
            InvoiceTemplate::updateDefaultInvoiceTemplate($request->input('template_type'));
        }

        InvoiceTemplate::create([
            'template_title' => $request->input('title'),
            'template_type' => $request->input('template_type'),
            'is_default_template' => $request->input('is_default_template'),
            'custom_content' => $request->input('content'),
        ]);
    }

    public function show($id)
    {
        $invoiceTemplate = InvoiceTemplate::getOne($id);
        if ($invoiceTemplate->custom_content != '') return $invoiceTemplate->custom_content;
        else return $invoiceTemplate->default_content;
    }

    public function getInvoiceEditData($id)
    {
        $invoiceTemplate = InvoiceTemplate::getOne($id);
        return [
            "template_title" => $invoiceTemplate->template_title,
            "template_type" => $invoiceTemplate->template_type,
            "is_default_template" => $invoiceTemplate->is_default_template,
            "content" => $invoiceTemplate->custom_content != '' ? $invoiceTemplate->custom_content : $invoiceTemplate->default_content,
            "isReStoreShow" => $invoiceTemplate->custom_content != '' ? true : false,
        ];
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'template_type' => 'required',
        ]);

        if ($request->input('is_default_template') == 1) {
            InvoiceTemplate::updateDefaultInvoiceTemplate($request->input('template_type'));
        }

        $success = InvoiceTemplate::updateData($id, [
            'template_title' => $request->input('title'),
            'template_type' => $request->input('template_type'),
            'is_default_template' => $request->input('is_default_template'),
            'custom_content' => $request->input('content')
        ]);

        $msg = Lang::get('lang.invoice_setting_saved_successfully');
        $status = 200;

        if (!$success) {

            $msg = Lang::get('lang.error_during_update');
            $status = 404;
        }

        return response()->json(['message' => $msg], $status);
    }

    public function getInvoiceTemplateToPrint($orderId, $salesOrReceivingType, $transferBranchName, $cashRegisterId, $orderType, $from)
    {
        if ($cashRegisterId == null) {
            $data = InvoiceTemplate::getInvoiceTemplateForNoCashReg($orderType);
        } else {
            $data = InvoiceTemplate::getInvoiceTemplateToPrint($cashRegisterId, $orderType);
        }

        $orderDetails = Order::getOrderDetailsForInvoice($orderId, $orderType, $cashRegisterId);

        $discountAmount = OrderItems::getDiscountAmount($orderId);

        $itemDetails = $this->getItemDetailsforInvoice($orderId);
        $paymentDetails = $this->makePaymentDetailsForInvoice($orderId);

        $appName = Config::get('app_name');
        $invoiceLogo = Config::get('invoiceLogo');

        $publicPath = \Request::root();
        $src = $publicPath . '/uploads/logo/' . $invoiceLogo;
        if ($from == 'email') {
            $logo = '<div style="text-align: center;width: 100%;">
                    <img class="invoice-logo" style=" max-width: 200px;left:45%;position:relative;text-align: center; height: auto;" src= "' . $src . '" alt="Logo">
                </div>';
        } else {
            $logo = '<div>
                   <img class="invoice-logo" style=" max-width: 200px; height: auto; margin: 0 auto;" src= "' . $src . '" alt="Logo">
               </div>';
        }

        $allSettingFormat = new AllSettingFormat;

        //customer
        if ($orderDetails->customer_name == null) {
            $orderDetails->customer_name = Lang::get('lang.walk_in_customer');
        }

        $replace = array(
            '{app_name}' => $appName,
            '{app_logo}' => $logo,
            '{invoice_id}' => $orderDetails->invoice_id,
            '{employee_name}' => $orderDetails->employee_name,
            '{date}' => $allSettingFormat->getDate($orderDetails->date),
            '{time}' => $allSettingFormat->timeFormat($orderDetails->created_at),
            '<tr>
                   <td style="padding: 7px 0;" class="text-center" colspan="5">{item_details}</td>
                </tr>' => $itemDetails,

            '<tr>
                   <td style="padding: 7px 0;" class="text-center" colspan="5">{payment_details}</td>
                </tr>' => $paymentDetails,
            '{sub_total}' => $allSettingFormat->getCurrency($allSettingFormat->thousandSep($orderDetails->sub_total)),
            '{tax}' => $allSettingFormat->getCurrency($allSettingFormat->thousandSep($orderDetails->total_tax)),

            '{total}' => $allSettingFormat->getCurrency($allSettingFormat->thousandSep($orderDetails->total)),
            '{exchange}' => $allSettingFormat->getCurrency($allSettingFormat->thousandSep($orderDetails->exchange)),
        );

        if ($orderDetails->table_id != null){
            $replace['{table_name}'] = $orderDetails->table_name;
        }else{
            /*$replace['<span>Table Name:&nbsp;</span><span style="font-size: 1rem;">&nbsp;</span><span style="font-size: 1rem;">{table_name}</span>'] = '';*/
            $replace['{table_name}'] = '';
        }

        if ($discountAmount != null) {
            $replace['{discount}'] = $allSettingFormat->getCurrency($allSettingFormat->thousandSep($discountAmount->overAllDiscount));
        } else {
            $replace['{discount}'] = $allSettingFormat->getCurrency($allSettingFormat->thousandSep(0.00));
        }

        if ($orderType == 'sales') {
            if ($salesOrReceivingType == 'customer') {
                $replace['{customer_name}'] = $orderDetails->customer_name;
                if ($orderDetails->phone_number != null) $replace['{phone_number}'] = $orderDetails->phone_number;
                else $replace['{phone_number}'] = '';
                if ($orderDetails->address != null) $replace['{address}'] = $orderDetails->address;
                else $replace['{address}'] = '';
            } else {
                $replace['{customer_name}'] = $transferBranchName;
            }
        } else {
            if ($salesOrReceivingType == 'supplier') {
                if ($orderDetails->supplier_name == null) {
                    $orderDetails->supplier_name = Lang::get('lang.walk_in_supplier');
                }
                $replace['{supplier_name}'] = $orderDetails->supplier_name;
            } else {
                $replace['{supplier_name}'] = $transferBranchName;
            }
        }

        return (['data' => strtr($data, $replace)]);
    }

    public function makePaymentDetailsForInvoice($orderId)
    {
        $allSettingFormat = new AllSettingFormat;
        $paymentDetails = Payments::getPaymentDetails($orderId);

        $row = "";

        foreach ($paymentDetails as $item) {

            $newRow = '<tr style="text-align: left;">
                    <th style="padding: 7px 0;">' . $item['name'] . '</th>
                    <th style="padding: 7px 0;"></th>
                    <th style="padding: 7px 0;"></th>
                    <th style="padding: 7px 0;"></th>
                    <td style="padding: 7px 0; text-align: right;">' . $allSettingFormat->getCurrency($allSettingFormat->thousandSep($item['paid'])) . '</td>
                </tr>';
            $row = $row . $newRow;
        }

        return $row;
    }

    public function getItemDetailsforInvoice($orderId)
    {
        $allSettingFormat = new AllSettingFormat;
        $itemDetails = OrderItems::getItemDetailsforInvoice($orderId);

        $row = "";

        foreach ($itemDetails as $item) {

            if ($item['variant_title'] == 'default_variant') {
                $item['variant_title'] = '';
            } else {
                $item['variant_title'] = " ( " . $item['variant_title'] . " ) ";
            }
            $newRow = '<tr>
                    <td style="padding: 7px 0; text-align: left; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">' . $item['title'] . $item['variant_title'] . '</td>
                    <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">' . $allSettingFormat->thousandSep($item['quantity']) . '</td>
                    <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">' . $allSettingFormat->getCurrency($allSettingFormat->thousandSep($item['price'])) . '</td>
                    <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">' . $allSettingFormat->thousandSep($item['discount']) . '%</td>
                    <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">' . $allSettingFormat->getCurrency($allSettingFormat->thousandSep($item['sub_total'])) . '</td>
                </tr>';
            $row = $row . $newRow;
        }

        return $row;
    }
}
