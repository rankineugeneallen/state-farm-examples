<?php
/**
 * Created by PhpStorm.
 * User: aarankin
 * Date: 5/14/2018
 * Time: 12:21 PM
 * Notes: To replace the Scripts.asp page to move solely to PHP
 * Runs SQL statement based on $Selection value and echos DB result
 */
/* connect to server */
$DBName = 'KresgeSeating'; //ChapelSeating
$servername = "ASLAN";
$connectionInfo = array("Database" => $DBName, "UID" => "REDACTED", "PWD" => "REDACTED");
$conn = sqlsrv_connect($servername, $connectionInfo);
global $conn;
$Selection = "";

if (!$conn)  {
 echo "Connection not established<br/>";
 die(print_r(sqlsrv_errors(), true));
}
if(isset($_GET['Selection']))
  $Selection = $_GET['Selection'];

switch($Selection){
  case "getSection":
    getSection();
    break;
  case "getAlignment":
    getAlignment();
    break;
  case "getStatus":
    getStatus();
    break;
  case "setStatus":
    setStatus();
    break;
  case "getOwnerID":
    genSession();
    break;
  case "getOwnedSeats":
    getOwnedSeats();
    break;
  case "clearSeat":
    clearSeat();
    break;
  case "endSession":
    endSession();
    break;
  case "setPrice":
    setPrice();
    break;
  case "getPrice":
    getPrice();
    break;
  case "getTotal":
    getTotal();
    break;
  case "timeOut":
    timeOut();
    break;
  case "storePayment":
    storePayment();
    break;
  case "getSeatID":
    getSeatID();
    break;
  case "checkDuplicate":
    checkDuplicate();
    break;
  default:
    break;
}

/**
 * This function gets the seating information for a given section when given a secion_id and event_id.
 *
 * GET Section_ID
 * GET Event_ID
 * Echo JSON Format Section_ID, Row_ID, Row_Number, Price, SPrice, Alignment, Transaction_ID, Status
 *
 * @return void
 */
function getSection() {
  global $conn;
  if(isset($_GET["Section_ID"]) && isset($_GET["Event_ID"])) {
    $SectionID = $_GET["Section_ID"];
    $EventID = $_GET["Event_ID"];
    
    $SQLStr = "SELECT * " .
              "FROM SectionView " .
              "WHERE Event_ID = '{$EventID}' AND Section_ID = '{$SectionID}' " .
              "ORDER BY Section_ID, Row_Number, Seat_ID;";
  
    $SectionStr = "[";
    $stmt = sqlsrv_query($conn, $SQLStr);
  
    if ($stmt === false) {
      errorHandling($stmt);
    }
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
      $SectionStr = $SectionStr . "{'Section_Id':'" . $row['Section_ID'] . "'," .
                                  "'Row_Id':'" . $row['Row_ID'] . "', " .
                                  "'Seat_Id':'" . $row['Seat_ID'] . "', " .
                                  "'Row_Number':'" . $row['Row_Number'] . "', " .
                                  "'Price':'" . number_format($row['Price'], 2,'.','') . "', " .
                                  "'SPrice':'" . number_format($row['SPrice'],2,'.','') . "', " .
                                  "'Alignment':'" . $row['Alignment'] . "', " .
                                  "'Transaction_ID':'" . $row['Transaction_ID'] . "', " .
                                  "'Status':'" . $row['Lock_Type'] . "'},";
    }
  
    $SectionStr = $SectionStr . "]";
    $SectionStr = str_replace("'", '"', $SectionStr);
    $SectionStr = str_replace(",]", ']', $SectionStr); //remove last , at end of query output
    echo $SectionStr;
  } else { echo("getSection(): Section_ID or Event_ID not set"); }
}

/**
 * This function returns the status of a given seat
 *
 * GET Section_ID
 * GET Row_ID
 * GET Seat_ID
 * GET Event_ID
 * Echo Lock_Type
 *
 * @return void
 */
