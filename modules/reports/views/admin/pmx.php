<?php print_r($data['parsed']); ?>
<h1>Product Mix Report</h1>
<table>
	<tr>
		<td>Rank</td>
		<td>Item Num</td>
		<td>Item Name</td>
		<td>Num Sold</td>
		<td>Price Sold</td>
		<td>Amount</td>
		<td>Cost</td>
		<td>Profit</td>
		<td>Food Cost %</td>
		<td>% Sales</td>
	</tr>
	<?php if ($data['parsed']['non-sales categories'])
	{
		$count = count($data['parsed']['non-sales categories']);
		for ($i = 1; $i<=$count; $i++)
		{
			echo '<tr><td>' . $i . '</td>';
			foreach ($data['parsed']['non-sales categories'][$i] as $row)
			{
				echo '<td>' . $row . '</td>';
			}
			echo '</tr>';
		}
	}
	elseif (count($data['parsed']['tot all alcohol:']))
	{
		$count = count($data['parsed']['tot all alcohol:']);
		echo '<tr><td></td><td></td><td>TOTALS All Alchohol: </td>';
		for ($i = 0; $i<=$count; $i++)
		{
			echo '<td>' . $data['parsed']['tot all alcohol:'][$i] . '</td>';
		}
		echo '</tr>';
	} ?>
</table>
