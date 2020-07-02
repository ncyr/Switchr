<section class="title">
	<h4><?php echo lang('payignite:invoice'); ?></h4>
</section>

<section class="item">
<div class="content">
<?php //echo "<pre>"; var_dump($entries); echo "</pre>"; ?>

    <div class='grp'>
        <div class='key'>Invoice #:</div> <div class='val'><?php echo $entries->id; ?></div></div>
    
    <div class='grp'>
        <div class='key'>Created On:</div> <div class='val'><?php echo date('m/d/y h:i A',$entries->date); ?></div>
    </div>
    
    <div class='grp'>
        <div class='key'>Subtotal:</div> $<div class='val'><?php echo substr_replace($entries->subtotal, '.', -2, -2); ?></div>
    </div>
    
    <div class='grp'>
        <div class='key'>Coupon:</div> <div class='val'><?php echo ($entries->coupon == false) ? "None" : $entries->coupon; ?></div>
    </div>
    
    <div class='grp'>
        <div class='key'>Total:</div> $<div class='val'><?php echo substr_replace($entries->total, '.', -2, -2); ?></div>
    </div>
    
    <div class='grp'><div class='key'>Paid:</div> <div class='val'><?php
                if ($entries->paid == 1) { 
                    echo "<span style='color:green';>Yes</span>";
                } else {
                    echo "<span style='color:red';>No</span>";
                } ?></div>
    </div>
    
    <div class='grp'>
        <div class='key'>Plan:</div> <div class='val'><?php echo $entries->lines->data['0']->plan->name; ?></div>
    </div><br/><br/><div style="clear:both"></div><hr>
	{{ streams:cycle stream="visits" namespace="payignite" where="`visit_subscription`='<?php echo $entries->subscription; ?>'" }}
		<div class='key'>Gym Name:</div>{{ gym_id.gym_name }}<br/>
		<div class='key'>Visits Purchased:</div>{{ visit_count }}<br/>
		<div class='key'>Visits Used:</div>{{ visit_used_visits }}<br/>
		<hr>
	{{ /streams:cycle }}

</div>
</section>