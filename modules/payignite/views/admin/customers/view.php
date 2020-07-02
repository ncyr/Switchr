
<section class="title">
	<h4><?php echo lang('payignite:customer_details') . " of " . "<a style='color:blue'; href='mailto:" . $details->email . "'>" . $details->email . "</a>"; ?></h4>
</section>

<section class="item">
<div class="content">
	
    <table class="table" cellpadding="0" cellspacing="0">
	<thead>
	    <tr>
                <th><?php echo lang('payignite:customer_id'); ?></th>
		<th><?php echo lang('payignite:created_date'); ?></th>
                <th><?php echo lang('payignite:customer_last_charge_failed'); ?></th>
                <th></th>
                <th></th>
                <th></th>
	    </tr>
	</thead>
	
        <tbody>
	    <?php
            // Debug Statements
            //
            //echo $details->subscriptions->data['0']->discount;
            //echo count($cards);
            //print_r($details['cards']);
            ?>
	    <tr>
		<td><?php
                echo $details->id;
                ?>
                </td>
		
                <td><?php
                echo date('m/d/y', $details->created);
                ?>
                </td>
                
                <td><?php
                echo $details->delinquent != null ? $details->delinquent : 'No'; //subscriptions->total_count;
                ?>
                </td>
                
                <td>
                </td>
		
                <td class="actions">
                </td>
                
                <td class="actions">
                </td>
	    </tr>
            <?php //endforeach; ?>
	</tbody>
    </table>
    
<br>
    
    <?php
    if ($details->cards->data == true):
    $card0 = $details->cards->data['0'];
    $cards = $details->cards->data;
    ?>
