<?php
//Error reporting
ini_set('display_errors','On');
//connects to database
$mysqli = new mysqli("oniddb.cws.oregonstate.edu","loy-db","7LOgg6VSYfMcvlBJ","loy-db");
if($mysqli->connect_errno){
  echo "Connection error".$mysqli->connect_errno." ".$mysqli->connect_error;
}

if(!($stmt = $mysqli->prepare("INSERT INTO suggestion (inputSuggestion) VALUE (?)"))){
	echo "Prepare failed: " . $stmt->errno ." ". $stmt->error;
          }
if(!($stmt->bind_param("s",$_POST['Suggestion']))){
	echo "Bind failed: " . $stmt->errno ." ". $stmt->error;
}
if(!($stmt->execute())){
    echo "Execute failed: " . $stmt->errno ." ". $stmt->error;
}else{
	echo "Added " . $stmt->affected_rows . " row(s) to suggestion.";
}

?>