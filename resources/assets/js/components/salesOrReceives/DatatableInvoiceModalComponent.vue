<template>
    <div>
        <a href=""
           class="action-button"
           data-toggle="modal"
           data-target="#datatable-invoice-modal"
           @click.prevent="getOrdersInfo(rowData.id)">
            {{ rowData.invoice_id }}
        </a>

        <div v-if="isActiveDatatableInvoiceModal" data-backdrop="static" class="modal fade" id="datatable-invoice-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-layout-header">
                        <div class="container-fluid p-0">
                            <div class="row">
                                <div class="col-10">
                                    <h4 class="m-0">{{trans('lang.invoice')}}</h4>
                                </div>
                                <div class="col-2 text-right">
                                    <button type="button" class="close" @click.prevent="closeModal()">
                                        <i class="la la-close icon-modal-cross"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-layout-content">
                        <pre-loader v-if="showPreloader"/>
                        <div v-else id="cart-print-area" v-html="invoiceTemplate" style="min-height: 500px; max-width: 800px; margin: 0 auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axiosGetPost from '../../helper/axiosGetPostCommon';
    export default {
        extends: axiosGetPost,
        name: "DatatableInvoiceModalComponent",
        props: ['rowData', 'rowIndex', 'id', 'order_type', 'tab_name', 'route_name'],
        data() {
            return {
                isActiveDatatableInvoiceModal: false,
                selectedRowData: this.rowData,
                responseData:'',
                ordersDetailsData: {},
                showPreloader:true,
                tabName:'',
                routeName:'',
                invoiceTemplate:'',
                customerDetails:'',
                itemDetails:'',
                paymentDetails:'',
                invoiceLogo:'',
                exchange:'',
                subTotal:'',
                discount:'',
                total:'',
                totalTax:'',
            }
        },
        mounted() {
            let instance = this;

            /*$('#datatable-invoice-modal').on('hidden.bs.modal', function (e) {
                instance.isActiveDatatableInvoiceModal = false;
                console.log('Test console');
            });*/

            $('#datatable-invoice-modal').on('hidePrevented.bs.modal', function (e) {
                instance.isActiveDatatableInvoiceModal = false;
            });
        },
        methods: {
            getOrdersInfo(id) {
                let instance = this;

                instance.isActiveDatatableInvoiceModal = true;
                this.axiosGet('/reports/ordersDetails/' + id,
                    function (response) {
                        instance.responseData = response.data;
                        instance.invoiceTemplate = instance.responseData.invoice_templates;
                        instance.ordersDetailsData = instance.responseData.orders_details;
                        instance.customerDetails = instance.responseData.customer_details;
                        instance.itemDetails =  instance.responseData.item_details;
                        instance.paymentDetails =  instance.responseData.payment_details;
                        instance.invoiceLogo =  instance.responseData.invoice_logo;
                        instance.exchange =  instance.responseData.exchange_amount.exchange;
                        instance.subTotal =  instance.responseData.orders_details.sub_total;
                        instance.total =  instance.responseData.orders_details.total;
                        instance.totalTax =  instance.responseData.orders_details.total_tax;
                        instance.discount = instance.responseData.orders_details.all_discount;
                        instance.printInvoiceGenerate();
                        instance.showPreloader = false;
                    },
                    function (response) {

                    },
                );
            },
            printReceiptBeforeDonePayment() {
                $('#cart-print-area').printThis({
                    importCSS: true,
                    importStyle: true,
                    printContainer: true,
                    header: null,
                });
                this.$emit('resetGetInvoiceBeforeDonePayment', false);
            },
            printInvoiceGenerate() {
                let instance = this;

                instance.itemDetails =  this.getInvoiceDetails(instance.itemDetails);
                instance.paymentDetails =  this.makePaymentDetailsForInvoice(instance.paymentDetails);

                let invoiceLogo = this.publicPath + '/uploads/logo/' + instance.invoiceLogo,
                    logo = `<div>
                                <img class="invoice-logo" style="max-width: 200px; height: auto; margin: 0 auto;" src= "${invoiceLogo}" alt="Logo">
                            </div>`;

                let obj = {
                    '{app_logo}': logo,
                    '{app_name}': this.app_name,
                    '{invoice_id}': instance.ordersDetailsData.invoice_id,
                    '{employee_name}': instance.ordersDetailsData.first_name + ' ' +instance.ordersDetailsData.last_name,
                    '{table_name}': '',
                    '{customer_name}': instance.ordersDetailsData.customer_name,
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

                if(this.ordersDetailsData.table_id != null){
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
            closeModal() {
                $('#datatable-invoice-modal').modal('hide');
                this.isActiveDatatableInvoiceModal = false;
                this.$hub.$emit('resetRegisterInfoModal', false);
            }
        }
    }
</script>