<?php
/*
 * Copyright 2015 SMB Streamline
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

function get_photos($directory, $subdirectory=null, $document_id=null, $module=null) {
    $ci = get_instance();
    $relative_path = "files/uploads/$directory";
    $dir = FCPATH . $relative_path;
    $url = base_url() . $relative_path;

    if (!empty($module)) {
        $relative_path = "application/modules/$module/files/uploads/$directory";
        $dir = FCPATH . $relative_path;
        $url = base_url() . $relative_path;
    }


    if (!empty($subdirectory)) {
        $relative_path .= "/$subdirectory";
        $dir .= "/$subdirectory";
        $url .= "/$subdirectory";
    }

    if (!empty($document_id)) {
        $relative_path .= "/$document_id";
        $dir .= "/$document_id";
        $url .= "/$document_id";
    }

    if (!is_readable($dir)) return NULL;

    $handle = opendir($dir);
    $photos = array();

    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && $entry != 'thumbs' && !is_dir($dir.'/'.$entry)) {
            $photos[] = array('full_size' => $url.'/'.$entry, 'thumbnail' => $url.'/thumbs/'.$entry, 'relative_path' => $relative_path."/$entry");
        } else if ($entry != "." && $entry != ".." && $entry != 'thumbs' && is_dir($dir.'/'.$entry)) {
            $deep_handle = opendir($dir.'/'.$entry);
            while (false !== ($deep_entry = readdir($deep_handle))) {
                if ($deep_entry != "." && $deep_entry != ".." && $deep_entry != 'thumbs') {
                    $photos[] = array('full_size' => $url.'/'.$entry.'/'.$deep_entry, 'thumbnail' => $url.'/'.$entry.'/thumbs/'.$deep_entry, 'relative_path' => $relative_path."/$entry");
                }
            }
        }
    }

    return $photos;

}
