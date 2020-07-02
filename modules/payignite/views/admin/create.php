<section class="title">
	<h4><?php echo lang('payignite:create_invoice'); ?></h4>
</section>

<section class="item">
<div class="content">
	<?php echo form_open('admin/payignite/create')?>
		<table class="table" cellpadding="0" cellspacing="0">
			<fieldset>
                                </tr>
					<td class="actions">
                                                <?php echo form_input('customer')?>
                                        </td>
					<td class="actions">
                                                <?php echo form_input('subscription')?>
                                        </td>
				</tr>
			</fieldset>
		</table>
		<?php echo form_submit('saveBtn', 'Save')?>
        <?php echo form_close()?>
</div>
</section>