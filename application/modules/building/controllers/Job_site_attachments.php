 <?php
class Job_site_attachments extends MY_Controller {
    public $uri_level = 1;

    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer', array('building' => null, 'job_site_attachments' => array('/job_site_attachments|Job site drawings')));
        $this->config->set_item('exclude', array('index', 'browse'));

        $this->config->set_item('exclude_segment', array());
    }

    public function browse($outputtype='html', $job_site_id) {
        return $this->index($outputtype, $job_site_id);
    }

    public function index($outputtype='html', $job_site_id) {
        require_capability('building:viewjobsites');

        $job_site = $this->job_site_model->get($job_site_id);

        $this->load->library('datagrid', array(
            'outputtype' => $outputtype,
            'uri_segment_1' => 'site',
            'uri_segment_2' => 'job_site_attachments',
            'module' => 'building',
            'row_actions' => array('edit', 'delete', 'download'),
            'row_action_capabilities' => array('edit' => 'building:editjobsites', 'delete' => 'building:deletejobsites', 'download' => 'building:viewjobsites'),
            'feature_type' => 'Custom Feature',
            'available_export_types' => array(),
            'model' => $this->job_site_attachment_model,
            'custom_title' => 'List of drawings for '. $this->address_model->get_formatted_address($job_site, '[[number]] [[street]] [[street_type_short]], [[city]]'),
            'url_param' => $job_site_id,
            'title_icon' => 'pdf',
            'sql_conditions' => array('job_site_id = '.$job_site_id)
        ));

        $this->datagrid->add_column(array(
            'table' => 'building_job_site_attachments',
            'field' => 'id',
            'label' => 'ID',
            'field_alias' => 'job_site_attachment_id'));
        $this->datagrid->add_column(array(
            'table' => 'building_job_site_attachments',
            'field' => 'description',
            'field_alias' => 'job_site_attachment_description',
            'label' => 'Description',
            'sortable' => true));

        $this->datagrid->render();
    }

    public function add($job_site_id) {
        return $this->edit(null, $job_site_id);
    }

    public function edit($job_site_attachment_id=null, $job_site_id = null) {

        require_capability('building:writejobsites');

        $job_site_attachment_data = new stdClass();

        if (!empty($job_site_attachment_id)) {
            require_capability('building:editjobsites');
            $job_site_attachment_data = $this->job_site_attachment_model->get($job_site_attachment_id);
            $job_site_id = $job_site_attachment_data->job_site_id;
            $job_site_attachment_data->url = base_url().'application/modules/building/files/job_site_drawings/'.$job_site_id.'/'.$job_site_attachment_data->hash;
            form_element::$default_data = (array) $job_site_attachment_data;

            if (!empty(form_element::$default_data['attachment'])) {
                form_element::$default_data['attachment'] = $job_site_attachment_data->filename_original;
            }

            // Set up title bar
            $title = "Edit Job site drawing";
            $help = "Use this form to edit the job site's drawing.";
        } else { // adding a new job_site
            $title = "Create a new job site drawing";
            $help = 'Use this form to create a new job site drawing.';
        }

        $this->config->set_item('replacer', array('building' => null, 'job_site_attachments' => array('/building/job_site_attachments/index|Job site drawings'), 'edit' => $title, 'add' => $title));
        $title_options = array('title' => $title,
                               'help' => $help,
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => $title,
                             'title_options' => $title_options,
                             'content_view' => 'job_site_attachments/edit',
                             'attachment' => $job_site_attachment_data,
                             'job_site_attachment_id' => $job_site_attachment_id,
                             'job_site_id' => $job_site_id,
                             'feature_type' => 'Custom feature',
                             'csstoload' => array()
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function process_edit() {

        require_capability('building:editjobsites');

        $job_site_id = $this->input->post('job_site_id');

        if ($job_site_attachment_id = (int) $this->input->post('id')) {
            $job_site_attachment = $this->job_site_attachment_model->get($job_site_attachment_id);
            $redirect_url = base_url().'building/job_site_attachments/edit/'.$job_site_attachment_id;
            $job_site_id = $job_site_attachment->job_site_id;
            $action_word = 'updated';
        } else {
            $redirect_url = base_url().'building/job_site_attachments/add';
            $job_site_attachment_id = null;
            $action_word = 'created';
        }

        if (empty($job_site_attachment_id)) {
            $config = array();
            $config['upload_path'] = $this->config->item('files_path').'job_site_drawings/'.$job_site_id;

            if (!file_exists($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, true);
            }

            $config['allowed_types'] = 'pdf|png|jpg|jpeg|gif|svg';
            $config['encrypt_name'] = true;

            $this->upload->initialize($config);

            if (!$this->upload->do_upload('attachment')) {
                if ($this->upload->display_errors('','') == 'You did not select an attachment to upload.') {
                    return false;
                }

                $readable_filetypes = strtoupper(str_replace('|', ', ', $config['allowed_types']));
                add_message("The type of attachment you uploaded is not allowed. Allowed types are $readable_filetypes.", 'danger');
                redirect(base_url().'building/job_site_attachments/edit/'.$job_site_attachment_id);
            }

            $attachment_data = $this->upload->data();

            $attachment = array('filename_original' => $attachment_data['orig_name'],
                          'hash' => $attachment_data['file_name'],
                          'job_site_id' => $job_site_id,
                          'description' => $this->input->post('description'),
                          'directory' => 'job_site_drawings/'.$job_site_id,
                          'file_type' => $attachment_data['file_type'],
                          'file_extension' => $attachment_data['file_ext'],
                          'file_size' => $attachment_data['file_size']);

            if (!($job_site_attachment_id = $this->job_site_attachment_model->add($attachment))) {
                add_message('Could not create this Job site drawing!', 'error');
            }
        } else {
            if (!$this->job_site_attachment_model->edit($job_site_attachment_id, array('description' => $this->input->post('description')))) {
                add_message('Could not update this Job site drawing!', 'error');
                redirect($redirect_url);
            }
        }

        add_message("Job site drawing $job_site_attachment_id has been successfully $action_word!", 'success');
        redirect(base_url().'building/job_site_attachments/index/html/'.$job_site_id);
    }

    public function delete($job_site_attachment_id, $job_site_id=null) {

        $attachment = $this->job_site_attachment_model->get($job_site_attachment_id);
        $result = $this->job_site_attachment_model->delete($job_site_attachment_id);
        unlink($this->config->item('files_path').'job_site_drawings/'.$attachment->job_site_id.'/'.$attachment->hash);

        if (IS_AJAX) {
            $json = new stdClass();

            if ($result) {
                $json->message = 'The drawing was successfully deleted';
                $json->id = $job_site_attachment_id;
                $json->type = 'success';
            } else {
                $json->message = "The drawing could not be deleted";
                $json->id = $job_site_attachment_id;
                $json->type = 'danger';
            }
            echo json_encode($json);
            die();
        } else {
            // @todo handle non-AJAX delete: flash message and redirection
        }
    }

    public function download($job_site_attachment_id) {
        $attachment = $this->job_site_attachment_model->get($job_site_attachment_id);
        $url = base_url().'application/modules/building/files/job_site_drawings/'.$attachment->job_site_id.'/'.$attachment->hash;
        redirect($url);
    }

}
