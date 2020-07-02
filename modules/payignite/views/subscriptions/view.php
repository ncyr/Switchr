<section class="title">
	<h4><?php echo lang('payignite:subscription'); ?></h4>
</section>

<section class="item">
<div class="content">

    <div class='grp'><div class='key'>Plan Name:</div> <div class='val'><?php echo $entries->plan->name; ?></div></div>
    
    <div class='grp'><div class='key'>Term:</div> <div class='val'><?php echo $entries->plan->interval_count . ' ' . ucfirst($entries->plan->interval) . '(s)'; ?></div></div>
    
    <div class='grp'><div class='key'>Plan Quantity:</div> <div class='val'><?php echo $entries->quantity; ?></div></div>
    
    <div class='grp'><div class='key'>Trial Period:</div> <div class='val'><?php echo ($entries->plan->trial_period_days == 0) ? 'No Trial' : $entries->plan->trial_period_days . ' days'; ?></div></div>
    
    <div class='grp'><div class='key'>Status:</div> <div class='val'><?php echo ucfirst($entries->status); ?></div></div>

</div>
</section>