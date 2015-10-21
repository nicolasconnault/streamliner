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
?>
<table cellspacing="0" cellpadding="10" border="0">
    <thead>
        <tr>
        <?php for ($i = 0; $i < count($headings); $i++) : ?>
            <th <?=$style?> width: <?=$widths[$i]?>px;"><strong><?=($i == 0) ? reset($headings) : next($headings)?></strong></th>
        <?php endfor; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $i => $row) : ?>
            <tr style="background-color: <?php echo ($i % 2) ? '#eee' : '#fff'?>;">
            <?php foreach ($row as $index => $value) : ?>
                <td border="0" style="width:<?=$widths[$index]?>px;"><?=stripslashes($value)?></td>
            <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
