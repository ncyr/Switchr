<section class="title">
	<h4><?php echo lang('payignite:customer_cards'); ?></h4>
</section>

<section class="item">
<div class="content">
    <?php if ($entries['data'] > 0): ?>
    <?php
    if ($entries->data == true):
    $card0 = $entries->data['0'];
    $cards = $entries->data;
    ?>
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
    $card = $entries->data[$x];
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
		
	<?php else: ?>
		<div class="no_data"><?php echo lang('payignite:no_entries'); ?></div>
	<?php endif;?>
	
</div>
</section>