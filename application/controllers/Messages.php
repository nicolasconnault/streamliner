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
class Messages extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->config->set_item('replacer', array('messages' => array('index|Messages')));
        $this->config->set_item('exclude', array('index'));

        // Being a global controller, messages doesn't need its second-level segment to be hidden
        $this->config->set_item('exclude_segment', array());
    }

    public function browse($outputtype='html') {
        return $this->index($outputtype);
    }

    public function save_message() {
        $params = array(
            'document_id '=> $this->input->post('document_id'),
            'document_type' => $this->input->post('document_type'),
            'message' => $this->input->post('message'),
            'id' => $this->input->post('message_id'),
            'author_id' => $this->session->userdata('user_id')
        );

        if (empty($params['id'])) {
            if (!($params['id'] = $this->message_model->add($params))) {
                send_json_message('The message could not be saved', 'danger');
            } else {
                send_json_message('The message has been successfully saved', 'success', array('message_id' => $params['id']));
            }
        } else {
            unset($params['author_id']); // Don't change the original author!
            $message = $this->message_model->get($params['id']);

            if ($message->author_id != $this->session->userdata('user_id') && !has_capability('orders:editothermessages')) {
                send_json_message('You are not authorised to edit another person\'s notes!', 'warning');
            } else {
                if (!$this->message_model->edit($params['id'], $params)) {
                    send_json_message('The message could not be saved', 'danger');
                } else {
                    send_json_message('The message has been successfully saved', 'success');
                }
            }
        }
    }

    public function remove_message($message_id) {
        $message = $this->message_model->get($message_id);
        if ($message->author_id != $this->session->userdata('user_id') && !has_capability('orders:deleteothermessages')) {
            send_json_message('You are not authorised to delete another person\'s notes!', 'warning');
        } else {
            $this->message_model->delete($message_id);
            send_json_message('The message was deleted');
        }
    }

    public function get_data($message_id) {
        $message = $this->message_model->get($message_id);
        $message->identifier = $message->document_type.'_'.$message->document_id;
        echo json_encode($message);
    }

    public function get_messages() {
        $document_id = $this->input->post('document_id');
        $document_type = $this->input->post('document_type');

        send_json_data(array('messages' => $this->message_model->get_with_author_names(compact('document_id', 'document_type'))));
    }
}
