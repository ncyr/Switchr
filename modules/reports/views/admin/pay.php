<h1>Payment Report</h1>
<pre>
	<table>
		<?php $parsed = array_keys($data['parsed']);

		foreach($parsed as $field)
		{
			if(is_array($data['parsed'][$field]))
			{
				echo '<tr><td><h3>'.$field.'<h2></td></tr>';
				foreach($data['parsed'][$field] as $row)
				{
					echo '<tr>';
					foreach($row as $key)
					{
						echo '<td>'.$key.'</td>';
					}
					echo '</tr>';
				}
			}
			else
			{
				echo '<td>'.$data['parsed'][$field].'</td>';
			}
		} ?>
	</table>
</pre>
