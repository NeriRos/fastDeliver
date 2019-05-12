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
        gethtml: function (row) {
            return row[this.index + '_html'];
        },
        getFormaction: function (row) {
            return row[this.index + '_formaction'];
        },
        getOrderid: function (row) {
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
		getOstreet: function (row) {
            return row[this.index + '_origin_street'];
        },
		getOcity: function (row) {
            return row[this.index + '_origin_city'];
        },
		getOcompany: function (row) {
            return row[this.index + '_origin_company'];
        },
		getOpostcode: function (row) {
            return row[this.index + '_origin_postcode'];
        },
		getDstreet: function (row) {
            return row[this.index + '_destination_street'];
        },
		getDcity: function (row) {
            return row[this.index + '_destination_city'];
        },
		getDcompany: function (row) {
            return row[this.index + '_destination_company'];
        },
		getDpostcode: function (row) {
            return row[this.index + '_destination_postcode'];
        },
		getShippingInstruction: function (row) {
            return row[this.index + '_shipping_instruction'];
        },
		getDhousenumber: function (row) {
            return row[this.index + '_destination_house_number'];
        },
		getDestinationEnterance: function (row) {
            return row[this.index + '_destination_enterance'];
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
		getOHouseNumber: function (row) {
            return row[this.index + '_origin_house_number'];
        },
		getBaldarClientCode: function (row) {
            return row[this.index + '_baldar_client_code'];
        },
        preview: function (row) {
            var modalHtml = mageTemplate(
                sendmailPreviewTemplate,
                {
                    html: this.gethtml(row), 
                    title: this.getTitle(row), 
                    label: this.getLabel(row), 
                    formaction: this.getFormaction(row),
                    orderid: this.getOrderid(row),
                    submitlabel: this.getSubmitlabel(row), 
                    cancellabel: this.getCancellabel(row),
					origin_street: this.getOstreet(row),
					origin_city: this.getOcity(row),
					origin_house_number: this.getOHouseNumber(row),
					destination_street: this.getDstreet(row),
					destination_house_number: this.getDhousenumber(row),
					destination_city: this.getDcity(row),
					origin_company: this.getOcompany(row),
					destination_company: this.getDcompany(row),
					shipping_instructions: this.getShippingInstruction(row),
					origin_postcode: this.getOpostcode(row),
					destination_postcode: this.getDpostcode(row),
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