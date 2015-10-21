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
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

        <div class="modal" id="pleaseWaitDialog" data-backdrop="static" data-keyboard="false" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Loading, please wait...</h3>
                    </div>
                    <div class="modal-body">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-info active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                                <span class="sr-only"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- .container-fluid -->
<?php
$jstoload = (empty($jstoload)) ? array() : $jstoload;
$jstoload_final = array_merge(
    array(
        'jquery/datatables/media/js/jquery.dataTables',
        'jquery/jquery.ui',
        'jquery/jquery.ui.touch-punch',
        'jquery/jquery.simplemodal',
        'jquery/jquery.json',
        'jquery/jquery.validate',
        'jquery/jquery.easytabs',
        'jquery/bootstrap-multiselect',
        'jquery/prettify',
        'jquery/select2',
        'tmpl',
        'load-image.all',
        'canvas-to-blob',
        'info-icons',
        'jquery/jquery.blueimp-gallery',
        'jquery/jquery-ui-slideraccess',
        'jquery/jquery-ui-timepicker-addon',
        'jquery/jquery.ui.touch-punch',
        'bootstrap',
        'hopscotch',
        'jquery/jquery.iframe-transport',
        'jquery/jquery.loading',
        'jquery/jquery.fileupload',
        'jquery/jquery.fileupload-process',
        'jquery/jquery.fileupload-image',
        'jquery/jquery.fileupload-audio',
        'jquery/jquery.fileupload-video',
        'jquery/jquery.fileupload-validate',
        'jquery/jquery.fileupload-ui',
        'jquery/jquery.jpanelmenu',
        'jquery/jRespond',
        'jasny-bootstrap',
        'application/file-upload',
        'common',
        'constants',
        'bootstrap-notify',
        'application/messages',
        'event_triggers',
        'jquery/bootstrap-tags',
        'dataTables.bootstrap',
        'final'),
    $jstoload);


?>

