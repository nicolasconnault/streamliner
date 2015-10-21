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
class Upload extends MY_Controller {

    public function index($directory=null) {
        $title_options = array('title' => 'test',
                               'help' => 'test',
                               'expand' => 'page',
                               'icons' => array());

        $pageDetails = array('title' => 'test',
                             'title_options' => $title_options,
                             'directory' => $directory,
                             'content_view' => 'upload',
                             'feature_type' => 'Streamliner Core',
                             );

        $this->load->view('template/default', $pageDetails);
    }

    public function upload_file($directory=null, $subdirectory=null, $document_id=null, $module=null) {
        if (empty($directory)) {
            $directory = $this->input->post('directory');
            $subdirectory = $this->input->post('subdirectory');
            $document_id = $this->input->post('document_id');
            $module = $this->input->post('module');
        }

        $full_path = $this->get_full_path($directory, $subdirectory, $document_id).'/';

        $module_prefix = (empty($module)) ? '' : "application/modules/$module/";

        $upload_path_url = base_url() . $module_prefix . 'files/uploads/'.$full_path;

        $config['upload_path'] = FCPATH . $module_prefix . 'files/uploads/'.$full_path;
        if (!file_exists($config['upload_path'])) {
            @mkdir($config['upload_path'], 0777, true);
        }

        if (!file_exists($config['upload_path'])) {
            die('Unable to create the folder '.$config['upload_path']);
        }

        $config['allowed_types'] = $this->get_allowed_file_types();
        $config['max_size'] = '3000000000';

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file')) {
            $existingFiles = get_dir_file_info($config['upload_path']);

            $foundFiles = array();
            $f=0;
            if (!empty($existingFiles)) {
                foreach ($existingFiles as $fileName => $info) {
                  if($fileName!='thumbs'){//Skip over thumbs directory
                    //set the data for the json array
                    $foundFiles[$f]['name'] = $fileName;
                    $foundFiles[$f]['size'] = $info['size'];
                    $foundFiles[$f]['url'] = $upload_path_url . $fileName;
                    $foundFiles[$f]['thumbnailUrl'] = $upload_path_url . 'thumbs/' . $fileName;
                    $foundFiles[$f]['deleteUrl'] = base_url() . "upload/deleteImage/$fileName/$directory/$subdirectory/$document_id";
                    $foundFiles[$f]['deleteType'] = 'DELETE';
                    $foundFiles[$f]['error'] = null;

                    $f++;
                  }
                }
            }
            $this->output->set_content_type('application/json')->set_output(json_encode(array('files' => $foundFiles)));
        } else {
            $data = $this->upload->data();
            /*
             * Array
              (
              [file_name] => png1.jpg
              [file_type] => image/jpeg
              [file_path] => /home/ipresupu/public_html/uploads/
              [full_path] => /home/ipresupu/public_html/uploads/png1.jpg
              [raw_name] => png1
              [orig_name] => png.jpg
              [client_name] => png.jpg
              [file_ext] => .jpg
              [file_size] => 456.93
              [is_image] => 1
              [image_width] => 1198
              [image_height] => 1166
              [image_type] => jpeg
              [image_size_str] => width="1198" height="1166"
              )
             */
            // to re-size for thumbnail images un-comment and set path here and in json array
            $config = array();
            $config['image_library'] = 'gd2';
            $config['source_image'] = $data['full_path'];
            $config['create_thumb'] = TRUE;
            $config['new_image'] = $data['file_path'] . 'thumbs';
            $config['maintain_ratio'] = TRUE;
            $config['thumb_marker'] = '';
            $config['width'] = 75;
            $config['height'] = 50;
            $this->load->library('image_lib', $config);

            if (!file_exists($config['new_image'])) {
                @mkdir($config['new_image'], 0777, true);
            }

            $this->image_lib->resize();


            //set the data for the json array
            $info = new StdClass;
            $info->name = $data['file_name'];
            $info->size = $data['file_size'] * 1024;
            $info->type = $data['file_type'];
            $info->url = $upload_path_url . $data['file_name'];

            // I set this to original file since I did not create thumbs.  change to thumbnail directory if you do = $upload_path_url .'/thumbs' .$data['file_name']
            $info->thumbnailUrl = $upload_path_url . 'thumbs/' . $data['file_name'];
            $info->deleteUrl = base_url() . "upload/deleteImage/{$data['file_name']}/$directory/$subdirectory/$document_id";
            $info->deleteType = 'DELETE';
            $info->error = null;

            $files[] = $info;
            //this is why we put this in the constants to pass only json data
            if (IS_AJAX) {
                echo json_encode(array("files" => $files));
                //this has to be the only data returned or you will get an error.
                //if you don't give this a json array it will give you a Empty file upload result error
                //it you set this without the if(IS_AJAX)...else... you get ERROR:TRUE (my experience anyway)
                // so that this will still work if javascript is not enabled
            } else {
                $file_data['upload_data'] = $this->upload->data();
                $this->load->view('upload/upload_success', $file_data);
            }
        }
    }

    public function deleteImage($file, $directory=null, $subdirectory=null, $document_id=null) {//gets the job done but you might want to add error checking and security

        $full_path = $this->get_full_path($directory, $subdirectory, $document_id) . '/';

        $success = @unlink(FCPATH . 'files/uploads/' .$full_path. $file);
        // $success = @unlink(FCPATH . 'files/uploads/thumbs/' . $file);
        //info to see if it is doing what it is supposed to
        $info = new StdClass;
        $info->sucess = $success;
        $info->path = base_url() . 'files/uploads/' .$full_path. $file;
        $info->file = is_file(FCPATH . 'files/uploads/' .$full_path. $file);

        if (IS_AJAX) {
            //I don't think it matters if this is set but good for error checking in the console/firebug
            echo json_encode(array($info));
        } else {
            //here you will need to decide what you want to show for a successful delete
            $file_data['delete_data'] = $file;
            $this->load->view('admin/delete_success', $file_data);
        }
    }

    private function get_allowed_file_types() {
        return 'png|jpg|jpeg|gif|PNG|JPG|JPEG|GIF';
    }

    private function get_full_path($directory, $subdirectory, $document_id) {
        $full_path = $directory;
        if (!empty($subdirectory)) {
            $full_path .= "/$subdirectory";
        }
        if (!empty($document_id)) {
            $full_path .= "/$document_id";
        }

        return $full_path;
    }
}
