<?php

//DETERMINE WHETHER TO RETURN BISNOW, MEDIA OR BISNOW MEDIA
$string = '';
foreach ($_POST as $key=>$value) {
	if ($key == "Number") {
		if ($value % 3 == 0) {
			$string .= "Bisnow ";
		} 
		if($value % 5 == 0) {
			$string .= "Media";
		}
		echo $string;
		break;
	}
}

/*
* For demonstration purposes only, MySQL connection code and raw queries should not really be here
*/
//CONNECT TO MYSQL
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "myDB";
$conn = new mysqli($servername, $username, $password, $dbname);

//ADD TRACKING TABLE IF NOT ALREADY THERE
$sql = "
CREATE TABLE IF NOT EXISTS tracking (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
value INT
");
$conn->query($sql);

//INSERT NEW VALUE
$sql = "INSERT INTO TABLE tracking(value) VALUES ($sql)";
$conn->query($sql);
?>