<h4><?php echo lang('payignite:customer_cards'); ?></h4>
    <table class="table" cellpadding="0" cellspacing="0">
        <thead>
	    <tr>
		<th><?php echo lang('payignite:customer_default_card') . " (" . $card0->type . ")"; ?></th>
		<th></th>
                <th></th>
                <th></th>
                <th></th>
	    </tr>
        </thead>
        
        <tbody>
        <thead>
	    <tr>
		<th><?php echo lang('payignite:customer_name'); ?></th>
		<th><?php echo lang('payignite:customer_card_last4'); ?></th>
                <th><?php echo lang('payignite:customer_card_cvc_check'); ?></th>
                <th><?php echo lang('payignite:customer_card_exp_date'); ?></th>
                <th></th>
	    </tr>
	</thead>
	    <tr>
		<td><?php
                    echo $card0->name; ?>
                </td>
		
                <td><?php
                    echo $card0->last4; ?>
                </td>
                
                <td><?php  if ($card0->cvc_check == 'pass') {
                    echo "<span style='color:green';>" . ucfirst($card0->cvc_check) . "</span>";
                } elseif ($card0->cvc_check == '') {
                    echo "None Entered";
                } else {
                    echo "<span style='color:red';> (" . ucfirst($card0->cvc_check) . ")</span>";
                } ?>
                </td>
                
                <td><?php
                    echo $card0->exp_month; ?>
                    /
                    <?php
                    echo $card0->exp_year; ?>
                </td>
		
                <td>
                </td>
	    </tr>
	<thead>
            <tr>
		<th><?php echo lang('payignite:customer_address1_check'); ?></th>
		<th><?php echo lang('payignite:customer_address2'); ?></th>
                <th><?php echo lang('payignite:customer_city'); ?></th>
                <th><?php echo lang('payignite:customer_state'); ?></th>
                <th><?php echo lang('payignite:customer_zip_check'); ?></th>
	    </tr>
	</thead>
	    <tr>
		<td><?php if ( ($card0->address_line1_check == 'pass') && ($card0->address_line1 != false) ) {
                    echo $card0->address_line1 . "<span style='color:green';> (" . ucfirst($card0->address_line1_check) . ")</span>";
                } elseif ($card0->address_line1 == '') {
                    echo "None Entered";
		} else {
                    echo $card0->address_line1 . "<span style='color:red';> (" . ucfirst($card0->address_line1_check) . ")</span>";
                } ?>
                </td>
		
                <td><?php
                    echo $card0->address_line2; ?>
                </td>
                
                <td><?php
                    echo $card0->address_city; ?>
                </td>
                
                <td><?php
                    echo $card0->address_state; ?>
                </td>

                <td><?php if ( ($card0->address_zip_check == 'pass') && ($card0->address_zip != false) ) {
                    echo $card0->address_zip . "<span style='color:green';> (" . ucfirst($card0->address_zip_check) . ")</span>";
                } elseif ($card0->address_zip == '') {
                    echo "None Entered";
                } else {
                    echo $card0->address_zip . "<span style='color:red';> (" . ucfirst($card0->address_zip_check) . ")</span>";
                } ?>
                </td>
            </tr>
	</tbody>
    </table>
    
    <?php
    if (count($cards) > 1):
    $j = count($cards);
    for($x = 1; $x < $j ; $x++):
    $card = $details->cards->data[$x];
    ?>
    <br>
    <table class="table" cellpadding="0" cellspacing="0">
        <thead>
	    <tr>
		<th><?php echo lang('payignite:customer_card') . " " . ($x + 1) . " (" . $card->type . ")"; ?></th>
		<th></th>
                <th></th>
                <th></th>
                <th></th>
	    </tr>
        </thead>
        
        <tbody>
        <thead>
	    <tr>
		<th><?php echo lang('payignite:customer_name'); ?></th>
		<th><?php echo lang('payignite:customer_card_last4'); ?></th>
                <th><?php echo lang('payignite:customer_card_cvc_check'); ?></th>
                <th><?php echo lang('payignite:customer_card_exp_date'); ?></th>
                <th></th>
	    </tr>
	</thead>
	    <tr>
		<td><?php
                    echo $card->name; ?>
                </td>
		
                <td><?php
                    echo $card->last4; ?>
                </td>
                
                <td><?php  if ($card->cvc_check == 'pass') {
                    echo "<span style='color:green';>" . ucfirst($card->cvc_check) . "</span>";
                } else {
                    echo "<span style='color:red';> (" . ucfirst($card->cvc_check) . ")</span>";
                } ?>
                </td>
                
                <td><?php
                    echo $card->exp_month; ?>
                    /
                    <?php
                    echo $card->exp_year; ?>
                </td>
		
                <td>
                </td>
	    </tr>
	<thead>
            <tr>
		<th><?php echo lang('payignite:customer_address1_check'); ?></th>
		<th><?php echo lang('payignite:customer_address2'); ?></th>
                <th><?php echo lang('payignite:customer_city'); ?></th>
                <th><?php echo lang('payignite:customer_state'); ?></th>
                <th><?php echo lang('payignite:customer_zip_check'); ?></th>
	    </tr>
	</thead>
	    <tr>
		<td><?php if ( ($card->address_line1_check == 'pass') && ($card->address_line1 != false) ) {
                    echo $card->address_line1 . "<span style='color:green';> (" . ucfirst($card->address_line1_check) . ")</span>";
                } elseif ($card->address_line1 == '') {
                    echo "None Entered";
		} else {
                    echo $card->address_line1 . "<span style='color:red';> (" . ucfirst($card->address_line1_check) . ")</span>";
                } ?>
                </td>
		
                <td><?php
                    echo $card->address_line2; ?>
                </td>
                
                <td><?php
                    echo $card->address_city; ?>
                </td>
                
                <td><?php
                    echo $card->address_state; ?>
                </td>

                <td><?php if ( ($card->address_zip_check == 'pass') && ($card->address_zip != false) ) {
                    echo $card->address_zip . "<span style='color:green';> (" . ucfirst($card->address_zip_check) . ")</span";
                } elseif ($card->address_zip == '') {
                    echo "None Entered";
                }else {
                    echo $card->address_zip . "<span style='color:red';> (" . ucfirst($card->address_zip_check) . ")</span>";
                } ?>
                </td>
            </tr>
	</tbody>
    </table>
    <?php
    endfor;
    endif;
    endif;
    ?>
        
        
    </table>

