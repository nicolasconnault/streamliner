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
abstract class DatagridActionIcon {
    protected $controller_folder;
    protected $module;
    protected $uri_segment_1;
    protected $uri_segment_2;
    protected $url_param;
    protected $row_action_capabilities;

    protected function __construct($label, $module=null, $uri_segment_1, $uri_segment_2, $row_action_capabilities=array(), $url_param=false) {
        $this->row_action_capabilities = $row_action_capabilities;
        $this->uri_segment_1 = $uri_segment_1;
        $this->uri_segment_2 = $uri_segment_2;
        $this->url_param = $url_param;
        $this->controller_folder = ($this->uri_segment_1 == 'site') ? '' : "$this->uri_segment_1/";
        $this->controller_folder = (empty($module)) ? $this->controller_folder : $module.'/'.$this->controller_folder;
    }

    static public function get_instance($action, $label, $module=null, $uri_segment_1, $uri_segment_2, $row_action_capabilities=array(), $url_param=false) {
        $classname = "DatagridActionIcon$action";
        if (array_key_exists($action, $row_action_capabilities) && !(has_capability($row_action_capabilities[$action]))) {
            return null;
        }

        if (class_exists($classname)) {
            return new $classname($label, $module, $uri_segment_1, $uri_segment_2, $row_action_capabilities, $url_param);
        } else {
            return new DatagridActionIconDefault($label, $module, $uri_segment_1, $uri_segment_2, $row_action_capabilities, $url_param);
        }
    }

    public static function getHTML($action, $label, $module=null, $uri_segment_1, $uri_segment_2, $row_action_capabilities=array(), $url_param=false) {
        $icon = self::get_instance($action, $label, $module, $uri_segment_1, $uri_segment_2, $row_action_capabilities, $url_param);
        if (!empty($icon)) {
            return $icon->_getHTML($label, $action);
        }
    }

    public function is_authorised($cap_type, $action_type) {
        $ci = get_instance();
        $result = has_capability("$this->uri_segment_1:$cap_type" . $ci->inflector->pluralize($this->uri_segment_2)) ||
            (array_key_exists($action_type, $this->row_action_capabilities) && has_capability($this->row_action_capabilities[$action_type]));
        return $result;
    }
}

class DatagridActionIconPDF extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'PDF' : $label;
        if ($this->is_authorised('view', 'pdf')) {
            return '<a class="action-link btn btn-primary" href="'.base_url().$this->controller_folder.'export/export_'.$this->uri_segment_2.'/pdf/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
                                     . '<i class="action-icon fa fa-file-pdf-o"></i> <span class="label">Download</label>'
                                     . '</a>';
        }
    }
}

class DatagridActionIconEdit extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'Edit' : $label;
        if ($this->is_authorised('edit', 'edit')) {
            return '<a class="action-link edit" href="'.base_url().$this->controller_folder.$this->uri_segment_2.'/edit/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
                                     . '<i class="action-icon fa fa-pencil"></i> <span class="label">'.$formatted_label.'</span>'
                                     . '</a>';
        }
    }
}

class DatagridActionIconView extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'View' : $label;
        if ($this->is_authorised('view', 'view')) {
            return '<a class="action-link view" href="'.base_url().$this->controller_folder.$this->uri_segment_2.'/edit/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-eye" title="View this record"></i> <span class="label">'.$formatted_label.'</span>'
                 . '</a>';
        }
    }
}

class DatagridActionIconDelete extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'Delete' : $label;
        if ($this->is_authorised('delete', 'delete')) {
            return '<a class="action-link delete" href="'.base_url().$this->controller_folder.$this->uri_segment_2.'/delete/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-trash-o" title="Completely delete this '.$this->uri_segment_2.' and all associated records" onclick="return deletethis();"></i> <span class="label">'.$formatted_label.'</span>'
                 . '</a>';
        }
    }
}

class DatagridActionIconDuplicate extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'Duplicate' : $label;
        if ($this->is_authorised('write', 'duplicate')) {
            return '<a class="action-link duplicate" href="'.base_url().$this->controller_folder.$this->uri_segment_2.'/duplicate/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-copy"></i> <span class="label">'.$formatted_label.'</span>'
                 . '</a>';
        }
    }
}

class DatagridActionIconUser_edit extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'Edit users' : $label;
        return '<a class="action-link user_edit" href="'.base_url().$this->controller_folder.$this->uri_segment_2.'/user_edit/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-user"></i> <span class="label">'.$formatted_label.'</span>'
                 . '</a>';
    }
}

class DatagridActionIconKey extends DatagridActionIcon {
    protected function _getHTML($label, $action) {
        $formatted_label = (is_int($label)) ? $action : $label;
        return '<a class="action-link '.$action.'" href="'.base_url().$this->controller_folder.$this->uri_segment_2.'/'.$action.'/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
               . '<i class="action-icon fa fa-'.$action.'"></i> <span class="label">'.$formatted_label.'</span>'
               . '</a>';
    }
}

class DatagridActionIconFlag extends DatagridActionIcon {
    protected function _getHTML($label, $action) {
        $formatted_label = (is_int($label)) ? $action : $label;
        return '<a class="action-link '.$action.'" href="'.base_url().$this->controller_folder.$this->uri_segment_2.'/statuses/%d/'.$this->url_param.'" title="Edit status triggers">'
               . '<i class="action-icon fa fa-'.$action.'"></i> <span class="label">'.$formatted_label.'</span>'
               . '</a>';
    }
}

class DatagridActionIconStatuses extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'Edit Statuses' : $label;
        return '<a class="action-link statuses" href="'.base_url().$this->controller_folder.$this->uri_segment_2.'/edit_statuses/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-check-square-o"></i> <span class="label">'.$formatted_label.'</span>'
                 . '</a>';
    }
}

class DatagridActionIconTriggers extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'Edit Triggers' : $label;
        return '<a class="action-link triggers" href="'.base_url().$this->controller_folder.$this->uri_segment_2.'/edit_triggers/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-bell"></i> <span class="label">'.$formatted_label.'</span>'
                 . '</a>';
    }
}

class DatagridActionIconHistory extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'View history' : $label;
        return '<a class="action-link history" href="'.base_url().$this->controller_folder.$this->uri_segment_2.'/history/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-clock-o"></i> <span class="label">'.$formatted_label.'</span>'
                 . '</a>';
    }
}

class DatagridActionIconDefault extends DatagridActionIcon {
    protected function _getHTML($label) {
        $formatted_label = (is_int($label)) ? 'Edit permissions' : $label;
        return '<a class="action-link edit-permissions" href="'.base_url().$this->controller_folder.$this->uri_segment_2.'/capabilities/%d/'.$this->url_param.'" title="'.$formatted_label.'">'
                 . '<i class="action-icon fa fa-key"></i> <span class="label">'.$formatted_label.'</span>'
                 . '</a>';
    }
}
