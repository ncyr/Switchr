<section class="title">
	<h4><?php echo lang('payignite:invoices'); ?></h4>
</section>

<section class="item">
<div class="content">

    <?php if ($entries['data'] > 0): ?>
	
    <table id="invoice_table" class="data-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th><?php echo lang('payignite:invoice_number'); ?></th>
                <th><?php echo lang('payignite:created_date'); ?></th>
                <th><?php echo lang('payignite:customer'); ?></th>
                <th><?php echo lang('payignite:subtotal'); ?></th>
                <th><?php echo lang('payignite:coupon'); ?></th>
                <th><?php echo lang('payignite:total'); ?></th>
                <th><?php echo lang('payignite:paid'); ?></th>
                <th><?php echo lang('payignite:closed'); ?></th>
                <th class='dis'><!--<a href="admin/payignite/#" onclick="toggle('closed')">Hide/Show<br>Closed Invoices</a>--></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entries['data'] as $row): ?>
            <tr class="invoice">
                <td><?php echo $row->id; ?></td>
                
                <td><?php echo date('m/d/y h:i:s A' , $row->date); ?>
                </td>
                
                <td><?php echo '<a href="admin/payignite/customers/view/' . $row->customer . '">' . $row->customer . '</a>'; ?>
                </td>
                
                <td><?php
                if ($row->subtotal == "0") {
                    echo "$ 0";
                } else {
                    echo "$ " . substr_replace($row->subtotal, '.', -2, -2);
                } ?>
                </td>
                
                <td><?php
                //echo $row->discount->coupon->id;
                
                if ($row->discount == false) {
                    echo "None";
                } else {
                    echo $row->discount->coupon->id;
                }
                
                ?>
                </td>
                
                <td><?php
                if ($row->total == "0") {
                    echo "$ 0";
                } else {
                    echo "$ " . substr_replace($row->total, '.', -2, -2);
                } ?>
                </td>
                
                <td><?php
                if ($row->paid == 1) { 
                    echo "<span style='color:green';>Yes</span>";
                } else {
                    echo "<span style='color:red';>No</span>";
                }
                
                ?>
                </td>
                
                <td class="closed"><?php
                if ($row->closed == 1) { 
                    echo "<span style='color:green';>Yes</span>";
                } else {
                    echo "<span style='color:red';>No</span>";
                }
                
                ?>
                </td>
                
                <td class="actions">
                <?php
                if ($row->closed == 0) {
                    echo anchor('admin/payignite/close/' . $row['id'], lang('payignite:close'), array('class' => 'btn blue'));
                } ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
		
		
    <?php else: ?>
            <div class="no_data"><?php echo lang('payignite:no_entries'); ?></div>
    <?php endif;?>
	
</div>
</section>