function getStatus(){
  $Status = "";
  global $conn;
  
  if (isset($_GET["Section_ID"]) && isset($_GET["Row_ID"]) &&
      isset($_GET["Seat_ID"]) && isset($_GET["Event_ID"])){
    $Section_ID = $_GET['Section_ID'];
    $Row_ID = $_GET['Row_ID'];
    $Seat_ID = $_GET['Seat_ID'];
    $Event_ID = $_GET['Event_ID'];
  
    $SQLStr = "SELECT Lock_Type ".
              "FROM reserved ".
              "WHERE Event_ID = '{$Event_ID}' AND Section_ID = '{$Section_ID}' AND Row_ID = '{$Row_ID}' AND Seat_ID = '{$Seat_ID}';";
    
    $stmt = sqlsrv_query($conn, $SQLStr);
    
    if($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
      $Status = $row["Lock_Type"];
    }
    
    echo $Status;
  } else { echo("getStatus(): Error, Section_ID, Row_ID, Seat_ID or Event_ID not set"); }
}

/**
 * This function sets the status of a given seat if it is set to 0
 *
 * GET Section_ID
 * GET Row_ID
 * GET Seat_ID
 * GET Event_ID
 * GET Owner_ID
 * GET Status
 * Echo True is update was successful
 *
 * @return void
 */
function setStatus(){
  global $conn;
  
  if (isset($_GET["Section_ID"]) && isset($_GET["Row_ID"]) &&
      isset($_GET["Seat_ID"]) && isset($_GET["Event_ID"]) &&
      isset($_GET["Owner_ID"]) && isset($_GET["Status"])) {
    $Section_ID = $_GET['Section_ID'];
    $Row_ID = $_GET['Row_ID'];
    $Seat_ID = $_GET['Seat_ID'];
    $Event_ID = $_GET['Event_ID'];
    $ID = $_GET['Owner_ID'];
    $Status = $_GET['Status'];
  
    $SQLStr = "UPDATE Reserved " .
              "SET Lock_Type = {$Status}, Transaction_ID = {$ID} " .
              "WHERE Event_ID = '{$Event_ID}' AND Section_ID = '{$Section_ID}' AND Row_ID = '{$Row_ID}' AND Seat_ID = {$Seat_ID};";
    
    $stmt = sqlsrv_prepare($conn, $SQLStr, array());
    if (sqlsrv_execute($stmt) === false)
      errorHandling($stmt);
    else
      echo("True");
      
  } else { echo("setStatus(): Section_ID, Row_ID, Seat_ID, Event_ID, Owner_ID, or Status not set"); }
}

/**
 * This function returns the Session ID from the server
 *
 * ECHO Session_ID
 *
 * @return void
 */
function genSession(){
  global $conn;
  
  $SQLStr = "SELECT Max(Session_ID) AS Session_ID ".
    "FROM Session;";
  
  $stmt = sqlsrv_query($conn, $SQLStr);
  
  if($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
    $Session_ID = $row["Session_ID"];
  }
  
  if ($Session_ID == null){
    $Session_ID = 0;
  }
  
  $Session_ID = $Session_ID + 1; // increase max Session_ID by one
  
  $SQLStr = "INSERT INTO Session (Session_ID) ".
            "VALUES ({$Session_ID});";
  
  $stmt = sqlsrv_prepare($conn, $SQLStr, array());
  if (sqlsrv_execute($stmt) === false){
    errorHandling($stmt);
  }
  
  echo($Session_ID);
}

/**
 * This function returns all the seats for a given owner and event in a Json Parsable Text
 *
 * GET Owner_ID
 * GET Event_ID
 * ECHO JSON Format Section_ID, Row_ID, Seat_ID, Price, SPrice, Lock_Type
 *
 * @return void
 */
