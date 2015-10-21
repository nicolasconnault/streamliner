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
<?php if (has_capability('users:assignroles')) { ?>
<div class="panel panel-primary">
    <div class="panel-heading"><?=get_title($add_title_options)?></div>
    <div class="panel-body">
        <?=print_dropdown_element( array(
            'name' => 'new_role',
            'label' => 'New role',
            'options' => $available_roles,
            'required' => false,
            'extra_html' => array('onchange' => "window.location='".base_url()."users/user/add_role/".$user_id."/'+this.value;")))?>
    </div>
</div>
<?php } ?>

<div class="panel panel-primary" id="roles">
    <div class="panel-heading"><?=get_title($roles_title_options)?></div>
    <div class="panel-body">
        <div id="userroletable">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Cap ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($user_roles as $role) : ?>
                    <tr>
                        <td valign="top"><?=$role->id?></td>
                        <td valign="top"><?=$role->name?></td>
                        <td valign="top"><?=$role->description?></td>
                        <td valign="top">
                        <?php if (has_capability('users:unassignroles')) { ?>
                            <a href="/users/user/delete_user_role/<?=$user_id?>/<?=$role->id?>">
                                <i class="fa fa-trash-o" onclick="return deletethis();"></i>
                            </a>
                        </td>
                        <?php } ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="panel panel-primary" id="capabilities">
    <div class="panel-heading"><?=get_title($capabilities_title_options)?></div>
    <div class="panel-body">
        <table id="usercaplist" class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>Cap ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Enabled</th>
                    <?php if (has_capability('users:assignroles')) { ?>
                        <th>Roles giving this capability (click to add)</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($all_caps as $cap) : ?>
                <tr class="<?=(in_array($cap->name, $user_capabilities)) ? 'enabled' : 'disabled'?>">
                    <td><?=$cap->id?></td>
                    <td><?=$cap->name?></td>
                    <td><?=$cap->label?></td>
                    <td><?=(in_array($cap->name, $user_capabilities)) ? 'Yes' : 'No'?></td>
                    <?php if (has_capability('users:assignroles')) { ?>
                    <td>
                        <?php if (!in_array($cap->name, $user_capabilities)) : ?>
                        <ul>
                            <?php foreach ($cap_roles[$cap->id] as $role_id => $role) : ?>
                                <?php if (array_key_exists($role_id, $available_roles)) : ?>
                                <li><a href="/users/user/add_role/<?=$user_id?>/<?=$role_id?>" title="Click to add this role to this user"><?=$role->name?></a></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </td>
                    <?php } ?>
            <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>
