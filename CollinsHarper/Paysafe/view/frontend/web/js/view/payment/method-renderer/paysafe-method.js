define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'Magento_Vault/js/view/payment/vault-enabler',
        'CollinsHarper_Paysafe/js/validator',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/model/messageList',
        'CollinsHarper_Paysafe/js/action/set-payment-method',
        'jquery/ui',
        'mage/translate'
    ],
    function (
        $,
        Component,
        additionalValidators,
        quote,
        customerData,
        VaultEnabler,
        validator,
        fullScreenLoader,
        globalMessageList,
        setPaymentMethodAction
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                active: false,
                template: 'CollinsHarper_Paysafe/payment/form',
                code: 'chpaysafe',
                imports: {
                    onActiveChange: 'active'
                }
            },
            /**
             * @returns {exports.initialize}
             */
            initialize: function () {
                this._super();
                this.vaultEnabler = new VaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getVaultCode());

                return this;
            },
            initObservable: function () {
                validator.setConfig(window.checkoutConfig.payment[this.getCode()]);
                this._super()
                    .observe(['active']);
                return this;
            },
            onActiveChange: function (isActive) {

            },
            getCode: function () {
                return this.code;
            },
            getVaultCode: function () {
                return window.checkoutConfig.payment[this.getCode()].vaultCode;
            },
            /**
             * @returns {Object}
             */
            getData: function () {
                var data = this._super();

                this.vaultEnabler.visitAdditionalData(data);

                return data;
            },

            /**
             * @returns {Bool}
             */
            isVaultEnabled: function () {
                return this.vaultEnabler.isVaultEnabled();
            },

            isActive: function () {
                var active = this.getCode() === this.isChecked();

                this.active(active);

                return active;
            },
            getTitle: function () {
                return window.checkoutConfig.payment[this.getCode()].title;
            },
            isSilentPost: function() {
                return window.checkoutConfig.payment[this.getCode()].enable_silent_post;
            },
            is3DSecureEnabled: function() {
                return window.checkoutConfig.payment[this.getCode()].enable_3dsecure;
            },
            isInteracEnabled: function() {
                return window.checkoutConfig.payment[this.getCode()].enable_interac;
            },
            /*
             *Code for hiding and showing credit card form based on selected Interac option 
             *
             */
            isShowInteracCcForm: function() {
                if (jQuery('#paysafe_use_interac').is(':checked')) {
                    jQuery("#payment_form_chpaysafe").hide();
                } else {
                    jQuery("#payment_form_chpaysafe").show();
                }
                return true;
            },
            /*
             * Code for validating credit card number field before submitting
             * 
             */
            isCheckCcNumber: function() {
                var self = this;
                jQuery('#' + this.getCode() + '_cc_number').blur(function() {
                    self.validate();
                });
            },
            /*
             * Code for validating credit card expiry month field before submitting
             * 
             */
            isCheckExpMonth: function() {
                var self = this;
                jQuery('#' + this.getCode() + '_expiration').on('change', function() {
                    self.validate();
                });
            },
            /*
             * Code for validating credit card expiry year field before submitting
             * 
             */
            isCheckExpYear: function() {
                var self = this;
                jQuery('#' + this.getCode() + '_expiration_yr').on('change', function() {
                    self.validate();
                });
            },
            /*
             * Code for validating credit card cvv number field before submitting
             * 
             */
            isCheckCvv: function() {
                var self = this;
                jQuery('#' + this.getCode() + '_cc_cid').blur(function() {
                    self.validate();
                });
            },
            /**
             * Get list of available CC types
             *
             * @returns {Object}
             */
            getCcAvailableTypes: function () {
                var availableTypes = validator.getAvailableCardTypes(),
                    billingAddress = quote.billingAddress(),
                    billingCountryId;

                this.lastBillingAddress = quote.shippingAddress();

                if (!billingAddress) {
                    billingAddress = this.lastBillingAddress;
                }

                billingCountryId = billingAddress.countryId;

                if (billingCountryId && validator.getCountrySpecificCardTypes(billingCountryId)) {

                    return validator.collectTypes(
                        availableTypes, validator.getCountrySpecificCardTypes(billingCountryId)
                    );
                }

                return availableTypes;
            },
            /**
             * Get list of available credit card types values
             * @returns {Object}
             */
            getCcAvailableTypesValues: function () {
                return _.map(this.getCcAvailableTypes(), function (value, key) {
                    return {
                        'value': key,
                        'type': value
                    };
                });
            },
            /**
             * Get full selector name
             *
             * @param {String} field
             * @returns {String}
             */
            getSelector: function (field) {
                return '#' + this.getCode() + '_' + field;
            },
            validate: function () {
                var $form = $(this.getSelector('form'));
                return $form.validation() && $form.validation('isValid');
            },
            placeOrderToPaySafe: function () {
                var self = this;
                setPaymentMethodAction(this.messageContainer).done(
                    function () {
                        fullScreenLoader.startLoader();
                        if (self.validate() && additionalValidators.validate()) {
                            self.isPlaceOrderActionAllowed(false);

                            var customerChooseInterac = $('#paysafe_use_interac').is(':checked');
                            var storeCardIndicator = ($(self.getSelector('enable_vault')).is(':checked')) ? "true" : "false";

                            $.ajax({
                                url: window.checkout.baseUrl + 'chpaysafe/hosted/redirect',
                                type: "post",
                                dataType: 'json',
                                data: {
                                    'customerChooseInterac': customerChooseInterac
                                },
                                success: function (data) {
                                    if (self.isSilentPost()) {
                                        var additionalData = self.getData().additional_data;
                                        var form = $(document.createElement('form'));
                                        $(form).attr("action", data.link[0].uri);
                                        $(form).attr("method", "POST");
                                        $(form).append('<input name="cardNum" value="'+additionalData.cc_number+'"/>');
                                        $(form).append('<input name="cardExpiryMonth" value="'+additionalData.cc_exp_month+'"/>');
                                        $(form).append('<input name="cardExpiryYear" value="'+additionalData.cc_exp_year+'"/>');
                                        $(form).append('<input name="cvdNumber" value="'+additionalData.cc_cid+'"/>');
                                        $(form).append('<input name="storeCardIndicator" value="'+storeCardIndicator+'"/>');
                                        $("body").append(form);
                                        $(form).submit();

                                    } else {
                                        window.top.location.href = data.link[0].uri;
                                    }
                                },
                                error: function (response)
                                {
                                    var error = JSON.parse(response.responseText);

                                    fullScreenLoader.stopLoader();
                                    globalMessageList.addErrorMessage({
                                        message: error.message
                                    });
                                }
                            });

                            return false;
                        }
                        fullScreenLoader.stopLoader();
                    }
                );
            }
        });
    }
);