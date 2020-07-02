<section class="title">
	<h4><?php echo lang('hosts:hosts'); ?></h4>
</section>

<section class="item">
	<?php echo form_open('admin/hosts/edit/delete_all');?>

	<?php if (!empty($items)): ?>

		<table>
			<thead>
				<tr>
					<th><?php echo form_checkbox(array('name' => 'action_to_all', 'class' => 'check-all'));?></th>
					<th><?php echo lang('hosts:host_host_name'); ?></th>
					<th><?php echo lang('hosts:host_user_email'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="5">
						<div class="inner"><?php $this->load->view('admin/partials/pagination'); ?></div>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach( $items as $item ): ?>
				<tr>
					<td><?php echo form_checkbox('action_to[]', $item['id']); ?></td>
					<td><?php echo $item['host_id']['host_name']; ?></td>
					<td><?php echo $item['user_id']['email']; ?></td>
					
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<div class="table_action_buttons">
			<?php $this->load->view('admin/partials/buttons', array('buttons' => array('delete'))); ?>
		</div>

	<?php else: ?>
		<div class="no_data"><?php echo lang('hosts:no_hosts'); ?></div>
	<?php endif;?>

	<?php echo form_close(); ?>
</section>
