<div class="container">
<table class="table">
	<thead>
		<tr>
			<th>Name</th>
			<th>Headline</th>
			<?php if (isset($_SESSION['user_id'])) {
				echo "<th>Action</th>";
			}?>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($profiles as $profile) {
			echo "<tr>";
			echo "<td><a href=view.php?profile_id=".$profile['profile_id'].">".htmlentities($profile['first_name'])." ".htmlentities($profile['last_name'])."</a></td>";
			echo "<td>".htmlentities($profile['headline'])."</td>";
			if (isset($_SESSION['user_id'])) {
				echo '<td>';
				if ($_SESSION['user_id'] == $profile['user_id']) {
                    echo '<span class="btn-group">';
                    echo '<a class="btn btn-sm btn-warning" href="edit.php?profile_id='.$profile['profile_id'].'">Edit</a>';
                    echo '<a class="btn btn-sm btn-danger" href="delete.php?profile_id='.$profile['profile_id'].'" class="btn btn-danger">Delete</a>';
                    echo '</span>';
                }
				echo "</td>";
			}
		}
		?>
	</tbody>
</table>
</div>