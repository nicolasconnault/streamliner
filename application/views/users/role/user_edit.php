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
<div id="add_div" class="panel panel-primary">
    <div class="panel-heading"><?=get_title($add_title_options)?></div>
    <div class="panel-body"></div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading"><?=get_title($list_title_options)?></div>
    <div id="userroletable" class="panel-body"></div>
        <table id="ajaxtable" class="table table-bordered table-striped table-hover" style="float: left;">
            <thead>
                <tr>
                    <th class="id">ID</th>
                    <th>First name</th>
                    <th>Last name</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user) : ?>
                <tr>
                    <td><?=$user->id?></td>
                    <td><?=$user->first_name?></td>
                    <td><?=$user->last_name?></td>
                    <td>
                        <a href="<?=base_url()?>users/role/delete_role_user/<?=$role_id?>/<?=$user->id?>">
                            <i class="fa fa-trash-o" title="Remove this user from this role" onclick="return deletethis();"/></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
