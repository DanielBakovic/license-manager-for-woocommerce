document.addEventListener('DOMContentLoaded', function(event) {
	var selectBulkProduct = jQuery('select#bulk__product');
	var selectSingleProduct = jQuery('select#single__product');
	var selectEditProduct = jQuery('select#edit__product');
	var selectEditOrder = jQuery('select#edit__order');

	if (selectBulkProduct) selectBulkProduct.select2();
	if (selectSingleProduct) selectSingleProduct.select2();
	if (selectEditProduct) selectEditProduct.select2();
	if (selectEditOrder) selectEditOrder.select2();
});