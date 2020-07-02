<section class="title">
	<h4><?php echo lang('payignite:payments'); ?></h4>
</section>

<section class="item">
<div class="content">
	<?php if ($entries['data'] > 0): ?>
	
		<table class="table data-table" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
                                        <th><?php echo lang('payignite:amount'); ?></th>
                                        <th><?php echo lang('payignite:paid'); ?></th>
					<th><?php echo lang('payignite:refunded'); ?></th>
                                        <th><?php echo lang('payignite:card'); ?></th>
					<th><?php echo lang('payignite:cardholder'); ?></th>
                                        <th><?php echo lang('payignite:charge_id'); ?></th>
					<th><?php echo lang('payignite:invoice'); ?></th>
					
		
				</tr>
			</thead>
			<tbody>
                            <?php
                            //echo '<pre>';
                            //var_dump($current_customer);
                            //echo '</pre>';
                            ?>
				<?php foreach ($entries['data'] as $row): ?>
                                <?php
                            //echo '<pre>';
                            //var_dump($row);
                            //echo '</pre>';
                            ?>
                                <?php if($row->customer == $current_customer['entries'][0]['customer_user_customer_id']): ?>
				<tr>
                                        <td><?php echo "$ " . substr_replace($row->amount, '.', -2, -2) ; ?></td>
                                        <td><?php echo ($row->paid == 1) ? "<span style='color:green'>Yes</span>" : "<span style='color:red'>No</span>"; ?></td>
					<td><?php echo ($row->refunded == true) ? "<span class='tooltip' title='Refunded: $".substr_replace($row->amount_refunded, '.', -2, -2)."' style='color:red'>Yes</span>" : "<span style='color:green'>No</span>"; ?></td>
					<td><?php echo $row->card->last4; ?></td>
                                        <td><?php echo $row->card->name; ?></td>
                                        <td><?php echo $row->id; ?></td>
					<td><?php echo $row->invoice; ?></td>
				</tr>
                                <?php endif; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
		
		
	<?php else: ?>
		<div class="no_data"><?php echo lang('payignite:no_entries'); ?></div>
	<?php endif;?>
	
</div>
</section>