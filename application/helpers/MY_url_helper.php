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
 * Returns a text link that sends data by POST (using a form) instead of an <a> tag
 * @param string $url
 * @param string $text
 * @param array $params POST parameters
 * @return string
 */
function anchor_post($url, $text, $params) {
    $id = substr(md5(time()), 5);
    $html = '<form method="post" id="'.$id.'" action="'.$url.'"><div onclick="$(\'#'.$id.'\').submit();" class="anchorpost">'."\n";
    foreach ($params as $key => $val) {
        $html .= '<input type="hidden" name="'.$key.'" value="'.$val.'" />'."\n";
    }
    $html .= $text.'</div></form>';
    return $html;
}
