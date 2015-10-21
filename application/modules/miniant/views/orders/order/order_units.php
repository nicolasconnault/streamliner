<?php
echo '<div id="order_units" class="'.get_tab_panel_class().'">';

if (has_capability('orders:manageunits') && !empty($order_id)) {

    $units_table_disabled = !empty($order_id);
    ?>
        <div class="table-responsive">
        <table id="units_table" class="table table-bordered table-condensed">
            <thead>
                <?php if ($is_installation) : ?>
                    <tr><th>Type</th><th>Unitry Type</th><th>Tenancy/Owner</th><th>Brand</th><th>Area Serving</th><th style="width: 250px" class="actions">Actions</th></tr>
                <?php elseif ($is_repair) : ?>
                    <tr><th>Type</th><th>Tenancy/Owner</th><th>Brand</th><th>Area Serving</th></tr>
                <?php else : ?>
                    <tr><th>Type</th><th>Tenancy/Owner</th><th>Brand</th><th>Area Serving</th><th style="width: 250px" class="actions">Actions</th></tr>
                <?php endif; ?>
            </thead>
            <tbody>
                <?php if (!$is_repair) : ?>
                <tr id="new_unit_row">
                    <td colspan="5">
                    <button <?=($locked) ? 'disabled="disabled"' : ''?> class="btn btn-success" type="button" id="new_unit_button">Add a unit</button>
                    </td>
                </tr>
                <?php endif;?>
            </tbody>
        </table>
        </div>
        <div class="modal" id="task-notes" tabindex="-1" role="dialog" arial-labelledby="task-notes-label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                        </button>
                        <h4 class="modal-title" id="task-notes-label">Task notes</h4>
                    </div>

                    <div class="modal-body">
                        <textarea name="task-notes" cols="60" rows="6" data-task_id=""></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button id="save-task-notes" type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    <?php
}
print_tabbed_form_navbuttons();
?>
    <div id="dialog-confirm" title="Use this site address?" style="display:none">
      <p></p>
    </div>
</div>