<br>
    
    <!-- Subscriptions -->
    <?php
    if ($details->subscriptions->data == true): echo "<h4>" . lang('payignite:customer_subscriptions') . "</h4>";
    foreach ($details->subscriptions->data as $sbn):
    ?>
    <table class="table" cellpadding="0" cellspacing="0">
	<thead>
	    <tr>
                <th><?php echo lang('payignite:plan_name_name'); ?></th>
		<th><?php echo lang('payignite:plan_term'); ?></th>
                
                <th><?php echo lang('payignite:plan_quantity'); ?></th>
                
                <th><?php echo lang('payignite:plan_trial_period'); ?></th>
                <th><?php echo lang('payignite:plan_status'); ?></th>
	    </tr>
	</thead>
	
        <tbody>
	    <tr>
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
                echo $sbn->quantity; ?>
                </td>

                
                <td>
                <?php if ($sbn->plan->trial_period_days == 0) {
                    echo "No Trial";
                } else {
                    echo $sbn->plan->trial_period_days . " days";
                } ?>
                </td>
                
                <td><?php
                echo ucfirst($sbn->status); ?>
                </td>
	    </tr>
	</tbody>
    </table>
    <?php
    endforeach;
    endif;
    ?>

<br>

    <!-- Invoices -->
    <?php
    if (count($invoices->data) > 0):
        echo "<h4>" . lang('payignite:invoices') . "</h4>";
    $i = count($invoices->data);
    for($y = 0; $y < $i ; $y++):
    $inv = $invoices->data[$y];
    // $inv['lines']['data'] may contain multiple entries (invoice items)... need resolution, or no ??
    ?>
    <table class="table" cellpadding="0" cellspacing="0">
	<thead>
	    <tr>
                <th><?php echo lang('payignite:invoice_id'); ?></th>
		<th><?php echo lang('payignite:created_date'); ?></th>
                <th><?php echo lang('payignite:subtotal'); ?></th>
                <th><?php echo lang('payignite:coupon'); ?></th>
                <th><?php echo lang('payignite:total'); ?></th>
                <th><?php echo lang('payignite:paid'); ?></th>
                <th><?php echo lang('payignite:closed'); ?></th>
	    </tr>
	</thead>
	
        <tbody>
	    <tr>
		<td><?php
                echo $inv->id;
                ?>
                </td>
		
                <td><?php
                echo date('m/d/y h:i:s A', $inv->date);
                ?>
                </td>
                
                <td><?php
                if ($inv->subtotal == "0") {
                    echo "$ 0";
                } else {
                    echo "$ " . substr_replace($inv->subtotal, '.', -2, -2);
                } ?>
                </td>
                
                <td><?php
                //echo $row->discount->coupon->id;
                
                if ($inv->discount == false) {
                    echo "None";
                } else {
                    echo $inv->discount->coupon->id;
                }
                
                ?>
                </td>
                
                <td><?php
                if ($inv->total == "0") {
                    echo "$ 0";
                } else {
                    echo "$ " . substr_replace($inv->total, '.', -2, -2);
                } ?>
                </td>
                
                <td><?php
                if ($inv->paid == 1) { 
                    echo "<span style='color:green';>Yes</span>";
                } else {
                    echo "<span style='color:red';>No</span>";
                }
                
                ?>
                </td>
                
                <td><?php
                if ($inv->closed == 1) { 
                    echo "<span style='color:green';>Yes</span>";
                } else {
                    echo "<span style='color:red';>No</span>";
                }
                
                ?>
                </td>
	    </tr>
	</tbody>
    </table>
    <?php
    endfor;
    endif;
    // Debug commands:
    //echo "<pre>";
    //print_r($invoices);
    //echo "</pre>";
    ?>
</div>
</section>