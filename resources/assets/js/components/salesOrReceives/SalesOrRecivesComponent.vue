<template>
    <div class="main-layout-wrapper" v-if="manage_sales == 1 || manage_receives == 1">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent m-0 pl-0">
                <li class="breadcrumb-item" v-if="order_type=='sales'">
                    <a href="#" data-toggle="modal" class="sales-nav app-color"
                       :class="{disabled:cart.length != 0 || !isConnected && offline == 1}"
                       data-target="#sales-or-return-type-select-modal">
                        {{ capitalizeFirstLetter(salesOrReturnType) }}
                        <i class="la la-angle-down"/>
                    </a>
                </li>
                <li class="breadcrumb-item" v-else>
                    <span class="sales-nav">
                        {{trans('lang.receives')}}
                    </span>
                </li>
                <li class="breadcrumb-item" v-if="order_type=='receiving'">
                    <span>
                        <a href="#" class="sales-nav" data-toggle="modal"
                           data-target="#sales-or-receiving-type-select-modal"
                           :class="{disabled:cart.length != 0}">
                        {{ capitalizeFirstLetter(salesOrReceivingType) }}
                            <i class="la la-angle-down"/>
                        </a>
                    </span>
                </li>
                <li class="breadcrumb-item" v-if="order_type=='sales'">
                    <a href="#" class="sales-nav" data-toggle="modal"
                       data-target="#sales-or-receiving-type-select-modal"
                       :class="{disabled:cart.length != 0 || salesOrReturnType == 'due'}"
                       v-if="salesOrReceivingType=='customer'">
                        {{ capitalizeFirstLetter(salesOrReceivingType) }}
                        <i class="la la-angle-down"></i>
                    </a>

                    <a href="#" class="sales-nav" data-toggle="modal"
                       data-target="#sales-or-receiving-type-select-modal"
                       :class="{disabled:cart.length != 0 || salesOrReturnType == 'due'}" v-else>
                        {{ capitalizeFirstLetter(salesOrReceivingType) }}
                        <i class="la la-angle-down"/>
                    </a>
                </li>
                <li class="breadcrumb-item" v-if="selectedBranchID && total_branch>1">
                    <a href="#" class="sales-nav" @click.prevent="branchModalAction()"
                       :class="{disabled:!isConnected && offline == 1 || cart.length != 0}">
                        {{ selectedBranchName }}
                        <i class="la la-angle-down"/>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page"
                    v-if="selectedCashRegisterID && selectedBranchID==selectedCashRegisterBranchID">
                    <a href="#" class="sales-nav" @click.prevent="cashRegisterModalAction()"
                       :class="{disabled:!isConnected && offline == 1}">
                        {{ selectedCashRegisterName }}
                        <i class="la la-angle-down"/>
                    </a>
                </li>
                <li>
                    <a href="#" class="p-1 px-2 ml-2 rounded app-color sales-nav text-white"
                       @click.prevent="openRegisterInfoModal">
                        {{trans('lang.register_info')}}
                    </a>
                </li>
                <li v-if="!isConnected">
                    <span class="offline-label mx-3 animated fadeIn delay-2s">
                        <i class="la la-wifi text-danger"/> {{ trans('lang.offline') }}
                    </span>
                </li>
                <li id="onlineLabel" v-if="isConnected && hideOnlineMessage">
                    <span class="online-label mx-3 animated fadeOut delay-5s">
                        <i class="la la-wifi"/> {{ trans('lang.online') }}
                    </span>
                </li>
            </ol>
        </nav>
        <div class="d-flex" style="height: calc(100vh - 6rem);" v-if="salesOrReturnType != 'due'">
            <div style="flex: 1 0;">
                <div class="main-layout-card mb-75">
                    <div class="main-layout-card-content p-2">
                        <div class="row mx-0">
                            <div class="col-12 p-0">
                                <!--Product search starts-->
                                <div class="input-group"
                                     v-if="salesOrReturnType == 'sales' || order_type =='receiving'">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="la la-search"></i></span>
                                    </div>
                                    <input id="search"
                                           type="text"
                                           class="form-control pr-4 rounded-right"
                                           :placeholder="trans('lang.search_product')"
                                           aria-label="Search"
                                           aria-describedby="search"
                                           v-model="productSearchValue"
                                           @keyup="searchProductInput"
                                           v-shortkey="productSearch"
                                           @shortkey="commonMethodForAccessingShortcut('productSearch')"
                                           ref="search">
                                    <div v-if="productSearchValue">
                                        <i class="la la-close position-absolute p-1 customer-search-cancel"
                                           @click.prevent="productSearchValue = '', getProductsBySearchBtn()">
                                        </i>
                                    </div>
                                </div>
                                <div class="input-group" v-else>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="la la-search"></i></span>
                                    </div>
                                    <input type="text" class="form-control pr-4 rounded-right"
                                           v-model="orderSearchValue"
                                           :placeholder="trans('lang.search_orders')"
                                           aria-label="Search"
                                           ref="searchOrder"
                                           id="searchOrder"
                                           aria-describedby="search"
                                           @input="searchOrderInput">
                                    <div v-if="orderSearchValue!=''">
                                        <i class="la la-close position-absolute p-1 customer-search-cancel"
                                           @click.prevent="orderSearchValue=''"></i>
                                    </div>
                                    <!-- order search result dropdown structure starts-->
                                    <div class="dropdown-menu dropdown-menu-right w-100"
                                         :class="{'show':orderSearchValue}">
                                        <pre-loader v-if="!hideOrderSearchPreLoader"
                                                    class="small-loader-container"></pre-loader>
                                        <div class="px-3 py-1 text-center"
                                             v-else-if="hideOrderSearchPreLoader && orders.length === 0">
                                            {{trans('lang.no_result_found')}}
                                        </div>
                                        <div class="customers-container" v-else-if="orders.length !== 0">
                                            <span v-for=" (order,index) in orders" @click.prevent="selectOrder(order)">
                                                <a href="#" class="dropdown-item">
                                                <h6 class="m-0"> {{trans('lang.invoice_id')}} : {{order.invoice_id}}
                                                    <br>
                                                    <small>{{trans('lang.date')}} : {{ dateFormats(order.date) }} {{ dateFormatsWithTime(order.date) }}</small>
                                                </h6>
                                                </a>
                                                <div class="dropdown-divider m-0"></div>
                                            </span>
                                        </div>
                                    </div>
                                    <!-- order search result dropdown structure ends-->
                                </div>
                                <!--Product search ends-->
                            </div>
                        </div>
                    </div>
                </div>
                <!--Products result starts-->
                <pre-loader
                        v-if="!hideProductSearchPreLoader && !(order_type==='sales' && salesOrReturnType==='returns')">
                </pre-loader>
                <div v-else>
                    <div class="row all-products mr-0">
                        <div v-if="salesOrReturnType == 'sales' || order_type =='receiving'"
                             class="col-6 col-md-4 col-lg-4 col-xl-3 pr-0 mb-75 pl-75 standard-product"
                             v-for="product in products">
                            <a href="#" class="app-color-text" data-toggle="modal"
                               :data-target="variantProductCard(product.variants)"
                               @click.prevent="productCardAction(product)">
                                <div class="product-card bg-white rounded">
                                    <div class="product-img-container image-property"
                                         :style="{ 'background-image': 'url(' + publicPath+'/uploads/products/' + product.productImage + ')' }">
                                    </div>
                                    <div class="product-card-content product-content">
                                        <div v-if="product.variants.length==1" class="position-relative h-100">
                                            <div v-for="variant in product.variants"
                                                 :class="{ 'h-100': variant.attribute_values=='default_variant' || product.variants.length>1}">
                                                <div v-if="variant.attribute_values!='default_variant'"
                                                     :class="{ 'h-100': variant.attribute_values=='default_variant' || product.variants.length>1}">
                                                    <div class="p-2 h-100 d-flex align-items-center product-card-font font-weight-bold text-center justify-content-center">
                                                        {{ product.title }}<br> {{'(' + variant.variant_title + ')'}}
                                                    </div>
                                                </div>
                                                <div v-else class="h-100">
                                                    <div class="p-2 h-100 d-flex align-items-center product-card-font font-weight-bold text-center justify-content-center">
                                                        <span class="limit"> {{ product.title }} </span>
                                                    </div>
                                                </div>
                                                <div class="product-card-font position-absolute rounded-right app-color text-white product-price price-section">
                                                    {{ numberFormat(variant.price) }}
                                                </div>
                                                <div v-if="variant.availableQuantity <= 0 && order_type == 'sales'"
                                                     class="product-card-font warning-message text-white sold-out">
                                                    {{trans('lang.out_of_stock')}}
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="position-relative h-100">
                                            <div class="p-2 h-100 d-flex align-items-center product-card-font font-weight-bold text-center justify-content-center ">
                                                <span class="limit"> {{ product.title }} </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <!--Show in mobile screen-->
                        <div id="cartShow" v-show='toggleCart'>
                            <a href="#" data-toggle="modal"
                               data-target="#cart-modal-for-mobile-view"
                               @click.prevent="openCartModalForMobile">
                                <i class="la la-shopping-cart"></i>
                            </a>
                        </div>
                        <div class="col-12"
                             v-if="products.length==0 && !(order_type==='sales'&& salesOrReturnType==='returns')">
                            <div class="main-layout-card">
                                <div class="main-layout-card-content text-center">
                                    {{trans('lang.no_result_found')}}
                                </div>
                            </div>
                        </div>
                        <div v-if="showLoadMore && isConnected && isEmptyObj(productSearchValue) && (salesOrReturnType == 'sales' || order_type =='receiving')"
                             class="col-12 text-center">
                            <load-more :buttonLoader="buttonLoader" :isDisabled="isLoadMoreDisabled"
                                       @submit="loadMoreSubmit"></load-more>
                        </div>
                    </div>

                </div>
                <!--Products result ends-->
            </div>
            <div id="layoutTop" style="position: relative; width: 30rem;">
                <div class="main-layout-card"
                     style="position: absolute; top: 0; bottom: 0; left: 0.75rem; right: 0; min-height: 450px;">
                    <cart-component v-if="isCartComponentActive"
                                    :is_selected_branch="isSelectedBranch"
                                    :selected_branch_id="selectedBranchID"
                                    :add_customer="addcustomer"
                                    :sales_or_receiving_type="salesOrReceivingType"
                                    :sales_return_status="salesOrReturnType"
                                    :order_type="order_type"
                                    :customer_group="customer_group"
                                    :offline_customers="offlineCustomers"
                                    :newAddedCustomer="newAddedCustomer"
                                    :offline_all_Branch="offlineAllBranch"
                                    :cart_arr="cart"
                                    :active_variant_id="activeVariantId"
                                    :active_product_id="activeProductId"
                                    :manage_price="manage_price"
                                    :user="user"
                                    :selectedCashRegisterID="selectedCashRegisterID"
                                    :sold_to="sold_to"
                                    :sold_by="sold_by"
                                    :final_cart="finalCart"
                                    :invoice_logo="invoice_logo"
                                    :last_invoice_number="lastInvoiceNumber"
                                    :invoicePrefix="invoice_prefix"
                                    :invoiceSuffix="invoice_suffix"
                                    :invoiceTemplate="invoiceTemplate"
                                    :bankOrCardAmount="bankOrCardAmount"
                                    :bankOrCardOptions="bankOrCardOptions"
                                    :calculateBank="calculateBank"
                                    :auto_invoice="auto_invoice"
                                    :payment_types="payment_types"
                                    :selectedBranchID="selectedBranchID"
                                    :sub_total="subTotal"
                                    :grand_total="grandTotal"
                                    :supplier="supplier"
                                    :count_hold_order="countHoldOrder"
                                    :order_hold_items="orderHoldItems"
                                    :internal_hold_orders="internalHoldOrders"
                                    :customer_hold_orders="customerHoldOrders"
                                    :customer_not_added="customerNotAdded"
                                    :selected_customer="selectedCustomer"
                                    :selected_search_branch="selectedSearchBranch"
                                    :restaurant_order_type="restaurantOrderType"
                                    :current_branch="currentBranch"
                                    :is_cash_register_branch="isCashRegisterBranch"
                                    :restaurant_table_id="restaurantTableId"
                                    :is_cash_register_used="isCashRegisterUsed"
                                    :is_hold_order_done="isHoldOrderDone"
                                    :is_place_order_active="isPlaceOrderActive"
                                    :order_id="orderID"
                                    :profit="profit"
                                    :is_connected="isConnected"
                                    :all_restaurant_tables="allRestaurantTables"
                                    :new_over_all_discount="newOverAllDiscount"
                                    :over_all_discount="overAllDiscount"
                                    :discount_amount="discount"
                                    :newDiscount_amount="newdiscount"
                                    :tax_amount="tax"
                                    :add_customer_short_key="addCustomerShortKey"
                                    :payment_short_key="paymentShortKey"
                                    :hold_card_item="holdCarditem"
                                    :cancel_card_item="cancelCarditem"
                                    @activeCartPaymentModal="activeCartPaymentModal"
                                    @newCustomerAddModalOpen="newCustomerAddModalOpen"
                                    @selectCustomerFromCart="selectCustomerFromCart"
                                    @removeSelectedCustomerFromCart="removeSelectedCustomerFromCart"
                                    @selectSearchBranchFromCart="selectSearchBranchFromCart"
                                    @removeSelectedBranchFromCart="removeSelectedBranchFromCart"
                                    @openTableModalFromCart="openTableModalFromCart"
                                    @setRestaurantOrderTypeFromCart="setRestaurantOrderTypeFromCart"
                                    @setCartItemsToCookieOrDBFromCart="setCartItemsToCookieOrDBFromCart"
                                    @openHoldOrderModalFromCart="openHoldOrderModalFromCart"
                                    @addOverAllDiscountFromCart="addOverAllDiscount"
                                    @allProductDiscountFromCart="allProductDiscount"
                                    @setTaxIncludedOrExcludedFromCart="setTaxIncludedOrExcludedFromCart"
                                    @orderHoldFromCart="orderHoldFromCart"
                    ></cart-component>
                </div>
            </div>
        </div>

        <div class="modal fade" id="register-info-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <register-info-modal class="modal-content"
                                     v-if="registerInfoModal"
                                     :current_branch="selectedBranchID"
                                     :modalID="registerInfoModalID"/>
            </div>
        </div>


        <!-- Modal in mobile screen -->
        <div id="cart-modal-for-mobile-view" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><i class="la la-close"></i>
                        </button>
                    </div>
                    <div class="modal-body" id="cartBody">
                        <div style="position: relative; width: 100%; margin-top: -20px; margin-bottom: -15px;">
                            <div class="main-layout-card" style="min-height: 90vh;">
                                <cart-component v-if="isCartComponentActiveForMobile"
                                                :is_selected_branch="isSelectedBranch"
                                                :selected_branch_id="selectedBranchID"
                                                :add_customer="addcustomer"
                                                :sales_or_receiving_type="salesOrReceivingType"
                                                :sales_return_status="salesOrReturnType"
                                                :order_type="order_type"
                                                :customer_group="customer_group"
                                                :offline_customers="offlineCustomers"
                                                :newAddedCustomer="newAddedCustomer"
                                                :offline_all_Branch="offlineAllBranch"
                                                :cart_arr="cart"
                                                :active_variant_id="activeVariantId"
                                                :active_product_id="activeProductId"
                                                :manage_price="manage_price"
                                                :user="user"
                                                :selectedCashRegisterID="selectedCashRegisterID"
                                                :sold_to="sold_to"
                                                :sold_by="sold_by"
                                                :final_cart="finalCart"
                                                :invoice_logo="invoice_logo"
                                                :last_invoice_number="lastInvoiceNumber"
                                                :invoicePrefix="invoice_prefix"
                                                :invoiceSuffix="invoice_suffix"
                                                :invoiceTemplate="invoiceTemplate"
                                                :bankOrCardAmount="bankOrCardAmount"
                                                :bankOrCardOptions="bankOrCardOptions"
                                                :calculateBank="calculateBank"
                                                :auto_invoice="auto_invoice"
                                                :payment_types="payment_types"
                                                :selectedBranchID="selectedBranchID"
                                                :is_cash_register_branch="isCashRegisterBranch"
                                                :restaurant_table_id="restaurantTableId"
                                                :sub_total="subTotal"
                                                :grand_total="grandTotal"
                                                :count_hold_order="countHoldOrder"
                                                :order_hold_items="orderHoldItems"
                                                :internal_hold_orders="internalHoldOrders"
                                                :customer_hold_orders="customerHoldOrders"
                                                :customer_not_added="customerNotAdded"
                                                :selected_customer="selectedCustomer"
                                                :selected_search_branch="selectedSearchBranch"
                                                :restaurant_order_type="restaurantOrderType"
                                                :current_branch="currentBranch"
                                                :is_cash_register_used="isCashRegisterUsed"
                                                :is_hold_order_done="isHoldOrderDone"
                                                :is_place_order_active="isPlaceOrderActive"
                                                :supplier="supplier"
                                                :order_id="orderID"
                                                :profit="profit"
                                                :is_connected="isConnected"
                                                :all_restaurant_tables="allRestaurantTables"
                                                :new_over_all_discount="newOverAllDiscount"
                                                :over_all_discount="overAllDiscount"
                                                :discount_amount="discount"
                                                :newDiscount_amount="newdiscount"
                                                :tax_amount="tax"
                                                :add_customer_short_key="addCustomerShortKey"
                                                :payment_short_key="paymentShortKey"
                                                :hold_card_item="holdCarditem"
                                                :cancel_card_item="cancelCarditem"
                                                @activeCartPaymentModal="activeCartPaymentModal"
                                                @newCustomerAddModalOpen="newCustomerAddModalOpen"
                                                @selectCustomerFromCart="selectCustomerFromCart"
                                                @removeSelectedCustomerFromCart="removeSelectedCustomerFromCart"
                                                @selectSearchBranchFromCart="selectSearchBranchFromCart"
                                                @removeSelectedBranchFromCart="removeSelectedBranchFromCart"
                                                @openTableModalFromCart="openTableModalFromCart"
                                                @setRestaurantOrderTypeFromCart="setRestaurantOrderTypeFromCart"
                                                @setCartItemsToCookieOrDBFromCart="setCartItemsToCookieOrDBFromCart"
                                                @openHoldOrderModalFromCart="openHoldOrderModalFromCart"
                                                @addOverAllDiscountFromCart="addOverAllDiscount"
                                                @allProductDiscountFromCart="allProductDiscount"
                                                @setTaxIncludedOrExcludedFromCart="setTaxIncludedOrExcludedFromCart"
                                                @orderHoldFromCart="orderHoldFromCart"
                                ></cart-component>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--End show in mobile screen-->

        <!-- Show Product Variant Modal Structure -->
        <div class="modal fade" id="show-product-variant-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered big-modal-dialog" role="document">
                <div class="modal-content pt-3 px-3" v-if="selectedProductWithVariants">
                    <div class="mb-3">
                        <a href="#"
                           class="close"
                           data-dismiss="modal"
                           aria-label="Close"
                           @click.prevent="">
                            <i class="la la-close text-grey"></i>
                        </a>
                        <h5 class="m-0 text-center">{{ selectedProductWithVariants.title }}</h5>
                    </div>
                    <div class="row  mx-0">
                        <div class="col-4 mb-4" v-for="(variant,index) in selectedProductWithVariants.variants">
                            <a href="#" class="app-color-text"
                               @click.prevent="addProductToCart(selectedProductWithVariants,variant.id)">
                                <div class="product-card bg-white border rounded">
                                    <div v-if="variant.imageURL" class="product-img-container image-property"
                                         :style="{ 'background-image': 'url(' + publicPath+'/uploads/products/' + variant.imageURL+ ')' }">
                                    </div>
                                    <div v-else class="product-img-container image-property"
                                         :style="{ 'background-image': 'url(' + publicPath+'/uploads/products/' + selectedProductWithVariants.productImage + ')'}">

                                    </div>
                                    <div class="product-variant-card-content position-relative p-2">
                                        <div class="mb-2 d-flex align-items-center product-card-font font-weight-bold text-center justify-content-center">
                                            {{ variant.variant_title }}
                                        </div>
                                        <h6 class="product-card-font"
                                            v-for="(variantAttribute,index) in variant.attribute_values">
                                            {{ selectedProductWithVariants.attributeName[index] }} : {{ variantAttribute
                                            }}
                                        </h6>
                                        <h6 class="product-card-font position-absolute rounded-right app-color text-white price-section">
                                            {{ numberFormat(variant.price) }}
                                        </h6>
                                        <h6 v-if="variant.availableQuantity <= 0 && order_type == 'sales'"
                                            class="product-card-font warning-message text-white sold-out">
                                            {{trans('lang.out_of_stock')}}
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal -->

        <!-- Add supplier Modal Structure -->
        <div class="modal fade" id="supplier-add-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <supplier-create-edit class="modal-content" v-if="isCustomerModalActive" :id="selectedItemId"
                                      :order_type="order_type"
                                      :modalID="'#supplier-add-edit-modal'"
                                      @newSupplier="newCustomer">
                </supplier-create-edit>
            </div>
        </div>
        <!-- End Modal -->

        <!-- Show Bank Transfer Details Modal -->
        <div class="modal fade modal-hide" id="bank-transfer-modal" tabindex="-1" role="dialog" aria-hidden="true"
             style="overflow-y: hidden;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <bank-transfer-details v-if="isActiveTrans" 
                                       :paid="paidAmount"
                                       @bankPayment="defaultPayment"/>
            </div>
        </div>
        <!-- End Bank Transfer Details Modal -->

        <!--Branch or cash register select modal-->
        <div class="modal fade" id="branch-or-cash-register-select-modal" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <div class="modal-content modal-layout-content" v-if="isBranchModalActive">
                    <pre-loader v-if="!hideBranchPreLoader" class="small-loader-container"/>
                    <div v-else>
                        <a href="#" class="position-absolute p-2 back-button"
                           @click.prevent="dashboard()">
                            <i class="la la-angle-left"/> {{ trans('lang.back_page') }}
                        </a>
                        <h6 class="mb-3 text-center">{{trans('lang.choose_branch')}}</h6>
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action"
                               :class="{'active':selectedBranchID==branch.id}" v-for="branch in branchList"
                               @click.prevent="selectBranch(branch.id, branch.name, branch.branch_type, branch.is_cash_register)">
                                {{ branch.name }}
                            </a>

                        </div>
                    </div>
                </div>
                <div class="modal-content modal-layout-content" v-if="isCashRegisterModalActive">
                    <pre-loader v-if="!hideCashRegisterPreLoader" class="small-loader-container"/>
                    <div v-else>
                        <a href="#" class="close" data-dismiss="modal"
                           aria-label="Close" @click.prevent="" v-if="checkCashRegisterOpen()">
                            <i class="la la-close text-grey"/>
                        </a>
                        <a href="#" class="position-absolute p-2 back-button"
                           @click.prevent="branchModalAction(1), isCashRegisterModalActive = false">
                            <i class="la la-angle-left"/> {{ trans('lang.back_page') }}
                        </a>
                        <h6 class="mb-3 text-center">
                            {{trans('lang.select_cash_register')}}
                        </h6>
                        <div class="accordion" id="accordionExample">
                            <div class="card" v-for="(cashRegister,index) in cashRegisterList">
                                <div class="d-flex justify-content-between">
                                    <a href="#" :id="'cash-register-'+index" data-toggle="collapse"
                                       :data-target="'#collapse-'+index" aria-expanded="true"
                                       :aria-controls="'collapse-'+index"
                                       class="card-header app-color-text p-2 d-flex justify-content-between align-items-center border-bottom-0"
                                       :class="{'card-header-with-enroll-btn':!checkCashRegisterOpenByUser(cashRegister)} && cashRegister.multiple_access==1"
                                       @click.prevent="cashRegisterCollapse(index,cashRegister.id,cashRegister,cashRegister.status)">
                                        <div class="d-flex  align-items-center">
                                            <i class="la la-chevron-circle-right la-2x cart-icon"
                                               :class="{'cart-icon-rotate':cashRegister.showItemCollapse}"/>
                                            <div>
                                                <div class="pl-2">{{ cashRegister.title }}</div>
                                                <div v-if="cashRegister.status=='open'"
                                                     class="pl-2 sales-cash-register">{{ cashRegister.register_status }}
                                                </div>
                                            </div>
                                        </div>
                                        <span v-if="cashRegister.status=='closed'"
                                              class="badge badge-danger badge-pill">{{trans('lang.closed')}}</span>
                                        <span v-else
                                              class="badge badge-success badge-pill">{{trans('lang.open')}}</span>
                                    </a>
                                    <a href="#"
                                       v-if="!checkCashRegisterOpenByUser(cashRegister) && cashRegister.multiple_access==1 && selectedCashRegisterID != cashRegister.id"
                                       class="p-2 text-white enroll-btn d-flex align-items-center font-weight-bold product-card-font"
                                       @click.prevent="setCashRegisterData(cashRegister,'enroll')">
                                        {{trans('lang.join')}}</a>
                                </div>
                                <div :id="'collapse-'+index" class="collapse border-top"
                                     :aria-labelledby="'cash-register-'+index" data-parent="#accordionExample">
                                    <div class="card-body card-body pb-3 pt-2 px-0">
                                        <form>
                                            <div class="row mx-0">
                                                <div class="mb-3 col-12" v-if="cashRegister.status=='open'">
                                                    <label :for="'note-'+index" class="label-in-cart">{{trans('lang.note')
                                                        }}{{cashRegister.note }}</label>
                                                    <!--<textarea :id="'note-'+index" name="note"-->
                                                    <!--class="form-control"-->
                                                    <!--v-model="cashRegister.note"></textarea>-->
                                                    <textarea :id="'note-'+index" :name="'note'"
                                                              v-validate="((openingAmount == closingAmount)  || note || noteValidation )? '':'required'"
                                                              class="form-control"
                                                              v-model="note"/>
                                                    <div class="heightError">
                                                        <small class="text-danger" v-show="errors.has('note')">
                                                            {{errors.first('note') }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="col-9" v-if="cashRegister.status=='closed'">
                                                    <label :for="'opening-amount-'+index">{{trans('lang.opening_amount_label')}}</label>

                                                    <payment-input :id="'opening-amount-'+index"
                                                                   v-model="cashRegister.openingAmount"></payment-input>

                                                </div>
                                                <div class="col-9" v-else>
                                                    <label>{{trans('lang.expected_closing_amount')}}:
                                                        {{numberFormat(expectedClosingAmount)}}</label>
                                                    <br>
                                                    <label :for="'closing-amount-'+index">{{trans('lang.closing_amount_label')}}</label>
                                                    <payment-input :id="'closing-amount-'+index"
                                                                   v-model="closingAmount"></payment-input>
                                                </div>
                                                <div class="col-3 mt-auto" v-if="cashRegister.status=='closed'">
                                                    <button class="btn app-color float-right"
                                                            :disabled="(cashRegister.openingAmount || cashRegister.openingAmount =='0')?false:true"
                                                            @click.prevent="setCashRegisterData(cashRegister,'open')">
                                                        {{trans('lang.open')}}
                                                    </button>
                                                </div>
                                                <div class="col-3 mt-auto" v-else>
                                                    <button class="btn btn-danger float-right"
                                                            :disabled="(disableCloseButton  && cashRegister.permision == 1)?false:true"
                                                            @click.prevent="setCashRegisterData(cashRegister,'close')">
                                                        {{trans('lang.close')}}
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--End modal-->

        <!-- Sales or returns  Type Select Modal Structure -->
        <div class="modal fade" id="sales-or-return-type-select-modal" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content px-4 pb-4 pt-3">
                    <pre-loader v-if="!hideSalesReturnsPreLoader" class="small-loader-container"/>
                    <div v-else>
                        <a href="#" class="close" data-dismiss="modal"
                           aria-label="Close" @click.prevent="">
                            <i class="la la-close text-grey"/>
                        </a>
                        <h6 class="mb-3 text-center">{{trans('lang.select_sales_or_returns_type')}}</h6>
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action"
                               :class="{'active':salesOrReturnType=='sales'}"
                               @click.prevent="selectSalesOrReturnType('sales')">{{trans('lang.sales')}}</a>
                            <a href="#" class="list-group-item list-group-item-action"
                               :class="{'active':salesOrReturnType=='returns'}"
                               @click.prevent="selectSalesOrReturnType('returns')">{{trans('lang.returns')}}</a>
                            <a href="#" class="list-group-item list-group-item-action"
                               :class="{'active':salesOrReturnType=='due'}"
                               @click.prevent="selectSalesOrReturnType('due')">{{trans('lang.due_payments')}}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- End Modal -->

        <!--Due payment component-->
        <div class="main-layout-card">
            <due-component v-if="salesOrReturnType=='due'" 
                           :branch_id="selectedBranchID"
                           @resetBranchAndCashRegisterModal="resetBranchAndCashRegisterModal"/>
        </div>
        <!--End deu payment component-->

        <!-- Sales or returns  Type Select Modal Structure -->
        <div class="modal fade" id="increase-local-storage-modal" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content px-4 pb-4 pt-3 text-center">
                    <div class="my-2">
                        <i class="la la-exclamation-circle la-5x text-info"/>
                    </div>
                    <h5>{{trans('lang.your_local_storage_is_full')}}</h5>
                    <h6>{{trans('lang.are_you_want_to_increase_your_local_storage')}}</h6>
                    <div class="my-4">
                        <button class="btn app-color" @click="increaseLocalStorageInChrome()">
                            {{trans('lang.yes')}}
                        </button>
                        <button class="btn btn-secondary cancel-btn mobile-btn"
                                @click="hideIncreaseLocalStorageModal()">
                            {{trans('lang.no')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal -->

        <!--Sales orReceiving Type Select Modal Structure-->
        <div class="modal fade" id="sales-or-receiving-type-select-modal" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content px-4 pb-4 pt-3">
                    <div>
                        <a href="#" class="close" data-dismiss="modal"
                           aria-label="Close" @click.prevent="">
                            <i class="la la-close text-grey"/>
                        </a>
                        <h6 class="mb-3 text-center">{{trans('lang.select_sales_or_receiving_type')}}</h6>
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action" v-if="order_type=='sales'"
                               :class="{'active':salesOrReceivingType=='customer'}"
                               @click.prevent="selectSalesOrReceivingType('customer')">{{trans('lang.customer')}}</a>
                            <a href="#" class="list-group-item list-group-item-action" v-else
                               :class="{'active':salesOrReceivingType=='supplier'}"
                               @click.prevent="selectSalesOrReceivingType('supplier')">{{trans('lang.supplier')}}</a>
                            <a href="#" class="list-group-item list-group-item-action"
                               :class="{'active':salesOrReceivingType == 'internal' && isActive}"
                               @click.prevent="selectSalesOrReceivingType('internal')">{{trans('lang.internal')}}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal -->

        <!-- Restaurant Table Selection Modal -->
        <div class="modal fade modal-hide" id="table-select-modal" tabindex="-1" role="dialog" aria-hidden="true"
             style="overflow-y: hidden;">
            <div class="modal-dialog modal-dialog-centered biggest-modal-dialog" role="document">
                <table-selection-modal v-if="isTableModalActive"
                                       :restaurant_tables_branch_wise="allRestaurantTables"
                                       :transfer_branch_name="selectedSearchBranch.name"
                                       :sales_or_receiving_type="salesOrReceivingType"
                                       :transfer_branch="selectedSearchBranch.id"
                                       :order_type="order_type"
                                       :final_cart="finalCart"
                                       :logo="invoice_logo"
                                       :sold_to="sold_to"
                                       :sold_by="sold_by"
                                       :user="user"
                                       :booked_tables="bookedTables"
                                       :customer_hold_orders="customerHoldOrders"
                                       :internal_hold_orders="internalHoldOrders"
                                       :order_hold_items="orderHoldItems"
                                       :invoice_template="invoiceTemplate"
                                       @getRestaurantTableId="getRestaurantTableId">
                </table-selection-modal>
            </div>
        </div>
        <!-- End Restaurant Table Selection Modal -->

        <!-- Show Cart Payment Modal Structure -->
        <div class="modal fade modal-hide" id="cart-payment-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered biggest-modal-dialog" role="document">
                <cart-payment-details v-if="isPaymentModalActive"
                                      :selectedCashRegisterID="selectedCashRegisterID"
                                      class="modal-content"
                                      :orderType="order_type"
                                      :salesOrReceivingType="salesOrReceivingType"
                                      :sold_to="sold_to"
                                      :sold_by="sold_by"
                                      :finalCart='finalCart'
                                      :user="user"
                                      :orderID="orderID"
                                      :invoiceId="invoiceId"
                                      :logo="invoice_logo"
                                      :last_invoice_number="lastInvoiceNumber"
                                      :invoice_prefix="invoicePrefix"
                                      :invoice_suffix="invoiceSuffix"
                                      :invoice_template="invoiceTemplate"
                                      :bankOrCardAmount="bankOrCardAmount"
                                      :bankOrCardOptions="bankOrCardOptions"
                                      :donePaymentShortcut="donePaymentItem"
                                      :calculateBank="calculateBank"
                                      :transferBranch="selectedSearchBranch.id"
                                      :transferBranchName="selectedSearchBranch.name"
                                      :autoInvoice="auto_invoice"
                                      :paymentTypes="payment_types"
                                      :selectedBranchID="selectedBranchID"
                                      :is_connected="isConnected"
                                      :is_cash_register_branch="isCashRegisterBranch"
                                      :is_cash_register_used="isCashRegisterUsed"
                                      :sales_default_invoice_template="salesDefaultTemplate"
                                      :receives_default_invoice_template="receiveDefaultTemplate"
                                      @setDestroyCart="destroyCart"
                                      @amount="bankOrCardTransfer"
                                      @getUpdatedInvoice="getUpdatedInvoice"
                                      @makeInvoiceIdNull="makeInvoiceIdNull"
                                      @makePlaceOrderTrue="makePlaceOrderTrue">
                </cart-payment-details>
            </div>
        </div>
        <!-- End Modal -->

        <!-- Add Customer Modal Structure -->
        <div class="modal fade" id="customer-add-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <customer-create-edit v-if="isCustomerModalActive"
                                      class="modal-content" :order_type="order_type"
                                      :modalID="'#customer-add-edit-modal'"
                                      :customerGroups="customer_group"
                                      status=true
                                      @newCustomer="newCustomer">
                </customer-create-edit>
            </div>
        </div>
        <!-- End Modal -->

        <!-- Hold Orders Modal Structure -->
        <div class="modal fade" id="hold-orders-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <pre-loader class="small-loader-container" v-if="!hideOrderHoldItemsPreLoader"/>
                    <div class="pt-2 px-4 pb-4" v-else>
                        <a href="#" class="close" data-dismiss="modal"
                           aria-label="Close" @click.prevent="">
                            <i class="la la-close text-grey"/>
                        </a>
                        <h5 class="mb-3 text-center">
                            {{trans('lang.hold_order_list')}}
                        </h5>

                        <div v-if="currentBranch !== null && salesOrReceivingType == 'internal'"
                             class="container-hold-orders">
                            <div class="row mr-0">
                                <div class="col-12 pr-0">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="la la-search"></i>
                                            </span>
                                        </div>
                                        <input type="text"
                                               :placeholder="trans('lang.search_invoice')"
                                               v-model="searchHoldOrder"
                                               class="form-control rounded-right">
                                    </div>
                                </div>
                                <div class="col-12 pr-0">
                                    <div>
                                        <div class="row mx-0 h-100 border hold-order-list-item"
                                             v-for="(internalHoldOrder, index) in filteredHoldOrder"
                                             v-if="internalHoldOrder.status == 'hold'">
                                            <div class="col-11 px-0">
                                                <a href="#"
                                                   class="d-block hold-items app-color-text"
                                                   @click.prevent="setHoldOrderToCart(internalHoldOrder)">
                                                    <div class="row">
                                                        <div class="col-5 text-left">
                                                            <span class="font-weight-bold pl-1">{{ internalHoldOrder.invoice_id }}</span>
                                                        </div>
                                                        <div class="col-7">
                                                            <span class="text-center">
                                                                {{ dateFormats(internalHoldOrder.date) }}
                                                                {{ timeFormateForDatetime(internalHoldOrder.time) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-1 px-0 my-auto">
                                                <a href="#"
                                                   data-toggle="modal"
                                                   data-target="#clear-cart-modal"
                                                   class="d-block pr-1"
                                                   @click.prevent="deleteHoldOrder(internalHoldOrder)">
                                                    <i class="la la-times-circle text-danger hold-delete-icon p-1 h-100"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <p class="text-center"
                                           v-if="order_type === 'sales' && filteredHoldOrder.length === 0">
                                            {{ trans('lang.no_result_found') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="currentBranch !== null && currentBranch.branch_type !== 'restaurant' && salesOrReceivingType == 'customer'"
                             class="container-hold-orders">
                            <div class="row mr-0">
                                <div class="col-12 pr-0">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="la la-search"></i>
                                            </span>
                                        </div>
                                        <input type="text"
                                               :placeholder="trans('lang.search_invoice')"
                                               v-model="searchHoldOrder"
                                               class="form-control rounded-right">
                                    </div>
                                </div>
                                <div class="col-12 pr-0">
                                    <div>
                                        <div class="row mx-0 h-100 border hold-order-list-item"
                                             v-for="(customerHoldOrder, index) in filteredHoldOrder"
                                             v-if="customerHoldOrder.status == 'hold'">
                                            <div class="col-11 px-0">
                                                <a href="#"
                                                   class="d-block hold-items app-color-text"
                                                   @click.prevent="setHoldOrderToCart(customerHoldOrder)">
                                                    <div class="row">
                                                        <div class="col-5 text-left">
                                                            <span class="font-weight-bold pl-1">{{ customerHoldOrder.invoice_id }}</span>
                                                        </div>
                                                        <div class="col-7">
                                                            <span class="text-center">
                                                                {{ dateFormats(customerHoldOrder.date) }}
                                                                {{ timeFormateForDatetime(customerHoldOrder.time) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-1 px-0 my-auto">
                                                <a href="#"
                                                   data-toggle="modal"
                                                   data-target="#clear-cart-modal"
                                                   class="d-block pr-1"
                                                   @click.prevent="deleteHoldOrder(customerHoldOrder)">
                                                    <i class="la la-times-circle text-danger hold-delete-icon p-1 h-100"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <p class="text-center"
                                           v-if="order_type === 'sales' && filteredHoldOrder.length === 0">
                                            {{ trans('lang.no_result_found') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hold Orders For Restaurant Module -->
                        <div v-else-if="currentBranch !== null && currentBranch.branch_type === 'restaurant' && salesOrReceivingType == 'customer'"
                             class="container-hold-orders">
                            <div class="row mr-0">
                                <div class="col-12 pr-0">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="la la-search"></i>
                                            </span>
                                        </div>
                                        <input type="text"
                                               :placeholder="trans('lang.search_invoice')"
                                               v-model="searchHoldOrder"
                                               class="form-control rounded-right">
                                    </div>
                                </div>
                                <div class="col-12 pr-0">
                                    <div>
                                        <div class="row mx-0 h-100 border hold-order-list-item"
                                             v-for="(customerHoldOrder, index) in filteredHoldOrder"
                                             v-if="customerHoldOrder.status == 'hold'">
                                            <div class="col-10 col-sm-11 col-md-11 col-lg-11 px-0">
                                                <a href="#"
                                                   class="d-block hold-items app-color-text"
                                                   @click.prevent="setHoldOrderToCart(customerHoldOrder)">
                                                    <div class="row">
                                                        <div class="col-7 col-sm-4 col-md-4 col-lg-4 text-left">
                                                            <span class="font-weight-bold invoice-id">{{ customerHoldOrder.invoice_id }}</span>
                                                        </div>
                                                        <div class="col-5 col-sm-3 col-md-3 col-lg-3">
                                                            <span class="font-weight-bold invoice-table">{{ customerHoldOrder.tableName }}</span>
                                                        </div>
                                                        <div class="col-12 col-sm-5 col-md-5 col-lg-5">
                                                            <span class="invoice-time">
                                                                {{ dateFormats(customerHoldOrder.date) }}
                                                                {{ timeFormateForDatetime(customerHoldOrder.time) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-2 col-sm-1 col-md-1 col-lg-1 px-0 my-auto">
                                                <a href="#"
                                                   data-toggle="modal"
                                                   data-target="#clear-cart-modal"
                                                   class="d-block pr-1"
                                                   @click.prevent="deleteHoldOrder(customerHoldOrder)">
                                                    <i class="la la-times-circle text-danger hold-delete-icon p-1 h-100"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <p class="text-center"
                                           v-if="order_type === 'sales' && filteredHoldOrder.length === 0">
                                            {{ trans('lang.no_result_found') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Hold Orders For Restaurant Module -->

                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal -->

        <!-- Confirmation Modal -->
        <confirmation-modal id="clear-cart-modal"
                            :message="'order_will_be_cancelled'"
                            :firstButtonName="'yes'"
                            :secondButtonName="'no'"
                            @confirmationModalButtonAction="confirmationModalButtonAction"/>
        <!-- End Modal -->

    </div>
</template>

<script>
    import {delayCall} from "../../helper/delayCall";
    import axiosGetPost from '../../helper/axiosGetPostCommon';
    import {
        subTotalAmount,
        cartItemsToCookie,
        deleteCartItemsFromCookieOrDB
    } from './helper/salesComponentCommonMethod.js';


    export default {
        props: [
            'user',
            'order_type',
            'sold_to',
            'sold_by',
            'addcustomer',
            'manage_price',
            'manage_sales',
            'manage_receives',
            'current_branch',
            'current_cash_register',
            'total_branch',
            'sales_return_status',
            'sales_receiving_type',
            'auto_invoice',
            'payment_types',
            'customer',
            'customer_group',
            'product',
            'shortcutKeyCollection',
            'app_name',
            'invoice_prefix',
            'invoice_suffix',
            'last_invoice',
            'is_branch_selected',
            'all_branch',
            'supplier',
            'hold_orders',
            'default_sales_invoice_template',
            'default_receive_invoice_template',
            'restaurant_tables',
            'booked_tables',
        ],
        extends: axiosGetPost,
        data: () => ({
            bookedTables: [],
            allRestaurantTables: [],

            //shortcuts
            productSearch: [],
            addCustomerShortKey: [],
            paymentShortKey: [],
            holdCarditem: [],
            cancelCarditem: [],
            donePaymentItem: [],

            offlineInvoiceNumber: '',
            internalSalesBranch: '',
            internalSalesBranchId: '',
            discountOnEntire: 'discountOnEntire',
            discountOnAllItem: 'discountOnAllItem',
            productTotalWithoutDiscount: 0,
            currentBranch: null,
            currentCashRegister: null,
            //products variables
            products: [],
            selectedProductWithVariants: null,
            productSearchValue: null,
            barcodeSearch: false,
            hideProductSearchPreLoader: false,
            //customers variables
            customers: [],
            customerNotAdded: true,
            selectedCustomer: [],
            newAddedCustomer: [],
            customerSearchValue: '',
            isCustomerModalActive: false,
            isNewCustomerAdded: false,
            hideCustomerSearchPreLoader: false,
            //cart variables
            cart: [],
            newCart: [],
            isPaymentModalActive: false,
            //final cart variables
            finalCart: [],
            total: 0,
            subTotal: 0,
            grandTotal: 0,
            discount: null,
            profit: 0,
            overAllDiscount: null,
            tax: 0,
            orderID: null,
            salesOrReceivingType: null,
            salesOrReturnType: null,
            //order hold variables
            orderHoldItems: [],
            hideOrderHoldItemsPreLoader: '',
            //branch variables
            branchList: [],
            selectedBranchID: '',
            selectedBranchName: '',
            selectedBranchType: '',
            isCashRegisterUsed: '',
            tempBranchID: null,
            hideBranchPreLoader: '',
            hideBranchSearchPreLoader: false,
            branchSearchValue: '',
            branches: [],
            selectedSearchBranch: [],
            offlineAllBranch: [],
            //cash register variables
            cashRegisterList: [],
            selectedCashRegisterID: '',
            selectedCashRegisterName: '',
            selectedCashRegisterBranchID: '',
            hideCashRegisterPreLoader: '',
            status: '',
            isBranchModalActive: false,
            isCashRegisterModalActive: false,
            invoiceTemplate: '',
            lastInvoiceNumber: '',
            invoiceSuffix: '',
            invoicePrefix: '',
            invoice_logo: '',
            activeProductId: '',
            activeVariantId: '',
            showDiscount: false,
            showOverAllDisc: false,
            hideCloseBtn: true,
            noteValidation: false,
            disableCloseButton: false,
            closingAmount: '',
            openingAmount: '',
            note: '',
            newCustomerId: '',
            paidAmount: '',
            bankOrCardAmount: '',
            bankOrCardOptions: {},
            orderSearchValue: '',
            orders: [],
            expectedClosingAmount: '',
            hideOrderSearchPreLoader: false,
            hideSalesReturnsPreLoader: true,
            isActiveTrans: false,
            calculateBank: false,
            newOverAllDiscount: null,
            newdiscount: null,
            isActive: false,
            // Active keyboard event
            open: null,
            highlightIndex: 0,
            intermediateSalesType: '',
            toggleCart: false,
            isSelectedBranch: true,
            paymentTypes: null,
            invoiceTemplateID: '',
            productDetails: [],
            productOfflineData: [],
            newCustomerInfo: null,
            offlineCustomers: [],
            hideOnlineMessage: false,
            totalStorage: 4500,
            remainingStorage: null,
            minimumSizeOfLocalStorage: 500,
            holdOrders: [],
            customerHoldOrders: [],
            internalHoldOrders: [],
            countHoldOrder: 0,
            invoiceId: null,
            isCartComponentActive: false,
            isCartComponentActiveForMobile: false,
            isCashRegisterBranch: '',
            salesDefaultTemplate: '',
            receiveDefaultTemplate: '',
            isEmptyObj: (object) => {
                if (_.isEmpty(object)) {
                    return true;
                } else {
                    return false;
                }
            },
            // Restaurant Variables
            isTableModalActive: false,
            searchHoldOrder: '',
            restaurantTableId: '',
            restaurantOrderType: 'dineIn',
            takeAway: false,
            dineIn: false,
            isHoldOrderDone: false,
            isPlaceOrderActive: true,
            justPayRestaurantTableId: '',
            isTaxExcludedFromCart: true,
            orderFromHold: null,
            // load more btn
            buttonLoader: false,
            isLoadMoreDisabled: false,
            showLoadMore: false,
            loadMoreBtnOffset: 0,
            productRowLimit: 28,
            totalProductCount: 0,
            registerInfoModal: false,
            registerInfoModalID: '#register-info-modal',
        }),
        computed: {
            filteredHoldOrder() {
                if (this.salesOrReceivingType == 'customer' && this.currentBranch !== null && this.currentBranch.branch_type === 'restaurant') {
                    // Returns result in restaurant customer
                    return this.customerHoldOrders.filter(customerHoldOrder => {
                        if (customerHoldOrder.tableId === null) return customerHoldOrder.invoice_id.toLowerCase().includes(this.searchHoldOrder.toLowerCase());
                        else return (customerHoldOrder.invoice_id.toLowerCase().includes(this.searchHoldOrder.toLowerCase()) || customerHoldOrder.tableName.toLowerCase().includes(this.searchHoldOrder.toLowerCase()));
                    });
                } else if (this.salesOrReceivingType == 'internal') {
                    // Returns result both retail and restaurant internal
                    return this.filteredHoldOrderSearch(this.internalHoldOrders);
                } else if (this.salesOrReceivingType == 'customer' && this.currentBranch !== null && this.currentBranch.branch_type !== 'restaurant') {
                    // Returns result in retail customer
                    return this.filteredHoldOrderSearch(this.customerHoldOrders);
                }
            },
        },
        watch: {
            discount: function (newVal, oldVal) {
                if (newVal != null && newVal != '') {
                    this.showDiscount = true;
                } else {
                    this.showDiscount = false;
                }
            },
            remainingStorage: function (newVal, oldVal) {
            },
            closingAmount: function (value) {
                if (value || value == '0') this.disableCloseButton = true;
            },
            overAllDiscount: function (value) {
                if (value != null && value !== '') {
                    this.showOverAllDisc = true;
                    this.showDiscount = true;
                } else {
                    this.showOverAllDisc = false;
                    this.showDiscount = false;
                }
            },
            isConnected: function (value) {
                let instance = this;
                if (!value && this.offline == 1) {
                    if (this.salesOrReceivingType == 'internal') {
                        this.internalHoldOrders = this.getDataByStatus(this.orderHoldItems, 'hold');
                        this.countHoldOrder = this.internalHoldOrders.length;
                    } else {
                        this.customerHoldOrders = this.getDataByStatus(this.orderHoldItems, 'hold');
                        this.countHoldOrder = this.customerHoldOrders.length;
                    }
                }
                if (this.offline == 1) {
                    if (value) {
                        this.onlineOfflineStatus(value);
                        this.showSuccessAlert(this.trans('lang.online_alert'));
                    } else this.showOfflineAlert(this.trans('lang.offline_alert'));
                }
            }
        },
        created() {
            this.setCartItemsToCookieOrDB();
            let tempBookedTables = [];
            this.booked_tables ? tempBookedTables = JSON.parse(this.booked_tables) : [];
            for (let item of tempBookedTables) {
                item = parseInt(item);
                this.bookedTables.push(item);
            }
            if (screen.width > 667) {
                this.toggleCart = false;
                if (this.sales_return_status != 'due') {
                    this.isCartComponentActive = true;
                } else this.isCartComponentActive = false;
            } else {
                this.isCartComponentActive = false;
                this.toggleCart = true;
            }
            this.getRestaurantTables = this.get_restaurant_table;
            this.salesDefaultTemplate = this.default_sales_invoice_template;
            this.receiveDefaultTemplate = this.default_receive_invoice_template;
            this.salesOrReceivingType = this.sales_receiving_type;
            this.lastInvoiceNumber = this.last_invoice;
            this.invoiceSuffix = this.invoice_suffix;
            this.invoicePrefix = this.invoice_prefix;
            this.isActive = "true";
            this.holdOrders = this.hold_orders;
            this.getShortCutValues();
            if (this.current_branch) {
                this.currentBranch = JSON.parse(this.current_branch);
            }
            if (!this.currentBranch) {
                this.getBranchData();
            } else {
                if (this.currentBranch.is_cash_register == 1) {
                    this.isCashRegisterBranch = true;
                    if (this.current_cash_register) {
                        this.currentCashRegister = JSON.parse(this.current_cash_register);

                        if (this.order_type == 'sales') {
                            this.invoiceTemplateID = this.currentCashRegister.sales_invoice_id;
                        } else {
                            this.invoiceTemplateID = this.currentCashRegister.receiving_invoice_id;
                        }
                    } else this.currentCashRegister = this.current_cash_register;

                    if (!this.currentCashRegister) {
                        this.getCashRegisterData();
                    } else {
                        this.getExpectedAmount(this.currentCashRegister.id);
                        this.selectedCashRegisterID = this.currentCashRegister.id;
                        this.selectedCashRegisterName = this.currentCashRegister.title;
                        this.selectedCashRegisterBranchID = this.currentCashRegister.branchID;

                        if (this.order_type == 'sales') {
                            this.invoiceTemplateID = this.currentCashRegister.sales_invoice_id;
                        } else {
                            this.invoiceTemplateID = this.currentCashRegister.receiving_invoice_id;
                        }
                        this.getInvoiceTemplate();
                    }
                } else {
                    if (this.order_type == 'sales') {
                        this.invoiceTemplate = this.salesDefaultTemplate;
                        this.isCashRegisterBranch = false;
                    } else {
                        this.invoiceTemplate = this.receiveDefaultTemplate;
                        this.isCashRegisterBranch = false;
                    }
                }

                this.selectedBranchID = this.currentBranch.id;
                this.getRestaurantTablesBranchWise(this.selectedBranchID);

                this.selectedBranchName = this.currentBranch.name;
                this.isCashRegisterUsed = this.currentBranch.is_cash_register;

                if (this.currentBranch.is_cash_register == 1) {
                    if (this.current_cash_register) {
                        this.currentCashRegister = JSON.parse(this.current_cash_register);
                    }
                    if (!this.currentCashRegister) {
                        this.getCashRegisterData();
                    } else {
                        this.getExpectedAmount(this.currentCashRegister.id);
                        this.selectedCashRegisterID = this.currentCashRegister.id;
                        this.selectedCashRegisterName = this.currentCashRegister.title;
                        this.selectedCashRegisterBranchID = this.currentCashRegister.branchID;
                    }
                }
            }
            if (this.order_type === 'sales') {
                this.getHoldOrders();
            }
            this.getInvoiceData('/invoice-logo');
            window.addEventListener('online', () => {
                this.storeLocalStorage(); // from axiosGetPostComon
                this.controlShowLoadMore();
                this.getProductData();
                this.getProductDataForOffline();
            });
        },
        mounted() {
            let instance = this;
            instance.offlineAllBranch = JSON.parse(this.all_branch);

            if (this.order_type == 'sales' || instance.customer != '' && instance.customer != null) this.offlineCustomers = JSON.parse(this.customer);

            if (instance.currentBranch != null) {
                instance.hideOrderSearchPreLoader = true;
                instance.getProductData();
                instance.getProductDataForOffline();
            }
            $(document).ready(function () {
                $("#pop_mouse1").click(function () {
                    $("input").focus();
                });
                $("#pop_mouse2").click(function () {
                    $("input").focus();
                });
            });
            if (this.order_type == 'sales') {
                this.salesOrReturnType = this.sales_return_status;
            }

            let stopPropagationElements = document.querySelectorAll("#d-1, #d-2");
            for (let stopPropagationElement of stopPropagationElements) {
                stopPropagationElement.addEventListener("click", function () {
                    event.stopPropagation();
                });
            }
            if (!this.currentBranch) {
                this.isBranchModalActive = true;
                this.openbranchORCashRegisterSelectModal();
            } else {
                if (JSON.parse(this.current_branch).is_cash_register == 1) {
                    if (!this.currentCashRegister) {
                        this.isCashRegisterModalActive = true;
                        this.openbranchORCashRegisterSelectModal();
                    } else {
                        if (this.currentCashRegister.branchID != this.currentBranch.id) {
                            this.isCashRegisterModalActive = true;
                            this.getCashRegisterData();
                            this.openbranchORCashRegisterSelectModal();
                        }
                    }
                } else {
                    //no cash reg block
                }
            }

            $('#branch-or-cash-register-select-modal').on('hidden.bs.modal', function (e) {
                instance.isBranchModalActive = false;
                instance.isCashRegisterModalActive = false;
            });

            $('#show-product-variant-modal').on('hidden.bs.modal', function (e) {
                instance.selectedProductWithVariants = null;
            });

            $('#cart-modal-for-mobile-view').on('hidden.bs.modal', function (e) {
                instance.isCartComponentActiveForMobile = false;
            });

            $('#cart-payment-modal').on('hidden.bs.modal', function (e) {
                instance.isPaymentModalActive = false;
            });

            $('#table-select-modal').on('hidden.bs.modal', function (e) {
                instance.isTableModalActive = false;
                instance.setRestaurantOrderTypeDineIn();
            });

            $('#clear-cart-modal').on('hidden.bs.modal', function (e) {
                // instance.isPaymentModalActive = false;
            });

            $('#hold-orders-modal').on('hidden.bs.modal', function (e) {
                instance.searchHoldOrder = '';
            });

            $('#register-info-modal').on('hidden.bs.modal', function (e) {
                instance.registerInfoModal = false;
            });

            this.$hub.$on('setOrderID', function (orderID, lastInvoiceId) {
                instance.lastInvoiceNumber = lastInvoiceId;
                instance.getExpectedAmount(instance.selectedCashRegisterID);
                instance.orderID = orderID;
                instance.setCartItemsToCookieOrDB(1);
            });

            if (this.salesOrReturnType != 'due') {
                $(window).resize(function () {
                    instance.productHeightSet();
                    instance.productVariantHeightSet();

                    if (window.innerWidth > 667) {
                        instance.toggleCart = false;
                        instance.isCartComponentActive = true;
                        instance.isCartComponentActiveForMobile = false;
                        instance.toggleCart = false;
                        $("#cart-modal-for-mobile-view").modal('hide');
                    } else {
                        instance.toggleCart = true;
                        instance.isCartComponentActive = false;
                        instance.isCartComponentActiveForMobile = true;
                        instance.toggleCart = true;
                    }

                });
            }

            $('#bank-transfer-modal').on('hidden.bs.modal', function (e) {
                instance.isActiveTrans = false;
            });

            $('#customer-add-edit-modal').on('hidden.bs.modal', function (e) {
                instance.isCustomerModalActive = false;
            });

            instance.focusOnSearchBar();
        },
        methods: {
            openRegisterInfoModal() {
                this.registerInfoModal = true;
                $("#register-info-modal").modal("show");
            },
            resetBranchAndCashRegisterModal() {
                this.isBranchModalActive = false;
                this.isCashRegisterModalActive = false;
            },
            focusOnSearchBar() {
                let instance = this;
                if (instance.manage_sales == 1 || instance.manage_receives == 1) {
                    setTimeout(() => {
                        if (instance.salesOrReturnType === 'returns') {
                            instance.$refs.searchOrder.focus();
                        } else if (instance.salesOrReturnType != 'due') {
                            this.isCartComponentActive = true;
                            instance.$refs.search.focus();
                        }
                    }, 1000);
                }
            },
            focusOnSearchBarWithOutTimeOut() {
                let instance = this;
                if (this.manage_sales == 1 || this.manage_receives == 1) {
                    if (this.salesOrReturnType === 'returns') {
                        this.$refs.searchOrder.focus();
                    } else if (instance.salesOrReturnType != 'due') {
                        this.$refs.search.focus();
                    }
                }
            },
            filteredHoldOrderSearch(data) {
                let instance = this;
                return data.filter(item => {
                    return item.invoice_id.toLowerCase().includes(instance.searchHoldOrder.toLowerCase());
                });
            },
            getDataByStatus(data, status) {
                let instance = this;
                return data.filter(function (element) {
                    return (element.salesOrReceivingType == instance.salesOrReceivingType && element.status == status && element.branchId == instance.selectedBranchID);
                });
            },
            deleteHoldOrder(data) {
                if (data) {
                    this.orderID = data.orderID;
                    this.invoiceId = data.invoice_id;
                    this.orderFromHold = data;
                }
            },
            allProductDiscount(value, index, unformatted) {
                let instance = this;
                instance.discount = value;
                instance.newdiscount = unformatted;
                this.cart.forEach(function (element) {
                    if (element.quantity > 0) {
                        element.discount = instance.discount;
                    }
                });
                instance.setCartItemsToCookieOrDB(1);
            },
            addOverAllDiscount(value, index, unformatted) {
                this.overAllDiscount = value;
                this.newOverAllDiscount = unformatted;
                let flag = true;
                let instance = this;
                if (this.overAllDiscount) {
                    instance.cart.forEach(function (element) {
                        if (element.orderType === 'discount') {
                            element.price = instance.overAllDiscount;
                            element.calculatedPrice = -(instance.overAllDiscount);
                            flag = false;
                        }
                    });
                    if (flag) {
                        instance.cart.push({
                            productID: null,
                            variantID: null,
                            taxID: null,
                            orderType: 'discount',
                            productTitle: instance.trans('lang.discount'),
                            price: this.overAllDiscount,
                            quantity: -1,
                            discount: null,
                            calculatedPrice: -(instance.overAllDiscount),
                            cartItemNote: '',
                            showItemCollapse: false,
                        });
                    }
                } else {
                    instance.cart = instance.cart.filter(element => element.orderType !== 'discount');
                }
                instance.setCartItemsToCookieOrDB(1);
            },
            productHeightSet() {
                $("div.product-card-content").removeAttr("style");
                this.$nextTick(function () {
                    var maxHeight = 0;

                    $("div.product-card-content").each(function () {
                        if ($(this).height() > maxHeight) {
                            maxHeight = $(this).height();
                        }
                    });
                    $("div.product-card-content").css('height', maxHeight + 'px');
                })
            },
            productVariantHeightSet() {
                $("div.product-variant-card-content .d-flex").removeAttr("style");
                var maxHeight2 = 0;

                $("div.product-variant-card-content .d-flex").each(function (e) {

                    if ($(this).height() > maxHeight2) {
                        maxHeight2 = $(this).height();
                    }
                });

                $("div.product-variant-card-content .d-flex").css('height', maxHeight2 + 'px');
            },
            openCartModalForMobile() {
                this.isCartComponentActive = false;
                this.isCartComponentActiveForMobile = true;
            },
            onlineOfflineStatus(value) {
                let instance = this;
                if (value) {
                    this.hideOnlineMessage = true;
                    setTimeout(function () {
                        instance.hideOnlineMessage = false;
                    }, 3000);
                }
            },
            increaseLocalStorage() {
                let instance = this;
                setTimeout(function () {
                    let storageStatus = instance.remainingStorage < instance.minimumSizeOfLocalStorage;
                    if (storageStatus) {
                        let browserName = instance.checkBrowser();
                        if (browserName === 'Chrome') {
                            $('#increase-local-storage-modal').modal('show');
                        } else {
                            return storageStatus;
                        }
                    } else {
                        return storageStatus;
                    }
                }, 1000);
            },

            increaseLocalStorageInChrome() {
                window.webkitStorageInfo.requestQuota(
                    window.PERSISTENT,
                    513010,
                    function (bytes) {
                    },
                    function (e) {
                    });
                this.hideIncreaseLocalStorageModal();

            },
            hideIncreaseLocalStorageModal() {
                $('#increase-local-storage-modal').modal('hide');
            },

            suggestionSelected(suggestion) {
                this.open = false;
                this.customerSearchValue = suggestion[0]
                this.branchSearchValue = suggestion[0]
                this.$emit('input', suggestion[1])
            },
            getExpectedAmount(id) {
                let instance = this;
                if (navigator.onLine && id != null && id != '') {
                    this.axiosGet('/get-register-amount/' + id,
                        function (response) {
                            instance.expectedClosingAmount = response.data;
                        },
                        function (error) {

                        }
                    );
                }
            },

            defaultPayment(amount, options) {
                this.calculateBank = true,
                    this.bankOrCardAmount = amount;
                this.bankOrCardOptions = options;
            },
            getShortCutValues() {
                let instance = this;
                instance.axiosGet('/shortcut-setting-data/{id}',
                    function (response) {
                        let shortcutCollection = response.data.shortcutSettings;

                        //product search
                        if (shortcutCollection.productSearch.shortcut_key.includes("+")) {
                            instance.productSearch = shortcutCollection.productSearch.shortcut_key.split("+");
                        } else {
                            instance.productSearch = [shortcutCollection.productSearch.shortcut_key];
                        }

                        //add customer
                        if (shortcutCollection.addCustomer.shortcut_key.includes("+")) {
                            instance.addCustomerShortKey = shortcutCollection.addCustomer.shortcut_key.split("+");
                        } else {
                            instance.addCustomerShortKey = [shortcutCollection.addCustomer.shortcut_key];
                        }

                        //pay
                        if (shortcutCollection.pay.shortcut_key.includes("+")) {
                            instance.paymentShortKey = shortcutCollection.pay.shortcut_key.split("+");
                        } else {
                            instance.paymentShortKey = [shortcutCollection.pay.shortcut_key];
                        }

                        //hold cart
                        if (shortcutCollection.holdCard.shortcut_key.includes("+")) {
                            instance.holdCarditem = shortcutCollection.holdCard.shortcut_key.split("+");
                        } else {
                            instance.holdCarditem = [shortcutCollection.holdCard.shortcut_key];
                        }

                        //cancelCard
                        if (shortcutCollection.cancelCarditem.shortcut_key.includes("+")) {
                            instance.cancelCarditem = shortcutCollection.cancelCarditem.shortcut_key.split("+");
                        } else {
                            instance.cancelCarditem = [shortcutCollection.cancelCarditem.shortcut_key];
                        }

                        //donePayment
                        if (shortcutCollection.donePayment1.shortcut_key.includes("+")) {
                            instance.donePaymentItem = shortcutCollection.donePayment1.shortcut_key.split("+");
                        } else {
                            instance.donePaymentItem = [shortcutCollection.donePayment1.shortcut_key];
                        }
                    },
                    function (error) {

                    },
                );
            },
            commonMethodForAccessingShortcut(data) {
                if (data == "productSearch" && this.shortcutKeyInfo.productSearchShortcut.status == 1 && this.shortcutStatus == 1) {
                    this.$refs.search.focus();
                }
            },
            addCustomer(event) {  // remove for cart
                $('#customer-add-edit-modal').modal('show');
                this.isCustomerModalActive = true;
            },
            holdCard(event) {
                this.orderHold();
            },
            cancelCard(event) {
                $('#clear-cart-modal').modal('show');
            },
            pay(event) {
                $('#cart-payment-modal').modal('show');
                this.isPaymentModalActive = true;
            },
            getInvoiceData(route) {
                let instance = this;
                this.setPreLoader(false);
                this.axiosGet(route,
                    function (response) {
                        instance.invoice_logo = response.data.logo.setting_value;
                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
                );
            },
            capitalizeFirstLetter(value) {
                return _.startCase(_.toLower(value));
            },
            selectSalesOrReceivingType(value) {
                let instance = this;
                this.isActive = "true";
                instance.salesOrReceivingType = value;
                $('#sales-or-receiving-type-select-modal').modal('hide');
                if (navigator.onLine) {
                    this.axiosPost('/sales-receiving-type-set', {
                        salesOrReceivingType: value,
                        orderType: instance.order_type,
                    }, function (response) {
                        instance.focusOnSearchBarWithOutTimeOut();
                    }, function (error) {

                    });
                }
                if (this.order_type == 'sales') {
                    this.getHoldOrders();
                }
                this.adjustPrice();
                instance.focusOnSearchBarWithOutTimeOut();
            },
            adjustPrice() {
                let instance = this;
                if (instance.order_type == 'sales') {
                    instance.products.forEach(function (product) {
                        product.variants.forEach(function (variant) {
                            if (instance.salesOrReceivingType == 'customer') variant.price = variant.selling_price;
                            else variant.price = variant.purchase_price;
                        });
                    })
                }
            },
            selectSalesOrReturnType(value) {
                let instance = this;

                if (value == 'due') {
                    instance.isCartComponentActive = false;
                } else instance.isCartComponentActive = true;

                instance.hideSalesReturnsPreLoader = false;
                instance.axiosGETorPOST(
                    {
                        url: '/sales-returns-type-set',
                        postData: {salesOrReturnType: value},
                    },
                    (success, responseData) => {

                        if (success) //response after then function
                        {
                            instance.hideSalesReturnsPreLoader = true;
                            instance.salesOrReturnType = value;
                            $('#sales-or-return-type-select-modal').modal('hide');
                            instance.focusOnSearchBar();
                        } else {
                            instance.hideSalesReturnsPreLoader = true;
                            $('#sales-or-return-type-select-modal').modal('hide')
                        }
                    }
                );
            },
            openbranchORCashRegisterSelectModal() {
                $('#branch-or-cash-register-select-modal').modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: true
                });
            },
            searchProductInput(event) {
                let instance = this;

                if (navigator.onLine) {
                    if (instance.productSearchValue) {
                        if (event.keyCode == 13) {
                            instance.barcodeSearch = true;
                            // instance.getProductData();
                            instance.getProductDataOfflineSearch();
                            instance.productSearchValue = null;
                        } else {
                            delayCall(function () {
                                if (instance.productSearchValue) {
                                    instance.getProductDataOfflineSearch();
                                }
                            });
                        }
                    } else {
                        this.loadMoreBtnOffset = 0;
                        instance.getProductData();
                        instance.getProductDataForOffline();
                    }
                } else {
                    if (instance.productSearchValue) {

                        if (event.keyCode == 13) {
                            instance.barcodeSearch = true;
                            instance.getProductDataOfflineSearch();
                            instance.productSearchValue = null;
                        } else {
                            delayCall(function () {
                                if (instance.productSearchValue) {
                                    instance.getProductDataOfflineSearch();
                                }
                            });
                        }
                    } else {
                        instance.getProductDataOfflineSearch();
                    }
                }
            },
            cancelOrder() {
                let instance = this;
                if (navigator.onLine) {
                    if (this.orderID) this.onlineCancelOrder();
                } else {
                    if (this.orderID || this.invoiceId) this.offlineCancelOrder();
                }
                if (instance.order_type === 'sales' && instance.filteredHoldOrder != undefined && instance.filteredHoldOrder.length === 0) {
                    $('#hold-orders-modal').modal('hide');
                }
            },
            onlineCancelOrder() {
                this.hideOrderHoldItemsPreLoader = false;
                this.axiosGETorPOST(
                    {
                        url: '/sales-cancel', //set url
                        postData: {orderID: this.orderID} //set post data
                    },
                    (success, responseData) => { // callback after axios method call
                        if (success)
                        {
                            if (this.order_type == 'sales') {
                                this.getHoldOrders(true);
                                this.hideOrderHoldItemsPreLoader = true;
                                this.orderID = null;
                                this.invoiceId = null;
                                this.restaurantTableId = '';
                            }
                        }
                    }
                );
            },
            offlineCancelOrder() {
                let instance = this,
                    itemStatus = true;
                if (this.orderFromHold != undefined && this.orderFromHold != null) {
                    this.orderFromHold.status = 'cancelled';
                }
                this.getAndSetDataToLocalStoreage(this.orderFromHold, this.orderID, this.invoiceId);
                instance.hideOrderHoldItemsPreLoader = true;
                if (this.order_type == 'sales') {
                    this.getHoldOrders(true);
                }
                this.orderID = null;
                this.invoiceId = null;
                this.restaurantTableId = '';
                this.destroyCart(true);
            },
            searchOrderInput(event) {
                let instance = this;
                instance.hideOrderSearchPreLoader = false;
                if (instance.orderSearchValue) {
                    delayCall(function () {
                        instance.orders = [];
                        instance.getOrderData();
                    });
                } else {
                    instance.hideOrderSearchPreLoader = true;
                }
            },
            getOrderData() {
                let instance = this;
                instance.axiosGETorPOST(
                    {
                        url: '/get-return-orders', //set url
                        postData: {
                            orderId: instance.orderSearchValue,
                        }
                    },
                    (success, responseData) => {
                        if (success) {
                            instance.orders = responseData;
                            instance.hideOrderSearchPreLoader = true;
                        }
                    });
            },
            variantProductCard(productVariantInfo) {
                if (productVariantInfo.length > 1) {
                    return '#show-product-variant-modal';
                }
            },
            productCardAction(product) {
                if (product.variants.length == 1) {
                    this.addProductToCart(product, product.variants[0].id);
                } else {
                    this.selectedProductWithVariants = product;
                    let instance = this;
                    setTimeout(function () {
                        instance.productVariantHeightSet();
                    }, 200)
                }
            },

            addProductToCart(product, productVariantID) {
                let flag = 0,
                    instance = this;
                instance.activeProductId = product.productID;
                instance.activeVariantId = productVariantID;

                setTimeout(function () {
                    instance.activeProductId = '';
                    instance.activeVariantId = '';
                }, 1000);
                if (this.cart.length > 0) {
                    this.cart.forEach(function (cartItem, index, cartArray) {
                        if (cartItem.productID == product.productID && cartItem.variantID == productVariantID) {
                            cartArray[index].quantity++;
                            if (cartArray[index].quantity > product.variants[0].availableQuantity && instance.order_type == 'sales') {
                                let variantTitle = product.variants[0].variant_title === 'default_variant' ? '' : `(${product.variants[0].variant_title})`;
                                let alertMessage = product.title + ' ' + variantTitle + ' ' + instance.trans('lang.is_out_of_stock');
                                instance.showWarningAlert(alertMessage);
                            }
                            flag = 1;
                        }
                    });
                }
                if (flag == 0) {
                    let insertCheckedVariant = _.filter(product.variants, ['id', productVariantID]);

                    if (!_.isEmpty(insertCheckedVariant)) {

                        if (insertCheckedVariant[0].availableQuantity <= 0 && instance.order_type == 'sales') {
                            let variantTitle = insertCheckedVariant[0].variant_title === 'default_variant' ? '' : `(${insertCheckedVariant[0].variant_title})`;
                            let alertMessage = product.title + ' ' + variantTitle + ' ' + instance.trans('lang.is_out_of_stock');
                            this.showWarningAlert(alertMessage);
                        }

                        this.cart.push({
                            productID: product.productID,
                            productTitle: product.title,
                            taxID: product.tax_id,
                            orderType: instance.order_type,
                            productTaxPercentage: product.taxPercentage,
                            variantID: insertCheckedVariant[0].id,
                            price: insertCheckedVariant[0].price,
                            unformPrice: insertCheckedVariant[0].price,
                            purchase_price: insertCheckedVariant[0].purchase_price,
                            variantTitle: insertCheckedVariant[0].variant_title,
                            quantity: 1,
                            discount: this.discount,
                            calculatedPrice: insertCheckedVariant[0].price,
                            cartItemNote: '',
                            showItemCollapse: false,
                            availbleQuantity: insertCheckedVariant[0].availableQuantity
                        });
                    }
                }
                this.setCartItemsToCookieOrDB(1);
                this.newCart = this.cart;
                $('#show-product-variant-modal').modal('hide');
            },
            setPropductNewPrice(price, index, value) {
                this.cart[index].price = price;
                this.cart[index].unformPrice = value;
                this.setCartItemsToCookieOrDB(1);
            },
            cartItemButtonAction(cartProductID, cartVariantID, orderType, action) {
                let instance = this;
                this.cart.forEach(function (cartItem, index, cartArray) {
                    if (cartItem.productID == cartProductID && cartItem.variantID == cartVariantID) {
                        if (action == '+') {
                            cartArray[index].quantity++;
                            if (cartItem.quantity == 0) {
                                cartArray.splice(index, 1);
                            }
                            if (instance.order_type === 'sales' && instance.salesOrReturnType === 'returns') {
                                cartItem.calculatedPrice = cartItem.quantity * cartItem.price;
                            }
                        } else if (action == '-') {
                            --cartArray[index].quantity;

                            if (cartItem.quantity == 0) {
                                cartArray.splice(index, 1);
                            }
                        } else {
                            if (orderType === 'discount') {
                                instance.overAllDiscount = null;
                                instance.newOverAllDiscount = null;
                            }
                            cartArray.splice(index, 1);
                        }
                    }

                    if (instance.cart.length == 0) {
                        instance.discount = null;
                        instance.newdiscount = null;
                    }
                });
                this.setCartItemsToCookieOrDB(1);
                this.newCart = this.cart;
                $('#show-product-variant-modal').modal('hide');
            },

            setCartItemsToCookieOrDB(flag = 0) {
                let obj = {
                        user: this.user,
                        cart: this.cart,
                        selectedCustomer: this.selectedCustomer,
                        selectedSearchBranch: this.selectedSearchBranch,
                        orderID: this.orderID,
                        discount: this.discount,
                        overAllDiscount: this.overAllDiscount,
                        lastInvoiceNumber: this.lastInvoiceNumber,
                        salesOrReceivingType: this.salesOrReceivingType,
                        isSelectedBranch: this.isSelectedBranch,
                        newOverAllDiscount: this.newOverAllDiscount,
                        customerNotAdded: this.customerNotAdded,
                        order_type: this.order_type,
                        newDiscount: this.newdiscount,
                    },
                    // cookieData
                    cookieData = cartItemsToCookie(flag, obj);
                this.setCookieDataToGlobal(cookieData);
                //subTotalAmount
                let subTotalAmountMethodData = subTotalAmount(this.total, this.profit, this.tax, this.subTotal, this.cart, this.productTotalWithoutDiscount, this.isTaxExcludedFromCart);
                this.setSubTotalDataToGlobal(subTotalAmountMethodData);
            },
            setCookieDataToGlobal(cookieData) {
                this.customerNotAdded = cookieData.customerNotAdded;
                this.discount = cookieData.discount;
                this.isSelectedBranch = cookieData.isSelectedBranch;
                this.newdiscount = cookieData.newDiscount;
                this.newOverAllDiscount = cookieData.newOverAllDiscount;
                this.orderID = cookieData.orderID;
                this.overAllDiscount = cookieData.overAllDiscount;
                this.cart = cookieData.cart;
                this.selectedCustomer = cookieData.selectedCustomer;
                this.selectedSearchBranch = cookieData.selectedSearchBranch;
            },
            setSubTotalDataToGlobal(subTotalAmountMethodData) {
                this.total = subTotalAmountMethodData.total;
                this.grandTotal = subTotalAmountMethodData.grandTotal;
                this.profit = subTotalAmountMethodData.profit;
                this.tax = subTotalAmountMethodData.tax;
                this.subTotal = subTotalAmountMethodData.subTotal;
                this.productTotalWithoutDiscount = subTotalAmountMethodData.cart;
            },
            focusOnCashRegister(index, status) {
                setTimeout(() => {
                    if (status == 'open') {
                        $("#closing-amount-" + index).focus();
                    } else if (status == 'closed') {
                        $("#opening-amount-" + index).focus();
                    }
                }, 1000);
            },
            cashRegisterCollapse(index, cashRegisterID, cashRegister, status) {
                this.focusOnSearchBar();
                this.focusOnCashRegister(index, status);
                this.$validator.reset();
                this.note = '';
                if (cashRegister.opening_amount) {
                    this.openingAmount = cashRegister.opening_amount;
                }
                if (status == 'closed') {
                    this.noteValidation = true;
                } else {
                    this.noteValidation = false;
                }
                this.cashRegisterList.forEach(function (cashRegister, i, array) {
                    if (i == index && cashRegister.id == cashRegisterID) {
                        array[i].showItemCollapse = !array[i].showItemCollapse;
                    } else {
                        array[i].showItemCollapse = false;
                    }
                });
            },
            getProductsBySearchBtn() {
                if (navigator.onLine) this.getProductData();
                else this.getProductDataOfflineSearch();
            },
            getProductDataOfflineSearch() {
                let instance = this;
                if (this.productSearchValue == '' || this.productSearchValue == null) {
                    instance.products = instance.productOfflineData.slice(0, 20);
                } else {
                    let sortedProducts = instance.productOfflineData;
                    let productBarcodedSearch = this.productBarcodedSearch();
                    if (!productBarcodedSearch) {
                        instance.products = sortedProducts.filter(function (element) {
                            return element.title.toLowerCase().includes(instance.productSearchValue.toLowerCase());
                        });
                    }
                    if (this.productSearchValue == null) {
                        instance.products = instance.productDetails.slice(0, 20);
                    }
                }
                instance.hideProductSearchPreLoader = true;

                instance.$nextTick(function () {
                    instance.productHeightSet();
                });

                instance.adjustPrice();
            },
            getProductData() { //getProductDataForOnline
                let instance = this;
                instance.hideProductSearchPreLoader = false;
                let data = {
                        rowLimit: instance.productRowLimit,
                        offset: instance.loadMoreBtnOffset,
                        currentBranch: this.currentBranch.id,
                        orderType: this.order_type,
                        searchValue: this.productSearchValue,
                    },
                    postUrl = '/sales-product';
                instance.axiosGETorPOST(
                    {
                        url: postUrl,
                        postData: data,
                    },
                    (success, responseData) => {
                        // let shortcutsKey = responseData.shortcutKeyCollection.allKeyboardShortcut;

                        if (success) //response after then function
                        {
                            // instance.totalProductCount = responseData.count;
                            instance.hideProductSearchPreLoader = true;

                            if (responseData.barcodeResultValue) {
                                instance.barcodeSearch = true;
                                //instance.barcodeSearchedProductAddToCart(responseData.barcodeResultValue);
                                this.productSearchValue = '';
                            } else {
                                if (instance.loadMoreBtnOffset <= 0) {
                                    this.products = responseData.products;
                                    this.productDetails = responseData.products;
                                } else {
                                    this.products = this.products.concat(responseData.products);
                                    this.productDetails = this.productDetails.concat(responseData.products);
                                }
                            }

                            instance.$nextTick(function () {
                                instance.productHeightSet();
                            });
                            instance.adjustPrice();
                            instance.buttonLoader = false;
                            instance.isLoadMoreDisabled = false;
                            instance.controlShowLoadMore();
                        }

                    }
                );
            },
            controlShowLoadMore() {
                let instance = this;
                if (instance.totalProductCount > instance.products.length) instance.showLoadMore = true;
                else instance.showLoadMore = false;
            },
            getProductDataForOffline() {
                let instance = this;
                let data = {
                        currentBranch: this.currentBranch.id,
                        orderType: this.order_type,
                        searchValue: '',
                        rowLimit: null,
                        offset: 0,
                    },
                    postUrl = '/sales-product';

                instance.axiosGETorPOST(
                    {
                        url: postUrl,
                        postData: data,
                    },
                    (success, responseData) => {
                        if (success) //response after then function
                        {
                            instance.productOfflineData = responseData.products;
                            instance.totalProductCount = responseData.count;
                            instance.adjustPrice();
                            instance.controlShowLoadMore();
                        }
                    }
                );
            },
            productBarcodedSearch() {
                let instance = this;
                let sortedProducts = instance.productOfflineData, productVariant;

                sortedProducts.forEach(function (element) {

                    let filteredVariantBarcode = null;


                    filteredVariantBarcode = element.variants.find(function (variant) {
                        return variant.bar_code == instance.productSearchValue;
                    });

                    if (filteredVariantBarcode == undefined) {
                        filteredVariantBarcode = element.variants.find(function (variant) {
                            return variant.sku == instance.productSearchValue;
                        });
                        if (filteredVariantBarcode != undefined) productVariant = filteredVariantBarcode;

                        // if (filteredVariantBarcode == undefined) {
                        //     filteredVariantBarcode = element.variants.find(function (variant) {
                        //         return variant.id == instance.productSearchValue;
                        //     });
                        //     if (filteredVariantBarcode != undefined) productVariant = filteredVariantBarcode;
                        // } else productVariant = filteredVariantBarcode;
                    } else productVariant = filteredVariantBarcode;
                });

                if (productVariant != undefined) {
                    instance.barcodeSearch = true;
                    let searchProduct = sortedProducts.find(function (product) {
                        return product.productID == productVariant.product_id;
                    });
                    let cart = {
                        productID: productVariant.product_id,
                        productTitle: searchProduct.title,
                        taxID: searchProduct.tax_id,
                        productTaxPercentage: searchProduct.taxPercentage,
                        variantID: productVariant.id,
                        price: productVariant.price,
                        purchase_price: productVariant.purchase_price,
                        variantTitle: productVariant.variant_title,
                    }
                    instance.barcodeSearchedProductAddToCart(cart);
                    instance.productSearchValue = null;
                    return true;
                }
                return false;
            },
            barcodeSearchedProductAddToCart(data) {
                let flag = 0;
                if (this.barcodeSearch) {
                    if (this.cart.length > 0) {
                        this.cart.forEach(function (cartItem, index, cartArray) {
                            if (cartItem.productID == data.productID && cartItem.variantID == data.variantID) {
                                cartArray[index].quantity++;
                                flag = 1;
                            }
                        });
                    }
                    if (flag == 0) {
                        this.cart.push({
                            productID: data.productID,
                            productTitle: data.productTitle,
                            taxID: data.taxID,
                            orderType: this.order_type,
                            productTaxPercentage: data.productTaxPercentage,
                            variantID: data.variantID,
                            price: data.price,
                            unformPrice: data.price,
                            purchase_price: data.purchase_price,
                            variantTitle: data.variantTitle,
                            quantity: 1,
                            discount: this.discount,
                            calculatedPrice: data.price,
                            cartItemNote: '',
                            showItemCollapse: false,
                        });
                    }
                    this.setCartItemsToCookieOrDB(1);
                    this.barcodeSearch = false;
                }
            },
            getBranchData() {
                let instance = this;
                instance.axiosGETorPOST(
                    {url: '/branches'}, //set url
                    (success, responseData) => { // callback after axios method call
                        if (success) //response after then function
                        {
                            instance.branchList = responseData;
                            if (instance.currentBranch) //check if branch is selected by user previously
                            {
                                instance.branchList.forEach(function (branch) {
                                    if (instance.currentBranch.id == branch.id) //checks if branch id matches
                                    {
                                        instance.selectedBranchID = branch.id; //set branch id
                                        instance.selectedBranchName = branch.name; //set branch name
                                        instance.selectedBranchType = branch.branch_type; //set branch name
                                        instance.isCashRegisterUsed = branch.is_cash_register; //cash register set or not selectedCashRegisterID
                                        if (instance.isCashRegisterUsed == 0) instance.selectedCashRegisterID = '';

                                        if (instance.order_type === 'sales') {
                                            instance.getRestaurantTablesBranchWise(instance.selectedBranchID);
                                        }
                                        instance.focusOnSearchBar();
                                    }
                                })
                            }
                        }
                        instance.hideBranchPreLoader = true;
                    });
            },
            selectBranch(branchID, branchName, branchType, cashRegisterUsed) {
                if (this.selectedBranchID == branchID) {
                    $('#branch-or-cash-register-select-modal').modal('hide');
                } else {
                    this.selectedBranchID = branchID;
                    this.selectedBranchName = branchName;
                    this.selectedBranchType = branchType;
                    this.isCashRegisterUsed = cashRegisterUsed;
                    if (this.isCashRegisterUsed == 0) this.selectedCashRegisterID = '';
                    if (!this.currentBranch || this.currentBranch.id != this.selectedBranchID) {
                        this.setBranchData();
                        if (!this.currentBranch) {
                            this.currentBranch = [];
                        }
                    }
                }
            },
            setBranchData() {
                let instance = this;
                this.hideBranchPreLoader = false;
                this.tempBranchID = this.selectedBranchID;
                if (navigator.onLine) {
                    instance.axiosGETorPOST(
                        {
                            url: '/sales-branch-set',
                            postData: {branchID: this.selectedBranchID, orderType: this.order_type},
                        },
                        (success, responseData) => {
                            if (success) //response after then function
                            {
                                instance.hideBranchPreLoader = true;
                                instance.hideOrderSearchPreLoader = true;
                                instance.hideProductSearchPreLoader = true;
                                instance.isBranchModalActive = false;
                                instance.isCashRegisterModalActive = true;
                                instance.hideCashRegisterPreLoader = false;
                                this.currentBranch.id = this.selectedBranchID;
                                this.currentBranch.name = this.selectedBranchName;
                                this.currentBranch.branch_type = this.selectedBranchType;
                                this.checkCashRegister(this.currentBranch.id);
                                this.getProductData();
                                this.getProductDataForOffline();
                                if (this.order_type === 'sales') {
                                    this.getHoldOrders();
                                }
                                //restaurant branch get only branch wise tables
                                if (this.order_type === 'sales') {
                                    this.getRestaurantTablesBranchWise(this.currentBranch.id);
                                }
                            }
                        }
                    );
                } else {
                    if (this.order_type === 'sales') {
                        this.getHoldOrders();
                    }
                }
            },
            checkCashRegister(branch_id) {
                let instance = this;
                this.axiosGet('/edit-branch/' + branch_id,
                    function (response) {
                        if (response.data.is_cash_register == 1) {
                            instance.getCashRegisterData();
                            instance.isCashRegisterBranch = true;
                        } else {
                            instance.isCashRegisterModalActive = false;
                            instance.hideCashRegisterPreLoader = true;
                            instance.isCashRegisterBranch = false;
                            if (instance.order_type == 'sales') {
                                instance.invoiceTemplate = instance.salesDefaultTemplate;
                            } else {
                                instance.invoiceTemplate = instance.receiveDefaultTemplate;
                            }
                            instance.invoiceTemplate = instance.salesDefaultTemplate;
                            $('#branch-or-cash-register-select-modal').modal('hide');
                        }
                    },
                );
            },
            getInvoiceTemplate() {
                let instance = this;
                let invoiceId = this.invoiceTemplateID;
                instance.invoiceTemplate = '';
                this.axiosGet('/get-invoice-template/' + invoiceId,
                    function (response) {
                        instance.invoiceTemplate = response.data;
                    }
                );
            },
            setHoldOrderToCart(holdItem) {
                let instance = this;
                this.orderFromHold = holdItem;
                // restaurant
                this.isPlaceOrderActive = false;
                instance.restaurantTableId = holdItem.tableId;
                if (holdItem.all_discount !== 0) instance.discount = holdItem.all_discount;
                holdItem.cart.forEach(function (product) {
                    if (product.orderType === 'discount') {
                        product.productTitle = instance.trans('lang.discount');
                        instance.overAllDiscount = product.calculatedPrice;
                        instance.newOverAllDiscount = product.calculatedPrice;
                        product.quantity = -1;
                        product.calculatedPrice = -(product.calculatedPrice);
                    }
                });
                if (navigator.onLine) {
                    if (holdItem.transfer_branch_id == null) {
                        instance.selectedSearchBranch = [];
                        instance.isSelectedBranch = true;
                    } else {
                        instance.selectedSearchBranch = {
                            name: holdItem.transfer_branch_name,
                            id: holdItem.transfer_branch_id
                        }
                        instance.isSelectedBranch = false;
                    }
                } else {
                    if (holdItem.transfer_branch_id != null) {
                        instance.selectedSearchBranch = {
                            name: holdItem.transfer_branch_name,
                            id: holdItem.transfer_branch_id
                        }
                        instance.isSelectedBranch = false;
                    } else if (holdItem.transferBranch == null) {
                        instance.selectedSearchBranch = [];
                        instance.isSelectedBranch = true;
                    } else {
                        instance.selectedSearchBranch = {
                            name: holdItem.transferBranchName,
                            id: holdItem.transferBranch
                        }
                        instance.isSelectedBranch = false;
                    }
                }
                if (instance.cart.length == 0) {
                    instance.cart = holdItem.cart;
                    instance.orderID = holdItem.orderID;
                    instance.invoiceId = holdItem.invoice_id;
                    if (holdItem.customer != null) {
                        if (instance.salesOrReceivingType == 'customer' && !_.isEmpty(holdItem.customer)) {
                            instance.selectedCustomer = [];
                            instance.selectedCustomer.push(holdItem.customer);
                            instance.customerNotAdded = false;
                        }
                    }
                    instance.cart.forEach(function (element) {
                        element.unformPrice = element.price;
                    });
                    if (instance.salesOrReceivingType == 'internal') {
                        instance.internalHoldOrders = instance.setHoldToPending(instance.internalHoldOrders, holdItem.orderID, holdItem.invoice_id, 'internal');
                    } else {
                        instance.customerHoldOrders = instance.setHoldToPending(instance.customerHoldOrders, holdItem.orderID, holdItem.invoice_id, 'customer');
                    }
                    instance.setCartItemsToCookieOrDB(1);
                    if (!this.isCartComponentActive) {
                        this.isCartComponentActiveForMobile = true;
                        $('#cart-modal-for-mobile-view').modal('show');
                    }
                    $('#hold-orders-modal').modal('hide');
                } else {
                    $('#clear-cart-modal').modal();
                }
                if (holdItem.tableId) {
                    this.justPayRestaurantTableId = holdItem.tableId;
                    // instance.cancelOrder();
                    this.getHoldOrders();
                }
            },
            setHoldToPending(data, orderID, invoice_id, salesOrReceivingType) {
                let itemStatus = true;
                let instance = this;
                data.forEach(function (orderHoldItem, index, array) {
                    if (orderHoldItem.orderID && orderHoldItem.orderID == orderID && orderHoldItem.status == 'hold') {
                        orderHoldItem.status = 'pending';
                        itemStatus = false;
                    } else if (orderHoldItem.orderID == null && orderHoldItem.invoice_id == invoice_id && orderHoldItem.status == 'hold' && itemStatus) {
                        orderHoldItem.status = 'pending';
                    }
                });
                let temp = this.getDataByStatus(data, 'hold');
                instance.countHoldOrder = temp.length;
                return data;
            },
            selectOrder(order) {
                let instance = this;
                instance.orderSearchValue = '';
                if (order.all_discount !== 0) instance.discount = order.all_discount;
                order.cart.forEach(function (product) {
                    if (product.orderType === 'discount') {
                        product.productTitle = instance.trans('lang.discount');
                        instance.overAllDiscount = -(product.calculatedPrice);
                        instance.newOverAllDiscount = -(product.calculatedPrice);
                        product.calculatedPrice = -(product.calculatedPrice);
                    }
                });
                if (instance.cart.length == 0) {
                    instance.cart = order.cart;
                    if (order.customer) {
                        instance.selectedCustomer.push(order.customer);
                        instance.customerNotAdded = false;
                    }
                    instance.setCartItemsToCookieOrDB(1);
                }
            },
            branchModalAction(flag = 0) {
                this.isBranchModalActive = true;
                if (flag === 0) {
                    this.openbranchORCashRegisterSelectModal();
                }
                if (this.branchList.length == 0) {
                    this.getBranchData();
                }
            },
            cashRegisterModalAction() {
                this.isCashRegisterModalActive = true;
                this.openbranchORCashRegisterSelectModal();
                if (this.cashRegisterList.length == 0) {
                    this.getCashRegisterData();
                }
            },
            getCashRegisterData() {
                let instance = this,
                    tempData;
                instance.axiosGETorPOST(
                    {url: '/cash-registers'}, //set url
                    (success, responseData) => { // callback after axios method call
                        if (success) //response after then function
                        {
                            tempData = responseData;
                            _.mapValues(tempData, function (cashRegister) {
                                cashRegister.openingAmount = null;
                                cashRegister.openingTime = null;
                                cashRegister.closingAmount = null;
                                cashRegister.closingTime = null;
                                cashRegister.note = null;
                                cashRegister.showItemCollapse = false;
                            });
                            instance.cashRegisterList = tempData;
                        }
                        if (instance.tempBranchID) {
                            let autoSelectCashRegister = _.filter(this.cashRegisterList, function (cashRegister) {
                                if (cashRegister.status == 'open' && _.includes(cashRegister.userID, instance.user.id.toString())) {

                                    return cashRegister;
                                }
                            });
                            if (!_.isEmpty(autoSelectCashRegister)) {
                                instance.selectedCashRegisterID = autoSelectCashRegister[0].id;
                                instance.selectedCashRegisterName = autoSelectCashRegister[0].title;
                                instance.selectedCashRegisterBranchID = autoSelectCashRegister[0].branchID;
                                $('#branch-or-cash-register-select-modal').modal('hide');
                            }
                            instance.tempBranchID = null;
                        }
                        instance.getExpectedAmount(instance.selectedCashRegisterID);
                        instance.hideCashRegisterPreLoader = true;
                        instance.focusOnSearchBarWithOutTimeOut();
                    })
                ;
            },
            checkCashRegisterOpenByUser(cashRegister) {
                let instance = this;
                if (cashRegister.status == 'open') {
                    if (instance.user.is_admin == 1) {
                        return false;
                    } else {
                        let test = _.includes(cashRegister.userID, instance.user.id.toString());
                        let check = test ? true : false;
                        return check;
                    }
                } else {
                    return true;
                }
            },
            checkCashRegisterOpen() {
                let instance = this, status;
                instance.cashRegisterList.forEach(function (cashRegister, index) {
                    if (cashRegister.status === 'open' && cashRegister.open_user_id === instance.user.id) {
                        status = true
                    }
                });
                if (status) {
                    return true;
                } else {
                    return false;
                }
            },
            setCashRegisterData: function (cashRegister, status) {
                if (this.order_type == 'sales') {
                    this.invoiceTemplateID = cashRegister.sales_invoice_id;
                } else {
                    this.invoiceTemplateID = cashRegister.receiving_invoice_id;
                }
                this.$validator.validateAll().then((result) => {
                    if (result || status === 'enroll'
                    ) {
                        this.disableCloseButton = false;
                        let cashRegisterData = cashRegister,
                            instance = this,
                            flag = 0;
                        if (status == 'open') {
                            if (this.checkCashRegisterOpen()) {
                                flag = 1;
                                this.showSuccessAlert(this.trans('lang.please_close_the_current_cash_register_to_continue'));
                            } else {
                                cashRegisterData.status = 'open';
                                cashRegisterData.openingTime = moment().format('YYYY-MM-DD H:mm');
                                $('#branch-or-cash-register-select-modal').modal('hide');
                            }
                        } else if (status == 'close') {
                            cashRegisterData.closingAmount = this.closingAmount;
                            cashRegisterData.note = this.note;
                            cashRegisterData.status = 'closed';
                            cashRegisterData.closingTime = moment().format('YYYY-MM-DD H:mm');
                            this.hideCashRegisterPreLoader = false;
                        } else {
                            $('#branch-or-cash-register-select-modal').modal('hide');
                        }
                        if (flag === 0) {
                            this.selectedCashRegisterID = cashRegisterData.id;
                            this.selectedCashRegisterName = cashRegisterData.title;
                            this.selectedCashRegisterBranchID = cashRegisterData.branchID;
                            let instance = this;
                            instance.axiosGETorPOST(
                                {
                                    url: '/cash-register-open-close',
                                    postData: cashRegisterData,
                                },
                                (success, responseData) => {
                                    if (success && (status == 'close' || status == 'open')
                                    ) {
                                        instance.getCashRegisterData();
                                        if (status == 'close') {
                                            instance.selectedCashRegisterID = null;
                                            instance.selectedCashRegisterName = null;
                                        }
                                        instance.focusOnSearchBarWithOutTimeOut();
                                    }
                                }
                            );
                            if (status == 'open') {
                                instance.getInvoiceTemplate();
                            }
                        }
                    }
                });
            },
            dashboard() {
                let instance = this;
                instance.redirect('/dashboard');
            },
            //emit from customer
            newCustomer(customer) {
                this.newCustomerId = customer.id;
                this.offlineCustomers.push(customer);
                this.newAddedCustomer = customer;
            },
            selectCustomerFromCart(selectedCustomer) {
                this.customerNotAdded = false;
                this.selectedCustomer = selectedCustomer;
                this.customerSearchValue = '';
            },
            removeSelectedCustomerFromCart(selectedCustomer) {
                this.customerNotAdded = true;
                this.selectedCustomer = selectedCustomer;
                this.customerSearchValue = '';
            },
            selectSearchBranchFromCart(branch) {
                this.selectedSearchBranch = branch;
                this.isSelectedBranch = false;
                this.setCartItemsToCookieOrDB(1);
            },
            removeSelectedBranchFromCart() {
                this.selectedSearchBranch = [];
                this.isSelectedBranch = true;
            },
            // emit from cart-component
            setTaxIncludedOrExcludedFromCart(value) {
                this.isTaxExcludedFromCart = value;
                this.setCartItemsToCookieOrDB();
            },
            setCartItemsToCookieOrDBFromCart(flag) {
                this.setCartItemsToCookieOrDB(flag);
            },
            bankOrCardTransfer(amount, modalId) {
                this.isActiveTrans = true;
                this.calculateBank = false,
                    $(modalId).modal('show');
                this.paidAmount = amount;
            },
            getUpdatedInvoice(updatedInvoiceForOffline) {
                this.lastInvoiceNumber = updatedInvoiceForOffline;
            },
            makeInvoiceIdNull(check) {
                if (check) {
                    this.invoiceId = null;
                    this.destroyCart(check);
                }
            },
            makePlaceOrderTrue(tableId) {
                let instance = this;
                this.restaurantOrderType = 'dineIn';
                this.isPlaceOrderActive = true;
                this.justPayRestaurantTableId = tableId;
                if (this.order_type === 'sales') {
                    this.getHoldOrders();
                }
                this.restaurantTableId = '';
                if(navigator.onLine){
                    this.getProductData();
                    this.getProductDataForOffline();
                }
            },
            orderHoldFromCart() {
                this.isHoldOrderDone = true;
                this.orderHold();
            },
            removeBookedTableIfEmpty() {
                let instance = this;
                let tempHoldOrders = [];
                if (instance.salesOrReceivingType == 'internal') tempHoldOrders = this.internalHoldOrders;
                else tempHoldOrders = this.customerHoldOrders;
                let temp = tempHoldOrders.filter(function (el) {
                    return el.tableId == instance.justPayRestaurantTableId;
                });
                if (temp.length == 0) {
                    this.bookedTables.forEach(function (el, index, array) {
                        if (el == instance.justPayRestaurantTableId) {
                            array.splice(index, 1);
                        }
                    });
                }
                ;
                this.justPayRestaurantTableId = null;
            },
            activeCartPaymentModal(value) {
                this.finalCart = value;
                this.isPaymentModalActive = true;
                if (this.isCartComponentActiveForMobile) {
                    $('#cart-modal-for-mobile-view').modal('hide');
                }
            },
            newCustomerAddModalOpen() {
                this.isCustomerModalActive = true;
            },
            bookedTableFromAvailable(selectedTableID) {
                let temp = this.bookedTables.find(function (el) {
                    return el == selectedTableID;
                });
                if (temp === undefined) this.bookedTables.push(selectedTableID);
            },
            confirmationModalButtonAction() {
                this.getRestaurantIdByOrderId();
                this.cancelOrder();
                this.orderID = null;
                this.invoiceId = null;
                this.destroyCart(true);
                this.overAllDiscount = null;
                this.newOverAllDiscount = null;
                this.selectedSearchBranch = [];
                this.isSelectedBranch = true;
                $('#clear-cart-modal').modal('hide');
            },
            getRestaurantIdByOrderId() {
                let instance = this;
                if (this.restaurantTableId) {
                    this.justPayRestaurantTableId = this.restaurantTableId;
                } else {
                    let obj = this.orderHoldItems.find(function (el) {
                        return el.orderID == instance.orderID;
                    });
                    this.justPayRestaurantTableId = obj != undefined ? obj.tableId : '';
                }
            },
            getHoldOrders(newOrderHold = false) {
                let instance = this;
                this.hideOrderHoldItemsPreLoader = false;
                if (navigator.onLine) {
                    instance.axiosGETorPOST(
                        {url: '/get-hold-orders'}, //set url
                        (success, responseData) => { // callback after axios method call
                            if (success) //response after then function
                            {
                                responseData.forEach(function (orderHoldItem, index, array) {
                                    if (orderHoldItem.orderID == instance.orderID) {
                                        array.splice(index, 1); //removing data from orderHoldItems if orderID matches which is set previously
                                    }
                                });
                                this.orderHoldItems = responseData;
                                if (instance.order_type == 'sales') {
                                    this.getHoldOrdersByBranchType();
                                } else {
                                    instance.orderHoldItems = responseData;
                                }
                                instance.hideOrderHoldItemsPreLoader = true;
                                if (this.currentBranch !== null) {
                                    if (instance.filteredHoldOrder.length === 0) {
                                        $('#hold-orders-modal').modal('hide');
                                    }
                                }
                            }
                        }
                    );
                } else {
                    this.getHoldOrdersByBranchType();
                }
                instance.hideOrderHoldItemsPreLoader = true;
                if (this.order_type === 'sales' && this.currentBranch !== null) {
                    if (instance.filteredHoldOrder.length === 0) {
                        $('#hold-orders-modal').modal('hide');
                    }
                }
            },
            getHoldOrdersByBranchType() {
                let instance = this;
                this.internalHoldOrders = this.getHoldOrdersBySalesOrReceivingType(this.orderHoldItems, 'internal');
                this.customerHoldOrders = this.getHoldOrdersBySalesOrReceivingType(this.orderHoldItems, 'customer');

                if (instance.salesOrReceivingType == 'internal') instance.countHoldOrder = instance.internalHoldOrders.length;
                else instance.countHoldOrder = instance.customerHoldOrders.length;
                this.removeBookedTableIfEmpty();
            },
            getHoldOrdersBySalesOrReceivingType(orderHoldItems, type) {
                let instance = this;
                let data = orderHoldItems.filter(function (element) {
                    element.isCashRegisterBranch = instance.isCashRegisterBranch;
                    element.cashRagisterId = instance.selectedCashRegisterID;
                    return (element.salesOrReceivingType == type && element.status == 'hold' && element.branchId == instance.selectedBranchID);
                });
                return data;
            },
            destroyCart(check) {
                if (check) {
                    deleteCartItemsFromCookieOrDB(this.user, this.order_type);
                    this.cart = [];
                    this.discount = null;
                    this.tax = 0;
                    this.grandTotal = 0;
                    this.total = 0;
                    this.subTotal = 0;
                    this.date = null;
                    this.selectedCustomer = [];
                    this.customerNotAdded = true;
                    this.customerSearchValue = '';
                    this.branchSearchValue = '';
                    this.overAllDiscount = null;
                    this.newOverAllDiscount = null;
                    this.newdiscount = null;
                    this.selectedSearchBranch = [];
                    this.isSelectedBranch = true;
                }
            },
            // Restaurant Module Methods
            setRestaurantOrderTypeDineIn() {
                this.restaurantOrderType = 'dineIn';
            },
            getRestaurantTableId(selectedTableID) {
                this.setRestaurantOrderTypeDineIn();
                this.restaurantTableId = selectedTableID;
                this.finalCart.tableId = this.restaurantTableId;
                this.orderHold();
                this.bookedTableFromAvailable(selectedTableID);
            },
            searchRestaurantOrders() {
                if (this.searchOrderTable) {
                    this.filteredRestaurantOrder = this.customerHoldOrders.filter((customerHoldOrder) => {
                        return customerHoldOrder.tableId == this.searchOrderTable;
                    })
                } else {
                    this.filteredRestaurantOrder = this.customerHoldOrders;
                }
            },
            getRestaurantTablesBranchWise(branchId) {
                let restaurantTable = this.restaurant_tables ? JSON.parse(this.restaurant_tables) : [];

                this.allRestaurantTables = restaurantTable.filter(function (element) {
                    return (element.branch_id == branchId);
                });
            },
            //emit restaurant
            openTableModalFromCart(finalCart) {
                this.finalCart = finalCart;
                this.isTableModalActive = true;
                $('#table-select-modal').modal('show');
                this.isCartComponentActiveForMobile = false;
                $('#cart-modal-for-mobile-view').modal('hide');
            },
            setRestaurantOrderTypeFromCart(type) {
                this.restaurantOrderType = type;
                this.makeIsPlaceOrderActive(this.restaurantOrderType);
            },
            makeIsPlaceOrderActive(type) {
                if (type == 'dineIn') this.isPlaceOrderActive = true;
                else if (type == 'takeAway') this.isPlaceOrderActive = false;
            },
            openHoldOrderModalFromCart() {
                $('#hold-orders-modal').modal('show');
                if (!this.isCartComponentActive) $('#cart-modal-for-mobile-view').modal('hide');
            },
            makeFinalCart(status) {
                let selectCustomerForCart = [];
                if (this.selectedCustomer[0]) selectCustomerForCart = this.selectedCustomer[0];
                this.finalCart = {
                    orderID: this.orderID,
                    orderType: this.order_type,
                    salesOrReceivingType: this.salesOrReceivingType,
                    createdBy: this.user.id,
                    status: status,
                    cart: this.cart,
                    customer: selectCustomerForCart,
                    subTotal: this.subTotal,
                    discount: this.discount,
                    overAllDiscount: this.overAllDiscount,
                    tax: this.tax,
                    profit: this.profit,
                    grandTotal: this.grandTotal,
                    cartNote: '',
                    branchId: this.selectedBranchID,
                    transferBranch: this.selectedSearchBranch.id,
                    transferBranchName: this.selectedSearchBranch.name,
                    tableId: this.restaurantTableId,
                    date: moment().format('YYYY-MM-DD h:mm A'),
                    time: moment().format('YYYY-MM-DD h:mm A'),
                };
            },
            makeFinalCartForOffLine() {
                let instance = this;
                let selectedTable = this.allRestaurantTables.find(function (el) {
                    return el.id == instance.restaurantTableId;
                });
                this.finalCart.isCashRegisterBranch = this.isCashRegisterBranch;
                this.finalCart.cashRagisterId = this.selectedCashRegisterID;
                this.finalCart.exchangeValue = 0;
                this.finalCart.payments = [];
                this.finalCart.current_invoice_number = this.finalCart.highest_invoice_number;
                this.finalCart.selectedBranchID = this.selectedBranchID;
                this.finalCart.time = moment().format('YYYY-MM-DD h:mm A');
                this.finalCart.status = 'hold';
                this.finalCart.tableName = selectedTable != undefined ? selectedTable.name : '';
            },
            makeInvoiceIdForFinalCart() {
                if (this.orderID) {
                    this.finalCart.invoice_id = this.invoiceId;
                } else {
                    if (this.invoiceId == null) {
                        this.finalCart.highest_invoice_number = this.lastInvoiceNumber;
                        if (this.isCashRegisterUsed == 0) {
                            this.finalCart.invoice_id = this.invoicePrefix + this.finalCart.highest_invoice_number + '-' + '0' + '-' + this.user.id + this.invoiceSuffix;
                        } else {
                            this.finalCart.invoice_id = this.invoicePrefix + this.finalCart.highest_invoice_number + '-' + this.selectedCashRegisterID + '-' + this.user.id + this.invoiceSuffix;
                        }
                        this.lastInvoiceNumber = parseInt(this.lastInvoiceNumber) + 1;
                    } else {
                        this.finalCart.highest_invoice_number = this.lastInvoiceNumber;
                        this.finalCart.invoice_id = this.invoiceId;
                    }
                }
            },
            orderHold() {
                this.cartSave('hold');
            },
            cartSave(status = 'done') {
                let instance = this;
                if (status == 'done') {
                    this.isPaymentModalActive = true;
                }
                this.makeFinalCart(status);
                if (status == 'hold') {
                    //hold and offline
                    this.isHoldOrderDone = true;
                    if (!navigator.onLine) {
                        this.makeInvoiceIdForFinalCart();
                        this.makeFinalCartForOffLine();
                        if (this.finalCart.orderID != null) {
                            this.orderHoldItems.forEach(function (orderHoldItem, index, array) {
                                if (orderHoldItem.orderID == instance.finalCart.orderID) {
                                    orderHoldItem.status = 'hold';
                                }
                            });
                        } else {
                            this.orderHoldItems.push(this.finalCart);
                        }
                        this.getHoldOrders(true);
                        this.getAndSetDataToLocalStoreage(this.finalCart, this.finalCart.orderID, this.finalCart.invoice_id);
                        if (this.finalCart) this.bookedTableFromAvailable(this.finalCart.tableId);   // table status
                        this.resetAfterCartSave();
                        this.transitionEffectOnHoldOrderIcon();
                    } else {
                        let instance = this;
                        instance.axiosGETorPOST(
                            {
                                url: '/store', //set url
                                postData: this.finalCart //set post data
                            },
                            (success, responseData) => { // callback after axios method call
                                if (success) //response after then function
                                {
                                    if (this.finalCart) this.bookedTableFromAvailable(this.finalCart.tableId);
                                    instance.invoiceId = null;
                                    this.resetAfterCartSave();
                                    this.transitionEffectOnHoldOrderIcon();
                                }
                            }
                        );
                    }
                }
            },
            resetAfterCartSave() {
                this.orderID = null;
                this.isSelectedBranch = true;
                this.destroyCart(true);
                this.selectedSearchBranch = [];
                if (this.order_type == 'sales') {
                    this.getHoldOrders(true);
                }
                this.isHoldOrderDone = false;
                this.isPlaceOrderActive = true;
                this.customerNotAdded = true;
                this.restaurantTableId = '';
                this.makeIsPlaceOrderActive(this.restaurantOrderType);
            },
            transitionEffectOnHoldOrderIcon() {
                $('.hold-icon').addClass('order-hold-transition');
                setTimeout(function () {
                    $('.hold-icon').removeClass('order-hold-transition');
                }, 1000);
            },
            getAndSetDataToLocalStoreage(newData, orderID, invoiceId) {
                newData ? newData = newData : newData = [];
                let localStorageData = localStorage.getItem('salesProduct'),
                    orderDetails = localStorageData ? JSON.parse(localStorageData) : [],
                    instance = this;
                if (orderDetails.length > 0) {
                    orderDetails.forEach(function (orderHoldItem, index, array) {
                        if (orderHoldItem == null) {
                            array.splice(index, 1);
                        } else if (orderHoldItem.orderID == orderID && orderHoldItem.orderID) {
                            array.splice(index, 1);
                        } else if (orderHoldItem.invoice_id == invoiceId && orderHoldItem.orderID == null) {
                            array.splice(index, 1);
                        }
                    });
                    orderDetails.push(newData);
                } else {
                    orderDetails = this.internalHoldOrders.concat(this.customerHoldOrders);
                    if (orderDetails.length === 0) orderDetails.push(newData);
                }
                localStorage.setItem('salesProduct', JSON.stringify(orderDetails));
            },
            // load more 
            loadMoreSubmit() {
                this.buttonLoader = true;
                this.isLoadMoreDisabled = true;
                this.loadMoreBtnOffset += parseInt(this.productRowLimit);
                this.getProductData();
            }
        }
    }
</script>