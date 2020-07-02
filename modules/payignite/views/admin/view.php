<section class="title">
	<h4><?php echo lang('payignite:invoice') . ' ' . $entries->id; ?></h4>
</section>

<section class="item">
<div class="content">
<?php
                                    echo'<pre>';
                                    print_r($entries);
                                    echo'</pre>';
                                    ?>
	
    <table class="table" cellpadding="0" cellspacing="0">
                    <thead>
                            <tr>
                                <th><?php echo lang('payignite:invoice_number'); ?></th>
                                <th><?php echo lang('payignite:total'); ?></th>
                                <th></th>
                            </tr>
                    </thead>
                    
                    <tbody>
                            <tr>
                                    <td>
                                    </td>
                                    <td><?php echo $row->total; ?></td>
                                    <td class="actions">
                                            <?php echo anchor('admin/payignite/invoice/pay/' . $row['id'], lang('payignite:pay'), 'class="button edit"'); ?>
                                            <?php echo anchor('admin/payignite/invoice/edit/' . $row['id'], lang('global:edit'), 'class="button edit"'); ?>
                                            <?php echo anchor('admin/payignite/invoice/delete/' . $row['id'], lang('global:delete'), array('class' => 'confirm button delete')); ?>
                                        </td>
                            </tr>
                    </tbody>
    </table>
		
		
	
</div>
</section>