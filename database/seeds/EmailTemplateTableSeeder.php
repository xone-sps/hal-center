<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Email Header
        $emailHeader = '<html>
                            <div style="max-width: 60%; color: #000000 !important; font-family: Helvetica; margin:auto; padding-bottom: 10px;">
                                <div style="text-align:center; padding-top: 10px; padding-bottom: 10px;">
                                    <h1>{app_name}</h1>
                                </div>
                                <div style="padding: 35px;padding-left:20px; font-size:17px; border-bottom: 1px solid #cccccc; border-top: 1px solid #cccccc;">';
        $emailFooter = '        </div>
                            </div>
                        </html>';

        DB::table("email_templates")->insert([
            'template_type' => 'user_invitation',
            'template_subject' => 'You are invited',
            'default_content' =>
            $emailHeader . 'Hi,<br>
                {invited_by} invited you to join with the team on {app_name}.<br>
                Please click the link below to accept the invitation!<br>
                {verification_link}' . $emailFooter
        ]);

        DB::table("email_templates")->insert([
            'template_type' => 'account_verification',
            'template_subject' => 'Account verification',
            'default_content' =>
            $emailHeader . 'Hi {first_name},<br>
                        Your account has been created.<br>
                        Please click the link below to verify your email.<br>
                        {verification_link}' . $emailFooter
        ]);


        DB::table("email_templates")->insert([
            'template_type' => 'reset_password',
            'template_subject' => 'Reset password',
            'default_content' =>
            $emailHeader . 'Hi,<br>
                        You have requested to change your password.<br>
                        Please click the link below to change your password!<br>
                        {reset_password_link}' . $emailFooter
        ]);

        DB::table("email_templates")->insert([
            'template_type' => 'pos_invoice',
            'template_subject' => 'Invoice',
            'default_content' =>
            $emailHeader . 'Hi {first_name},<br>
                        Thanks for shopping with us.<br>
                        Please find the attachment for your purchase ({invoice_id}) details.' . $emailFooter
        ]);


        //Low stock Template 
        $lowStockHeader = '<div style="text-align: center; font-family: DejaVu Sans, \'Raleway\', sans-serif;">
                            <p>{app_logo}</p>
                            <h1 style="font-weight: lighter; margin-bottom: 0;">{app_name}</h1>
                            <br>
                            <small>Low Stock Notification</small>
                            <br>
                            <h3 style="text-align:center;">Out Of Stock List</h3>
                        </div>
                        <!--header bottom start-->
                        <div style="margin-bottom:-30px; height:170px; width: 100%; overflow: hidden; display: block; font-family: DejaVu Sans, \'Raleway\', sans-serif;">
                                <div style="float:left; width: 50%;">
                                    <p style="font-weight:bold;">Branch Name: <span>{branch_name}</span></p>
                                    <p style="font-weight:bold;">Branch Manager: <span>{branch_manager}</span></p>
                                </div>
                                <div style="float:right; width: 45%;">
                                    <p style="font-weight:bold; text-align: right;">Date : <span>{date}</span></p>
                                    <p style="font-weight:bold; text-align: right;">Time : <span>{time}</span></p>
                                </div>
                        </div>
                        <table style="border-top: 1px solid #bfbfbf; border-bottom: 1px solid #bfbfbf; border-collapse: collapse; font-weight:500; width: 100%; max-width: 100%; margin-bottom: 0; background-color: transparent; font-family: DejaVu Sans, \'Raleway\', sans-serif;">
                            <tr>
                                <th style="text-align: left; padding: 7px 0; border-bottom: 1px solid #bfbfbf; width: 40%;">Item</th>
                                <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Re Order</th>
                                <th style="text-align: right; padding: 7px 0; border-bottom: 1px solid #bfbfbf;">Qty</th>
                            </tr>';
        $lowStockFooter = '</table>';


        DB::table("email_templates")->insert([
            'template_type' => 'low_stock',
            'template_subject' => 'Low Stock Template',
            'default_content' =>
            $lowStockHeader . '<br><td style="padding: 7px 0;" class="text-center" colspan="5">{item_details}</td>' . $lowStockFooter
        ]);
    }
}