function getOwnedSeats(){
  global $conn;
  if(isset($_GET["Owner_ID"]) && isset($_GET["Event_ID"])) {
    $Event_ID = $_GET['Event_ID'];
    $Owner_ID = $_GET['Owner_ID'];
    
    $SQLStr = "SELECT Section_ID, Row_ID, Seat_ID, Price, SPrice, Lock_Type " .
              "FROM SectionView " .
              "WHERE Event_ID = '{$Event_ID}' AND Transaction_ID = '{$Owner_ID}' " .
              "ORDER BY Section_ID, Row_ID, Seat_ID ASC;";
    
    $SeatStr = "[";
    $stmt = sqlsrv_query($conn, $SQLStr);
    
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
      $SeatStr = $SeatStr ."{'Section_Id':'".$row["Section_ID"]."'," .
                            "'Row_Id':'" . $row["Row_ID"] . "'," .
                            "'Seat_Id':'" . $row["Seat_ID"] . "'," .
                            "'Price':'" . $row["Price"] . "'," .
                            "'SPrice':'" . $row["SPrice"] . "'," .
                            "'Lock_Type':'" . $row["Lock_Type"] . "'},";
    }
    
    $SeatStr = $SeatStr . "]";
    $SeatStr = str_replace("'", '"', $SeatStr);
    $SeatStr = str_replace(",]", ']', $SeatStr); //remove last , at end of query output
    
    echo $SeatStr;
    
  } else { echo("getOwnedSeats(): Owner_ID or Event_ID not set"); }
}

/**
 * Clears selection on seat
 *
 * GET Section_ID
 * GET Row_ID
 * GET Seat_ID
 * GET Event_ID
 *
 * @return void
*/
function clearSeat(){
  $Status = 0;
  $Owner = 0;
  global $conn;
  if (isset($_GET["Section_ID"]) && isset($_GET["Row_ID"]) &&
      isset($_GET["Seat_ID"]) && isset($_GET["Event_ID"])) {
    $Section_ID = $_GET['Section_ID'];
    $Row_ID = $_GET['Row_ID'];
    $Seat_ID = $_GET['Seat_ID'];
    $Event_ID = $_GET['Event_ID'];
  
  
    $SQLStr = "SELECT Lock_Type, Transaction_ID " .
              "FROM Reserved " .
              "WHERE Event_ID = '{$Event_ID}' AND Section_ID = '{$Section_ID}' AND Row_ID = '{$Row_ID}' AND Seat_ID = {$Seat_ID};";
  
    $stmt = sqlsrv_query($conn, $SQLStr);
  
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
      $Status = $row["Lock_Type"];
      $Owner = $row["Transaction_ID"];
    }
  
    $temp = 0 - 1;
    if ($Status <> $temp){
      echo($Status . " != " . $temp);
    }
    if ($Status <> $temp) {
      echo($Owner . " != " . $_GET['Owner_ID']);
    }
    if($Status != 1) {
  
      $SQLStr = "UPDATE Reserved " .
        "SET Lock_Type = 0, Transaction_ID = NULL, Price = NULL, Price_ID = 1 " .
        "WHERE Event_ID = '{$Event_ID}' AND Section_ID = '{$Section_ID}' AND Row_ID = '{$Row_ID}' AND Seat_ID = {$Seat_ID};";
  
      $stmt = sqlsrv_prepare($conn, $SQLStr, array());
  
      if (sqlsrv_execute($stmt) === false) {
        errorHandling($stmt);
      }
  
      echo("True");
    }
  } else { echo("clearSeat(): Section_ID, Row_ID, Seat_ID, or Event_ID not set"); }
}

/**
 * Deletes session_ID from the Session table when time expires
 *
 * GET Owner_ID
 *
 * @return void
 * */
function endSession(){
  global $conn;
  if (isset($_GET["Owner_ID"])) {
    $Session_ID = $_GET['Owner_ID'];
    
    $SQLStr = "DELETE FROM Session " .
              "WHERE Session_ID = {$Session_ID};";
    
    $stmt = sqlsrv_prepare($conn, $SQLStr, array());
    sqlsrv_execute($stmt);
    
    $SQLStr = "UPDATE Reserved " .
              "SET Transaction_ID = null, Lock_Type = 0 " .
              "WHERE Transaction_ID = {$Session_ID} AND Lock_Type = -1;";
    
    $stmt = sqlsrv_prepare($conn, $SQLStr, array());
    
    if (sqlsrv_execute($stmt) === false) {
      errorHandling($stmt);
    }
  } else { echo("endSession(): Owner_ID not set"); }
}


