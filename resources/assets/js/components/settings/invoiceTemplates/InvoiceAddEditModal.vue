<template>
    <div>
        <div class="modal-layout-header">
            <div class="row">
                <div class="col-10">
                    <h4 class="m-0" v-if="id">{{ trans('lang.edit_invoice_template') }}</h4>
                    <h4 class="m-0" v-else>{{ trans('lang.add_invoice_template') }}</h4>
                </div>
                <div class="col-2 text-right">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click.prevent="">
                        <i class="la la-close icon-modal-cross"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-layout-content">

            <pre-loader v-if="!hidePreLoader"></pre-loader>

            <div class="container-fluid p-0" v-show="hidePreLoader">
                <div class="row">
                    <div class="col-12">
                        <div class="form-row">
                            <div class="form-group col-12">
                                <label for="title">{{ trans('lang.title') }}</label>
                                <input v-validate="'required'" name="title" type="text" class="form-control" id="title"
                                       v-model="title">
                                <div class="heightError">
                                    <small class="text-danger" v-show="errors.has('title')">{{ errors.first('title')
                                        }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="templateType">{{ trans('lang.type') }}</label>
                                <select v-validate="'required'" data-vv-as="type" name="template_type"
                                        v-model="template_type" id="templateType" class="custom-select">
                                    <option value="" disabled selected>{{ trans('lang.choose_one') }}</option>
                                    <option value="sales">{{ trans('lang.sales') }}</option>
                                    <option value="receiving">{{ trans('lang.receiving') }}</option>
                                </select>
                                <div class="heightError">
                                    <small class="text-danger" v-show="errors.has('template_type')">{{
                                        errors.first('template_type') }}</small>
                                </div>
                            </div>
                            <div class="form-group offset-1 col-5">
                                <label for="templateType">{{ trans('lang.is_default') }}</label>
                                <div class=" d-flex align-items-center">
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="is_default_template" class="custom-control-input" id="default-template-yes" value="1" v-model="isDefaultTemplate">
                                        <label class="custom-control-label" for="default-template-yes">{{ trans('lang.yes') }}</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline" v-if="isDefaultTemplate !== 1">
                                        <input type="radio" name="is_default_template" class="custom-control-input" id="default-template-no" checked="checked" value="0" v-model="isDefaultTemplate">
                                        <label class="custom-control-label" for="default-template-no">{{ trans('lang.no') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12">
                                <div class="form-group">
                                    <textarea class="form-control" rows="3" v-model="content"
                                              id="custom-content"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12">
                                <div class="chip" v-for="reset in invoiceKey">
                                    {{reset}}
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-7 mb-0">
                                <button class="btn btn-primary app-color mobile-btn" type="submit"
                                        :disabled="is_disabled" @click.prevent="is_disable(), save()">
                                    {{ trans('lang.save') }}
                                </button>
                                <button class="btn btn-secondary cancel-btn mobile-btn" data-dismiss="modal"
                                        @click.prevent="">
                                    {{ trans('lang.cancel') }}
                                </button>
                            </div>
                            <div class="form-group col-5 mb-0">
                                <div class="text-right">
                                    <button class="btn btn-danger waves-effect waves-light mobile-btn ml-auto"
                                            v-if="isReStoreShow" @click.prevent="restoreToDefault()">
                                        {{trans('lang.restore_default')}}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {

        props: ['id', 'modalID'],
        extends: axiosGetPost,
        data() {
            return {
                title: '',
                template_type: '',
                isDefaultTemplate: 0,
                content: '',
                isCustom: '',
                invoiceKey: ["{app_logo}", "{app_name}", "{table_name}", "{invoice_id}", "{supplier_name}", "{employee_name}", "{date}", "{time}", "{item_details}", "{sub_total}", "{total}", "{payment_details}", "{exchange}", "{due}", "{customer_name}", "{phone_number}", "{address}"],
                is_disabled: false,
                restoreButtonTriggered: false,
                isReStoreShow: false,
            }
        },

        created() {
            if (this.id) {
                this.getInvoiceTemplateData('/get-invoice-edit-data/' + this.id);
            }
        },
        mounted() {

            let instance = this;

            $("#custom-content").summernote(
                {
                    callbacks: {
                        onChange: function () {
                            var code = $(this).summernote("code");
                            instance.content = code;
                        }
                    }
                }
            );
        },
        methods: {
            save() {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.inputFields = {
                            title: this.title,
                            template_type: this.template_type,
                            is_default_template: this.isDefaultTemplate,
                            content: this.content,
                        };

                        if (this.id) {
                            this.postDataMethod('/save-invoice-template/' + this.id, this.inputFields);

                        } else {
                            this.postDataMethod('/add-invoice-template', this.inputFields);
                        }
                    }
                });
            },
            getInvoiceTemplateData(route) {
                let instance = this;
                instance.setPreLoader(false);
                instance.axiosGet(route,
                    function (response) {
                        instance.title = response.data.template_title;
                        instance.template_type = response.data.template_type;
                        instance.isDefaultTemplate = response.data.is_default_template;
                        instance.content = response.data.content;
                        instance.isReStoreShow = response.data.isReStoreShow;

                        $("#custom-content").summernote("code", instance.content);
                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
                );
            },
            postDataThenFunctionality(response) {
                let instance = this;
                instance.$hub.$emit('reloadDataTable');
                $(this.modalID).modal('hide');
            },
            postDataCatchFunctionality(error) {
            },
            restoreToDefault() {
                this.content = '';
                this.restoreButtonTriggered = true;
                this.save();
            },
            is_disable() {
                this.is_disabled = true;
                this.restoreButtonTriggered = false;
            },
        },
    }
</script>