<?php
class Unit_attachment_Model extends MY_Model {

    public $table = 'miniant_unit_attachments';

    public function delete($attachment_id) {
        $attachment = $this->get($attachment_id);
        unlink($this->config->item('files_path').'/'.$attachment->directory.'/'.$attachment->hash);
        return parent::delete($attachment_id);
    }
}
