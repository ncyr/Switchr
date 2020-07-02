<section class="title">
	<h4><?php echo lang('payignite:subscriptions'); ?></h4>
</section>

<section class="item">
<div class="content">
    <?php
    //echo "<pre>";
    //var_dump($entries);
    //echo "</pre>";
    //exit;
    ?>
    <?php
    if ($entries->subscriptions->data == true):
    
    ?>
    <table class="cus-data-table" cellpadding="0" cellspacing="0">
	<thead>
	    <tr>
                <th><?php echo lang('payignite:plan_name_name'); ?></th>
		<th><?php echo lang('payignite:plan_term'); ?></th>
                <th><?php echo lang('payignite:plan_status'); ?></th>
	    </tr>
	</thead>
	
        <tbody>
            <?php foreach ($entries->subscriptions->data as $sbn): ?>
	    <tr onClick="window.document.location='payignite/subscriptions/view/<?php echo $sbn->id; ?>'" title='Click to view'">
		<td><?php
                echo $sbn->plan->name;
                ?>
                </td>
		
                <td><?php
                if ($sbn->plan->interval_count > 1) {
                    echo $sbn->plan->interval_count . " " . $sbn->plan->interval . "s";
                } else {
                    echo $sbn->plan->interval_count . " " . $sbn->plan->interval;
                } ?>
                </td>
                
                <td><?php
                echo ucfirst($sbn->status); ?>
                </td>
	    </tr>
            <?php endforeach; ?>
	</tbody>
    </table>
    <?php
    
    //endif;
    ?>
	<?php else: ?>
		<div class="no_data"><?php echo lang('payignite:no_entries'); ?></div>
	<?php endif;?>
	
</div>
</section>