<!-- First search in current module if $module has been passed to the view, then in Core files (allows modules to override core JS) -->
    <?php foreach ($jstoload_final as $jsfile): ?>
        <?php if (ENVIRONMENT == 'production') {
            $jsfile .= '.min.js';
        } else {
            $jsfile .= '.js';
        }

        if (!empty($module)) {
            $full_path = base_url()."application/modules/$module/includes/js/$jsfile";
            $abs_path = APPPATH."modules/$module/includes/js/$jsfile";
        }

        if (empty($abs_path) || !file_exists($abs_path) ) {
            $full_path = base_url()."includes/js/$jsfile";
        }

        if (ENVIRONMENT != 'production') {
            $full_path .= '?'.rand(999,9999999);
        }
        ?>
        <script type="text/javascript" src="<?=$full_path?>"> /* <![CDATA[ */ /* ]]> */ </script>
    <?php endforeach; ?>

        <?php if (!empty($jstoloadinfooter)) {
            foreach ($jstoloadinfooter as $jsfile): ?>
          <?php if (ENVIRONMENT == 'production') {
                    $jsfile .= '.min.js';
                } else {
                    $jsfile .= '.js';
                }

                if (!empty($module)) {
                    $full_path = base_url()."application/modules/$module/includes/js/$jsfile";
                    $abs_path = APPPATH."modules/$module/includes/js/$jsfile";
                }

                if (empty($abs_path) || !file_exists($abs_path) ) {
                    $full_path = base_url()."includes/js/$jsfile";
                }

                if (ENVIRONMENT != 'production') {
                    $full_path .= '?'.rand(999,9999999);
                }
                ?>
                <script type="text/javascript" src="<?=$full_path?>"> /* <![CDATA[ */ /* ]]> */ </script>
            <?php endforeach;
        }
        if (!empty($jstoloadforie)) {
            foreach ($jstoloadforie as $jsfile): ?>
                <?php if ($this->config->item('site_type') == 'production') {
                    $jsfile .= '.min.js';
                } else {
                    $jsfile .= '.js';
                }
                ?>
                <!--[if IE]>
                <script type="text/javascript" src="/includes/js/<?=$jsfile?>"> /* <![CDATA[ */ /* ]]> */ </script>
                <![endif]-->
            <?php endforeach;
        }

        // Add tour JS files if site is in demo mode
        $tours = $this->config->item('tours');
        if (ENVIRONMENT == 'demo' && count($tours) > 0) {
            foreach ($tours as $module_name => $module_tours) {
                foreach ($module_tours as $module_tour) {
                    $jsfile = base_url()."application/modules/$module_name/includes/js/tours/tour_$module_tour";
                    if (ENVIRONMENT == 'production') {
                        $jsfile .= '.min.js';
                    } else {
                        $jsfile .= '.js';
                    }
                    echo '<script type="text/javascript" src="'.$jsfile.'"> </script>'."\n";
                }
            }
        }
        ?>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="<?=base_url()?>includes/bower_components/metisMenu/dist/metisMenu.min.js"></script>

    <!-- Morris Charts JavaScript -->
    <script src="<?=base_url()?>includes/bower_components/raphael/raphael-min.js"></script>
    <script src="<?=base_url()?>includes/bower_components/morrisjs/morris.min.js"></script>
    <!--<script src="<?=base_url()?>includes/js/morris-data.js"></script>-->

    <!-- Custom Theme JavaScript -->
    <script src="<?=base_url()?>includes/js/sb-admin-2.js"></script>
    <script type="text/javascript">
    /*<![CDATA[ */
    var display_please_wait_modal = true;
    var caps = <?=json_encode($this->session->userdata('user_caps')) ?>;
    var base_url = '<?=base_url()?>';
    <?php if ($this->session->userdata('user_id')) : ?> var logged_in_user_id = <?=$this->session->userdata('user_id')?>;<?php endif;?>
    var pleaseWaitDiv = $('#pleaseWaitDialog');
    pleaseWaitDiv.modal({show: false});
    pleaseWaitDiv.modal('hide');

    var myApp;
    myApp = myApp || (function () {
        var pleaseWaitDiv = $('#pleaseWaitDialog');
        return {
            showPleaseWait: function() {
                if (display_please_wait_modal) {
                    pleaseWaitDiv.modal('show');
                }
            },
            hidePleaseWait: function () {
                if (display_please_wait_modal) {
                    pleaseWaitDiv.modal('hide');
                }
            },
        };
    })();

    $(document).ajaxStart(function () {
        myApp.showPleaseWait();
    }).ajaxStop(function () {
        myApp.hidePleaseWait();
    }).ajaxError(function () {
        myApp.hidePleaseWait();
    });

    $(function() {
        <?php if (ENVIRONMENT == 'demo') { ?>
            $('button.help').addClass("btn-primary");
            $('button.help').effect("pulsate", { times:3 }, 3000);
        <?php } ?>
        activate_datepickers();
        activate_multiselects();

        $('.btn.unsaved-warning').click(function(event) {
            var confirmed = confirm("Are you sure you want to move away from this form? Any un-saved changes will be lost!");
            if (confirmed == true) {
                return true;
            } else {
                event.preventDefault();
                return false;
            }
        });

        $('.navbar-btn.help').popover({
            trigger: 'hover',
            html: true
        });

        $('i.info-icon').popover({trigger: 'hover'});
        $('.dropdown-add-link').on('click', function() {
            open_popup_form($(this).attr('data-link'), $(this).attr('data-return'));
        });
        $('.dropdown-edit-link').on('click', function() {
            open_popup_form($(this).attr('data-link'), $(this).attr('data-return'));
        });

        setup_conditional_form_elements();

        // Enable bootstrap tooltips
        $(document).tooltip({selector: '[data-toggle="tooltip"]'});

        // Add table-responsive class to dataTables
        $('#ajaxtable_wrapper').addClass('table-responsive');

        // Fire jRespond, set up custom JS for specific screen widths
        var jRes = jRespond([
            {
                label: 'handheld',
                enter: 0,
                exit: 767
            },{
                label: 'tablet',
                enter: 768,
                exit: 979
            },{
                label: 'laptop',
                enter: 980,
                exit: 1199
            },{
                label: 'desktop',
                enter: 1200,
                exit: 10000
            }
        ]);

        jRes.addFunc({
            breakpoint: 'handheld',
            enter: function() {

            },
            exit: function() {

            }
        });

        jRes.addFunc({
            breakpoint: 'tablet',
            enter: function() {

            },
            exit: function() {

            }
        });

        jRes.addFunc({
            breakpoint: 'laptop',
            enter: function() {

            },
            exit: function() {

            }
        });

        jRes.addFunc({
            breakpoint: 'desktop',
            enter: function() {

            },
            exit: function() {

            }
        });

    });
    //]]>
    </script>

</body>
</html>
