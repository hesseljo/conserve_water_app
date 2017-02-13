<!--David DeRisi & David Hite - CS - 361 - Project B - "User Story #2" - cw-utils.php
		3/06/16-->
<!-- This php file, as well as any accompaniment was/were derived
from coding projects used in completion of both CS275 and CS290.-->

<?php
//This function is essentially the same as the script found in cw.php lines 106 to 167, it attempts to access the week/month table data based on date information
//if an entry exists, it is updated, otherwise a new row is added.  This function is invoked in cw.php, it accepts date data through the week, month and year variables
//as well as the newly added total which is manipulated to update the week and month tables, the flag indicates upon which table the subsequent operations are to occur,
//and the mysqli variable holds the database info and allow it to remain in scope for function execution.
function addWeekMonth($week, $month, $year, $total1, $flag, $mysqli) {
	/*
	echo "<br><tr>\n<td> Year: " . $year . "\n</td>\n</tr><br>";
	echo "<br><tr>\n<td> Month: " . $month . "\n</td>\n</tr><br>";
	echo "<br><tr>\n<td> Week: " . $week . "\n</td>\n</tr><br>";
	*/
	
	if ($flag == 1){
		$dateA = $week;					//variables holding date data respective of the flagged operation`
		$dateB = $month;
		
		//storing away query statements, respective of their flagged operation, for use later in adding/updating table data
		$queryA = "SELECT week.total_gallons_used FROM week WHERE week.week_number = \"{$dateA}\" AND week.month_number = \"{$dateB}\"";
		//$queryB = "UPDATE week SET total_gallons_used = \"{$total1}\" WHERE week.week_number = \"{$dateA}\" AND week.month_number = \"{$dateB}\"";
		$queryC = "INSERT INTO week(total_gallons_used, week_number, month_number) VALUES (?,?,?)";		
	}
	
	if ($flag == 2){
		$dateA = $month;
		$dateB = $year;	
		
		$queryA = "SELECT month.total_gallons_used FROM month WHERE month.month_number = \"{$dateA}\" AND month.year_number = \"{$dateB}\"";
		//$queryB = "UPDATE month SET total_gallons_used = \"{$total1}\" WHERE month.month_number = \"{$dateA}\" AND month.year_number = \"{$dateB}\"";
		$queryC = "INSERT INTO month(total_gallons_used, month_number, year_number) VALUES (?,?,?)";		
	}
	
	if(!($stmt2 = $mysqli->prepare($queryA))){
			echo "Prepare failed: "  . $stmt2->errno . " " . $stmt2->error;
		}
	if(!$stmt2->bind_result($total2)){ 
			echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
		}		
	if(!$stmt2->execute()){
			echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
		}		
		
	$stmt2->store_result ();
		
	if($stmt2->num_rows () > 0){						//If match found in table, total2 is extracted
		while($stmt2->fetch()){
			//echo "<br>Total 2: " . $total2 . ".";
			//$total1 += $total2;
			}
		$stmt2->close();
			echo "<br>Total 2: " . $total2 . ".<br>";
			//echo "<br>Total 1: " . $total1 += $total2 . ".<br>";			//update total1 to reflect change
			$total1 += $total2;
			
		if ($flag == 1){
			//these needed to come after the adjustment, otherwise the updated value would not be stored in the table
			$queryB = "UPDATE week SET total_gallons_used = \"{$total1}\" WHERE week.week_number = \"{$dateA}\" AND week.month_number = \"{$dateB}\"";
		}	
		if ($flag == 2){	
			
			$queryB = "UPDATE month SET total_gallons_used = \"{$total1}\" WHERE month.month_number = \"{$dateA}\" AND month.year_number = \"{$dateB}\"";		
		}		
		
		if(!($stmt3 = $mysqli->query($queryB))){		//update total3 in db
				echo "Execute failed: "  . $stmt3->errno . " " . $stmt3->error;
			}else{
				//echo "Updated week, set total gallons used to " . $total3 . " where day.date = " . $date1 . ".<br>";
				output($stmt3, $total1, $week, $month, $year, $flag, 1);
			}
	}else{									//date not found, add new row to week table, and update added status of new entry
			$stmt2->close();
			
			if(!($stmt4 = $mysqli->prepare($queryC))){
				echo "Prepare failed: "  . $stmt4->errno . " " . $stmt4->error;
			}
			if(!($stmt4->bind_param("dii",$total1,$dateA,$dateB))){
					echo "Bind failed: "  . $stmt4->errno . " " . $stmt4->error;
			}			
			if(!$stmt4->execute()){
				echo "Execute failed: "  . $stmt4->errno . " " . $stmt4->error;
			} 
			else {
				//echo "Added " . $stmt4->affected_rows . " rows to day.<br>";
				output($stmt4, $total1, $week, $month, $year, $flag, 2);
				}					
		}
}
//this function directs testing output based on the flag options passed into the function
//accepts stmt, sum, week, month, year, flagA and B, stmt holds query information, sum the updated total,
//week, month and year associated with this instance, flagA indicates whether this operation is on the week 
//or month table, and flagB indicates whether the operation was an INSERT or an UPDATE
//returns no value, outputs information regarding database interaction (fail/Success) and displays variable data for 
//comparison to actual table data.
function output($stmt, $sum, $week, $month, $year, $flagA, $flagB){
	if ($flagA == 1){
		if($flagB == 1){
			echo "Updated week, set total gallons used to " . $sum . " where week.week_number = " . $week . " and week.month_number = " . $month . ".<br>";
		}
		if($flagB == 2){
			echo "Added " . $stmt->affected_rows . " rows to week.<br>";
		}
	}
	
	if ($flagA == 2){
		if($flagB == 1){
			echo "Updated month, set total gallons used to " . $sum . " where month.month_number = " . $month . " and month.year_number = " . $year . ".<br>";
		}
		if($flagB == 2){
			echo "Added " . $stmt->affected_rows . " rows to month.<br>";
		}
	}	
}