/**
 * This sets the price the user should pay for a given seat in accordance to a certain price type.
 *
 * GET Section_ID
 * GET Row_ID
 * GET Seat_ID
 * GET Event_ID
 * GET Price
 * GET P_ID
 *
 * @return void
 */
function setPrice(){
  global $conn;
  if (isset($_GET["Section_ID"]) && isset($_GET["Row_ID"]) &&
    isset($_GET["Seat_ID"]) && isset($_GET["Event_ID"]) &&
    isset($_GET["Price"]) && isset($_GET["P_ID"])) {
    $Section_ID = $_GET['Section_ID'];
    $Row_ID = $_GET['Row_ID'];
    $Seat_ID = $_GET['Seat_ID'];
    $Event_ID = $_GET['Event_ID'];
    $Price = $_GET['Price'];
    $P_ID = $_GET['P_ID'];
    
    $SQLStr = "UPDATE Reserved " .
      "SET Price = {$Price}, Price_ID = {$P_ID} " .
      "WHERE Event_ID = '{$Event_ID}' and Section_ID = '{$Section_ID}' and Row_ID = '{$Row_ID}' and Seat_ID = {$Seat_ID};";
    
    //$stmt = sqlsrv_query($conn, $SQLStr);
    $stmt = sqlsrv_prepare($conn, $SQLStr, array());
    if(sqlsrv_execute($stmt) === false)
      errorHandling($stmt);
    
  } else { echo("setPrice(): Section_ID, Row_ID, Seat_ID, Event_ID, or Price not set"); }
}

/**
 * Returns price of one seat
 *
 * GET Section_ID
 * GET Row_ID
 * GET Seat_ID
 * GET Event_ID
 * GET Owner_ID
 * GET Price_ID
 * ECHO Price
 *
 * @return void
 */
function getPrice(){
  global $conn;
  $Price = 0;
  if (isset($_GET["Section_ID"]) && isset($_GET["Row_ID"]) &&
      isset($_GET["Seat_ID"]) && isset($_GET["Event_ID"]) &&
      isset($_GET["Owner_ID"]) && isset($_GET["Price_ID"])) {
    $Section_ID = $_GET['Section_ID'];
    $Row_ID = $_GET['Row_ID'];
    $Seat_ID = $_GET['Seat_ID'];
    $Event_ID = $_GET['Event_ID'];
    $Owner_ID = $_GET['Owner_ID'];
    
    $SQLStr = "SELECT Price " .
              "FROM Reserved " .
              "WHERE Event_ID = '{$Event_ID}' AND Transaction_ID = '{$Owner_ID}' AND Section_ID = '{$Section_ID}' AND Row_ID = '{$Row_ID}' and Seat_ID = {$Seat_ID};";
  
    $stmt = sqlsrv_query($conn, $SQLStr);
  
    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
      $Price = $row["Price"];
    } else { errorHandling($row); }
  
    echo number_format((float)$Price, 2, '.', '');
  } else { echo "getPrice(): Section_ID, Row_ID, Seat_ID, Event_ID, Owner_ID, P_ID not set"; }
  
}

/**
 * Get the sum of all reserved seat prices for given user
 *
 * GET Owner_ID
 * GET Event_ID
 * ECHO Total
 *
 * @return void
 */
function getTotal(){
  global $conn;
  if (isset($_GET["Owner_ID"]) && isset($_GET["Event_ID"])) {
    $Owner_ID = $_GET['Owner_ID'];
    $Event_ID = $_GET['Event_ID'];
  
    $SQLStr = "SELECT SUM(Price) AS Total " .
              "FROM Reserved " .
              "WHERE Event_ID = '{$Event_ID}' AND Transaction_ID = '{$Owner_ID}';";
  
  
    $stmt = sqlsrv_query($conn, $SQLStr);
    if($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
      //echo $row["Total"];
      echo number_format((float)$row["Total"],'2', '.', '');
    } else { errorHandling($row); }
  } else { echo("getTotal(): Owner_ID, or Event_ID not set"); }
}

