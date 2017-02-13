<!--David DeRisi & David Hite - CS - 361 - Project B - "User Story #2" - cw.php
		3/06/16-->
<!-- This php file, as well as any accompaniment was/were derived
from coding projects used in completion of both CS275 and CS290.-->

<?php
include 'cw-utils.php';

//Turn on error reporting
ini_set('display_errors', 'On');
//Connects to the database
$mysqli = new mysqli("localhost","root","conserveh2o","conserveWater");
if($mysqli->connect_errno){
	echo "Connection error " . $mysqli->connect_errno . " " . $mysqli->connect_error;
	}

//Adds new row to item table and populates the data		
if (isset($_POST['Add'])) {
	$flow_Rate = $_POST['flowRate'];							//Calculating the actual gallons used for this instance
	$duration = $_POST['timeUsed'];								//from the flow rate and the duration
	
	if (($flow_Rate <= 0) || ($duration <= 0) || (strlen($_POST['Name']) <= 0)){     //error checking user input
		
		echo "<br>Invalid input, please try again.<br><br>";
		
	}else{
	
		$used_Gal = $flow_Rate*$duration;
		//echo "Used Gallons: " . $used_Gal . ".";		
																	//Updating the table with the new information, this snippet, and others like it appear frequently 
		if(!($stmt = $mysqli->prepare("INSERT INTO item(name, flow_rate, time_used, description, total) VALUES (?,?,?,?,?)"))){     //preparing query
			echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;			//if false returned 
		}
		if(!($stmt->bind_param("sdisd",$_POST['Name'],$_POST['flowRate'],$_POST['timeUsed'],$_POST['Description'], $used_Gal))){	//binding the paramaters per the prepared statement
			echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;				//if false returned
		}
		
		if(!$stmt->execute()){																	//executing the query
			echo "Execute failed: "  . $stmt->errno . " " . $stmt->error;			//if false returned
		} 
		else {
			echo "Added " . $stmt->affected_rows . " rows to item.";			//affected rows returns the total number of rows altered
			}
		
		//From here, pull out the data from the item table that is indicated as not added, open the day table and check dates, if there is a match, add the gallons used to that daily total,
		//otherwise, add a new row to the day table to begin accumulating the total for the newly entered day.
		
		//select the newest entry in the item table, or any entry with a zero in the "added" column, total1 holds the total from this table
		if(!($stmt1 = $mysqli->prepare("SELECT item.id, item.total, DATE(item.date) FROM item WHERE item.added = 0"))){
				echo "Prepare failed: "  . $stmt1->errno . " " . $stmt1->error;
			}
			if(!$stmt1->execute()){
				echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}
			if(!$stmt1->bind_result($iid, $total1, $date1)){ 
				echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}
			while($stmt1->fetch()){
				//echo "<br><tr>\n<td>" . $total1 . "\n</td>\n<td>\n" . $date1 ."\n</td>\n</tr>";
				}
			$stmt1->close();
			
			
			//extracting a week number to be used in week table management
			if(!($stmt1a = $mysqli->prepare("SELECT WEEK('{$date1}') FROM item WHERE item.added = 0"))){
				echo "Prepare failed: "  . $stmt1a->errno . " " . $stmt1a->error;
			}
			if(!$stmt1a->execute()){
				echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}
			if(!$stmt1a->bind_result($week)){ 
				echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}
			while($stmt1a->fetch()){
				//echo "<br><tr>\n<td> Week: " . $week . "\n</td>\n</tr><br>";
				}
			$stmt1a->close();
			
			//extracting a month number will be used later for week and month table management
			if(!($stmt1b = $mysqli->prepare("SELECT MONTH('{$date1}') FROM item WHERE item.added = 0"))){
				echo "Prepare failed: "  . $stmt1b->errno . " " . $stmt1b->error;
			}
			if(!$stmt1b->execute()){
				echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}
			if(!$stmt1b->bind_result($month)){ 
				echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}
			while($stmt1b->fetch()){
				//echo "<br><tr>\n<td> Month: " . $month . "\n</td>\n</tr><br>";
				}
			$stmt1b->close();
			
			//extracting a year number to be used later for month table management
			if(!($stmt1c = $mysqli->prepare("SELECT YEAR('{$date1}') FROM item WHERE item.added = 0"))){
				echo "Prepare failed: "  . $stmt1c->errno . " " . $stmt1c->error;
			}
			if(!$stmt1c->execute()){
				echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}
			if(!$stmt1c->bind_result($year)){ 
				echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}
			while($stmt1c->fetch()){
				//echo "<br><tr>\n<td> Year: " . $year . "\n</td>\n</tr><br>";
				}
			$stmt1c->close();
			
			//attempt selecting from day table a row with a corresponding date.  The day table in this case is intended to keep a running daily total of the water usage
			//If a matching date is not found, i.e. num_rows is 0, then a new row will be added to the day table starting that days running total.  Otherwise an update query
			//is invoked that updates the current total value on the given date in the day table to the new value, the 'added' boolean in the item table is updated in either case to reflect that
			//it has been accounted for.  There should not be multiple entries in the day table with the same date.  A test to double check the day table for mutliple entries
			if(!($stmt2 = $mysqli->prepare("SELECT day.total_gallons_used FROM day WHERE day.date = \"{$date1}\""))){
				echo "Prepare failed: "  . $stmt2->errno . " " . $stmt2->error;
			}
			
			if(!$stmt2->bind_result($total2)){ 
				echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}		
			if(!$stmt2->execute()){
			echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}		
			
			$stmt2->store_result ();
			
			if($stmt2->num_rows () > 0){						//If date found in day table, total2 is extracted
				while($stmt2->fetch()){
					//echo "<br>Total 2: " . $total2 . ".";
					}
				$stmt2->close();
					//echo "<br>Total 2: " . $total2 . ".";
					//echo "<br>Total 3: " . $total3 = $total2 + $total1 . ".<br>";			//update total2 to reflect change, store in total3
					$total3 = $total2 + $total1;
				if(!($stmt3 = $mysqli->query("UPDATE day SET total_gallons_used = \"{$total3}\" WHERE day.date = \"{$date1}\""))){		//update total3 in db
					echo "Execute failed: "  . $stmt3->errno . " " . $stmt3->error;
				}else{
					echo "Updated day, set total gallons used to " . $total3 . " where day.date = " . $date1 . ".<br>";
				}
				
				addWeekMonth($week, $month, $year, $total1, 1, $mysqli);				//updating the week table
				
				addWeekMonth($week, $month, $year, $total1, 2, $mysqli);				//updating the month table
				
				if(!($stmt3a = $mysqli->query("UPDATE item SET added = !added WHERE id = {$iid}"))){							//updated added status of total1 from item table
					echo "Execute failed: "  . $stmt3a->errno . " " . $stmt3a->error;
				}else{
					echo "Updated item, set added to 1.<br>";
				}
			}else{									//date not found, add new row to day table, and update added status of new entry
				$stmt2->close();
				if(!($stmt4 = $mysqli->prepare("INSERT INTO day(total_gallons_used, date) VALUES (?,?)"))){
					echo "Prepare failed: "  . $stmt4->errno . " " . $stmt4->error;
				}
				if(!($stmt4->bind_param("ds",$total1, $date1))){
						echo "Bind failed: "  . $stmt4->errno . " " . $stmt4->error;
				}			
				if(!$stmt4->execute()){
					echo "Execute failed: "  . $stmt4->errno . " " . $stmt4->error;
				} 
				else {
					echo "Added " . $stmt4->affected_rows . " rows to day.<br>";
					}
				
				addWeekMonth($week, $month, $year, $total1, 1, $mysqli);		//updating the week table
				
				addWeekMonth($week, $month, $year, $total1, 2, $mysqli);		//updating the month table
				
				if(!($stmt4a = $mysqli->query("UPDATE item SET added = !added WHERE id = {$iid}"))){					//updated added status of total1 from item table
					echo "Execute failed: "  . $stmt4a->errno . " " . $stmt4a->error;
				}else{
					echo "Updated item, set added to 1.<br>";
				}			
			}
	}		
}


