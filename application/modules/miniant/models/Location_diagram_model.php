<?php

class Location_diagram_model extends MY_Model {
    public $table = 'miniant_location_diagrams';

    public function copy_if_exists_for_address($order_id, $parent_sq_id=null) {
        $this_order = $this->order_model->get($order_id);
        $all_orders_at_this_address = $this->address_model->get_orders($this_order->site_address_id);
        $latest_location_diagram_date = 0;
        $latest_location_diagram = null;
        $latest_location_order_id = null;

        if (empty($all_orders_at_this_address)) {
            return false;
        }

        foreach ($all_orders_at_this_address as $order) {
            if (!empty($order->location_diagram_id) && $order->revision_date > $latest_location_diagram_date) {
                $latest_location_diagram_date = $order->revision_date;
                $latest_location_diagram = $this->get($order->location_diagram_id);
                $latest_location_order_id = $order->id;
            }
        }

        if (empty($latest_location_diagram) && !empty($parent_sq_id)) {
            $parent_servicequote = $this->servicequote_model->get($parent_sq_id);
            $order = $this->order_model->get($parent_servicequote->order_id);
            $latest_location_diagram_date = $order->revision_date;
            $latest_location_diagram = $this->get($order->location_diagram_id);
            $latest_location_order_id = $order->id;
        }

        if (!empty($latest_location_diagram) && empty($this_order->location_diagram_id)) {
            $new_diagram_id = $this->add(array('diagram' => $latest_location_diagram->diagram));
            $this->order_model->edit($order_id, array('location_diagram_id' => $new_diagram_id));

            $upload_path = $this->config->item('files_path').'location_diagrams';

            if (file_exists($upload_path.'/'.$latest_location_order_id.'.png')) {
                copy($upload_path.'/'.$latest_location_order_id.'.png', $upload_path.'/'.$this_order->id.'.png');
            } else {
                return false;
            }
        }

        return true;
    }
}