/**
 * Clears selection on seat
 *
 * GET Section_ID
 * GET Row_ID
 * GET Seat_ID
 * GET Event_ID
 *
 * @return void
 */
function timeOut(){
  global $conn;
  if(isset($_GET["Owner_ID"])) {
    $Session_ID = $_GET["Owner_ID"];
    
    if($Session_ID == ""){
      echo "SESSION_EXPIRED";
    } else {
      
      $SQLStr = "SELECT Session_ID " .
        "FROM Session " .
        "WHERE Start_Time < DATEADD(HOUR, -1, GETDATE()) AND Session_ID = {$Session_ID};";
      
      $stmt = sqlsrv_query($conn, $SQLStr);
      
      if ($stmt === false) {
        errorHandling($stmt);
      } else { //success
        $row = sqlsrv_fetch_array($stmt);
        if ($row == "")
          echo "";
        /*else if ($row === false) //redundant?
          errorHandling($row);*/
        else {
          echo("SESSION_EXPIRED");
          endSession();
        }
      }
    }
  } else
    echo("timeOut: Owner_ID not set");
}

/**
 * Stores payment in purchases when transaction goes through
 *
 * GET Ref_Num
 * GET Transaction_ID
 * GET Event_ID
 * GET First_Name
 * GET Last_Name
 * GET Email
 * GET Address
 * GET Phone
 * GET Seats
 * GET Cnum
 * GET CCType
 * GET Status
 * GET Total
 * ECHO true if addition was successful
 *
 * @return void
 */
function storePayment(){
  global $conn;
  if (isset($_GET["Ref_Num"]) && isset($_GET["Transaction_ID"]) && isset($_GET["Event_ID"]) &&
      isset($_GET["First_Name"]) && isset($_GET["Last_Name"]) && isset($_GET["Email"]) &&
      isset($_GET["Address"]) && isset($_GET["Phone"]) && isset($_GET["Seats"]) &&
      isset($_GET["Cnum"]) && isset($_GET["CCType"]) && isset($_GET["Status"]) && isset($_GET["Total"])){
    $Ref_Num = $_GET['Ref_Num'];
    $Transaction_ID = $_GET['Transaction_ID'];
    $Event_ID = $_GET['Event_ID'];
    $First_Name = $_GET['First_Name'];
    $Last_Name = $_GET['Last_Name'];
    $Email = $_GET['Email'];
    $Phone = $_GET['Phone'];
    $Address = $_GET['Address'];
    $Seats = $_GET['Seats'];
    $Cnum = ($_GET['Cnum']);
    $CCType = ($_GET['CCType']);
    $Status = $_GET['Status'];
    $Total = ($_GET['Total']);
    
    //set DateTime before storing
    $Date = date("m-d-y");
    $Time = date("h:i:sa");
  
    $SQLStr = "INSERT " .
              "INTO Purchases " .
              "VALUES ('{$Ref_Num}', '{$Transaction_ID}', '{$Event_ID}', '{$First_Name}', '{$Last_Name}', '{$Email}',
                       '{$Phone}', '{$Address}' , '{$Seats}', '{$Date}', '{$Time}', '{$Cnum}', '{$CCType}', '{$Status}', '{$Total}');";
   
    $stmt = sqlsrv_prepare($conn, $SQLStr, array());
    
    if (sqlsrv_execute($stmt) === false){
      echo false;
      errorHandling($stmt);
    } else {
      echo true;
    }
    
  } else { echo "storePayment(): Ref_Num, Transaction_ID, First_Name, Last_Name, Email, Phone, Address, Seats, Cnum, CCType, Status, Total not set"; }
}

/**
 * Sends back unique seat id out of seat data
 *
 * GET Transaction_IDE
 * ECHO JSON Format Event_ID, Section_ID, Row_ID, Seat_ID, Price_ID
 *
 * @return void
 *
 */
