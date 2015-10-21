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
/**
 * Given an array of language string indices, retrieves both the English and Chinese version of these strings and arranges them in an array ready to use in PDF documents for either single- or combined-language use.
 */
function prepare_lang_strings($strings=array()) {
    $ci = get_instance();
    $lang_strings = array();

    $english_strings = $ci->lang->load('qc', 'english', true);
    $chinese_strings = $ci->lang->load('qc', 'ch', true);

    foreach ($strings as $string) {
        $lang_strings[$string] = array(QC_SPEC_LANGUAGE_EN => $english_strings[$string],
                                       QC_SPEC_LANGUAGE_CH => '<font face="chinese">' . $chinese_strings[$string].'</font>',
                                       QC_SPEC_LANGUAGE_COMBINED => $english_strings[$string] .
            '<font face="chinese">(' . $chinese_strings[$string].')</font>');
    }

    return $lang_strings;
}
