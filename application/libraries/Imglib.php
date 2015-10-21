<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
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
class imglib {
	var $ci;

	const SIZE_TO_FIT = 1;
	const SIZE_NO_LARGER_THAN = 2;

	private $max_width, $max_height, $orig_width, $orig_height, $path;

	public function imglib() {
		$this->ci = get_instance();
	}

	//Deletes an image, including any down-sized/scaled-cropped copies
	public function delete($path) {
		$image_directory = dirname($path);
		$image_name = basename($path);
		$image_ext = substr($image_name, strrpos($image_name, '.')+1);
		$image_name = substr($image_name, 0, strrpos($image_name, '.'));
		$floatCapture = "(-)?[0-9]+(\\.[0-9]+)?";
		//                    original name                    optional section for cropped images                    size
		$pattern = "/^".preg_quote($image_name, '/')."_(".$floatCapture."_".$floatCapture."-".$floatCapture."_)?[0-9]+\\-[0-9]+\\.".preg_quote($image_ext, '/')."$/";

		//echo "Checking for thumbs in: ".$image_directory.'<br>';
		$handle = opendir($image_directory);
		while (false !== ($file = readdir($handle))) {
        	if(is_dir($file)) {
				//echo $file. " is NOT a regular file, skipping.<br>";
				continue;
			}
			//else echo $file. " is a regular file.<br>";

			$name = basename($file);
			$match  = preg_match($pattern, $name);
			if($match > 0) {
				//echo "Unlinking '".$file. "'.<br>";
				@unlink($image_directory.'/'.$file);
			}
    	}

		@unlink($path);
	}

	private function _sizeNoLargerThan(&$true_width, &$true_height) {
		//if both dimentions greater than org, return path.
		if($this->orig_width < $this->max_width && $this->orig_height < $this->max_height) {
			$true_width = $this->orig_width;
			$true_height = $this->orig_height;
			return substr($this->path, 1);
		}

		//compute % difference between desired and actual dimentions, and
		//resize on closest dimention
		if(($this->orig_width / $this->max_width) >  ($this->orig_height / $this->max_height) ) {
			//resize by width
			$true_width = $this->max_width;
			$true_height = floor($this->orig_height * ($this->max_width/$this->orig_width));
		}

		else{
			//resize by height
			$true_height = $this->max_height;
			$true_width = floor($this->orig_width * ($this->max_height/$this->orig_height));
		}

		return null;
	}

	private function _sizeToFit(&$true_width, &$true_height) {
		//if either dimention greater than org, return path.
		if($this->orig_width < $this->max_width || $this->orig_height < $this->max_height) {
			$true_width = $this->orig_width;
			$true_height = $this->orig_height;
			return substr($this->path, 1);
		}

		//compute % difference between desired and actual dimentions, and
		//resize on closest dimention
		if(($this->orig_width / $this->max_width) <  ($this->orig_height / $this->max_height) ) {
			//resize by width
			$true_width = $this->max_width;
			$true_height = floor($this->orig_height * ($this->max_width/$this->orig_width));
		}

		else{
			//resize by height
			$true_height = $this->max_height;
			$true_width = floor($this->orig_width * ($this->max_height/$this->orig_height));
		}

		return null;
	}