function getSeatID(){
  global $conn;
  $SeatStr = "";
  if (isset($_GET["Transaction_ID"])) {
    $Transaction_ID = $_GET['Transaction_ID'];
    
    $SQLStr = "SELECT Event_ID, Section_ID, Row_ID, Seat_ID, Price_ID " .
              "FROM Reserved " .
              "WHERE Transaction_ID='{$Transaction_ID}';";
    
    $stmt = sqlsrv_query($conn, $SQLStr);
    //$SeatStr = "[";
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
      $SeatStr = $SeatStr . $row["Event_ID"] . "-" .
                            $row["Section_ID"] . "-" .
                            $row["Row_ID"] . "-" .
                            $row["Seat_ID"] . "-" .
                            $row["Price_ID"] . ","; //"-" . $row["Price_ID"] . ",";
    }
    $SeatStr = substr($SeatStr,0 ,-1); //remove , from end
    echo $SeatStr;
  } else { echo "getSeatID(): Transaction_ID, First_Name, Last_Name, Email, Phone, Seats not set"; }
}


/**
 * Checks if there is a duplicate transaction in purchases table
 *
 * GET Transaction_ID
 * GET First_Name
 * GET Last_Name
 * GET Email
 * GET Phone
 * GET Seats
 * ECHO 1 for good submission
 * ECHO -1 for some duplicate data
 * ECHO -2 for all duplicate data
 *
 * @return void
 */
function checkDuplicate(){
  global $conn;
  if (isset($_GET["Transaction_ID"]) && isset($_GET["First_Name"]) &&
      isset($_GET["Last_Name"]) && isset($_GET["Email"]) &&
      isset($_GET["Phone"]) && isset($_GET["Seats"])) {
    $Transaction_ID = $_GET['Transaction_ID'];
    $First_Name = $_GET["First_Name"];
    $Last_Name = $_GET["Last_Name"];
    $Email = $_GET["Email"];
    $Phone= $_GET["Phone"];
    $Seats = $_GET["Seats"];
    //Address?
    
    $SQLStr = "SELECT Transaction_ID, First_Name, Last_Name, Email, Phone, Seats " .
              "FROM Purchases " .
              "WHERE Transaction_ID = {$Transaction_ID};";
    
    $stmt = sqlsrv_query($conn, $SQLStr);
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if ($row || $row == "") {
      if ($row == "") {
        echo "1"; // good submission, no duplicates
      } else {
        $First_NameD = $row["First_Name"];
        $Last_NameD = $row["Last_Name"];
        $EmailD = $row["Email"];
        $PhoneD = $row["Phone"];
        $SeatsD = $row["Seats"];
    
        // check if they are the same from the server
        if ($First_Name == $First_NameD && $Last_Name == $Last_NameD &&
          $Email == $EmailD && $Phone == $PhoneD && $Seats == $SeatsD)
          echo "-2"; // everything is the same.
        else
          echo "-1"; // duplicate transaction IDs. Maybe the user is paying with different card?
      }
    }
    else {
      echo "-3\n";
      errorHandling($stmt);
    }
  } else { echo "checkDuplicates(): Transaction_ID, First_Name, Last_Name, Email, Phone, Seats not set"; }
}

/**
 * Gets alignment of section for design
 *
 * GET Section_ID
 * ECHO Alignment
 *
 * @return void
 */
function getAlignment(){
  global $conn;
  if(isset($_GET["Section_ID"])) {
    $Section_ID = $_GET['Section_ID'];
    
    $SQLStr = "SELECT Alignment " .
      "FROM Seating " .
      "WHERE Section_ID = '{$Section_ID}';";
    
    $stmt = sqlsrv_query($conn, $SQLStr);
    
    if($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
      echo $row["Alignment"];
    } else { errorHandling($row); }
    
  } else { echo("getAlignment: Section_ID not set"); }
}

/**
  * For developemental purposes
  * Prints error from $get, kills error
  * 
  * @params $get - error code
  * @return void
  */
function errorHandling($get){
  $err = sqlsrv_errors(SQLSRV_ERR_ERRORS);
  echo($get);
  die(print_r($err, true));
}