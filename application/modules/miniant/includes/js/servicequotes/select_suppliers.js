$(function() {
    $('#select_suppliers_form').submit(function(event) {
        var no_supplier_selected = true;

        $('#servicequote_suppliers tbody > tr').each(function(key, item) {
            if ($(item).find('input:checked').length > 0) {
                no_supplier_selected = false;
            }
        });

        if (no_supplier_selected) {
            event.preventDefault();
            print_message('Please select at least one supplier', 'warning', 'order');
            return false;
        }

    });
});
