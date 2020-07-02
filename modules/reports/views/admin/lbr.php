<h1>Labor report</h1>
<table>
	<tr>
		<td>Employee Name</td>
		<td>Date</td>
		<td>Job</td>
		<td>Export Emp Code</td>
		<td>Time In</td>
		<td>Time Out</td>
		<td>Reg Rate</td>
		<td>Reg Hours</td>
		<td>Reg Pay</td>
		<td>OT Rate</td>
		<td>OT Hours</td>
		<td>OT Pay</td>
		<td>Total Hours</td>
		<td>Total Pay</td>
		<td>CC Tips</td>
		<td>DECL Tips</td>
	</tr>
	<?php print_r($data);
	if (is_array($data['parsed']['payroll']))
	{
		foreach ($data['parsed']['payroll'] as $row)
		{
			echo '<tr>';
			foreach ($row as $field)
			{
				if (is_array($field))
				{
					foreach ($field as $value)
					{
						echo "<td>".$value."</td>";
					}
				}
				else
				{
					echo "<td>".$field."</td>";
				}
			}
			echo '</tr>';
		}
	} ?>
</table>
