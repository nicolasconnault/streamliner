<div class="panel panel-primary">
    <div class="panel-heading"><?=get_title($title_options)?></div>
    <?php
    echo form_open(base_url().'miniant/orders/documents/process_edit/', array('id' => 'order_document_edit_form', 'class' => 'form-horizontal'));
    ?>
    <div class="panel-body">
        <?php
        print_form_container_open();
        print_hidden_element(array('name' => 'order_id', 'default_value' => $order_id));
        print_hidden_element(array('name' => 'invoice_tenancy_id', 'default_value' => $invoice_tenancy->id));
        print_input_element(array(
            'label' => 'System time',
            'name' => 'system_time',
            'required' => true,
            'render_static' => true,
            'static_displayvalue' => get_hours_and_minutes_from_seconds($invoice_tenancy->system_time) . ' hours'
        ));

        $corrected_time_value = (empty($invoice_tenancy->technician_time)) ? round($invoice_tenancy->system_time / 60 / 60, 2) : round($invoice_tenancy->technician_time / 60 / 60, 2);

        print_input_element(array(
            'label' => 'Corrected time',
            'name' => 'technician_time',
            'required' => true,
            'data-slider-min' => 0,
            'data-slider-max' => 99,
            'data-slider-step' => 0.25,
            'data-slider-value' => $corrected_time_value,
            'data-slider-orientation' => 'horizontal',
            'id' => 'slider-corrected-time',
            'style' => 'width: 600px',
        ));

        print_submit_container_open();
        print_submit_button('Save and complete review');
        print_cancel_button(base_url().'miniant/orders/documents/index/html/'.$order_id);
        print_submit_container_close();
        print_form_container_close();
    echo '</div>';
    echo form_close();
    ?>
</div>
<script type="text/javascript">
    $(function() {
        $('#slider-corrected-time').slider({
            value: <?=$corrected_time_value?>,
            formater: function(value) {

                var start_date = new Date(0);
                var end_date = new Date(start_date.getMilliseconds() + (value * 60 * 60 * 1000));
                var total_seconds = Date.parse(end_date) / 1000;
                var total_hours = Math.floor(total_seconds / 60 / 60);
                var remainder_minutes = (total_seconds / 60) - (total_hours * 60);
                return pad_number(total_hours, 2) + ':' + pad_number(remainder_minutes, 2) + ' hours';
            }
        });
    });

        function pad_number(n,w){
          var pad=new Array(1+w).join('0');
          return (pad+n).slice(-pad.length);
        }
</script>
