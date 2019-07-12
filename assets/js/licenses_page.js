document.addEventListener('DOMContentLoaded', function(event) {
	var selectBulkProduct   = jQuery('select#bulk__product');
	var selectBulkOrder     = jQuery('select#bulk__order');
	var selectSingleProduct = jQuery('select#single__product');
	var selectSingleOrder   = jQuery('select#single__order');
	var selectEditProduct   = jQuery('select#edit__product');
	var selectEditOrder     = jQuery('select#edit__order');

	if (selectBulkProduct) selectBulkProduct.select2();
	if (selectBulkOrder) selectBulkOrder.select2();
	if (selectSingleProduct) selectSingleProduct.select2();
	if (selectSingleOrder) selectSingleOrder.select2();
	if (selectEditProduct) selectEditProduct.select2();
	if (selectEditOrder) selectEditOrder.select2();
});