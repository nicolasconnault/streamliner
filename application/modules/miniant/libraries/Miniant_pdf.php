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
require(APPPATH.'libraries/Pdf.php');

class miniant_pdf extends pdf {
	public function Header() {
		if ($this->header_xobjid === false) {
			// start a new XObject Template
			$this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
			$headerfont = $this->getHeaderFont();
			$headerdata = $this->getHeaderData();

			$this->y = $this->header_margin;
            $this->x = $this->original_lMargin;

            $this->Image(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);

			$cell_height = $this->getCellHeight($headerfont[2] / $this->k);
			// set starting margin for text data cell
            $header_x = $this->original_lMargin + ($headerdata['logo_width'] * 1.1);

            $cw = ($this->w - $this->original_lMargin - $this->original_rMargin - ($headerdata['logo_width'] * 1.1)) / 2;
			$this->SetTextColorArray($this->header_text_color);
            $this->setCellHeightRatio(1.95);

            $this->SetFont($this->_config['page_font'], '', $this->_config['page_font_size']-2);
            $company_description = '<p>REFRIGERATION<br />AIR CONDITIONING<br />&amp; MECHANICAL SERVICES</p>';
			$this->writeHTMLCell(60, 20, $header_x+5, $this->y+2, $company_description, 0, 0, false, false, 'C');

            $address = '
                <p>PO BOX 280, SOUTH FREMANTLE<br />
                WESTERN AUSTRALIA 6162<br />
                ADMINISTRATION: (08) 9418 5388<br />
                FAX: (08) 9418 5344<br />
                admin@temperaturesolutions.net.au<br />
                http://tempsol.net.au</p>
                ';
			$this->writeHTMLCell(60, 30, $header_x+71, $this->y, $address, 0, 0, false, true, 'R');

            // Company ABN/ACN data
            $company_data = '<p>ACN 068 713 557 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ABN 55638 487 669</p>';
			$this->writeHTMLCell(60, 20, 20, $this->y+21, $company_data, 0, 0, false, true, 'L');

            // Document title
            $this->SetFont($this->_config['header_font'], '', $this->_config['header_font_size']);
			$this->writeHTMLCell(210, 30, 0, $this->y+5, $this->_config['header_title'], 0, 0, false, false, 'C');
			$this->endTemplate();
		}
		// print header template
		$x = 0;
		$dx = 0;
		if (!$this->header_xobj_autoreset AND $this->booklet AND (($this->page % 2) == 0)) {
			// adjust margins for booklet mode
			$dx = ($this->original_lMargin - $this->original_rMargin);
		}
        $x = 0 + $dx;

        $this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
		if ($this->header_xobj_autoreset) {
			// reset header xobject template at each page
			$this->header_xobjid = false;
		}
	}

    public function writeDocumentTitle($title) {
        $this->SetFont($this->_config['page_font'], 'I', $this->_config['page_font_size']+2);
        $this->setY(50, true, true);
        $this->setX($this->original_lMargin-5, true, true);
        $this->Cell(0, 2, $title, 0, 0, 'C', false, false, 0, true);
        $this->setY(60);
        $this->SetFont($this->_config['page_font'], '', $this->_config['page_font_size']);
    }
}
