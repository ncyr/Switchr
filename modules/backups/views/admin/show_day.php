<table>
	<tr>
		<th>Filename:</th>
		<th>Filesize:</th>
	</tr>
	<?php foreach($files as $file=>$fileSize): ?>
		<tr class="fileRow">
			<td><a href="uploads/<?=$this->session->userdata('current_store')?>/<?=$this->uri->segment(4)?>/<?=$file?>"><?=$file?></a></td>
			<td><?=(0.0009765625 * $fileSize)?> KB</td>
		</tr>
	<?php endforeach ?>
</table>