	//
	public function size($path, $max_width, $max_height, &$true_width, &$true_height, $sizeStrategy = imglib::SIZE_NO_LARGER_THAN) {
		$this->max_width 	= $max_width;
		$this->max_height = $max_height;
		$this->path		= $path;
		//get the aspect ratio, and the dimentions of the
		//thumb given that, and the maximum dimentions
		$image_dims = @getimagesize($path);
		if($image_dims === false) {
			return null;
		}

		$this->orig_width 	= $image_dims[0];
		$this->orig_height	= $image_dims[1];
		//$aspect_ratio = $orig_width / $orig_height;

		if($sizeStrategy === imglib::SIZE_TO_FIT) {
			$returnPath = $this->_sizeToFit($true_width, $true_height);
			if(is_string($returnPath)) return $returnPath;
		}
		else if($sizeStrategy === imglib::SIZE_NO_LARGER_THAN) {
			$returnPath = $this->_sizeNoLargerThan($true_width, $true_height);
			if(is_string($returnPath)) return $returnPath;
		}
		else {
			return null;
		}


		//check if a file already exists
		$image_directory = dirname($path);
		$image_name = basename($path);
		$image_name = substr($image_name, 0, strrpos($image_name, '.'));
		//echo $image_name;
		$target_file = $image_directory.'/'.$image_name.'_'.$true_width."-".$true_height. substr($path, strrpos($path, '.'));
		if(file_exists($target_file)) {
			return substr($target_file, 1);
		}


		//it doesn't, create one.
		if(preg_match("/\\.png$/", $path)) $gdImage = @imagecreatefrompng($path);
		if(preg_match("/\\.jpg$/", $path) || preg_match("/\\.jpeg$/", $path)) $gdImage = @imagecreatefromjpeg($path);
		if(preg_match("/\\.gif$/", $path)) $gdImage = @imagecreatefromgif($path);

		if($gdImage !== false) {
			$gdThumb = imagecreatetruecolor  ($true_width, $true_height);
			if($gdThumb === false) {
				echo "Couldn't create thumb at ".$true_width."x".$true_height;
				return null;
			}
			imagealphablending($gdThumb, false); //Turn off alphablending to trat alpha channel literlary.
			imagesavealpha  ($gdThumb, true); //Save with alpha channel, not colour-key.
			imagefilledrectangle($gdThumb, 0, 0, $true_width, $true_height, imagecolorallocatealpha($gdThumb, 0, 0, 0, 127));
			$resizeSuccess = imagecopyresampled($gdThumb, $gdImage, 0, 0, 0, 0, $true_width, $true_height, $this->orig_width, $this->orig_height);
			if($resizeSuccess) {
				//Do some sharpening with GD2 (disabled for the mo)
				/*
				$success = imageconvolution($gdThumb, array(array(-1, -1, -1), array(-1, 40, -1) , array(-1, -1, -1)), 32, 0);
				if(!$success) {
					echo "Couldn't sharpen image";
				}*/

				//save
				if(preg_match("/\\.png$/", $target_file)) imagepng($gdThumb, $target_file);
				if(preg_match("/\\.jpg$/", $target_file) || preg_match("/\\.jpeg$/", $target_file)) imagejpeg($gdThumb, $target_file, 90);
				if(preg_match("/\\.gif$/", $target_file)) imagegif($gdThumb, $target_file);
			}
			else {
				echo "Couldn't resize image.";
				return null;
			}
		}
		else {
			echo "Couldn't open: ".$path;
			return null;
		}

		$target_file = substr($target_file, 1);
		return  $target_file;
	}

	/**
	* format: name_<scale>_<offsetX>-<offsetY>_<width>-<height>.ext
	*/
	public function sizeAndCrop($path, $scale, $offsetX, $offsetY, $width, $height) {
		//echo 'Imglib::sizeAndCrop('.$path.')';
		$image_dims = @getimagesize($path);
		if($image_dims === false) {
			return null;
		}

		$orig_width 	= $image_dims[0];
		$orig_height	= $image_dims[1];
		$scaledWidth 	= $orig_width * $scale;
		$scaledHeight 	=  $orig_height * $scale;

		//check if a file already exists
		$image_directory = dirname($path);
		$image_name = basename($path);
		$image_name = substr($image_name, 0, strrpos($image_name, '.'));
		//echo $image_name;
		$target_file = $image_directory.'/'.$image_name.'_'.$scale.'_'.$offsetX."-".$offsetY.'_'.$width."-".$height. substr($path, strrpos($path, '.'));
		//$target_file = $image_directory.'/'.$image_name.'_'.$width."-".$height. substr($path, strrpos($path, '.'));
		if(file_exists($target_file)) {
			return substr($target_file, 1);
		}

		//it doesn't, create one.
		if(preg_match("/\\.png$/i", $path)) $gdImage = @imagecreatefrompng($path);
		if(preg_match("/\\.jpg$/i", $path) || preg_match("/\\.jpeg$/i", $path)) $gdImage = @imagecreatefromjpeg($path);
		if(preg_match("/\\.gif$/i", $path)) $gdImage = @imagecreatefromgif($path);

		if($gdImage !== false) {
			$gdThumb = imagecreatetruecolor  ($width, $height);
			/**
			* For images smaller than the destination area.
			* It fills with transparent white; so PNGs will be transparent, and
			* GIF and JPEG will be white.
			*/
			imagefill($gdThumb, $width-1, $height-1, imagecolorallocatealpha($gdThumb, 255, 255, 255, 127));

			//Translate and scale the image
			imagesavealpha($gdThumb, true);
			$success = imagecopyresampled($gdThumb, $gdImage,
									$offsetX, $offsetY,			 //dest origin
									0, 0,						 //source origin
									$scaledWidth, $scaledHeight, //dest dims
									$orig_width, $orig_height);  //Src dims

			if($success) {
				//Do some sharpening with GD2 (disabled for the mo)

				$success = imageconvolution($gdThumb, array(array(-1, -1, -1), array(-1, 40, -1) , array(-1, -1, -1)), 32, 0);
				if(!$success) {
					echo "Couldn't sharpen image";
				}

				//save
				if(preg_match("/\\.png$/i", $target_file)) imagepng($gdThumb, $target_file);
				if(preg_match("/\\.jpg$/i", $target_file) || preg_match("/\\.jpeg$/i", $target_file)) imagejpeg($gdThumb, $target_file, 90);
				if(preg_match("/\\.gif$/i", $target_file)) imagegif($gdThumb, $target_file);
			}
			else {
				echo "Couldn't resize image.";
			}
		}
		else {
			echo "Couldn't open: ".$path;
		}

		$target_file = substr($target_file, 1);
		return  $target_file;
	}
}
?>
