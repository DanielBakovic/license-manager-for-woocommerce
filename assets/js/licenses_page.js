document.addEventListener('DOMContentLoaded', function(event) {
    let importLicenseProduct = jQuery('select#bulk__product');
    let importLicenseOrder   = jQuery('select#bulk__order');
    let addLicenseProduct    = jQuery('select#single__product');
    let addLicenseOrder      = jQuery('select#single__order');
    let editLicenseProduct   = jQuery('select#edit__product');
    let editLicenseOrder     = jQuery('select#edit__order');
    //let licensesTableForm   = document.getElementById('lmfwc-license-table');

    let productDropdownSearchConfig = {
        ajax: {
            cache: true,
            delay: 500,
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: function(params) {
                return {
                    action: 'lmfwc_dropdown_search',
                    security: security.dropdownSearch,
                    term: params.term,
                    page: params.page,
                    type: 'product'
                };
            },
            processResults: function(data, params) {
                console.log(data);
                console.log(params);
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            }
        },
        placeholder: i18n.placeholderSearchProducts,
        minimumInputLength: 1,
        allowClear: true
    };
    let orderDropdownSearchConfig = {
        ajax: {
            cache: true,
            delay: 500,
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: function(params) {
                return {
                    action: 'lmfwc_dropdown_search',
                    security: security.dropdownSearch,
                    term: params.term,
                    page: params.page,
                    type: 'shop_order'
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            }
        },
        placeholder: i18n.placeholderSearchOrders,
        minimumInputLength: 1,
        allowClear: true
    };

    if (importLicenseProduct) {
        importLicenseProduct.select2(productDropdownSearchConfig);
    }

    if (importLicenseOrder) {
        importLicenseOrder.select2(orderDropdownSearchConfig);
    }

    if (addLicenseProduct) {
        addLicenseProduct.select2(productDropdownSearchConfig);
    }

    if (addLicenseOrder) {
        addLicenseOrder.select2(orderDropdownSearchConfig);
    }

    if (editLicenseProduct) {
        editLicenseProduct.select2(productDropdownSearchConfig);
    }

    if (editLicenseOrder) {
        editLicenseOrder.select2(orderDropdownSearchConfig);
    }
});