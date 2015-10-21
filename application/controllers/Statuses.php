<?php
/*
 * Copyright 2015 SMB Streamline
 *
 * Contact nicolas <nicolas@smbstreamline.com.au>
 *
 * Licensed under the "Attribution-NonCommercial-ShareAlike" Vizsage
 * Public License (the "License"). You may not use this file except
 * in compliance with the License. Roughly speaking, non-commercial
 * users may share and modify this code, but must give credit and
 * share improvements. However, for proper details please
 * read the full License, available at
 *  	http://vizsage.com/license/Vizsage-License-BY-NC-SA.html
 * and the handy reference for understanding the full license at
 *  	http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html
 *
 * Unless required by applicable law or agreed to in writing, any
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 */
class Statuses extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer',
            array(
                'order' => 'Order Statuses',
                'job' => 'Job Statuses',
                'diagnostic' => 'Diagnostic Statuses',
                'assignment' => 'Assignment Statuses',
            )
        );
        $this->config->set_item('exclude', array('index', 'html', 'browse', 'statuses', 'edit'));

        // Being a global controller, doesn't need its second-level segment to be hidden
        $this->config->set_item('exclude_segment', array());
    }

    public function browse($outputtype='html', $document_type) {
        return $this->index($outputtype, $document_type);
    }

    public function index($outputtype='html', $document_type) {
        $sql_conditions = array();

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'row_actions' => array('edit', 'delete', 'triggers'),
            'available_export_types' => array('csv'),
            'sql_conditions' => $sql_conditions,
            'row_action_capabilities' => array(
                'delete' => $document_type.':deletestatuses',
                'edit' => $document_type.':editstatuses',
                'triggers' => $document_type.':editstatuses'
            ),
            'show_add_button' => has_capability($document_type.':writestatuses'),
            'model' => $this->status_model,
            'uri_segment_1' => 'site',
            'uri_segment_2' => 'statuses',
            'url_param' => $document_type,
            'custom_columns_callback' => $this->status_model->get_custom_columns_callback($document_type)
        ));

        $this->datagrid->add_column(array(
            'table' => 'statuses',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'status_id',
            'in_combo_filter' => true,
            'width' => '5%'
        ));
        $this->datagrid->add_column(array(
            'table' => 'statuses',
            'field' => 'name',
            'label' => 'Name',
            'field_alias' => 'status_name',
            'in_combo_filter' => true,
            'width' => '20%'
        ));
        $this->datagrid->add_column(array(
            'table' => 'statuses',
            'field' => 'description',
            'label' => 'Description',
            'field_alias' => 'status_description',
            'in_combo_filter' => true,
            'width' => '30%'
        ));

        $this->datagrid->setup_filters();
        $this->datagrid->render();
    }

    public function edit_triggers($status_id, $document_type) {
        $this->db->order_by('sortorder');
        $events = $this->event_model->get_dropdown('name', false);
        $status = $this->status_model->get($status_id);
        $title = "Event triggers for status '$status->name', for ".$this->inflector->pluralize($document_type);
        $this->config->set_item('replacer',
            array(
                  'statuses' => array('/statuses/index/html/'.$document_type.'|Statuses'), 'edit' => $title, 'add' => $title,
                  $this->inflector->pluralize($document_type) => array('/orders/order/index|'.ucfirst($this->inflector->pluralize($document_type)))
                )
        );

        $title_options = array('title' => $title,
                               'help' => "Use this page to set up event triggers for the '$status->name' status, for
                                    ".$this->inflector->pluralize($document_type).".
                                    An event trigger will be activated when this status is changed, which will in turn
                                    change the statuses of other documents (orders, diagnostics etc.) associated with that $document_type",
                               'expand' => 'page',
                               'icons' => array('add'));

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => '/statuses/edit_triggers',
                             'status_id' => $status_id,
                             'events' => $events,
                             'status' => $status,
                             'document_type' => $document_type,
                             'feature_type' => 'Streamliner Core',
                             'jstoloadinfooter' => array(
                                 'application/status_triggers',
                                 )
                             );

        $this->load->view('template/default', $pageDetails);
    }
}
