define([
    'jquery'
], function($) {
    'use strict';

    $.widget('sqli.testSoap', {
        options: {
            testUrl: '',
            merchantIdSelector: '#twint_soap_merchant_id',
            cashRegisterIdSelector: '#twint_soap_cash_register_id',
            testSelector: '#admin-test-soap-button',
            messagesSelector: '.test-soap-success-messages',
            errorMessagesSelector: '.test-soap-error-messages'
        },

        _create: function () {
            this._super();
            this._initListeners();
        },

        _initListeners() {
            $(this.options.testSelector).on('click',  $.proxy(this.onTestButton, this));
        },

        _testSoap() {
            var self = this;
            $.ajax({
                url: this.options.testUrl,
                type: 'POST',
                data: {
                    'merchant_uuid': this.getMerchantUuid(),
                    'cash_register_id': this.getCashRegisterId()
                },
                async: false,
                success: $.proxy(this.onTest, this),
                error: $.proxy(this.onError, this)
            });
        },

        getMerchantUuid() {
            return $(this.options.merchantIdSelector).val();
        },

        getCashRegisterId() {
            return $(this.options.cashRegisterIdSelector).val();
        },

        onTestButton() {
            this._testSoap();
        },

        onTest(body) {
            this.removeMessage();
            var error = body.error;
            var request = body.request || [];
            var response = body.response || [];
            if (!response) {
                error = "Empty Response";
            }
            if (error ) {
                this.addErrorMessage(error, request, response);
            } else {
                this.addMessage(response);
            }
        },

        onError(xhr, options, thrownError) {
            this.removeMessage();
            var error = xhr.status;
            this.addErrorMessage(error, [], []);
        },

        addMessage(data) {
            var messages = $(this.options.messagesSelector);
            var dataJson = JSON.stringify(data, undefined, 2);
            if (messages) {
                messages.html(dataJson);
            }
        },

        addErrorMessage(errorMessage, request, response) {
            var messages = $(this.options.errorMessagesSelector );
            if (messages) {
                var requestHtml = "<p class='xml-request'>" + request + "</p>";
                var responseHtml = "<p class='xml-response'>" + response + "</p>";
                messages.html(errorMessage + requestHtml + responseHtml);
            }
        },

        removeMessage() {
            var messages = $(this.options.messagesSelector);
            if (messages) {
                messages.html('');
            }
            var errorMessages = $(this.options.errorMessagesSelector);
            if (errorMessages) {
                errorMessages.html('');
            }
        }
    });

    return $.sqli.testSoap;
});
