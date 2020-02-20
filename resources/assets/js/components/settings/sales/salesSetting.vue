<template>
    <div class="main-layout-card">
        <div class="main-layout-card-header">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.sales_settings') }}</h5>
                </div>
            </div>
        </div>
        <div class="main-layout-card-content">
            <pre-loader v-if="!hidePreLoader"></pre-loader>
            <form v-else>
                <div class="row">

                    <div class="form-group col-md-6">
                        <label>{{ trans('lang.offline_mode') }}</label>
                        <div class="form-check">
                            <input
                                    class="form-check-input"
                                    type="radio"
                                    id="offline_mode_enable"
                                    value="1"
                                    v-model="offlineMode"
                            />
                            <label
                                    for="offline_mode_enable"
                                    class="radio-button-label"
                            >{{ trans('lang.enable') }}</label>
                            <input
                                    class="form-check-input"
                                    type="radio"
                                    id="offline_mode_dissable"
                                    value="0"
                                    v-model="offlineMode"
                            />
                            <label
                                    for="offline_mode_enable"
                                    class="radio-button-label"
                            >{{ trans('lang.disable') }}</label>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label>{{ trans('lang.out_of_stock_products_mode') }}</label>
                        <div class="form-check">
                            <input
                                    class="form-check-input"
                                    type="radio"
                                    id="out_of_stock_products_enable"
                                    value="1"
                                    v-model="outOfStock"
                            />
                            <label
                                    for="out_of_stock_products_enable"
                                    class="radio-button-label"
                            >{{ trans('lang.yes') }}</label>
                            <input
                                    class="form-check-input"
                                    type="radio"
                                    id="out_of_stock_products_dissable"
                                    value="0"
                                    v-model="outOfStock"
                            />
                            <label
                                    for="out_of_stock_products_dissable"
                                    class="radio-button-label"
                            >{{ trans('lang.no') }}</label>
                        </div>
                    </div>
                </div>
                <button
                        v-if="permission_key == 'manage'"
                        type="submit"
                        class="btn btn-primary app-color mobile-btn"
                        @click.prevent="saveSalesSetting()"
                >{{ trans('lang.save') }}
                </button>
            </form>
        </div>
    </div>
</template>

<script>
    import axiosGetPost from "../../../helper/axiosGetPostCommon";

    export default {
        extends: axiosGetPost,
        props: ["permission_key"],
        data() {
            return {
                hidePreLoader: true,
                offlineMode: "",
                outOfStock: ""
            };
        },
        created() {
            this.getSalesSetting();

        },
        mounted() {

        },
        methods: {
            getSalesSetting() {
                let instance = this;
                instance.hidePreLoader = false;
                instance.axiosGet(
                    "/sales-setting",
                    function (response) {
                        instance.offlineMode = response.data.offlineMode;
                        instance.outOfStock = response.data.outOfStock;
                        instance.hidePreLoader = true;
                    },
                    function (error) {
                        instance.hidePreLoader = true;
                    }
                );
            },
            saveSalesSetting() {
                this.$validator.validateAll().then(result => {
                    if (result) {
                        let instance = this;
                        instance.hidePreLoader = false;
                        this.axiosPost(
                            "/sales-setting-save",
                            {
                                offlineMode: this.offlineMode,
                                outOfStock: this.outOfStock
                            },
                            function (response) {
                                instance.hidePreLoader = true
                            },
                            function (error) {
                                instance.hidePreLoader = true;
                                instance.showErrorAlert(error.data.message);
                            }
                        );
                    }
                });
            }
        }
    };
</script>