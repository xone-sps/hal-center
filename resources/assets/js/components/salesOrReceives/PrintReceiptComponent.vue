<template>
    <div style="display:none">
        <div id="cart-print-area">
            <span v-html="invoiceTemplate"></span>
        </div>
    </div>
</template>

<script>
    import axiosGetPost from "../../helper/axiosGetPostCommon";

    export default {
        extends: axiosGetPost,

        props: [
            "print_invoice_before_done_payment",
            "sales_or_receiving_type",
            "transfer_branch_name",
            "payment_list_data",
            "exchange_value",
            "order_type",
            "final_cart",
            "payments",
            "sold_to",
            "sold_by",
            "balance",
            "user",
            "logo",
            "invoice_template",
            "invoiceId"
        ],
        data() {
            return {
                invoiceTemplate: "",
                soldBy: "",
                itemDetails: "",
                paymentDetails: ""
            };
        },
        computed: {
            isMobile() {
                return window.innerWidth <= 1024
            }
        },
        watch: {
            print_invoice_before_done_payment: function (newVal) {
                if (newVal) {
                    this.printReceiptBeforeDonePayment();
                }
            }
        },
        created() {
            let instance = this;
            instance.printInvoiceGenerate();
        },
        mounted() {
        },
        methods: {
            printReceiptBeforeDonePayment() {
                if (!this.isMobile) {
                    $("#cart-print-area").printThis({
                        importCSS: false,
                        importStyle: true,
                        printContainer: true,
                        debug: false,
                        header: null
                    });
                } else {
                    this.axiosPost('/socket/template-bin', {
                        'html': this.invoiceTemplate
                    }, (res) => {
                        console.log(res)
                    }, (err) => {
                        console.log(err)
                    });
                }
                this.$emit("resetGetInvoiceBeforeDonePayment", false);
            },
            printInvoiceGenerate() {
                let instance = this;
                instance.invoiceTemplate = instance.invoice_template;
                instance.soldBy =
                    instance.user.first_name + " " + instance.user.last_name;
                instance.itemDetails = instance.getInvoiceDetails(
                    instance.final_cart.cart
                );

                let customerName,
                    customerPhone = '',
                    customerAddress = '';

                if (instance.order_type == "sales") {
                    if (instance.sales_or_receiving_type == "customer") {
                        if (instance.final_cart.customer.length === 0) {
                            customerName = instance.trans("lang.walk_in_customer");
                        } else {
                            customerName = `${instance.final_cart.customer.first_name} ${instance.final_cart.customer.last_name}`;
                            if (instance.final_cart.customer.phone_number != null) {
                                customerPhone = instance.final_cart.customer.phone_number;
                            }
                            if (instance.final_cart.customer.address != null) {
                                customerAddress = instance.final_cart.customer.address;
                            }


                        }
                    } else {
                        customerName = instance.final_cart.transferBranchName;
                    }
                } else {
                    if (instance.sales_or_receiving_type == "supplier") {
                        customerName =
                            instance.final_cart.customer.length === 0
                                ? instance.trans("lang.walk_in_supplier")
                                : `${instance.final_cart.customer.first_name} ${instance.final_cart.customer.last_name}`;
                    } else {
                        customerName = instance.final_cart.transferBranchName;
                    }
                }

                let invoiceLogo = this.publicPath + "/uploads/logo/" + this.logo,
                    logo = `<div>
                                <img class="invoice-logo" style="max-width: 200px; height: auto; margin: 0 auto;" src= "${invoiceLogo}" alt="Logo">
                            </div>`;

                let obj = {
                    "{app_logo}": logo,
                    "{app_name}": this.app_name,
                    "{invoice_id}": "",
                    "{table_name}": "",
                    "{employee_name}": this.soldBy,
                    "{customer_name}": customerName,
                    "{supplier_name}": customerName,
                    "{date}": this.dateFormats(this.date),
                    "{time}": this.dateFormatsWithTime(this.date),
                    "{item_details}": this.itemDetails,
                    "{payment_details}": "",
                    "{sub_total}": instance.numberFormat(this.final_cart.subTotal),
                    "{tax}": instance.numberFormat(this.final_cart.tax),
                    "{total}": instance.numberFormat(this.final_cart.grandTotal),
                    "{discount}": instance.numberFormat(this.final_cart.discount),
                    "{exchange}": "",
                    "{phone_number}": customerPhone,
                    "{address}": customerAddress,
                };


                for (let [key, value] of Object.entries(obj)) {
                    this.invoiceTemplate = this.invoiceTemplate.replace(key, value);
                }

                this.invoiceTemplate = this.invoiceTemplate;
            },
            getInvoiceDetails(itemDetails) {
                let row = "";
                for (let item of itemDetails) {
                    if (
                        item.variantTitle == "default_variant" ||
                        item.variantTitle == undefined
                    ) {
                        item.variantTitle = "";
                    }
                    if (item.discount == null) {
                        item.discount = "0.00";
                    }
                    let newRow = `<tr>
                            <td style="padding: 7px 0; text-align: left; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${
                        item.productTitle
                    } ${item.variantTitle}</td>
                            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${this.decimalFormat(
                        item.quantity
                    )}</td>
                            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${this.numberFormat(
                        item.price
                    )}</td>
                            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${this.decimalFormat(
                        item.discount
                    )}%</td>
                            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${this.numberFormat(
                        item.calculatedPrice
                    )}</td>
                        </tr>`;
                    row = row + newRow;
                }
                return row;
            }
        }
    };
</script>