if (isset ($_POST['addGoal'])) {	//Place for user to add usage goal
	
	$userGoal = $_POST['Goal'];

	if (($userGoal < 0)){     //error checking user input		!(filter_var($userGoal, FILTER_VALIDATE_FLOAT))
		echo "<br>Invalid input, please try again.<br><br>";	
	}else{
		if(!($stmt = $mysqli->prepare("INSERT INTO goal(goal) VALUES (?)"))){
		echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
		}
		if(!($stmt->bind_param("d",$userGoal))){
			echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
		}		
		if(!$stmt->execute()){
			echo "Execute failed: "  . $stmt->errno . " " . $stmt->error;
		}else {
			echo "Added " . $stmt->affected_rows . " rows to goal.";
		}		
    }
}

if (isset ($_POST['remove'])) {				//place for user to remove usage data, if said data was entered in error
	if(!($stmt = $mysqli->prepare("SELECT total, date FROM item WHERE id = {$_POST['itemID']}"))){
		echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->execute()){
		echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
	}
	if(!$stmt->bind_result($tot, $date)){ 
		echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
	}
	if(!$stmt->fetch()){
		echo "Fetch failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
	}
	$stmt->close();
	
	echo "<br><tr>\n<td> rTotal: " . $tot . "\n</td>\n</tr><br>";
	echo "<br><tr>\n<td> rDate: " . $date . "\n</td>\n</tr><br>";
	removeData($mysqli, $tot, $date);
	
	if(!($stmt = $mysqli->prepare("DELETE FROM item WHERE id = {$_POST['itemID']}"))){
		echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
	}
	if(!$stmt->execute()){
		echo "Execute failed: "  . $stmt->errno . " " . $stmt->error;
	}else {
		echo "Deleted " . $stmt->affected_rows . " row(s) from item.";
		}
	$stmt->close();
}
?>


