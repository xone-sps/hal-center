<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceTemplateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Sales Invoice Template 1
        $salesHeader1 = '<style>
            section#invoice-sales {
              font-family: Roboto, \'Google Sans\', \'Open Sans\', BoonHome, \'Phetsarath OT\', arial, serif, sans-serif !important;
            }
            table#table-sales * {
               font-family: Roboto, \'Google Sans\', \'Open Sans\', BoonHome, \'Phetsarath OT\', arial, serif, sans-serif !important; 
            }
        </style>
        <section id="invoice-sales" style="margin: 0 auto; max-width: 500px; font-family: \'Open Sans\' , Roboto, \'Phetsarath OT\' , sans-serif;">
            <!--header top start-->
            <div style="text-align: center; font-family: \'Open Sans\' , Roboto, \'Phetsarath OT\' , sans-serif;">
                <p>{app_logo}</p>
                <h1 style="font-weight: lighter; margin-bottom: 0;">{app_name}</h1>
                <br>
                <small>Sales Receipt</small>
                <br>
                <h3 style="text-align:center;">INVOICE</h3>
            </div>
            <!--header top end-->

            <!--header bottom start-->
            <div style="margin-bottom:-30px; height:245px; width: 100%; overflow: hidden; display: block; font-family: \'Open Sans\' , Roboto, \'Phetsarath OT\' , sans-serif;">
                 <div style="float:left; width: 50%;">
                     <p style="font-weight:bold;">Invoice ID: <span>{invoice_id}</span></p>
                     <p style="font-weight:bold;">Sold To: <span>{customer_name}</span></p>
                     <p style="font-weight:bold;">Sold By: <span>{employee_name}</span></p>
                     <p style="font-weight:bold;">Phone: <span>{phone_number}</span></p>
                     <p style="font-weight:bold;">Address: <span>{address}</span></p>
                 </div>
                 <div style="float:right; width: 45%;">
                     <p style="font-weight:bold; text-align: right;">Date : <span>{date}</span></p>
                     <p style="font-weight:bold; text-align: right;">Time : <span>{time}</span></p>
                 </div>
            </div>
                <table id="table-sales" style="border-top: 1px solid #bfbfbf; border-bottom: 1px solid #bfbfbf; border-collapse: collapse; width: 100%; max-width: 100%; margin-bottom: 0; background-color: transparent;">
                    <tr>
                        <th style="text-align: left; padding: 7px 0; border-bottom: 1px solid #bfbfbf; width: 40%;">Items</th>
                        <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Qty</th>
                        <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Price</th>
                        <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Discount</th>
                        <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Total</th>
                    </tr>';
        $salesFooter1 = '</table>
            </div>
       </section>';

        DB::table("invoice_templates")->insert([
            'template_type' => 'sales',
            'is_default_template' => 0,
            'template_title' => 'Small Sales Invoice',
            'default_content' =>
                $salesHeader1 . '<br>
                <tr>
                   <td style="padding: 7px 0;" class="text-center" colspan="5">{item_details}</td>
                </tr>
                <tr>
                   <th style="text-align: left; padding: 7px 0;">Sub Total</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{sub_total}</td>
                </tr>
                <tr>
                   <th style="text-align: left; padding: 7px 0;">Tax</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{tax}</td>
                </tr>
                <tr>
                   <th style="text-align: left; padding: 7px 0;">Discount</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{discount}</td>
                </tr>
                <tr>
                   <th style="text-align: left;">Total</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{total}</td>
                </tr>
                <tr>
                   <td style="padding: 7px 0;" class="text-center" colspan="5">{payment_details}</td>
                </tr>
                <tr>
                   <th style="text-align: left; padding: 7px 0;">Exchange</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{exchange}</td>
                </tr>' . $salesFooter1
        ]);

        // Sales Invoice Template 2
        $invoiceHeader2 = '
            <style>
            section#invoice-sales {
              font-family: Roboto, \'Google Sans\', \'Open Sans\', BoonHome, \'Phetsarath OT\', arial, serif, sans-serif !important;
            }
            table#table-sales * {
               font-family: Roboto, \'Google Sans\', \'Open Sans\', BoonHome, \'Phetsarath OT\', arial, serif, sans-serif !important; 
            }
        </style>
            <div style="text-align: center; font-family: \'Open Sans\' , Roboto, \'Phetsarath OT\' , sans-serif;">
                        <p>{app_logo}</p>
                        <h1 style="font-weight: lighter; margin-bottom: 0;">{app_name}</h1>
                        <br>
                        <small>Sales Receipt</small>
                        <br>
                        <h3 style="text-align:center;">INVOICE</h3>
                    </div>
                    <!--header bottom start-->
                    <div style="margin-bottom:-30px; height:245px; width: 100%; overflow: hidden; display: block; font-family: \'Open Sans\' , Roboto, \'Phetsarath OT\' , sans-serif;">
                         <div style="float:left; width: 50%;">
                             <p style="font-weight:bold;">Invoice ID: <span>{invoice_id}</span></p>
                             <p style="font-weight:bold;">Sold To: <span>{customer_name}</span></p>
                             <p style="font-weight:bold;">Sold By: <span>{employee_name}</span></p>
                             <p style="font-weight:bold;">Phone: <span>{phone_number}</span></p>
                             <p style="font-weight:bold;">Address: <span>{address}</span></p>
                         </div>
                         <div style="float:right; width: 45%;">
                             <p style="font-weight:bold; text-align: right;">Date : <span>{date}</span></p>
                             <p style="font-weight:bold; text-align: right;">Time : <span>{time}</span></p>
                         </div>
                    </div>
                    <table id="table-sales" style="border-top: 1px solid #bfbfbf; border-bottom: 1px solid #bfbfbf; border-collapse: collapse; font-weight:500; width: 100%; max-width: 100%; margin-bottom: 0; background-color: transparent;">
                        <tr>
                            <th style="text-align: left; padding: 7px 0; border-bottom: 1px solid #bfbfbf; width: 40%;">Items</th>
                            <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Qty</th>
                            <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Price</th>
                            <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Discount</th>
                            <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Total</th>
                        </tr>';
        $invoiceFooter2 = '</table>';


        DB::table("invoice_templates")->insert([
            'template_type' => 'sales',
            'is_default_template' => 1,
            'template_title' => 'Large Sales Invoice',
            'default_content' =>
                $invoiceHeader2 . '<br>
                <tr>
                   <td style="padding: 7px 0;" class="text-center" colspan="5">{item_details}</td>
                </tr>
                <tr>
                   <th style="text-align: left; padding: 7px 0;">Sub Total</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{sub_total}</td>
                </tr>
                <tr>
                   <th style="text-align: left; padding: 7px 0;">Tax</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{tax}</td>
                </tr>
                <tr>
                   <th style="text-align: left; padding: 7px 0;">Discount</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{discount}</td>
                </tr>
                <tr>
                   <th style="text-align: left;">Total</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{total}</td>
                </tr>
                <tr>
                   <td style="padding: 7px 0;" class="text-center" colspan="5">{payment_details}</td>
                </tr>
                <tr>
                   <th style="text-align: left; padding: 7px 0;">Exchange</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{exchange}</td>
                </tr>' . $invoiceFooter2
        ]);

        //Invoice Receive Template 1
        $invoiceReceiveHeader1 = '
        <style>
            section#invoice-sales {
              font-family: Roboto, \'Google Sans\', \'Open Sans\', BoonHome, \'Phetsarath OT\', arial, serif, sans-serif !important;
            }
            table#table-sales * {
               font-family: Roboto, \'Google Sans\', \'Open Sans\', BoonHome, \'Phetsarath OT\', arial, serif, sans-serif !important; 
            }
        </style>
        <section id="invoice-sales" style="margin: 0 auto; max-width: 500px;">
        <!--header top start-->
       <div style="text-align: center; font-family: \'Open Sans\' , Roboto, \'Phetsarath OT\' , sans-serif;">
            <p>{app_logo}</p>
            <h1 style="font-weight: lighter; margin-bottom: 0;">{app_name}</h1>
            <br>
            <small>Purchase Receipt</small>
            <br>
            <h3 style="text-align:center;">INVOICE</h3>
       </div>
       <!--header top end-->

        <!--header bottom start-->
        <div style="margin-bottom:-30px; height:170px; width: 100%; overflow: hidden; display: block; font-family: \'Open Sans\' , Roboto, \'Phetsarath OT\' , sans-serif;">
             <div style="float:left; width: 50%;">
                 <p style="font-weight:bold;">Invoice ID: <span>{invoice_id}</span></p>
                 <p style="font-weight:bold;">Purchased From: <span>{supplier_name}</span></p>
                 <p style="font-weight:bold;">Purchased By: <span>{employee_name}</span></p>
             </div>
             <div style="float:right; width: 45%;">
                 <p style="font-weight:bold; text-align: right;">Date : <span>{date}</span></p>
                 <p style="font-weight:bold; text-align: right;">Time : <span>{time}</span></p>
             </div>
        </div>
        <table id="table-sales" style="border-top: 1px solid #bfbfbf; border-bottom: 1px solid #bfbfbf; border-collapse: collapse; font-weight:500; width: 100%; max-width: 100%; margin-bottom: 0; background-color: transparent; font-family: \'Open Sans\' , Roboto, \'Phetsarath OT\' , sans-serif;">
           <tr>
               <th style="text-align: left; padding: 7px 0; border-bottom: 1px solid #bfbfbf; width: 40%;">Items</th>
               <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Qty</th>
               <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Price</th>
               <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Discount</th>
               <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Total</th>
           </tr>';
        $invoiceReceiveFooter1 = '</table>
       </div>
   </section>';

        DB::table("invoice_templates")->insert([
            'template_type' => 'receiving',
            'is_default_template' => 0,
            'template_title' => 'Small Purchase Invoice',
            'default_content' =>
                $invoiceReceiveHeader1 . '<br>
                <tr>
                   <td style="padding: 7px 0;" class="text-center" colspan="5">{item_details}</td>
                </tr>
                <tr>
                   <th style="text-align: left; padding: 7px 0;">Sub Total</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{sub_total}</td>
                </tr>
                <tr>
                   <th style="text-align: left; padding: 7px 0;">Tax</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{tax}</td>
                </tr>
                <tr>
                   <th style="text-align: left; padding: 7px 0;">Discount</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{discount}</td>
                </tr>
                <tr>
                   <th style="text-align: left;">Total</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{total}</td>
                </tr>
                <tr>
                   <td style="padding: 7px 0;" class="text-center" colspan="5">{payment_details}</td>
                </tr>
                <tr>
                   <th style="text-align: left; padding: 7px 0;">Exchange</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{exchange}</td>
                </tr>' . $invoiceReceiveFooter1
        ]);

        //Invoice Receive Template 2
        $receiveHeader2 = '
                <style>
                    section#invoice-sales {
                      font-family: Roboto, \'Google Sans\', \'Open Sans\', BoonHome, \'Phetsarath OT\', arial, serif, sans-serif !important;
                    }
                    table#table-sales * {
                       font-family: Roboto, \'Google Sans\', \'Open Sans\', BoonHome, \'Phetsarath OT\', arial, serif, sans-serif !important; 
                    }
                </style>
                <div style="text-align: center; font-family: \'Open Sans\' , Roboto, \'Phetsarath OT\' , sans-serif;">
                               <p>{app_logo}</p>
                               <h1 style="font-weight: lighter; margin-bottom: 0;">{app_name}</h1>
                               <br>
                               <small>Purchased Receipt</small>
                               <br>
                               <h3 style="text-align:center;">INVOICE</h3>
                           </div>
                           <!--header bottom start-->
                            <div style="margin-bottom:-30px; height:170px; width: 100%; overflow: hidden; display: block; font-family: \'Open Sans\' , Roboto, \'Phetsarath OT\' , sans-serif;">
                                 <div style="float:left; width: 50%;">
                                     <p style="font-weight:bold;">Invoice ID: <span>{invoice_id}</span></p>
                                     <p style="font-weight:bold;">Purchased From: <span>{supplier_name}</span></p>
                                     <p style="font-weight:bold;">Purchased By: <span>{employee_name}</span></p>
                                 </div>
                                 <div style="float:right; width: 45%;">
                                     <p style="font-weight:bold; text-align: right;">Date : <span>{date}</span></p>
                                     <p style="font-weight:bold; text-align: right;">Time : <span>{time}</span></p>
                                 </div>
                            </div>
                           <table id="table-sales" style="border-top: 1px solid #bfbfbf; border-bottom: 1px solid #bfbfbf; border-collapse: collapse; font-weight:500; width: 100%; max-width: 100%; margin-bottom: 0; background-color: transparent; font-family: \'Open Sans\' , Roboto, \'Phetsarath OT\' , sans-serif;">
                               <tr>
                                   <th style="text-align: left; padding: 7px 0; border-bottom: 1px solid #bfbfbf; width: 40%;">Items</th>
                                   <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Qty</th>
                                   <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Price</th>
                                   <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Discount</th>
                                   <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Total</th>
                               </tr>';
        $receiveFooter2 = '</table>';


        DB::table("invoice_templates")->insert([
            'template_type' => 'receiving',
            'is_default_template' => 1,
            'template_title' => 'Large Purchase Invoice',
            'default_content' =>
                $receiveHeader2 . '<br>
                 <tr>
                   <td style="padding: 7px 0;" class="text-center" colspan="5">{item_details}</td>
                </tr>
                <tr>
                   <th style="text-align: left; padding: 7px 0;">Sub Total</th>
                   <th style="padding: 7px 0;"></th>
                   <th style="padding: 7px 0;"></th>
                   <th style="padding: 7px 0;"></th>
                   <td style="text-align: right; padding: 7px 0;">{sub_total}</td>
                </tr>
                <tr>
                   <th style="text-align: left; padding: 7px 0;">Tax</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{tax}</td>
                </tr>
                <tr>
                   <th style="text-align: left; padding: 7px 0;">Discount</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right; padding: 7px 0;">{discount}</td>
                </tr>
                <tr>
                   <th style="text-align: left;">Total</th>
                   <th></th>
                   <th></th>
                   <th></th>
                   <td style="text-align: right;">{total}</td>
                </tr>
                <tr>
                   <td style="padding: 7px 0;" class="text-center" colspan="5">{payment_details}</td>
                </tr>
                <tr>
                   <th style="text-align: left; padding: 7px 0;">Exchange</th>
                   <th style="padding: 7px 0;"></th>
                   <th style="padding: 7px 0;"></th>
                   <th style="padding: 7px 0;"></th>
                   <td style="text-align: right; padding: 7px 0;">{exchange}</td>
                </tr>' . $receiveFooter2
        ]);
    }
}
