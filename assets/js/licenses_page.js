document.addEventListener('DOMContentLoaded', function(event) {
    var importLicenseProduct = jQuery('select#bulk__product');
    var importLicenseOrder   = jQuery('select#bulk__order');
    var addLicenseProduct    = jQuery('select#single__product');
    var addLicenseOrder      = jQuery('select#single__order');
    var editLicenseProduct   = jQuery('select#edit__product');
    var editLicenseOrder     = jQuery('select#edit__order');
    var bulkAddSource        = jQuery('input[type="radio"].bulk__type');
    var bulkAddSourceRows    = jQuery('tr.bulk__source_row');

    var productDropdownSearchConfig = {
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
    var orderDropdownSearchConfig = {
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

    if (bulkAddSource) {
        bulkAddSource.change(function() {
            var value = jQuery('input[type="radio"].bulk__type:checked').val();

            if (value !== 'file' && value !== 'clipboard') {
                return;
            }

            // Hide the currently visible row
            jQuery('tr.bulk__source_row:visible').addClass('hidden');

            // Display the selected row
            jQuery('tr#bulk__source_' + value + '.bulk__source_row').removeClass('hidden');
        })
    }
});