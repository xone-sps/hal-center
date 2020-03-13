<template>
    <div>
        <div>
            <div class="main-layout-wrapper">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent m-0">
                        <li class="breadcrumb-item">
                            <span v-if="order_type=='sales'">{{trans('lang.sales_details')}}
                                (<a href="#" @click="goBack">{{trans('lang.back_page')}}</a>)
                            </span>
                            <span v-else>{{trans('lang.receives_details')}}
                                    (<a href="#" @click="goBack">{{trans('lang.back_page')}}</a>)
                            </span>
                        </li>
                    </ol>
                </nav>
                <div class="main-layout-card" style="padding-bottom: 20px;">
                    <div class="main-layout-card-header-with-button">
                        <div class="main-layout-card-content-wrapper">
                            <div class="main-layout-card-header-contents text-right">
                                <button class="btn btn-info app-color mobile-btn" type="submit"
                                        @click.prevent="printReceiptBeforeDonePayment">
                                    Print
                                </button>
                            </div>
                        </div>
                    </div>
                    <pre-loader v-if="showPreloader"/>
                    <div id="cart-print-area" v-else v-html="invoiceTemplate"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import axiosGetPost from '../../helper/axiosGetPostCommon';

    export default {
        extends: axiosGetPost,
        props: ['id', 'order_type', 'tab_name', 'route_name'],
        data() {
            return {
                responseData: '',
                ordersDetailsData: {},
                showPreloader: true,
                tabName: '',
                routeName: '',
                invoiceTemplate: '',
                customerDetails: '',
                itemDetails: '',
                paymentDetails: '',
                invoiceLogo: '',
                exchange: '',
                subTotal: '',
                discount: '',
                total: '',
                totalTax: '',
            }
        },
        computed: {
            isMobile() {
                return window.innerWidth <= 920
            }
        },
        mounted() {
            this.tabName = this.tab_name;
            this.routeName = this.route_name;
            this.getOrdersInfo();
        },
        methods: {
            getOrdersInfo() {
                let instance = this;
                this.axiosGet('/reports/ordersDetails/' + instance.id,
                    function (response) {
                        instance.responseData = response.data;
                        instance.invoiceTemplate = instance.responseData.invoice_templates;
                        instance.ordersDetailsData = instance.responseData.orders_details;
                        instance.customerDetails = instance.responseData.customer_details;
                        instance.customerDetails = instance.responseData.phone_number;
                        instance.customerDetails = instance.responseData.address;
                        instance.itemDetails = instance.responseData.item_details;
                        instance.paymentDetails = instance.responseData.payment_details;
                        instance.invoiceLogo = instance.responseData.invoice_logo;
                        instance.exchange = instance.responseData.exchange_amount.exchange;
                        instance.subTotal = instance.responseData.orders_details.sub_total;
                        instance.total = instance.responseData.orders_details.total;
                        instance.totalTax = instance.responseData.orders_details.total_tax;
                        instance.discount = instance.responseData.orders_details.all_discount;
                        instance.printInvoiceGenerate();
                        instance.showPreloader = false;
                    },
                    function (response) {

                    },
                );
            },
            printReceiptBeforeDonePayment() {
                if (!this.isMobile) {
                    $('#cart-print-area').printThis({
                        importCSS: true,
                        importStyle: true,
                        printContainer: true,
                        header: null,
                    });
                } else {
                    this.$emit("printingDone", true);
                    this.axiosPost('/socket/store/image-template', {
                        'html': this.HTMLcontent
                    }, (res) => {
                        this.$emit("printingDone", false);
                    }, (err) => {
                        this.$emit("printingDone", false);
                    });
                }
                this.$emit('resetGetInvoiceBeforeDonePayment', false);
            },
            printInvoiceGenerate() {
                let instance = this;

                instance.itemDetails = this.getInvoiceDetails(instance.itemDetails);
                instance.paymentDetails = this.makePaymentDetailsForInvoice(instance.paymentDetails);

                let invoiceLogo = this.publicPath + '/uploads/logo/' + instance.invoiceLogo,
                    logo = `<div>
                                <img class="invoice-logo" style="max-width: 200px; height: auto; margin: 0 auto;" src= "${invoiceLogo}" alt="Logo">
                            </div>`;

                let obj = {
                    '{app_logo}': logo,
                    '{app_name}': this.app_name,
                    '{invoice_id}': instance.ordersDetailsData.invoice_id,
                    '{employee_name}': instance.ordersDetailsData.first_name + ' ' + instance.ordersDetailsData.last_name,
                    '{table_name}': '',
                    '{customer_name}': instance.ordersDetailsData.customer_name,
                    '{address}': instance.ordersDetailsData.address,
                    '{supplier_name}': instance.ordersDetailsData.customer_name,
                    '{date}': this.dateFormats(this.ordersDetailsData.created_at),
                    '{time}': this.dateFormatsWithTime(this.ordersDetailsData.created_at),
                    '{item_details}': this.itemDetails,
                    '{payment_details}': this.paymentDetails,
                    '{sub_total}': instance.numberFormat(this.subTotal),
                    '{tax}': instance.numberFormat(this.totalTax),
                    '{total}': instance.numberFormat(this.total),
                    '{discount}': instance.numberFormat(this.discount),
                    '{exchange}': instance.numberFormat(this.exchange),
                };

                if (instance.ordersDetailsData.sales_or_receiving_type == 'customer') {
                    obj['{phone_number}'] = this.ordersDetailsData.phone_number;
                    obj['{address}'] = this.ordersDetailsData.address;
                }

                if (this.ordersDetailsData.table_id != null) {
                    obj['{table_name}'] = this.ordersDetailsData.table_name;
                }

                for (let [key, value] of Object.entries(obj)) {
                    this.invoiceTemplate = this.invoiceTemplate.replace(key, value);
                }

                this.invoiceTemplate = this.invoiceTemplate;
            },
            getInvoiceDetails(itemDetails) {
                let row = "";
                for (let item of itemDetails) {
                    if (item.variantTitle == 'default_variant' || item.variantTitle == undefined) {
                        item.variantTitle = '';
                    }
                    if (item.discount == null) {
                        item.discount = '0.00';
                    }

                    let newRow = `<tr>
                            <td style="padding: 7px 0; text-align: left; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${item.title} ${item.variantTitle}</td>
                            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${this.decimalFormat(item.quantity)}</td>
                            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${this.numberFormat(item.price)}</td>
                            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${this.decimalFormat(item.discount)}%</td>
                            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${this.numberFormat(item.total)}</td>
                        </tr>`;
                    row = row + newRow;
                }
                return row;
            },
            makePaymentDetailsForInvoice(paymentDetails) {
                let row = "";
                for (let item of paymentDetails) {
                    let newRow = `<tr style="text-align: left;">
                        <th style="padding: 7px 0;">${item.name}</th>
                        <th style="padding: 7px 0;"></th>
                        <th style="padding: 7px 0;"></th>
                        <th style="padding: 7px 0;"></th>
                        <td style="padding: 7px 0; text-align: right;">${this.numberFormat(item.paid)}</td>
                    </tr>`;
                    row = row + newRow;
                }
                return row;
            },

            goBack() {
                let instance = this;
                instance.redirect(`/${this.routeName}?tab_name=${this.tabName}&&${this.routeName}`);
            }
        },

    }
</script>
