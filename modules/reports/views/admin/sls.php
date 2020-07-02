<div id="content-body">
	<h2><?=$data['parsed']['something']?></h2>
	<ul>
		<li><strong>Gross Sales: </strong> <?=$data['parsed'][' Gross Sales']?></li>
		<li><strong>Net Sales: </strong> <?=$data['parsed']['Net Sales']?></li>
	</ul>

	<h4>Tax By Tax ID:</h4>
	<ul>
		<li><strong>Exclusive: </strong><?=$data['parsed']['tax by tax id']['exclusive']?></li>
		<li><strong>Retail: </strong><?=$data['parsed']['tax by tax id']['retail']?></li>
		<li><strong>Inclusive: </strong><?=$data['parsed']['tax by tax id']['inclusive']?></li>
		<li><strong>No Tax: </strong><?=$data['parsed']['tax by tax id']['no_tax']?></li>
	</ul>

	<h4>Comps:</h4>
	<ul>
		<?php foreach($data['parsed']['comps'] as $key=>$row): ?>
			<li><strong><?=$key?>: </strong><?=$row?></li>
		<?php endforeach ?>
	</ul>

	<h4>Voids:</h4>
	<ul>
		<?php foreach($data['parsed']['voids'] as $key=>$row): ?>
			<li><strong><?=$key?>: </strong><?=$row?></li>
		<?php endforeach ?>
	</ul>

	<h4>Comps:</h4>
	<ul>
		<?php foreach($data['parsed']['comps'] as $key=>$row): ?>
			<li><strong><?=$key?>: </strong><?=$row?></li>
		<?php endforeach ?>
	</ul>

	<h4>Petty Cash:</h4>
	<ul>
		<?php foreach($data['parsed']['petty cash'] as $key=>$row): ?>
			<li><strong><?=$key?>: </strong><?=$row?></li>
		<?php endforeach ?>
	</ul>

	<h4>Guest Count by Day Part:</h4>
	<ul>
		<?php foreach($data['parsed']['guest count by day part'] as $key=>$row): ?>
			<li><strong><?=$key?>: </strong><?=$row?></li>
		<?php endforeach ?>
	</ul>

	<h4>Exempt Taxables:</h4>
	<ul>
		<?php foreach($data['parsed']['exempt taxables'] as $key=>$row): ?>
			<li><strong><?=$key?>: </strong><?=$row?></li>
		<?php endforeach ?>
	</ul>

	<h4>Check Count by Day Part:</h4>
	<ul>
		<?php foreach($data['parsed']['check count by day part'] as $key=>$row): ?>
			<li><strong><?=$key?>: </strong><?=$row?></li>
		<?php endforeach ?>
	</ul>

	<h4>Non-cash Payments:</h4>
	<table>
		<?php foreach($data['parsed']['non-cash payments'] as $key=>$row): ?>
			<tr>
				<td>
					<strong><?=$key?>: </strong>
					<?php foreach($row as $key=>$row): ?>
						<?=$row?>
					<?php endforeach ?>
				</td>
			</tr>
		<?php endforeach ?>
	</table>

	<h4>Totals:</h4>
	<table>
		<?php foreach($data['parsed']['totals'] as $key=>$row): ?>
		<tr>
			<td>
				<strong>'.$key.': </strong>';
				<?php foreach($data['parsed']['totals'] as $key=>$row): ?>
					<?=$row?>
				<?php endforeach ?>
			</td>
		</tr>
		<?php endforeach ?>
	</table>

	<?=print_r($data);?>
</div>
