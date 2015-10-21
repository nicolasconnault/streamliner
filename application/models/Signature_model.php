<?php
require_once(APPPATH.'/core/has_type_trait.php');
class Signature_Model extends MY_Model {
    use has_types;
    public $table = 'signatures';

    public function get_image($signature_id) {
        $signature = $this->get($signature_id);
        $this->load->helper('signature');
        /*
         *  @param string|array $json
         *  @param array $options OPTIONAL; the options for image creation
         *    imageSize => array(width, height)
         *    bgColour => array(red, green, blue) | transparent
         *    penWidth => int
         *    penColour => array(red, green, blue)
         *    drawMultiplier => int
         */
        $image = sigJsonToImage($signature->signature_text, array());
        $temp_img = '/tmp/sig_png_'.rand(0,543435).'.png';
        imagepng($image, $temp_img);
        return $temp_img;
    }
}

