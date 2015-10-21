<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_reorder_document_status_types extends CI_Migration {

    public function up() {
        $this->load->model('document_types_statuses_model');
        $this->document_types_statuses_model->edit(1  ,array('sortorder' => 1));
        $this->document_types_statuses_model->edit(2  ,array('sortorder' => 2));
        $this->document_types_statuses_model->edit(3  ,array('sortorder' => 3));
        $this->document_types_statuses_model->edit(4  ,array('sortorder' => 21));
        $this->document_types_statuses_model->edit(5  ,array('sortorder' => 8));
        $this->document_types_statuses_model->edit(6  ,array('sortorder' => 11));
        $this->document_types_statuses_model->edit(7  ,array('sortorder' => 12));
        $this->document_types_statuses_model->edit(8  ,array('sortorder' => 13));
        $this->document_types_statuses_model->edit(9  ,array('sortorder' => 20));
        $this->document_types_statuses_model->edit(10 ,array('sortorder' => 16));
        $this->document_types_statuses_model->edit(11 ,array('sortorder' => 14));
        $this->document_types_statuses_model->edit(12 ,array('sortorder' => 10));
        $this->document_types_statuses_model->edit(13 ,array('sortorder' => 17));
        $this->document_types_statuses_model->edit(14 ,array('sortorder' => 18));
        $this->document_types_statuses_model->edit(15 ,array('sortorder' => 6));
        $this->document_types_statuses_model->edit(16 ,array('sortorder' => 7));
        $this->document_types_statuses_model->edit(17 ,array('sortorder' => 4));
        $this->document_types_statuses_model->edit(18 ,array('sortorder' => 5));
        $this->document_types_statuses_model->edit(19 ,array('sortorder' => 9));
        $this->document_types_statuses_model->edit(20 ,array('sortorder' => 19));
        $this->document_types_statuses_model->edit(21 ,array('sortorder' => 15));
        $this->document_types_statuses_model->edit(22 ,array('sortorder' => 1));
        $this->document_types_statuses_model->edit(23 ,array('sortorder' => 1));
        $this->document_types_statuses_model->edit(24 ,array('sortorder' => 2));
        $this->document_types_statuses_model->edit(25 ,array('sortorder' => 14));
        $this->document_types_statuses_model->edit(26 ,array('sortorder' => 1));
        $this->document_types_statuses_model->edit(27 ,array('sortorder' => 4));
        $this->document_types_statuses_model->edit(28 ,array('sortorder' => 3));
        $this->document_types_statuses_model->edit(29 ,array('sortorder' => 13));
        $this->document_types_statuses_model->edit(30 ,array('sortorder' => 17));
        $this->document_types_statuses_model->edit(31 ,array('sortorder' => 3));
        $this->document_types_statuses_model->edit(32 ,array('sortorder' => 9));
        $this->document_types_statuses_model->edit(33 ,array('sortorder' => 10));
        $this->document_types_statuses_model->edit(34 ,array('sortorder' => 6));
        $this->document_types_statuses_model->edit(35 ,array('sortorder' => 8));
        $this->document_types_statuses_model->edit(36 ,array('sortorder' => 5));
        $this->document_types_statuses_model->edit(37 ,array('sortorder' => 7));
        $this->document_types_statuses_model->edit(38 ,array('sortorder' => 11));
        $this->document_types_statuses_model->edit(39 ,array('sortorder' => 10));
        $this->document_types_statuses_model->edit(40 ,array('sortorder' => 2));
        $this->document_types_statuses_model->edit(41 ,array('sortorder' => 3));
        $this->document_types_statuses_model->edit(42 ,array('sortorder' => 4));
        $this->document_types_statuses_model->edit(43 ,array('sortorder' => 5));
        $this->document_types_statuses_model->edit(44 ,array('sortorder' => 1));
        $this->document_types_statuses_model->edit(45 ,array('sortorder' => 1));
        $this->document_types_statuses_model->edit(46 ,array('sortorder' => 2));
        $this->document_types_statuses_model->edit(47 ,array('sortorder' => 14));
        $this->document_types_statuses_model->edit(48 ,array('sortorder' => 13));
        $this->document_types_statuses_model->edit(49 ,array('sortorder' => 4));
        $this->document_types_statuses_model->edit(50 ,array('sortorder' => 5));
        $this->document_types_statuses_model->edit(51 ,array('sortorder' => 6));
        $this->document_types_statuses_model->edit(52 ,array('sortorder' => 15));
        $this->document_types_statuses_model->edit(53 ,array('sortorder' => 8));
        $this->document_types_statuses_model->edit(54 ,array('sortorder' => 9));
        $this->document_types_statuses_model->edit(55 ,array('sortorder' => 10));
        $this->document_types_statuses_model->edit(56 ,array('sortorder' => 7));
        $this->document_types_statuses_model->edit(57 ,array('sortorder' => 3));
        $this->document_types_statuses_model->edit(58 ,array('sortorder' => 11));
        $this->document_types_statuses_model->edit(59 ,array('sortorder' => 12));
        $this->document_types_statuses_model->edit(60 ,array('sortorder' => 1));
        $this->document_types_statuses_model->edit(61 ,array('sortorder' => 15));
        $this->document_types_statuses_model->edit(62 ,array('sortorder' => 16));
        $this->document_types_statuses_model->edit(63 ,array('sortorder' => 7));
        $this->document_types_statuses_model->edit(64 ,array('sortorder' => 3));
        $this->document_types_statuses_model->edit(65 ,array('sortorder' => 4));
        $this->document_types_statuses_model->edit(66 ,array('sortorder' => 8));
        $this->document_types_statuses_model->edit(67 ,array('sortorder' => 5));
        $this->document_types_statuses_model->edit(68 ,array('sortorder' => 1));
        $this->document_types_statuses_model->edit(69 ,array('sortorder' => 2));
        $this->document_types_statuses_model->edit(70 ,array('sortorder' => 6));
        $this->document_types_statuses_model->edit(71 ,array('sortorder' => 12));
    }

    public function down() {

    }
}
