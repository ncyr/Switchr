<div class='mainInfo ui-widget ui-widget-content ui-corner-all'>
	<h3 class="ui-widget-header">Confirm Store Delete...</h3>
	<form action="admin/hosts/delete_host/<?=$hostId?>" method="post">
		<input id="<?=$hostId?>" type="hidden" name="confirm" value="yes"/>
		<input id="button" type="submit" value="Confirm Delete"/>
	</form>
</div>
