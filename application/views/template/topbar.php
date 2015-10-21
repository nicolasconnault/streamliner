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
if (empty($wide_layout)) {
?>
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <button id="topbar-toggle" type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        <a class="navbar-brand" href="/home"><?=$this->setting_model->get_value('Site Name')?></a>
      </div>

        <?php if ($this->session->userdata('user_id')) : ?>
        <ul class="nav navbar-top-links navbar-right">
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                    <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
                </a>
                <ul class="dropdown-menu dropdown-user">
                    <li><a href="<?=base_url()?>users/user/edit/<?=$this->session->userdata('user_id')?>"><i class="fa fa-user fa-fw"></i> User Profile</a> </li>
                    <!--<li><a href="#"><i class="fa fa-gear fa-fw"></i> Settings</a> </li>-->
                    <li class="divider"></li>
                    <li><a href="<?=base_url()?>logout"><i class="fa fa-sign-out fa-fw"></i> Logout</a> </li>
                </ul>
                <!-- /.dropdown-user -->
            </li>
        </ul>

        <div class="navbar-default sidebar" role="navigation">
            <div id="sidebar-nav" class="sidebar-nav navbar-collapse">
                <ul class="nav" id="side-menu">
                    <!--
                    <li class="sidebar-search">
                        <div class="input-group custom-search-form">
                            <input type="text" class="form-control" placeholder="Search...">
                            <span class="input-group-btn">
                            <button class="btn btn-default" type="button">
                                <i class="fa fa-search"></i>
                            </button>
                        </span>
                        </div>
                    </li>
                    -->
                    <li>
                        <a href="<?=base_url()?>home"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                    </li>
                    <?=get_dynamic_nav(true)?>
                </ul>
            </div>
            <!-- /.sidebar-collapse -->
        </div>
        <!-- /.navbar-static-side -->
    <?php endif; ?>
    </nav>

<?php } else { ?>

<div class="sidebar-nav navmenu navmenu-default navmenu-fixed-right offcanvas">
  <a class="navmenu-brand" href="/home"><?=$this->setting_model->get_value('Site Name')?></a>
  <ul class="nav navmenu-nav">
    <li>
        <a href="<?=base_url()?>home"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
    </li>
    <?=get_dynamic_nav(true)?>
  </ul>
</div>

<div class="navbar navbar-default">
  <a class="navmenu-brand" href="/home"><?=$this->setting_model->get_value('Site Name')?></a>

  <button type="button" class="navbar-toggle" data-recalc="false" data-placement="right" data-toggle="offcanvas" data-target=".navmenu" data-canvas="body">
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
  </button>
</div>
<?php } ?>
