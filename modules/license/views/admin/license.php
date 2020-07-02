<script>
    
jQuery(document).ready(function(){
	$("#report_store").change(function(){
		var storeId = $(this).val();
		$.ajax({
			url: "/admin/stores/store_change/" + storeId,
			success: function(){
				alert('Store Changed, Reloading Page...');
                window.location = '/admin/license';
			}
			});
	});
});
</script>
<section class="title">
    <h2>Current Store Subscriptions</h2>
    <a href="/admin/license/add_license" class="btn green">Add Store License</a> <a href="/admin/license/edit_license" class="btn orange">Edit Store License</a> <a href="/admin/license/remove_license" class="btn red">Remove Store License</a>
</section>
<section class="item">
<?php if (false !== ($storesByOwner) && count($storesByOwner) > 1): ?>
    <span>
        <select id="report_store">
            <option value="">-- Change Location --</option>
            <?php foreach ($storesByOwner as $store): ?>
                <option value="<?=$store->id?>"><?=$store->store_name?></option>
            <?php endforeach ?>
        </select>
    </span>
<?php endif ?><br/>
<br/>

<input type="text" class="tooltip" title="Copy/paste this key during installation." style="width:242px; text-align:center" value="Store Key: <?=$currentStoreLicense->key?>" disabled />
<ul>
	<?php 
		foreach($storeServices as $service){
            $status = (!$service->status_code == 1) ? 'Inactive' : 'Active';
			echo '
                <li style="font-size: 18px; width:248px;background-color:#FFFFFF; padding:5px; border-radius: 5px; margin-bottom:5px;">
                <a href="/admin/license/show_license/' . $service->id . '"> ' . $service->service_name . '</a><br/>
                <span style="font-weight: bold">Status:</span> ' . $status . '<br/>
                <span style="font-weight: bold">Expires:</span> ' . $service->expiration . '<br/>
                </li><br/><hr/>';
		}
	?>
</ul>
<hr/>
<ul>
<?php foreach($allLicenses as $license):?>
    <li><?=$license->key?> - Status: <?=$license->status_code;?> - Store ID: <?=$license->store_id;?></li>
<?php endforeach;?>
</ul>
</section>