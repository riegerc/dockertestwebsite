<?php
require_once "db.php";
?>
<html>
<head><title>Thy Shop</title></head>
<body>
	<h1>Welcome to thy spy Shop!</h1>
	<ul>
	<?php	
		echo "\n<li>Username\t|\tEmail\t|\tUserrole</li>\n";
		$result = readFromDb("SELECT * FROM account INNER JOIN role ON role_id = fk_role_id");		
		foreach($result as $row){
			echo "<li>$row[username]\t|\t$row[email]\t|\t$row[role_name]</li>\n";
			#echo "<li>$row[1] $row[3] $row[6] </li>";
		}	
	?> 
	</ul>
</body>
</html>



