<?php
require_once(APPPATH.'/core/has_type_trait.php');
class Servicequote_document_model extends MY_Model {
    use has_types;

    public $table = 'miniant_servicequote_documents';

    public function get_custom_columns_callback() {
        return function(&$db_records) {
            $this->load->model('miniant/miniant_account_model', 'account_model');


            if (empty($db_records)) {
                return null;
            }

            foreach ($db_records as $key => $row) {

                $db_records[$key]['type_id'] = $this->servicequote_document_model->get_type_string($row['type_id']);

                if ($contact = $this->contact_model->get($row['recipient_contact_id'])) {
                    $account = $this->account_model->get($contact->account_id);
                    $db_records[$key]['recipient_contact_id'] = $account->name;
                }
            }
        };
    }
}
