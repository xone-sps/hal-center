<?php

namespace App\Models;


use App\Http\Controllers\API\InvoiceTemplateController;

class InvoiceTemplate extends BaseModel
{
    protected $fillable = ['template_type', 'is_default_template', 'template_title', 'default_content', 'custom_content'];

    public static function getInvoiceTemplate($request)
    {
        return InvoiceTemplate::orderBy($request->columnKey, $request->columnSortedBy)->get();
    }

    public static function getInvoiceTemplateToPrint($cashRegisterId, $orderType)
    {
        $cashRagister = CashRegister::find($cashRegisterId);

        if ($orderType == 'sales') {
            $invoice = InvoiceTemplate::find($cashRagister->sales_invoice_id);

        } else {
            $invoice = InvoiceTemplate::find($cashRagister->receiving_invoice_id);
        }

        if ($invoice->custom_content != '') {
            return $invoice->custom_content;
        } else {
            return $invoice->default_content;
        }

    }

    public static function getInvoiceTemplateForNoCashReg($orderType)
    {
        $invoice = InvoiceTemplate::select('*')->where('is_default_template', 1)->where('template_type', $orderType)->first();

        if ($invoice->custom_content != '') {
            return $invoice->custom_content;
        } else {
            return $invoice->default_content;
        }
    }

    public static function updateDefaultInvoiceTemplate($type)
    {
        InvoiceTemplate::where('is_default_template', 1)
            ->where('template_type', $type)
            ->update(['is_default_template' => 0]);
    }

    public static function getDefaultTemplate()
    {
        $salesInvoiceTemplateData = InvoiceTemplate::where('is_default_template', 1)->where('template_type', 'sales')->first();
        $receiveInvoiceTemplateData = InvoiceTemplate::where('is_default_template', 1)->where('template_type', 'receiving')->first();

        //Sales
        if ($salesInvoiceTemplateData->custom_content != '') {
            $salesInvoiceTemplateData->invoice_template = $salesInvoiceTemplateData->custom_content;
        } else {
            $salesInvoiceTemplateData->invoice_template = $salesInvoiceTemplateData->default_content;
        }
        //Receives
        if ($receiveInvoiceTemplateData->custom_content != '') {
            $receiveInvoiceTemplateData->invoice_template = $receiveInvoiceTemplateData->custom_content;
        } else {
            $receiveInvoiceTemplateData->invoice_template = $receiveInvoiceTemplateData->default_content;
        }
        return ['sales_invoice' => $salesInvoiceTemplateData, 'receive_invoice' => $receiveInvoiceTemplateData];

    }
}
