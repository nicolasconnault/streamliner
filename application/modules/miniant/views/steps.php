<div class="tree well">
    <ul id="unit_types">

    <?php foreach ($tree as $unit_type_id => $unit_type) : ?>
        <li id="unit_type_<?=$unit_type_id?>">
            <span class="unit_type"><i class="fa fa-cog"></i> <?=$unit_type->unit_type?></span>
            <ul class="part_types">
                <li id="new_part_type_li_<?=$unit_type_id?>" style="display: none">
                    <span class="part_type new"><i class="fa fa-plus"></i> New component type</span>
                </li>

            <?php foreach ($unit_type->part_types as $part_type) : ?>
                <li id="part_type_<?=$part_type->id?>" style="display: none">
                    <span class="part_type"
                        data-name="<?=$part_type->name?>"
                        data-for_diagnostic="<?=$part_type->for_diagnostic?>"
                        data-unit_type_id="<?=$part_type->unit_type_id?>"
                        data-id="<?=$part_type->id?>">
                        <i class="fa fa-cog"></i> <span class="element_name"><?=$part_type->name?></span>
                    </span>
                    <a class="part_type btn btn-sm btn-warning delete">Delete</a>
                    <a class="part_type btn btn-sm btn-info edit">Edit</a>
                    <ul class="issue_types">
                        <li id="new_issue_type_li_<?=$part_type->id?>" style="display: none">
                            <span class="issue_type new"><i class="fa fa-plus"></i> New issue type</span>
                        </li>

                    <?php foreach ($part_type->part_type_issue_types as $part_type_issue_type) : ?>
                        <li id="part_type_issue_type_<?=$part_type_issue_type->id?>" style="display: none">
                            <span class="issue_type"
                                data-issue_type_id="<?=$part_type_issue_type->issue_type_id?>"
                                data-part_type_id="<?=$part_type_issue_type->part_type_id?>"
                                data-id="<?=$part_type_issue_type->id?>">
                                <i class="fa fa-exclamation-triangle"></i> <span class="element_name"><?=$part_type_issue_type->name?></span>
                            </span>
                            <a class="part_type_issue_type btn btn-sm btn-warning delete">Delete</a>
                            <a class="part_type_issue_type btn btn-sm btn-info edit">Edit</a>
                            <ul class="steps">
                                <li id="new_step_li_<?=$part_type_issue_type->id?>" style="display: none">
                                    <span class="step new"><i class="fa fa-plus"></i> New step</span>
                                </li>

                            <?php foreach ($part_type_issue_type->steps as $step) : ?>
                                <li id="step_<?=$step->id?>" style="display: none">
                                    <span class="step"
                                        data-step_id="<?=$step->step_id?>"
                                        data-part_type_issue_type_id="<?=$step->part_type_issue_type_id?>"
                                        data-required="<?=$step->required?>"
                                        data-needs_sq="<?=$step->needs_sq?>"
                                        data-immediate="<?=$step->immediate?>"
                                        data-id="<?=$step->id?>">
                                        <i class="fa fa-footsteps"></i> <span class="element_name"><?=$step->name?></span>
                                    </span>
                                    <a class="part_type_issue_type_step btn btn-sm btn-warning delete">Delete</a>
                                    <a class="part_type_issue_type_step btn btn-sm btn-info edit">Edit</a>
                                    <ul class="required_parts">

                                        <li id="new_required_part_li_<?=$step->id?>" style="display: none">
                                            <span class="required_part new"><i class="fa fa-plus"></i> New required part/labour</span>
                                        </li>

                                    <?php foreach ($step->required_parts as $required_part) : ?>
                                        <li id="required_part_<?=$required_part->id?>" style="display: none">
                                            <span class="required_part"
                                                data-part_type_id="<?=$required_part->part_type_id?>"
                                                data-part_type_issue_type_step_id="<?=$required_part->part_type_issue_type_step_id?>"
                                                data-quantity="<?=$required_part->quantity?>"
                                                data-id="<?=$required_part->id?>">
                                                <i class="fa fa-cogs"></i> <span class="element_name"><?=$required_part->name?> (<?=$required_part->quantity?>)</span>
                                            </span>
                                            <a class="required_part btn btn-sm btn-warning delete">Delete</a>
                                            <a class="required_part btn btn-sm btn-info edit">Edit</a>
                                        </li>
                                    <?php endforeach; // required_parts ?>

                                    </ul>
                                </li>

                            <?php endforeach; // steps ?>
                            </ul>
                        </li>

                    <?php endforeach; // part type issue types ?>
                    </ul>
                </li>

            <?php endforeach; // part types ?>
            </ul>
        </li>

    <?php endforeach; // unit types ?>
    </ul>
</div>
<?php $this->load->view('orders/popovers') ?>
