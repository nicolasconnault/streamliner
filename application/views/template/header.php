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
<?php
$csstoload = (empty($csstoload)) ? array() : $csstoload;
$top_nav_items = get_top_nav_items();
$body_classes = (empty($body_classes)) ? '' : $body_classes;
$body_id = rtrim(preg_replace('/([0-9]*)/', '', str_replace('/', '_', $this->uri->uri_string())), '_');
if (empty($body_id) && !$this->session->userdata('user_id')) {
    $body_id = 'login';
}
$csstoload_final = array_merge(
    $csstoload,
    array(
        // 'bootstrap-theme.min',
        'hopscotch',
        'bootstrap-notify',
        'bootstrap-multiselect',
        'prettify',
        'alert-bangtidy',
        'alert-notification-animations',
        'jquery.ui',
        'jquery.ui.timepicker',
        'jquery.fileupload',
        'jquery.fileupload-ui',
        'jquery.dataTables.yadcf',
        'simplemodal',
        'timeline',
        'sb-admin-2',
        'select2',
        'font-awesome',
        'blueimp-gallery',
        'bootstrap-tags',
        'bootstrap-editable',
        'jasny-bootstrap',
        'dataTables.bootstrap',
        'styles'
    )
);

header('Content-type: text/html; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?=$title?></title>
    <base href="<?=base_url()?>" />
    <meta name="verify-a" value="7388b2656185a863039a">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="content-language" content="english" />
    <meta name="description" content="<?=$this->config->item('Site name')?>" />
    <meta name="keywords" content="admin" />
    <meta name="author" content="SMB Streamline" />
    <meta name="copyright" content="SMB Streamline" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- MetisMenu CSS -->
    <link href="<?=base_url()?>includes/bower_components/metisMenu/dist/metisMenu.min.css" rel="stylesheet">

    <!-- Morris Charts CSS -->
    <link href="<?=base_url()?>includes/bower_components/morrisjs/morris.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="<?=base_url()?>includes/bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <?php if (ENVIRONMENT == 'production') : ?>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
        <script type="text/javascript" src="<?=base_url()?>includes/js/jquery/jquery.min.js"> /* <![CDATA[ */ /* ]]> */ </script>
        <link rel="stylesheet" href="<?=base_url()?>css/get_compressed/<?=implode('~', $csstoload_final)?>" type="text/css" />
        <?php
        if (!empty($wide_layout)) {
            ?><link rel="stylesheet" href="<?=base_url()?>includes/css/wide_layout.css" type="text/css" /><?php
        }
        ?>
    <?php else : ?>
        <link href="<?=base_url()?>includes/css/bootstrap.min.css" rel="stylesheet">
        <script type="text/javascript" src="<?=base_url()?>includes/js/jquery/jquery.js"> /* <![CDATA[ */ /* ]]> */ </script>
        <?php

        foreach ($csstoload_final as $cssfile) {
            if (file_exists(APPPATH."../includes/css/$cssfile.css")) {
            ?>
                <link rel="stylesheet" href="<?=base_url()?>includes/css/<?=$cssfile?>.css" type="text/css" />
            <?php
            } else {
                $modules = scandir(APPPATH.'modules');
                foreach ($modules as $module) {
                    if (in_array($module, array('.', '..'))) {
                        continue;
                    }

                    if (file_exists(APPPATH."modules/$module/includes/css/$cssfile.css")) {
                    ?>
                        <link rel="stylesheet" href="<?=base_url()."application/modules/$module/"?>includes/css/<?=$cssfile?>.css" type="text/css" />
                    <?php
                    }
                }
            }
        }

        if (!empty($wide_layout)) {
            ?><link rel="stylesheet" href="<?=base_url()?>includes/css/wide_layout.css" type="text/css" /><?php
        }

        // Automatically load every module's mod_styles.css file
        $modules = scandir(APPPATH.'modules');
        foreach ($modules as $module) {
            if (in_array($module, array('.', '..'))) {
                continue;
            }

            if (file_exists(APPPATH."modules/$module/includes/css/mod_styles.css")) {
            ?>
                <link rel="stylesheet" href="<?=base_url()."application/modules/$module/"?>includes/css/mod_styles.css" type="text/css" />
            <?php
            }
        }
     endif; ?>

    <!--[if IE]>
      <link rel="stylesheet" type="text/css" href="includes/css/ie.css" />
    <![endif]-->
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script type="text/javascript">
    /*<![CDATA[ */
    var caps = <?php echo json_encode($this->session->userdata('user_caps')) ?>;
    //]]>
    </script>
</head>
<body id="<?=(isset($body_id)) ? $body_id : "home"?>" class="<?=$body_classes?>">
    <div id="wrapper">
