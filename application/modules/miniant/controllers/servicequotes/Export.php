<?php
class Export extends MY_Controller {

    public function export_documents($format='pdf', $servicequote_document_id, $servicequote_id) {
        $this->load->model('miniant/miniant_account_model', 'account_model');
        $this->load->helper('download');
        $servicequote_document = $this->servicequote_document_model->get($servicequote_document_id);

        $contact = $this->contact_model->get($servicequote_document->recipient_contact_id);
        $account = $this->account_model->get($contact->account_id);

        if (!file_exists($servicequote_document->filepath)) {
            add_message('The file '. $servicequote_document->filepath.' does not exist on disk!', 'danger');
            redirect(base_url().'miniant/servicequotes/documents/index/html/'.$servicequote_id);
        }

        $data = file_get_contents($servicequote_document->filepath);
        force_download($this->servicequote_document_model->get_type_string($servicequote_document->type_id).'_SQ'.$servicequote_id.'_'.$account->name.'.pdf', $data);
    }
}
