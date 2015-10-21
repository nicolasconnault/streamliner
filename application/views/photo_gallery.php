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
// This view is used to create photo galleries of any kind. It's not related to the Upload script, which makes its own call to the blueimp gallery
// Passing the "checkboxes" variable will add a checkbox to each image for selecting/deleting etc. A $unique_id variable can also be given to specify which photos they belong to
?>

<div id="blueimp-gallery<?=$id?>" class="blueimp-gallery blueimp-gallery-controls">
    <div class="slides"></div>
    <h3 class="title"></h3>
    <a class="prev">‹</a>
    <a class="next">›</a>
    <a class="close">×</a>
    <a class="play-pause"></a>
    <ol class="indicator"></ol>
</div>
<div id="photos-<?=$id?>">
    <?php if (!empty($photos)) : ?>
    <table>
        <tr>
        <?php foreach ($photos as $label => $file) : ?>
            <?php if (empty($file['full_size'])) : ?>
                <?php foreach ($file as $label2 => $file2) : ?>
                <td>
                    <a href="<?=$file2['full_size']?>" title="<?=$label?>">
                        <img src="<?=$file2['thumbnail']?>" alt="<?=$label?>">
                    </a>
                </td>
                <?php endforeach; ?>
            <?php else: ?>
            <td>
                <a href="<?=$file['full_size']?>" title="<?=$label?>">
                    <img src="<?=$file['thumbnail']?>" alt="<?=$label?>">
                </a>
            </td>
            <?php endif;?>
        <?php endforeach; ?>
        </tr>
        <?php if (!empty($checkboxes)) : ?>
            <tr class="checkboxes-row">
            <?php foreach ($photos as $label => $file) : ?>
                <?php if (empty($file['full_size'])) : ?>
                    <?php foreach ($file as $label2 => $file2) : ?>
                    <td style="text-align: center">
                        <input type="checkbox" class="photo-checkbox" name="selected_photos<?php if ($id) echo "[$unique_id]"?>[]" value="<?=$file2['relative_path']?>" />
                    </td>
                    <?php endforeach; ?>
                <?php else: ?>
                <td style="text-align: center">
                    <input type="checkbox" class="photo-checkbox" name="selected_photos<?php if ($id) echo "[$unique_id]"?>[]" value="<?=$file['relative_path']?>" />
                </td>
                <?php endif;?>
            <?php endforeach; ?>
            </tr>
        <?php endif; ?>
    </table>
    <?php endif; ?>
</div>
<script>
document.getElementById('photos-<?=$id?>').onclick = function (event) {
    event = event || window.event;
    var target = event.target || event.srcElement,
        link = target.src ? target.parentNode : target,
        options = {index: link, event: event, container: "#blueimp-gallery<?=$id?>"},
        links = this.getElementsByTagName('a');
    blueimp.Gallery(links, options);
};

$(function() {
    $('.photo-checkbox').on('click', function(event) {
        event.stopPropagation();
    });
    $('tr.checkboxes-row').on('click', function(event) {
        event.stopPropagation();
    });
});
</script>
