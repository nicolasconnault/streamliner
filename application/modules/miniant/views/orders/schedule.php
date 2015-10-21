<div class="panel panel-primary">
    <div class="panel-heading"><?=get_title($title_options)?></div>
    <div class="panel-body">
        <div class="panel panel-info">
            <div class="panel-heading"><?=get_title($tasks_title_options)?></div>
            <div class="panel-body">
                <!-- This looks better than a dropdown, but much more complex to set up
                <div style="display: block; text-align:center; width: 100%">
                <div class="btn-group">
                    <button type="button" id="prev-month" class="btn btn-default"><i class="fa fa-arrow-left"></i><?=date('F Y', strtotime(date('Y-m-1')." -1 month"));?></button>
                    <button type="button" id="current-month" class="btn btn-info"><?=date('F Y', strtotime(date('Y-m-d')));?></button>
                    <button type="button" id="next-month" class="btn btn-default"><?=date('F Y', strtotime(date('Y-m+1')." +1 month"));?> <i class="fa fa-arrow-right"></i></button>
                </div>
                </div>
                -->
                <label for="month-selector">Select a month for Maintenance contracts: </label>
                <form action="<?=base_url()?>miniant/orders/schedule" method="post">
                <select name="maintenance-date" id="month-selector" onchange="$(this).parent().submit();">
                    <?php for ($i = -12; $i < 24; $i++) {
                        $selected = null;

                        if ($i < 0) {
                            $timestamp = strtotime(date("Y-m$i", $maintenance_date)." $i month");
                        } else if ($i == 0) {
                            $timestamp = $maintenance_date;
                            $selected = 'selected="selected"';
                        } else if ($i > 0) {
                            $timestamp = strtotime(date("Y-m+$i", $maintenance_date)." +$i month");
                        }
                        $date = date('F Y', $timestamp);
                        echo '<option '.$selected.' value="'.$timestamp.'">'.$date.'</option>';
                    } ?>
                </select>
                </form>
                <table class="table table-condensed table-striped table-hover" id="schedule-table">
                <?php //TODO Add a month selector for maintenance contracts ?>
                    <thead>
                        <tr>
                            <th>Job Number</th>
                            <th>Request type</th>
                            <th>Preferred date</th>
                            <th># of units</th>
                            <th>Site address</th>
                            <th>Site contact</th>
                            <th>Type of equipment</th>
                            <th>Tasks</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Job Number</th>
                            <th>request type</th>
                            <th>Preferred date</th>
                            <th>units</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th>Type </th>
                            <th></th>
                        </tr>
                    </tfoot>
                    <tbody>
              <?php
                    foreach ($orders as $order_type => $order_array) {
                        if ($order_type == 'maintenance' || $order_type == 'service') {
                            if (empty($order_array[date('Y', $maintenance_date)][date('m', $maintenance_date)])) {
                                continue;
                            } else {
                                $order_array = $order_array[date('Y', $maintenance_date)][date('m', $maintenance_date)];
                            }
                        }

                        foreach ($order_array as $order) {
              ?>
                        <tr>
                            <td><?=$order->id?></td>
                            <td><?=$order->order_type?></td>
                            <td><?=unix_to_human($order->preferred_start_date)?></td>
                            <td><?=count($order->units)?></td>
                            <td><?=$this->address_model->get_formatted_address($order->site_address_id)?></td>
                            <td><?=$this->contact_model->get_name($order->site_contact_id)?></td>
                            <td><?=reset($order->units)->type?></td>
                            <td>
<?php if ($this->order_model->has_schedulable_assignments($order->id)) { ?>
                                <div class="draggable-event <?=$order->order_type?> btn btn-<?=strtolower($order->order_type)?>" title="click to drag" data-orderid="<?=$order->id?>">
                                    <i class="fa fa-fw fa-arrows"></i><?=$order->order_type?>
                                </div>
<?php } ?>
                            </td>
                        </tr>
                        <?php
                        }
                    }
                ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel panel-info">
            <div class="panel-heading"><?=get_title($calendar_title_options)?></div>
            <div class="panel-body">
                <p>
                <input type='hidden' id='drop-remove' />
                </p>
                </div>
                <div id='calendar'></div>
                <script type="text/javascript">
                //<![CDATA[
                    var technicians = <?=json_encode($technicians)?>;
                //]]>
                </script>
            </div>
        </div>
    </div>
</div>
