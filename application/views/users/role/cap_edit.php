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
<div id="add_div"  class="panel panel-primary">
    <div class="panel-heading"><?=get_title($add_title_options)?></div>
    <div class="panel-body">
        <ul id="assignable">
        <?php print_recursive_list($assignable_caps, 'label', 'children', null, "users/role/add_cap_to_role/$role_id/", 'id'); ?>
        </ul>
    </div>
</div>
<div class="panel panel-primary">
<div class="panel-heading"><?=get_title($list_title_options)?></div>
    <div class="panel-body"></div>
        <table id="ajaxtable" class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>Cap ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Included capabilities</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Cap ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Included capabilities</th>
                    <th>Actions</th>
                </tr>
            </tfoot>
            <tbody>
            <?php foreach ($capabilities as $cap) : ?>
                <tr>
                    <td valign="top"><?=$cap->id?></td>
                    <td valign="top"><?=$cap->name?></td>
                    <td valign="top"><?=$cap->label?></td>
                    <td><ul>
                    <?php foreach ($dependencies[$cap->id] as $dependent_cap) : ?>
                        <li><?=$dependent_cap->label?></li>
                    <?php endforeach; ?>
                    </ul></td>
                    <td valign="top">
                        <a href="<?=base_url()?>users/role/delete_role_cap/<?=$role_id?>/<?=$cap->id?>">
                            <i class="fa fa-trash-o" title="Remove this capability from this role" onclick="return deletethis();"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div id="add_div"></div>
</div>
