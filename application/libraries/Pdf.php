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


# override the default TCPDF config file
if(!defined('K_TCPDF_EXTERNAL_CONFIG')) {
	define('K_TCPDF_EXTERNAL_CONFIG', TRUE);
}
if (!defined('K_CELL_HEIGHT_RATIO')) {
    define('K_CELL_HEIGHT_RATIO', 2.5);
}
if (!defined('K_TCPDF_CALLS_IN_HTML')) {
    define('K_TCPDF_CALLS_IN_HTML', true);
}
if (!defined('K_PATH_IMAGES')) {
    define('K_PATH_IMAGES', '');
}

# include TCPDF
require(APPPATH.'config/tcpdf'.EXT);
require_once($tcpdf['base_directory'].'/tcpdf.php');



/************************************************************
 * TCPDF - CodeIgniter Integration
 * Library file
 * ----------------------------------------------------------
 * @author Jonathon Hill http://jonathonhill.net
 * @version 1.0
 * @package tcpdf_ci
 ***********************************************************/
class pdf extends TCPDF {


	/**
	 * TCPDF system constants that map to settings in our config file
	 *
	 * @var array
	 * @access private
	 */
	private $cfg_constant_map = array(
		'K_PATH_MAIN'	=> 'base_directory',
		'K_PATH_URL'	=> 'base_url',
		'K_PATH_FONTS'	=> 'fonts_directory',
		'K_PATH_CACHE'	=> 'cache_directory',
		'K_PATH_IMAGES'	=> 'image_directory',
		'K_BLANK_IMAGE' => 'blank_image',
		'K_SMALL_RATIO'	=> 'small_font_ratio',
		'K_CELL_HEIGHT_RATIO'	=> 'cell_height_ratio',
	);


	/**
	 * Settings from our APPPATH/config/tcpdf.php file
	 *
	 * @var array
	 * @access private
	 */
	public $_config = array();


	/**
	 * Initialize and configure TCPDF with the settings in our config file
	 *
	 */
	function __construct($params) {

		# load the config file
		require(APPPATH.'config/tcpdf'.EXT);
		$this->_config = $tcpdf;
		unset($tcpdf);

        foreach ($params as $key => $value) {
            $this->_config[$key] = $value;
        }

		# set the TCPDF system constants
		foreach($this->cfg_constant_map as $const => $cfgkey) {
			if(!defined($const)) {
				define($const, $this->_config[$cfgkey]);
				#echo sprintf("Defining: %s = %s\n<br />", $const, $this->_config[$cfgkey]);
			}
		}

		# initialize TCPDF
		parent::__construct(
			$this->_config['page_orientation'],
			$this->_config['page_unit'],
			$this->_config['page_format'],
			$this->_config['unicode'],
			$this->_config['encoding'],
			$this->_config['enable_disk_cache']
		);


		# language settings
		if(is_file($this->_config['language_file'])) {
			include($this->_config['language_file']);
			$this->setLanguageArray($l);
			unset($l);
		}

		# margin settings
		$this->SetMargins($this->_config['margin_left'], $this->_config['margin_top'], $this->_config['margin_right']);

		# header settings
		$this->print_header = $this->_config['header_on'];
		#$this->print_header = FALSE;
		$this->setHeaderFont(array($this->_config['header_font'], '', $this->_config['header_font_size']));
		$this->setHeaderMargin($this->_config['header_margin']);
		$this->SetHeaderData(
			$this->_config['header_logo'],
			$this->_config['header_logo_width'],
			$this->_config['header_title'],
			$this->_config['header_string']
		);

		# footer settings
		$this->print_footer = $this->_config['footer_on'];
		$this->setFooterFont(array($this->_config['footer_font'], '', $this->_config['footer_font_size']));
		$this->setFooterMargin($this->_config['footer_margin']);

		# page break
		$this->SetAutoPageBreak($this->_config['page_break_auto'], $this->_config['footer_margin']);

		# cell settings
		$this->cMargin = $this->_config['cell_padding'];
		$this->setCellHeightRatio($this->_config['cell_height_ratio']);

		# document properties
		$this->author = $this->_config['author'];
		$this->creator = $this->_config['creator'];

		# font settings
		$this->SetFont($this->_config['page_font'], '', $this->_config['page_font_size']);

		# image settings
		$this->imgscale = $this->_config['image_scale'];

	}

    function moveY($value=1) {
        $this->setY($this->getY() + $value);
    }

    function call_method($method, $params=array()) {
        return '<tcpdf method="'.$method.'" params="' . urlencode(serialize($params)) . '" />';
    }

    function horizontal_table($data, $thwidth, $tdwidth, $cellpadding=8, $border=1) {
        $table = '<table cellpadding="'.$cellpadding.'" border="'.$border.'">';

        foreach ($data as $label => $value) {
            $table .= '<tr>
                <th width="'.$thwidth.'" style="text-align: right"><strong>'.$label.'</strong></th>
                <td width="'.$tdwidth.'">'.$value.'</td>
            </tr>';
        }

        $table .= '</table>';

        return $table;
    }

    public function print_heading($heading, $line_breaks_before=0, $font_style='B', $font_size=null, $font_family=null, $background_color="#CCC") {

        $current_font_family = $this->getFontFamily();
        $current_font_style = $this->getFontStyle();
        $current_font_size = $this->getFontSizePt();

        if (is_null($font_size)) {
            $font_size = $this->_config['page_font_size'];
        }
        if (is_null($font_family)) {
            $font_family = $this->_config['page_font'];
        }

        $this->SetFont($font_family, $font_style, $font_size);
        $html = '';
        for ($i = 0; $i < $line_breaks_before; $i++) {
            $html .= '<br />';
        }
        $html .= '<table cellpadding="8"><tr><td style="background-color: '.$background_color.'">'.$heading.'</td></tr></table><br /><br />';
        $this->writeHTML($html, false, true, false, false, '');
        $this->SetFont($current_font_family, $current_font_style, $current_font_size);
    }

    public function print_paragraph($paragraph, $line_breaks_before=0, $font_style='', $font_size=null, $font_family=null, $background_color="#CCC") {
        $current_font_family = $this->getFontFamily();
        $current_font_style = $this->getFontStyle();
        $current_font_size = $this->getFontSizePt();

        if (is_null($font_size)) {
            $font_size = $this->_config['page_font_size'];
        }
        if (is_null($font_family)) {
            $font_family = $this->_config['page_font'];
        }

        $this->SetFont($font_family, $font_style, $font_size);
        $html = '';
        for ($i = 0; $i < $line_breaks_before; $i++) {
            $html .= '<br />';
        }

        $html .= "<span>$paragraph</span><br /><br />";
        $this->writeHTML($html, false, false, false, false, '');
        $this->SetFont($current_font_family, $current_font_style, $current_font_size);
    }
}
