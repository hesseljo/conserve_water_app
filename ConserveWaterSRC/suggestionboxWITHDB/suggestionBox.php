<?php
//Error reporting
ini_set('display_errors','On');
//connects to database
$mysqli = new mysqli("oniddb.cws.oregonstate.edu","loy-db","7LOgg6VSYfMcvlBJ","loy-db");
if($mysqli->connect_errno){
  echo "Connection error".$mysqli->connect_errno." ".$mysqli->connect_error;
}

?>

<!DOCTYPE html>
<html>
  <body>
    <div>
      <table>
        <tr>
            <td><b>Suggestion Box: </b></td>
        </tr>

        <?php

          if(!($stmt = $mysqli->prepare("SELECT suggestion.inputSuggestion FROM suggestion"))){
            echo "Prepare failed: " . $stmt->connect_errno ." ". $stmt->connect_error;
          }
          if(!$stmt->execute()){
            echo "Execute failed: " . $mysqli->connect_errno ." ". $mysqli->connect_error;
          }
          if(!$stmt->bind_result($inputSuggestion)){
            echo "Bind failed: " . $mysqli->connect_errno ." ". $mysqli->connect_error;
          }
          while($stmt->fetch()){
            echo "<tr>\n<td>\n" . $inputSuggestion . "\n</td>\n</tr>\n";
          }
          $stmt->close();
        ?>

       </table>
	</div>
    <div>   
      <form method="post" action="addSuggestion.php">
      </br>
        <fieldset>
          <legend><b> Suggestion</b></legend>
            <select name="Suggestion">
              <?php
                if(!($stmt = $mysqli->prepare("SELECT id, inputSuggestion FROM suggestion"))){
                  echo "Prepare failed: " . $stmt->errno ." ". $stmt->error;
                }
                if(!$stmt->execute()){
                  echo "Execute failed: " . $mysqli->connect_errno ." ". $mysqli->connect_error;
                }
                if(!$stmt->bind_result($id, $inputSuggestion)){
                  echo "Bind failed: " . $mysqli->connect_errno ." ". $mysqli->connect_error;
                }
                while($stmt->fetch()){
                  echo '<option value=" '. $id . ' "> ' . $inputSuggestion . '</option>\n';
                }
                $stmt->close();
              ?>
            </select>
        </fieldset>
          <p><input type="submit"/></p>
    </form>
   </div> 