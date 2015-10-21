<?php
$first_unit = reset($units);
$address = $this->address_model->get_formatted_address($order->site_address_id);
$is_breakdown = $this->order_model->get_type_id('Breakdown') == $order->order_type_id;
$is_maintenance = $this->order_model->get_type_id('Maintenance') == $order->order_type_id;
$is_installation = $this->order_model->get_type_id('Installation') == $order->order_type_id;
$is_service = $this->order_model->get_type_id('Service') == $order->order_type_id;
?>
<div class="panel panel-primary">
<div class="panel-heading"><?=get_title($title_options)?></div>
    <div class="panel-body">
        <table class="table table-condensed table-striped">
            <thead>
                <tr><th>Booking date</th><th>Start time</th><th>Ref #</th><th>Equipment type</th><th>Contact name</th><th># of units</th><th>Senior Technician</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td><?=mdate('%D %d%S %F', $assignment->appointment_date)?></td>
                    <td><?=mdate('%h:%i %a', $assignment->appointment_date)?></td>
                    <td>J<?=$assignment->order_id?></td>
                    <td><?=$equipment_type?></td>
                    <td><?=$order->site_contact_first_name . " " . $order->site_contact_surname?></td>
                    <td><?=count($units)?></td>
                    <td><?=$assignment->senior_technician?></td>
                </tr>

            </tbody>
        </table>

        <script type="text/javascript"
          src="https://maps.googleapis.com/maps/api/js?sensor=false">
        </script>
        <script type="text/javascript">
          function initialize() {

            $.post('http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=<?=$address?>', function(data) {
                if (undefined == data.results[0]) {
                    return false;
                }
                var lat = data.results[0].geometry.location.lat;
                var lng = data.results[0].geometry.location.lng;

                var mapOptions = {
                  center: new google.maps.LatLng(lat, lng),
                  zoom: 16
                };
                var map = new google.maps.Map(document.getElementById("tech_map-canvas"), mapOptions);

                var marker = new google.maps.Marker({
                    position: new google.maps.LatLng(lat, lng),
                    title: "<?=$address?>",
                    draggable: false
                });
                google.maps.event.addListener(marker, 'click', function() {
                    alert(this.title);
                });

                marker.setMap(map);
            }, 'json');
          }
          google.maps.event.addDomListener(window, 'load', initialize);
        </script>
        <div id="tech_map-canvas"></div>

        <div id="tech-task-details">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#notes" data-toggle="tab">Notes</a></li>
                <li><a href="#equipment" data-toggle="tab">Equipment</a></li>
                <li><a href="#address" data-toggle="tab">Address</a></li>
            </ul>
            <div class="tabbable tab-content">
                <div class="tab-pane" id="address">
                    <?=$this->address_model->get_formatted_address($order->site_address_id)?>
                </div>
                <div class="tab-pane" id="equipment">
                    <br />
                    <table class="table table-condensed">
                        <tr><th>Unit type</th><th>Brand</th><th>Area Serving</th><th>Tenancy/Owner</th></tr>

                        <?php foreach ($units as $unit) : ?>
                        <tr>
                            <td><?=$unit->type?></td>
                            <td><?=$unit->brand?></td>
                            <td><?=$unit->area_serving?></td>
                            <td><?=$unit->tenancy?></td>
                        </tr>
                        <?php endforeach; ?>

                    </table>
                </div>
                <div class="tab-pane active" id="notes">
                    <br />
                    <?php $this->load->view('messages', array('display' => '', 'document_type' => 'order', 'document_id' => $order->id)); ?>
                </div>
            </div>
        </div>

        <?php if (has_capability('orders:editunitdetails')) { ?>
        <div id="begin-task" class="panel panel-info">
            <div class="panel-body">
                <?=$dialog?>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
</div>