<div>
	<table border = "1">
	
	<!--Display info from usage table-->
		<tr>
			<th>Demo Usage Information</th>
		</tr>
		<tr>
			<th>Name</th>
			<th>Flow Rate (gallons/minute)</th>
			<th>Duration (in minutes)</th>
			<th>Description</th>
			<th>Gallons Used</th>
			<th>Date</th>
		</tr>
		<!--Embedded php, contains prepare statement, throws error if fail, if not, binds user selection
 Runs the statement and returns an object that possesses queried information, output variable declaration,
 binding of the variables, then fetch relevant data and insert into bound variables. -->
 
 <?php 
			if(!($stmt = $mysqli->prepare("SELECT item.name, item.flow_rate, item.time_used, item.description, item.total, DATE(item.date) FROM item"))){
				echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
			}

			if(!$stmt->execute()){
				echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}
			if(!$stmt->bind_result($name, $flow_rate, $time_used, $description, $tot, $date)){ 
				echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}
			while($stmt->fetch()){
				echo "<tr>\n<td>" . $name . "\n</td>\n<td>\n" . $flow_rate . "\n</td>\n<td>\n" . $time_used . "\n</td>\n<td>\n" . $description . "\n</td>\n<td>\n" . $tot . "\n</td>\n<td>\n" . $date ."\n</td>\n</tr>";
			}
			$stmt->close();
			?>
	</table>
			<br>
			<br>
			
</div>
<br>
<br>
<br>
<div>
	<table border = "1">
	
	<!--Display info from usage table-->
		<tr>
			<th>Demo Daily Running Total</th>
		</tr>
		<tr>
			<th>Total Gallons Used</th>
			<th>Date</th>
		</tr>
		<!--Embedded php, contains prepare statement, throws error if fail, if not, binds user selection
 Runs the statement and returns an object that possesses queried information, output variable declaration,
 binding of the variables, then fetch relevant data and insert into bound variables. -->
 
 <?php 
			if(!($stmt = $mysqli->prepare("SELECT day.total_gallons_used, day.date FROM day"))){
				echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
			}

			if(!$stmt->execute()){
				echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}
			if(!$stmt->bind_result($total_gallons_used, $date)){ 
				echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}
			while($stmt->fetch()){
				echo "<tr>\n<td>" . $total_gallons_used . "\n</td>\n<td>\n" . $date ."\n</td>\n</tr>";
			}
			$stmt->close();
			?>
	</table>
			<br>
			<br>
			
