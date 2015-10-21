<table style="font-weight: normal; width: 98%" cellspacing="0" cellpadding="10" border="0">
    <thead><tr><th style="font-weight: bold" width="10%">Unit</th><th style="font-weight: bold" width="90%">Description of Work Done</th></tr></thead>
    <tbody>
    <?php foreach ($units as $i => $unit) : ?>
        <?php if (empty($unit->issues)) : ?>
            <tr style="background-color: <?php echo ($i % 2) ? '#eee' : '#fff'?>;">
                <td width="10%"><?=$unit->id?></td>
                <td width="90%">
                    <?=$order_dowd?>
                </td>
            </tr>
        <?php else : ?>
            <tr>
                <td width="10%"><?=$unit->id?></td>
                <td width="90%">
                    <ul>
                    <?php foreach ($unit->issues as $issue) :?>
                        <li><?=$issue->dowd_text?></li>
                    <?php endforeach; ?>
                    </ul>
                </td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
    </tbody>
</table>
