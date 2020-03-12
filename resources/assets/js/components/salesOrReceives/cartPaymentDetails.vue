<template>
    <div class="modal-layout-content">
        <a href="#" class="position-absolute variant-modal-close-btn p-2 close" data-dismiss="modal" aria-label="Close"
           @click.prevent="closeModal">
            <i class="la la-close text-grey"></i>
        </a>
        <div class="row mx-0 modal-content-row">
            <div class="col-12 col-md-6 cart-border-right text-center">
                <div class="horizontal-scroll">
                    <h5 class="text-center mb-4">{{trans('lang.'+orderType)+' '+trans('lang.details')}}</h5>
                    <div v-if="logo =='default-logo.jpg'"></div>
                    <div class="invoiceLogo text-center" v-else>
                        <img class="text-center" :src="publicPath+'/uploads/logo/'+logo" height="50px" width="70px"
                             alt="">
                    </div>
                    <div>
                        <div class="text-center header-line-height">
                            <small class='text-center font-weight-bold'>{{ app_name }}</small>
                            <br>
                            <small class='text-center'>{{ dateFormats(finalCart.date) }}</small>
                            <br>
                            <small class='text-center'>{{trans('lang.'+orderType)+' '+trans('lang.receipt')}}</small>
                            <br>
                            <small class="text-left">{{ trans('lang.'+sold_by)}}: {{user.first_name + " "+
                                user.last_name}}
                            </small>
                            <br>
                            <small v-if="orderType == 'sales'">
                            <span v-if="salesOrReceivingType == 'customer'">
                                <span v-if="finalCart.customer.length === 0">{{ trans('lang.'+sold_to)}}: {{ trans('lang.walk_in_customer')}}</span>
                                <span v-else>
                                    {{ trans('lang.'+sold_to)}}: {{finalCart.customer.first_name + " " + finalCart.customer.last_name}}
                                </span>
                            </span>
                                <span v-else>
                                {{ trans('lang.'+sold_to)}}: {{finalCart.transferBranchName}}
                            </span>
                            </small>
                            <small v-else>
                            <span v-if="salesOrReceivingType == 'supplier'">
                                <span v-if="finalCart.customer.length === 0">{{ trans('lang.received_from')}}: {{ trans('lang.walk_in_supplier')}}</span>
                                <span v-else>
                                    {{ trans('lang.received_from')}}: {{finalCart.customer.first_name + " " + finalCart.customer.last_name}}
                                </span>
                            </span>
                                <span v-else>
                                {{ trans('lang.received_from')}}: {{finalCart.transferBranchName}}
                            </span>
                            </small>
                            <small class="text-left invoice-show" style="display: none">{{ trans('lang.invoice_id')
                                }}:{{
                                invoiceID }}
                            </small>
                        </div>
                        <div class="invoice-table">
                            <table class="table product-card-font" style="font-weight:500">
                                <thead class="border-top-0">
                                <tr>
                                    <th class="cart-summary-table text-left" v-if="finalCart.cart.length>1">{{
                                        trans('lang.items') }}
                                    </th>
                                    <th class="cart-summary-table text-left" v-else>{{ trans('lang.item') }}</th>
                                    <th class="cart-summary-table text-left">{{ trans('lang.qty') }}</th>
                                    <th class="cart-summary-table text-right">{{ trans('lang.price') }}</th>
                                    <th class="cart-summary-table text-right">{{ trans('lang.discount') }}</th>
                                    <th class="cart-summary-table text-right">{{ trans('lang.total') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="cartItem in finalCart.cart">
                                    <td class="cart-summary-table text-left">
                                        {{ cartItem.productTitle }}
                                        <br>
                                        <span v-if="cartItem.productTitle != cartItem.variantTitle && cartItem.variantTitle != 'default_variant' && cartItem.variantTitle != '' && cartItem.orderType != 'discount'">( {{ cartItem.variantTitle }} )</span>
                                        <p v-if="cartItem.cartItemNote!='' && cartItem.cartItemNote != null"
                                           class="cart-note pb-0 mb-0">
                                            {{ trans('lang.note') }}: <span>{{ cartItem.cartItemNote }}</span>
                                        </p>
                                    </td>
                                    <td class="cart-summary-table" v-if="cartItem.orderType != 'discount'">
                                        {{ cartItem.quantity }}
                                    </td>
                                    <td class="cart-summary-table" v-else></td>
                                    <td class="text-right cart-summary-table" v-if="cartItem.orderType != 'discount'">
                                        {{ numberFormat(cartItem.price) }}
                                    </td>
                                    <td class="cart-summary-table" v-else></td>
                                    <td class="text-right cart-summary-table" v-if="cartItem.discount >0">
                                        {{ decimalFormat(cartItem.discount) }}%
                                    </td>
                                    <td class="text-right cart-summary-table"
                                        v-else-if="cartItem.orderType != 'discount'">
                                        {{ decimalFormat('0.00') }}%
                                    </td>
                                    <td class="cart-summary-table" v-else></td>
                                    <td class="text-right cart-summary-table">
                                        {{ numberFormat(cartItem.calculatedPrice) }}
                                    </td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td class="cart-summary-table font-weight-bold text-left">{{ trans('lang.sub_total')
                                        }}
                                    </td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="text-right cart-summary-table">{{ numberFormat(finalCart.subTotal) }}
                                    </td>
                                </tr>
                                <tr v-if="finalCart.tax>0">
                                    <td class="cart-summary-table text-left">{{ trans('lang.item_tax') }}</td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="text-right cart-summary-table ">{{ numberFormat(finalCart.tax) }}</td>
                                </tr>
                                <tr>
                                    <td class="cart-summary-table font-weight-bold text-left">{{ trans('lang.total')
                                        }}
                                    </td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="text-right cart-summary-table ">{{ numberFormat(finalCart.grandTotal)
                                        }}
                                    </td>
                                </tr>
                                <tr v-if="payment.paymentName" v-for="payment in payments">
                                    <td class="cart-summary-table text-left">{{ payment.paymentName }}</td>
                                    <td class="cart-summary-table">
                                        <small class="font-weight-bold" v-if="payment.paid">{{ trans('lang.paid') }}<br>
                                            {{payment.paid}}
                                        </small>
                                    </td>
                                    <td class="cart-summary-table text-left" v-if="payment.paymentName">
                                        <small class="font-weight-bold" v-if="payment.exchange">{{
                                            trans('lang.exchange') }}
                                            <br> {{payment.exchange}}
                                        </small>
                                    </td>
                                    <td class="cart-summary-table">
                                        {{ numberFormat(payment.paid - payment.exchange) }}
                                    </td>
                                </tr>
                                <tr v-for="(paymentTypes, index) in paymentListData">
                                    <td class="cart-summary-table text-left">{{paymentTypes.paymentName}}</td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="text-right cart-summary-table">{{numberFormat(paymentTypes.paid)}}</td>
                                </tr>
                                <tr v-if="exchangeValue>0">
                                    <td class="cart-summary-table text-left">{{ trans('lang.exchange') }}</td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="text-right cart-summary-table ">{{ numberFormat(exchangeValue) }}</td>
                                </tr>
                                <tr v-if="!printReceiptView">
                                    <td class="cart-summary-table font-weight-bold text-left" v-if="balance >= 0">
                                        {{ trans('lang.due') }}
                                    </td>
                                    <td class="cart-summary-table font-weight-bold text-left" v-else>{{
                                        trans('lang.change') }}
                                    </td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="text-right cart-summary-table">{{ numberFormat(absValue(balance)) }}</td>
                                </tr>
                                <tr v-if="finalCart.cartNote !=''">
                                    <td class="cart-summary-table" colspan="3">
                                        <small>{{ trans('lang.note') }}: {{ finalCart.cartNote }}</small>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                            <barcode style="display: none"
                                     :value="invoiceID"
                                     font-options="bold"
                                     class="text-center invoice-show"
                                     height="30"
                                     display-value=false>
                            </barcode>
                        </div>
                    </div>
                </div>
                <a href="#" v-if="!isPaymentDone && !printLoading"
                   @click.prevent="printReceiptBeforeDonePayment()"
                   class="px-2 btn-before-receipt">
                    <i class="la la-print pr-3"></i>
                    {{ trans('lang.print_received') }}
                </a>
                <a href="#" v-else-if="!isPaymentDone"
                   class="px-2 btn-before-receipt">
                    <i class="la la-spin la-spinner mr-3"></i>
                    {{ trans('lang.print_received') }}
                </a>
            </div>

            <div class="col-12 col-md-6" id="js-payment">
                <div v-if="!isPaymentDone || invoice_template == ''">
                    <div class="row mx-0 mb-4" id="js-payment-title">
                        <div class="col-6"><h5>{{ trans('lang.total') }}</h5></div>
                        <div class="col-6 text-right payment-amount"><h5> {{ numberFormat(finalCart.grandTotal) }} </h5>
                        </div>
                    </div>
                    <pre-loader v-if="!hidePaymentListGetLoader || invoice_template == ''"></pre-loader>
                    <div v-else>
                        <div class="payment-description" id="js-payment-description">
                            <div class="row mx-0 mb-2" v-for="(paymentTypes, index) in paymentListData">
                                <div class="col-4 col-sm-6 col-md-5 col-lg-4 col-xl-4 mt-auto">
                                    <label>{{ paymentTypes.paymentName }}</label>
                                </div>
                                <div class="col-4 col-sm-5 col-md-6 col-lg-7 col-xl-7 pl-0">
                                    <label>{{numberFormat(paymentTypes.paid)}}</label>
                                </div>
                                <div class="col-1 mt-auto p-0 text-right">
                                    <a href="#"
                                       @click.prevent="clearPayment(index,paymentTypes.paymentID,paymentTypes.paid)">
                                        <i class="text-danger la la-trash"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="row mx-0 mb-2">
                                <div class="col-4 col-sm-6 col-md-5 col-lg-4 col-xl-4 mt-auto">
                                    <label :for="paid">{{paymentName}}</label>
                                </div>
                                <div class="col-4 col-sm-6 col-md-7 col-lg-8 col-xl-8 pl-0 right-zero-padding">
                                    <payment-input id="'paid'" v-if="PaymentGauard"
                                                   :inputValue="decimalFormat(paymentValue)"
                                                   @input="getPaymentAmount">
                                    </payment-input>
                                </div>
                            </div>
                        </div>
                        <div class="payment-action" id="js-payment-action">
                            <div class="row mx-0 mt-2 no-gutters">
                                <div class="col-12">
                                    <hr class="custom-margin">
                                    <div class="payment-group" style="overflow: hidden;">
                                        <span v-for="(paymentTypes, index) in paymentList">
                                           <button v-if="(salesOrReceivingType == 'customer' || salesOrReceivingType == 'supplier') && paymentTypes.type != 'credit'"
                                                   :id="paymentTypes.id"
                                                   class="btn app-color mobile-btn mr-1 mb-1"
                                                   :class="{activePayment: paymentName==paymentTypes.name}"
                                                   @click.prevent="setPayment(paymentTypes.id,paymentTypes.name,paymentTypes.status,paymentTypes.type)">
                                                    {{ paymentTypes.name }}
                                            </button>
                                            <button v-else-if="(salesOrReceivingType == 'customer' || salesOrReceivingType == 'supplier') && !isEmptyObj(finalCart.customer)"
                                                    :id="paymentTypes.id"
                                                    class="btn app-color mobile-btn mr-1 mb-1"
                                                    :class="{activePayment: paymentName==paymentTypes.name}"
                                                    @click.prevent="setPayment(paymentTypes.id,paymentTypes.name,paymentTypes.status,paymentTypes.type)">
                                                    {{ paymentTypes.name }}
                                            </button>
                                            <button v-else-if="salesOrReceivingType == 'internal'"
                                                    :id="paymentTypes.id"
                                                    class="btn app-color mobile-btn mr-1 mb-1"
                                                    :class="{activePayment: paymentName==paymentTypes.name}"
                                                    @click.prevent="setPayment(paymentTypes.id,paymentTypes.name,paymentTypes.status,paymentTypes.type)">
                                                    {{ paymentTypes.name }}
                                            </button>
                                        </span>
                                    </div>

                                    <!-- NB: Temporary unavailable feature,  When bank payment and card payment active -->

                                    <!--<div class="payment-group">

                                    class="payment-button"

                                       <span v-for="(paymentTypes, index) in paymentList">
                                           <button v-if="paymentTypes.type == 'card'"
                                                   :id="paymentTypes.id"
                                                   class="btn app-color mobile-btn m-1"
                                                   :class="{activePayment: paymentName==paymentTypes.name}"
                                                   @click.prevent="setPayment(paymentTypes.id,paymentTypes.name,paymentTypes.status,paymentTypes.type),openCardModal()">
                                                    {{ paymentTypes.name }}
                                            </button>
                                            <button v-else-if="paymentTypes.type == 'bank'"
                                                    :id="paymentTypes.id"
                                                    class="btn app-color mobile-btn m-1"
                                                    :class="{activePayment: paymentName==paymentTypes.name}"
                                                    @click.prevent="setPayment(paymentTypes.id,paymentTypes.name,paymentTypes.status,paymentTypes.type),openBankModal()">
                                                    {{ paymentTypes.name }}
                                            </button>
                                            <button v-else
                                                    :id="paymentTypes.id"
                                                    class="btn app-color mobile-btn m-1"
                                                    :class="{activePayment: paymentName==paymentTypes.name}"
                                                    @click.prevent="setPayment(paymentTypes.id,paymentTypes.name,paymentTypes.status,paymentTypes.type)">
                                                    {{ paymentTypes.name }}
                                            </button>
                                       </span>
                                    </div>-->
                                    <hr class="custom-margin">
                                    <span v-if="balance==0 || paidAmount > finalCart.grandTotal">
                                        <button class="btn btn-block app-color payment-button"
                                                v-shortkey="donePaymentShortcut"
                                                :disabled="!isConnected && offline == 0"
                                                @shortkey="shortcutForDonePayment('donePayment')"
                                                @click.prevent="storeInvoice()">
                                                {{ trans('lang.done_payment') }}
                                        </button>
                                    </span>
                                    <span v-else>
                                        <button class="btn btn-block app-color payment-button"
                                                :disabled="!isConnected && offline == 0"
                                                @click.prevent="storeInvoice()">
                                                {{ trans('lang.add_payment') }}
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mx-0" v-else>
                    <div class="col-12 text-center">
                        <h4>{{ trans('lang.payment_received') }}</h4>
                    </div>
                    <a v-if="!printLoading" href="#"
                       @click.prevent="printReceipt()" class="printReceiptButton">
                        <i class="la la-print pr-3"></i>
                        {{ trans('lang.print_received') }}
                    </a>
                    <a v-else href="#"
                     class="printReceiptButton">
                        <i class="la la-spin la-spinner mr-3"></i>
                        {{ trans('lang.print_received') }}
                    </a>
                </div>
            </div>
        </div>
        <invoice :printInvoice="printInvoice"
                 :HTMLcontent="HTMLcontent"
                 @printingDone="printLoadingState"
                 @resetGetInvoice="resetGetInvoice">
        </invoice>
        <print-receipt-component :print_invoice_before_done_payment="printInvoiceBeforeDonePayment"
                                 :sales_or_receiving_type="salesOrReceivingType"
                                 :transfer_branch_name="transferBranchName"
                                 :payment_list_data="paymentListData"
                                 :exchange_value="exchangeValue"
                                 :order_type="orderType"
                                 :final_cart="finalCart"
                                 :payments="payments"
                                 :sold_to="sold_to"
                                 :sold_by="sold_by"
                                 :balance="balance"
                                 :invoice_template="invoice_template"
                                 :user="user"
                                 :logo="logo"
                                 @printingBeforeDone="printLoadingState"
                                 @resetGetInvoiceBeforeDonePayment="resetGetInvoiceBeforeDonePayment">
        </print-receipt-component>
    </div>
</template>

<script>

    import VueBarcode from 'vue-barcode';
    import axiosGetPost from '../../helper/axiosGetPostCommon';
    import Product from "../settings/product/product";

    export default {
        extends: axiosGetPost,
        components: {
            Product,
            'barcode': VueBarcode
        },
        props: [
            'selectedCashRegisterID',
            'finalCart',
            'user',
            'orderType',
            'salesOrReceivingType',
            'orderID',
            'sold_to',
            'sold_by',
            'logo',
            'bankOrCardAmount',
            'calculateBank',
            'bankOrCardOptions',
            'donePaymentShortcut',
            'transferBranch',
            'transferBranchName',
            'paymentTypes',
            'autoInvoice',
            'invoice_template',
            'last_invoice_number',
            'invoice_prefix',
            'invoice_suffix',
            'selectedBranchID',
            'invoiceId',
            'is_cash_register_branch',
            'sales_default_invoice_template',
            'receives_default_invoice_template',
            'is_cash_register_used',
            'is_connected',
        ],
        data() {
            return {
                paymentList: [],
                isPaymentDone: false,
                invoice: [],
                PaymentGauard: true,
                payments: [],
                savedPayments: [],
                balance: this.finalCart.grandTotal,
                printReceiptView: false,
                invoiceID: '',
                autoInvoiceGenerate: false,
                donePaymentCheck: false,
                hidePaymentListGetLoader: null,
                exchangeValue: '',
                currentOrderId: '',
                paymentListData: [],
                paymentName: '',
                paymentId: '',
                paymentStatus: '',
                paid: '',
                noRoundingAmount: '',
                finalCartAmount: '',
                roundingDifference: 0,
                printInvoice: false,
                printInvoiceBeforeDonePayment: false,
                paymentType: '',
                paymentValue: '',
                paymentOptions: {},
                paidAmount: '',
                HTMLcontent: '',
                autoInvoiceStatus: '',
                highestInvoiceNumber: '',
                lastInvoiceNumber: '',
                isCashRegisterBranch: '',
                isConnected: true,
                isEmptyObj: (object) => {
                    if (_.isEmpty(object)) {
                        return true;
                    }
                },
                printLoading: false,
            }
        },
        created() {
            this.noRoundingAmount = this.finalCart.grandTotal;
            this.isCashRegisterBranch = this.is_cash_register_branch;
            this.setPaymentListData();
            this.setAutoInvoiceStatus();
            if (this.orderID) {
                this.setSavedPayments();
            }
            this.currentOrderId = this.orderID;
        },
        watch: {
            calculateBank: function (newValue) {
                if (newValue) {
                    this.defaultPayment(this.bankOrCardAmount, this.bankOrCardOptions);
                }
            },
            is_connected: function (value) {
                this.isConnected = value;
            },
        },
        mounted() {
            let instance = this;
            instance.lastInvoiceNumber = parseInt(instance.last_invoice_number);

            // future use for bank and card payment
            // $('#bank-transfer-modal').on('hidden.bs.modal', function (e) {
            //     instance.selectDefaultMethod();
            // });
            // $('#card-payment-modal').on('hidden.bs.modal', function (e) {
            //     instance.selectDefaultMethod();
            // });
            $(window).resize(function () {
                instance.setPaymentDescHeight();
            });

        },
        beforeDestroy() {
            if (!this.donePaymentCheck) {
                this.storeInvoice('continue');
            }
        },
        methods: {
            closeModal() {
            },
            shortcutForDonePayment(value) {
                if (this.shortcutKeyInfo.donePayment.status == 1 && this.shortcutStatus == 1) {
                    this.storeInvoice();
                }
            },
            getPaymentAmount(value) {
                this.paid = value;
                this.calculateBalance();
            },
            defaultPayment(amount, options) {
                this.paid = amount;
                this.paymentValue = amount;
                this.paymentOptions = options;
                this.calculateBalance();
                this.storeInvoice();
            },
            openBankModal() {
                this.$emit('amount', this.paid, '#bank-transfer-modal');
            },
            openCardModal() {
                this.$emit('amount', this.paid, '#card-payment-modal');
            },
            setAutoInvoiceStatus() {
                let instance = this;
                instance.autoInvoiceStatus = JSON.parse(instance.autoInvoice);
                if (instance.autoInvoiceStatus.setting_value == 1) {
                    instance.autoInvoiceGenerate = true;
                }
            },
            getAutoEmailReceive() {
                let instance = this;
                instance.axiosGet('/get-auto-email-receive', function (response) {
                        if (response.data.autoEmailReceive.setting_value == 1) {
                            instance.autoEmailReceive = true;
                        }
                    },
                    function (error) {

                    });
            },
            setPayment(id, name, status, type, check = true) {
                let instance = this;
                instance.PaymentGauard = false;
                this.paymentName = name;
                this.paymentId = id;
                this.paymentStatus = status;
                this.paymentType = type;
                this.paymentOptions = {};
                this.paid = parseFloat(this.rounding(this.noRoundingAmount)).toFixed(2);
                this.paymentValue = this.paid;
                this.roundingDifference = parseFloat(this.paid - this.noRoundingAmount);
                setTimeout(function () {
                    instance.PaymentGauard = true;
                });
                if (check) {
                    this.calculateBalance();
                }
            },
            selectDefaultMethod() {
                let instance = this;
                for (let i = 0; i < instance.paymentList.length; i++) {
                    this.payments.push({
                        paymentID: instance.paymentList[i].id,
                        paymentName: null,
                        paid: null,
                        exchange: null,
                    });
                    if (instance.paymentList[i].is_default == 1) {
                        instance.setPayment(instance.paymentList[i].id, instance.paymentList[i].name, instance.paymentList[i].status, instance.paymentList[i].type, false);
                    }
                }
            },
            clearPayment(index, payment_id, paid) {
                this.paymentListData.splice(index, 1);
                this.noRoundingAmount += parseFloat(paid);
                this.paid = this.rounding(this.noRoundingAmount).toFixed(2);
                this.paymentValue = this.paid;
                this.roundingDifference = this.paid - this.noRoundingAmount;
                this.calculateBalance();
            },
            checkPaymentSelected() {
                return _.filter(this.payments, ['paymentName', null]).length;
            },
            calculateBalance() {
                let paidAmount = 0,
                    exchangedAmount = 0;
                let instance = this;

                this.paymentListData.forEach(function (payment, index) {
                    if (payment.paid) {
                        paidAmount += parseFloat(payment.paid);
                    }
                    if (payment.exchange) {
                        exchangedAmount += parseFloat(payment.exchange);
                    }
                });
                this.exchangeValue = exchangedAmount;
                this.paidAmount = paidAmount;
                this.balance = (this.finalCart.grandTotal + this.roundingDifference - paidAmount - (this.paid)).toFixed(2);
            },
            setDestroyCart(value) {
                this.$emit('setDestroyCart', value);
            },
            setPaymentListData() {
                let instance = this;
                instance.paymentList = JSON.parse(instance.paymentTypes);
                instance.selectDefaultPayment();
                instance.hidePaymentListGetLoader = true;
                instance.setPaymentDescHeight();
            },
            selectDefaultPayment() {
                let instance = this;
                for (let i = 0; i < instance.paymentList.length; i++) {
                    this.payments.push({
                        paymentID: instance.paymentList[i].id,
                        paymentName: null,
                        paid: null,
                        exchange: null,
                    });
                    if (instance.paymentList[i].is_default == 1) {
                        instance.setPayment(instance.paymentList[i].id, instance.paymentList[i].name, instance.paymentList[i].status, instance.paymentList[i].type);
                    }
                }
            },
            setSavedPayments() {
                let instance = this;

                if (navigator.onLine) {
                    instance.axiosGETorPOST(
                        {
                            url: '/continue-sale-payments', //set url
                            postData: {orderID: this.orderID} //set post data
                        },
                        (success, responseData) => { // callback after axios method call
                            if (success) //response after then function
                            {
                                instance.savedPayments = responseData;

                            }
                        });
                }
            },
            printReceipt() {
                this.printInvoice = true;
            },
            printReceiptBeforeDonePayment() {
                this.printInvoiceBeforeDonePayment = true;
            },
            storeInvoice(action = 'store') {
                let instance = this;
                const foundPayment = this.paymentListData.find((payment) => payment.paymentID === this.paymentId);
                if (foundPayment) {
                    foundPayment.paid = parseFloat(foundPayment.paid) + parseFloat(this.paid);
                } else {
                    this.paymentListData.push({
                        paid: this.paid,
                        paymentID: this.paymentId,
                        paymentName: this.paymentName,
                        paymentType: this.paymentType,
                        PaymentTime: moment().format('YYYY-MM-DD H:mm'),
                        options: this.paymentOptions,
                    });
                }
                let paidAmount = 0,
                    exchangedAmount = 0;
                this.paymentListData.forEach(function (payment, index) {
                    if (payment.paid) {
                        paidAmount += parseFloat(payment.paid);
                        if (paidAmount - instance.finalCart.grandTotal > 0) {

                            let exchange = paidAmount - instance.finalCart.grandTotal;
                            instance.paymentListData[index].exchange = exchange;
                        } else {
                            instance.paymentListData[index].exchange = 0;
                        }
                    }
                });
                if (this.balance == 0 || this.paidAmount > this.finalCart.grandTotal) {

                    let cartItemsToStore = this.finalCart,
                        postURL;
                    cartItemsToStore.highest_invoice_number = this.lastInvoiceNumber;

                    if (cartItemsToStore.orderType == 'sales') {
                        postURL = '/store';
                    } else postURL = '/purchase-store';

                    if (this.exchangeValue > 0) {
                        this.paymentListData.forEach(function (payment, index) {
                            if (payment.paymentType == 'cash') {
                                payment.paid = payment.paid + instance.exchangeValue;
                                payment.exchange = instance.exchangeValue
                            }
                        });
                    }

                    cartItemsToStore.payments = this.paymentListData;
                    cartItemsToStore.orderID = this.orderID;
                    cartItemsToStore.exchangeValue = this.exchangeValue;
                    cartItemsToStore.selectedBranchID = this.selectedBranchID;
                    cartItemsToStore.user = this.user;
                    cartItemsToStore.transferBranch = this.transferBranch;
                    if (this.isCashRegisterBranch === true) {
                        cartItemsToStore.cashRagisterId = this.selectedCashRegisterID;
                    }
                    cartItemsToStore.isCashRegisterBranch = this.isCashRegisterBranch;
                    cartItemsToStore.time = moment().format('YYYY-MM-DD h:mm A');
                    if (action == 'continue') {
                        cartItemsToStore.status = 'pending';

                        if (cartItemsToStore.orderType == 'sales') {
                            postURL = '/continue-sale';
                        } else postURL = '/continue-purchase';
                    }
                    instance.hidePaymentListGetLoader = false;
                    instance.isPaymentDone = false;
                    //Offline invoice store
                    if (!navigator.onLine && this.offline == 1 && action != 'continue') {
                        if (cartItemsToStore.orderID) {
                            cartItemsToStore.invoice_id = this.invoiceId;
                        } else {
                            if (this.invoiceId == null) {
                                cartItemsToStore.current_invoice_number = this.lastInvoiceNumber;
                                if (this.is_cash_register_used == 0) {
                                    cartItemsToStore.invoice_id = this.invoice_prefix + this.lastInvoiceNumber + '-' + '0' + '-' + this.user.id + this.invoice_suffix;
                                } else {
                                    cartItemsToStore.invoice_id = this.invoice_prefix + this.lastInvoiceNumber + '-' + this.selectedCashRegisterID + '-' + this.user.id + this.invoice_suffix;
                                }
                                this.lastInvoiceNumber = this.lastInvoiceNumber + 1;
                            } else {
                                cartItemsToStore.current_invoice_number = this.lastInvoiceNumber;
                                cartItemsToStore.invoice_id = this.invoiceId;
                            }
                        }
                        let localStorageData = localStorage.getItem('salesProduct');
                        if (localStorageData != null) {
                            let orderDetails = JSON.parse(localStorageData);
                            if (orderDetails.length > 0) {
                                orderDetails.forEach(function (orderHoldItem, index, array) {
                                    if (orderHoldItem.orderID == cartItemsToStore.orderID && orderHoldItem.orderID) {
                                        array.splice(index, 1);
                                    } else if (orderHoldItem.invoice_id == cartItemsToStore.invoice_id && orderHoldItem.orderID == null) {
                                        array.splice(index, 1);
                                    }
                                });
                            }
                            orderDetails.push(cartItemsToStore);
                            localStorage.setItem('salesProduct', JSON.stringify(orderDetails));
                        } else {
                            localStorage.setItem('salesProduct', JSON.stringify([cartItemsToStore]));
                        }
                        let instance = this;
                        instance.donePaymentCheck = true;
                        instance.$hub.$emit('setOrderID', null);
                        instance.isPaymentDone = true;

                        instance.generateOfflineInvoice(cartItemsToStore);

                        //To send the updated last invoice number to it mother component Sales or receives
                        instance.$emit('getUpdatedInvoice', instance.lastInvoiceNumber);
                        instance.printReceiptView = true;
                        instance.$emit('makePlaceOrderTrue', cartItemsToStore.tableId);
                    } else if (navigator.onLine) {
                        this.lastInvoiceNumber = this.lastInvoiceNumber + 1;
                        cartItemsToStore.highest_invoice_number = this.lastInvoiceNumber;
                        instance.axiosGETorPOST(
                            {
                                url: postURL, //set url
                                postData: cartItemsToStore, //set post data
                            },
                            (success, responseData) => {

                                // callback after axios method call
                                if (success) //response after then function
                                {
                                    if ("invoiceTemplate" in responseData) {
                                        instance.HTMLcontent = responseData.invoiceTemplate.data;
                                    }
                                    this.lastInvoiceNumber = responseData.lastInvoiceId;
                                    if (action == 'continue') {
                                        instance.$hub.$emit('setOrderID', responseData.orderID, responseData.lastInvoiceId);
                                    } else {
                                        instance.invoiceID = responseData.invoiceID;
                                        instance.setDestroyCart(true);
                                        instance.donePaymentCheck = true;
                                        instance.$hub.$emit('setOrderID', null, responseData.lastInvoiceId);

                                        if (instance.autoInvoiceGenerate == true) {
                                            $('#cart-payment-modal').modal('hide');
                                            instance.printReceipt();
                                        } else {
                                            instance.isPaymentDone = true;
                                            instance.printReceiptView = true;
                                        }
                                        instance.$emit('makePlaceOrderTrue', cartItemsToStore.tableId);
                                    }
                                }
                                instance.hidePaymentListGetLoader = true;
                            }
                        );
                    }
                } else {
                    instance.noRoundingAmount = parseFloat(instance.finalCart.grandTotal - paidAmount);
                    instance.paid = parseFloat(instance.rounding(instance.noRoundingAmount));
                    instance.paymentValue = instance.paid;
                    instance.roundingDifference = parseFloat(instance.paid - instance.noRoundingAmount);
                    instance.calculateBalance();
                }
                if (action == 'continue') {
                    instance.$emit('makeInvoiceIdNull', false);
                } else {
                    instance.$emit('makeInvoiceIdNull', true);
                }
            },
            generateOfflineInvoice(cartItemsToStore) {

                let instance = this;

                let invoiceLogo = this.publicPath + '/uploads/logo/' + this.logo,
                    logo = `<div>
                                <img class="invoice-logo" style="max-width: 200px; height: auto; margin: 0 auto;" src= "${invoiceLogo}" alt="Logo">
                            </div>`,
                    employeeName = cartItemsToStore.user.first_name + " " + cartItemsToStore.user.last_name,
                    itemDetails = instance.getInvoiceDetails(cartItemsToStore.cart),
                    paymentDetails = instance.makePaymentDetailsForInvoice(cartItemsToStore.payments),
                    subTotal = instance.numberFormat(cartItemsToStore.subTotal),
                    tax = instance.numberFormat(cartItemsToStore.tax),
                    total = instance.numberFormat(cartItemsToStore.grandTotal),
                    exchange = instance.numberFormat(cartItemsToStore.exchangeValue),
                    discount = instance.numberFormat(cartItemsToStore.overAllDiscount),
                    invoiceTemplate = this.invoice_template;

                let customerName;

                if (this.orderType == 'sales') {
                    if (this.salesOrReceivingType == 'customer') {
                        customerName = cartItemsToStore.customer.length === 0 ? 'Walk In Customer' : `${cartItemsToStore.customer.first_name} ${cartItemsToStore.customer.last_name}`;
                    } else {
                        customerName = this.transferBranchName;
                    }
                } else {
                    if (this.salesOrReceivingType == 'supplier') {
                        customerName = cartItemsToStore.customer.length === 0 ? 'Walk In Supplier' : `${cartItemsToStore.customer.first_name} ${cartItemsToStore.customer.last_name}`;
                    } else {
                        customerName = this.transferBranchName;
                    }
                }

                let obj = {
                    '{app_name}': this.app_name,
                    '{app_logo}': '',
                    '{invoice_id}': '',
                    '{employee_name}': employeeName,
                    '{customer_name}': customerName,
                    '{supplier_name}': customerName,
                    '{date}': this.dateFormats(this.date),
                    '{time}': this.dateFormatsWithTime(this.date),
                    '{item_details}': itemDetails,
                    '{payment_details}': paymentDetails,
                    '{sub_total}': subTotal,
                    '{tax}': tax,
                    '{total}': total,
                    '{exchange}': exchange,
                    '{discount}': discount,

                };
                if (this.is_cash_register_used == 0) {
                    obj['{invoice_id}'] = this.invoiceId ? this.invoiceId : this.invoice_prefix + cartItemsToStore.current_invoice_number + '-' + '0' + '-' + this.user.id + this.invoice_suffix;
                } else {
                    obj['{invoice_id}'] = this.invoiceId ? this.invoiceId : this.invoice_prefix + cartItemsToStore.current_invoice_number + '-' + this.selectedCashRegisterID + '-' + this.user.id + this.invoice_suffix;
                }

                for (let [key, value] of Object.entries(obj)) {
                    invoiceTemplate = invoiceTemplate.replace(key, value);
                }

                this.HTMLcontent = invoiceTemplate;
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
                            <td style="padding: 7px 0; text-align: left; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${item.productTitle} ${item.variantTitle}</td>
                            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${this.decimalFormat(item.quantity)}</td>
                            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${this.numberFormat(item.price)}</td>
                            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${this.decimalFormat(item.discount)}%</td>
                            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${this.numberFormat(item.calculatedPrice)}</td>
                        </tr>`;
                    row = row + newRow;
                }
                return row;
            },
            makePaymentDetailsForInvoice(paymentDetails) {
                let row = "";
                for (let item of paymentDetails) {
                    let newRow = `<tr style="text-align: left;">
                        <th style="padding: 7px 0;">${item.paymentName}</th>
                        <th style="padding: 7px 0;"></th>
                        <th style="padding: 7px 0;"></th>
                        <th style="padding: 7px 0;"></th>
                        <td style="padding: 7px 0; text-align: right;">${this.numberFormat(item.paid)}</td>
                    </tr>`;
                    row = row + newRow;
                }
                return row;
            },
            rounding(amount) {

                if (this.paymentStatus === 'near_half') {
                    return Math.round((amount) * 2).toFixed() / 2;

                } else if (this.paymentStatus === 'near_integer') {
                    return Math.round(amount);

                } else {
                    return amount;
                }
            },
            setPaymentDescHeight() {
                setTimeout(function () {
                    let totalHeight = $('#js-payment').height();
                    let paymentTitleHeight = $('#js-payment-title').height();
                    let paymentActionHeight = $('#js-payment-action').height();
                    let paymentDescHeight = totalHeight - (paymentTitleHeight + paymentActionHeight + 30);
                    $('#js-payment-description').height(paymentDescHeight);
                }, 1000);
            },
            resetGetInvoice(resetGetInvoice) {
                this.printInvoice = resetGetInvoice;
            },
            resetGetInvoiceBeforeDonePayment(resetGetInvoice) {
                this.printInvoiceBeforeDonePayment = resetGetInvoice;
            },
            absValue(value) {
                return Math.abs(value);
            },
            printLoadingState(state) {
                this.printLoading = state
            }
        },
    }
</script>