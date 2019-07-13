document.addEventListener('DOMContentLoaded', function(event) {
    let selectBulkProduct   = jQuery('select#bulk__product');
    let selectBulkOrder     = jQuery('select#bulk__order');
    let selectSingleProduct = jQuery('select#single__product');
    let selectSingleOrder   = jQuery('select#single__order');
    let selectEditProduct   = jQuery('select#edit__product');
    let selectEditOrder     = jQuery('select#edit__order');
    //let licensesTableForm   = document.getElementById('lmfwc-license-table');

    if (selectBulkProduct) {
        selectBulkProduct.select2();
    }

    if (selectBulkOrder) {
        selectBulkOrder.select2();
    }

    if (selectSingleProduct) {
        selectSingleProduct.select2();
    }

    if (selectSingleOrder) {
        selectSingleOrder.select2();
    }

    if (selectEditProduct) {
        selectEditProduct.select2();
    }

    if (selectEditOrder) {
        selectEditOrder.select2();
    }
});