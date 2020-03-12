<template>
    <div style="display:none">
        <div id="cart-print">
            <span v-html="HTMLcontent"></span>
        </div>
    </div>
</template>

<script>
    import axiosGetPost from '../../helper/axiosGetPostCommon';

    export default {
        extends: axiosGetPost,

        props: [
            'printInvoice',
            'HTMLcontent'
        ],
        watch: {
            printInvoice: function (newVal) {
                if (newVal) {
                    this.printReceipt();
                }
            }
        },
        computed: {
            isMobile() {
                return window.innerWidth <= 1024
            }
        },
        methods: {
            printReceipt() {
                if (!this.isMobile) {
                    $('#cart-print').printThis({
                        importCSS: false,
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
                this.$emit('resetGetInvoice', false);
            },
        },
    }
</script>