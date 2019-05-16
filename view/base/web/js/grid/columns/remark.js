define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'mage/template',
    'text!LightX_FastDeliver/templates/grid/cells/order/remark.html',
    'Magento_Ui/js/modal/modal'
], function (Column, $, mageTemplate, sendmailPreviewTemplate) {
    'use strict';
 
    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            fieldClass: {
                'data-grid-html-cell': true
            }
        },
        getHTML: function (row) {
            return row[this.index + '_html'];
        },
        getFormaction: function (row) {
            return row[this.index + '_formaction'];
        },
        getOrderId: function (row) {
            return row[this.index + '_orderid'];
        },
        getLabel: function (row) {
            return row[this.index + '_html'];
        },
        getTitle: function (row) {
            return row[this.index + '_title'];
        },
        getSubmitlabel: function (row) {
            return row[this.index + '_submitlabel'];
        },
        getCancellabel: function (row) {
            return row[this.index + '_cancellabel'];
        },
		getOriginStreet: function (row) {
            return row[this.index + '_origin_street'];
        },
		getOriginCity: function (row) {
            return row[this.index + '_origin_city'];
        },
		getOriginCompany: function (row) {
            return row[this.index + '_origin_company'];
        },
		getOriginPostCode: function (row) {
            return row[this.index + '_origin_postcode'];
        },
		getDestinationStreet: function (row) {
            return row[this.index + '_destination_street'];
        },
		getDestinationCity: function (row) {
            return row[this.index + '_destination_city'];
        },
		getDestinationCompany: function (row) {
            return row[this.index + '_destination_company'];
        },
		getDestinationPostCode: function (row) {
            return row[this.index + '_destination_postcode'];
        },
		getShippingInstruction: function (row) {
            return row[this.index + '_shipping_instruction'];
        },
		getDestinationHouseNumber: function (row) {
            console.log("Test", row[this.index + '_destination_house_number'],  row[this.index + '_destination_entrance'], row);
            
            return row[this.index + '_destination_house_number'];
        },
		getDestinationEntrance: function (row) {
            return row[this.index + '_destination_entrance'];
        },
		getDestinationFloor: function (row) {
            return row[this.index + '_destination_floor'];
        },
		getDestinationApartment: function (row) {
            return row[this.index + '_destination_apartment'];
        },
		getContactName: function (row) {
            return row[this.index + '_contact_name'];
        },
		getContactTelephone: function (row) {
            return row[this.index + '_contact_telephone'];
        },
		getContactEmail: function (row) {
            return row[this.index + '_contact_email'];
        },
		getExecutionDate: function (row) {
            return row[this.index + '_execution_date'];
        },
		getSiteInternalOrderId: function (row) {
            return row[this.index + '_site_internal_order_id'];
        },
		getOriginHouseNumber: function (row) {
            return row[this.index + '_origin_house_number'];
        },
		getBaldarClientCode: function (row) {
            return row[this.index + '_baldar_client_code'];
        },
        preview: function (row) {
            var modalHtml = mageTemplate(
                sendmailPreviewTemplate,
                {
                    html: this.getHTML(row), 
                    title: this.getTitle(row), 
                    label: this.getLabel(row), 
                    formaction: this.getFormaction(row),
                    orderid: this.getOrderId(row),
                    submitlabel: this.getSubmitlabel(row), 
                    cancellabel: this.getCancellabel(row),
					origin_street: this.getOriginStreet(row),
					origin_city: this.getOriginCity(row),
					origin_house_number: this.getOriginHouseNumber(row),
					destination_street: this.getDestinationStreet(row),
					destination_house_number: this.getDestinationHouseNumber(row),
					destination_city: this.getDestinationCity(row),
					origin_company: this.getOriginCompany(row),
					destination_company: this.getDestinationCompany(row),
					shipping_instructions: this.getShippingInstruction(row),
					origin_postcode: this.getOriginPostCode(row),
					destination_postcode: this.getDestinationPostCode(row),
					contact_name: this.getContactName(row),
					contact_telephone: this.getContactTelephone(row),
					contact_email: this.getContactEmail(row),
					execution_date: this.getExecutionDate(row),					
					site_internal_order_id: this.getSiteInternalOrderId(row),					
					baldar_client_code: this.getBaldarClientCode(row),					
                    linkText: $.mage.__('Go to Details Page')
                }
            );
            var previewPopup = $('<div/>').html(modalHtml);
			//previewPopup.find(".brand_id").html(this.getBrandOptions(row));
			//previewPopup.find(".brand_id").val(this.getBrand(row));
            previewPopup.modal({
                title: this.getTitle(row),
                innerScroll: true,
                modalClass: '_image-box',
                buttons: []}).trigger('openModal');
        },
        getFieldHandler: function (row) {
            return this.preview.bind(this, row);
        }
    });
});