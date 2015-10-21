<div class="panel panel-primary">
    <div class="panel-heading"><h3>Diagnostic Rules</h3></div>
    <div class="panel-body">
    <ul>
<?php foreach ($tree as $unit_type_id => $unit_type) : ?>
        <li><a href="<?=base_url()?>miniant/orders/steps/view_tree/<?=$unit_type_id?>"><?=$unit_type->unit_type?></a></li>
<?php endforeach; ?>
    </ul>
    </div>
</div>