//this function is essentially the reverse of addWeekMonth, where we access the tables and subtract data that is being removed from the item table.
function removeData($mysqli, $totalR, $dateR){
	echo "<br><tr>\n<td> Total to be removed: " . $totalR . "\n</td>\n</tr><br>";
	echo "<br><tr>\n<td> Date: " . $dateR . "\n</td>\n</tr><br>";
	//echo "<br><tr>\n<td> Year: " . $year . "\n</td>\n</tr><br>";
	//echo "<br><tr>\n<td> Month: " . $month . "\n</td>\n</tr><br>";
	//echo "<br><tr>\n<td> Week: " . $week . "\n</td>\n</tr><br>";
	
	
	//update day
	$queryD = "SELECT day.total_gallons_used FROM day WHERE DAY(day.date) = DAY('{$dateR}')";
	if(!($stmt5 = $mysqli->prepare($queryD))){
			echo "Prepare failed: "  . $stmt5->errno . " " . $stmt5->error;
		}
	if(!$stmt5->bind_result($total4)){ 
			echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
		}		
	if(!$stmt5->execute()){
			echo "Execute failed day: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
		}
	while($stmt5->fetch()){
				echo "<tr>\n<td>" . $total4 . "\n</td>\n</tr>";
			}
	$stmt5->close();
	
	$tempT = $total4 - $totalR;
	echo "<br><tr>\n<td> Daily Reduced total: " . $tempT . "\n</td>\n</tr><br>";
	
	$queryDa = "UPDATE day SET total_gallons_used = \"{$tempT}\" WHERE DAY(day.date) = DAY('{$dateR}')";
	
	if(!($stmt5a = $mysqli->query($queryDa))){		//update total3 in db
				echo "Execute failed (day): "  . $stmt5a->errno . " " . $stmt5a->error;
			}else{
				echo "Updated day, reduced total gallons used to " . $tempT . " where day.date = " . $dateR . ".<br>";
				//output($stmt3, $total1, $week, $month, $year, $flag, 1);
			}
	

	//update week
	$queryE = "SELECT week.total_gallons_used FROM week WHERE week.week_number = WEEK('{$dateR}')";
	if(!($stmt6 = $mysqli->prepare($queryE))){
			echo "Prepare failed: "  . $stmt6->errno . " " . $stmt6->error;
		}
	if(!$stmt6->bind_result($total5)){ 
			echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
		}		
	if(!$stmt6->execute()){
			echo "Execute failed (week): "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
		}
	while($stmt6->fetch()){
				echo "<tr>\nWeekly Total thus far: <td>" . $total5 . "\n</td>\n</tr><br>";
			}
	$stmt6->close();
	
	$tempT = $total5 - $totalR;
	$queryEa = "UPDATE week SET total_gallons_used = \"{$tempT}\" WHERE week.week_number = WEEK('{$dateR}')";
	
	if(!($stmt6a = $mysqli->query($queryEa))){		//update total3 in db
				echo "Execute failed: "  . $stmt6a->errno . " " . $stmt6a->error;
			}else{
				echo "Updated week, reduced total gallons used to " . $tempT . " where week.week_number = " . $dateR . ".<br>";
				//output($stmt3, $total1, $week, $month, $year, $flag, 1);
			}


	//update month
	$queryF = "SELECT month.total_gallons_used FROM month WHERE month.month_number = MONTH('{$dateR}')";
	if(!($stmt7 = $mysqli->prepare($queryF))){
			echo "Prepare failed: "  . $stmt7->errno . " " . $stmt7->error;
		}
	if(!$stmt7->bind_result($total6)){ 
			echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
		}		
	if(!$stmt7->execute()){
			echo "Execute failed (month): "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
		}
	while($stmt7->fetch()){
				echo "<tr>\nMonthlyTotal thus far: <td>" . $total6 . "\n</td>\n</tr><br>";
			}
	$stmt7->close();
	
	$tempT = $total6 - $totalR;
	$queryFa = "UPDATE month SET total_gallons_used = \"{$tempT}\" WHERE month.month_number = MONTH('{$dateR}')";
	
	if(!($stmt7a = $mysqli->query($queryFa))){		//update total3 in db
				echo "Execute failed: "  . $stmt7a->errno . " " . $stmt7a->error;
			}else{
				echo "Updated month, reduced total gallons used to " . $tempT . " where month.month_number = " . $dateR . ".<br>";
				//output($stmt3, $total1, $week, $month, $year, $flag, 1);
			}
}




?>