</div>

<br>
<br>
<br>
<div>
	<table border = "1">
	
	<!--Display info from usage table-->
		<tr>
			<th>Demo Weekly Running Total</th>
		</tr>
		<tr>
			<th>Total Gallons Used</th>
			<th>Week of the year</th>
			<th>Month</th>
		</tr>
		<!--Embedded php, contains prepare statement, throws error if fail, if not, binds user selection
 Runs the statement and returns an object that possesses queried information, output variable declaration,
 binding of the variables, then fetch relevant data and insert into bound variables. -->
 
 <?php 
			if(!($stmt = $mysqli->prepare("SELECT week.total_gallons_used, week.week_number, week.month_number FROM week"))){
				echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
			}

			if(!$stmt->execute()){
				echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}
			if(!$stmt->bind_result($total_gallons_used, $weekoftheyear, $month)){ 
				echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}
			while($stmt->fetch()){
				echo "<tr>\n<td>" . $total_gallons_used . "\n</td>\n<td>\n" . $weekoftheyear . "\n</td>\n<td>\n" . $month . "\n</td>\n</tr>";
			}
			$stmt->close();
			?>
	</table>
			<br>
			<br>
			
</div>

<br>
<br>
<br>
<div>
	<table border = "1">
	
	<!--Display info from usage table-->
		<tr>
			<th>Demo Monthly Running Total</th>
		</tr>
		<tr>
			<th>Total Gallons Used</th>
			<th>Month</th>
			<th>Year</th>
		</tr>
		<!--Embedded php, contains prepare statement, throws error if fail, if not, binds user selection
 Runs the statement and returns an object that possesses queried information, output variable declaration,
 binding of the variables, then fetch relevant data and insert into bound variables. -->
 
 <?php 
			if(!($stmt = $mysqli->prepare("SELECT month.total_gallons_used, month.month_number, month.year_number FROM month"))){
				echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
			}

			if(!$stmt->execute()){
				echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}
			if(!$stmt->bind_result($total_gallons_used, $monthoftheyear, $year1)){ 
				echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
			}
			while($stmt->fetch()){
				echo "<tr>\n<td>" . $total_gallons_used . "\n</td>\n<td>\n" . $monthoftheyear . "\n</td>\n<td>\n" . $year1 . "\n</td>\n</tr>";
			}
			$stmt->close();
			?>
	</table>
			<br>
			<br>
			
</div>


<!--Insert new data into the table, Usage information html form-->
<br>
<p>Add Usage information:</p>
<div>
	<form method="post" action=""> 

		<fieldset>
			<legend>Usage</legend>
			<p>Name: <input type="text" name="Name" /></p>
			<p>Flow Rate: <input type="number" name="flowRate" step="0.1" /></p>
			<p>Duration: <input type="number" name="timeUsed" /></p>
			<p>Description: <input type="text" name="Description" /></p>
		</fieldset>
		<p><input type="submit" name="Add" value="Add Usage" /></p>
	</form>
</div>
</br>
<p>Daily Goal Usage Information:</p>
<div>
	<form method="post" action=""> 

		<fieldset>
			<legend>Daily Usage Goal</legend>
			<p>Enter amount (in Gal.): <input type="number" name="Goal" step="0.1" /></p>
			
		</fieldset>
		<p><input type="submit" name="addGoal" value="Add Goal" /></p>
	</form>
</div>
</br>
<div>
	<form method="post">
		<p>Remove Item</p>
		<select name="itemID">
			<?php 
				if(!($stmt = $mysqli->prepare("SELECT id, name FROM item"))){
					echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
				}

				if(!$stmt->execute()){
					echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
				}
				if(!$stmt->bind_result($itemID, $itemName)){ 
					echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
				}
				while($stmt->fetch()){
					echo '<option value=" '. $itemID . ' "> ' . $itemName . ' </option>\n';
				}
				$stmt->close();
			?>
		</select>
		<p><input type="submit" name="remove" value="Remove"/></p>
	</form>
</div>