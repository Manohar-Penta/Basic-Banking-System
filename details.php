<!DOCTYPE html>
<html>
<head>
  <title>Customer Details</title>
  <style>
    /* CSS styles for the navigation bar */
    ul.navbar {
        box-shadow:1px 1px 2px black;
      list-style-type: none;
      margin: 0;
      padding: 0;
      overflow: hidden;
      background-color: rgba(127,138,211,255);
    }
    
    ul.navbar li {
      float: left;
    }
    
    ul.navbar li a {
      display: block;
      color: white;
      text-align: center;
      text-shadow: 1px 1px black;
      padding: 10px 30px;
      text-decoration: none;
    }
    
    ul.navbar li a:hover {
      background-color: rgba(51,62,139,255);
    }
    
    /* CSS styles for the table */
    table {
      border-collapse: collapse;
      width: 100%;
    }
    
    th, td {
      padding: 8px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    
    th {
      background-color: #f2f2f2;
    }
    div {
          background-color:rgba(218,223,255,255);
          color:white;
          border:2px black;
          border-radius:6px;
          text-shadow:0px 0px black;
          margin:10px 2px;
          padding : 3px;
          text-align: center;
      }
      h1 {
          background:rgba(127,138,211,255);
          font-size:40px;
          text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black;
          color:white;
          text-align:center;
          display: inline-block;
          border: 1px solid black;
          padding:5px;
          border-radius:4px;
      }
    p {
      text-shadow:1px 1px 4px rgba(51,62,139,255);
      font-size:30px;
      margin: 2px 50px;
      padding: 5px;
    }
    button {
      border-radius:4px;
      box-shadow:1px 1px 1px rgba(51,62,139,255);
      color:white;
      margin-top:15px;
      margin-bottom:15px;
      padding:10px;
      font-size:20px;
      background:rgba(51,62,139,255);
      position: relative;
    }
  </style>
</head>
<body>
  <!-- Navigation bar -->
  <ul class="navbar">
    <li><a href="index.php">Home</a></li>
    <li><a href="customers.php">Customers</a></li>
    <li><a href="transactions.php">Transactions</a></li>
  </ul>
  <div>
  <?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bankrecords";

// Get the customer ID from the query parameter
$customerID = $_GET["id"];

// Create a new MySQLi object
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Prepare a parameterized SQL statement to retrieve customer details
$sql = "SELECT * FROM customers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);

// Execute the statement and get the result
$stmt->execute();
$result = $stmt->get_result();

// Check if a row is returned
if ($result->num_rows == 1) {
  // Fetch the customer details
  $row = $result->fetch_assoc();
  
  // Display the customer details
  echo "<h1>Customer Details</h1>";
  echo "<p><strong>Name    :    </strong> " . $row["name"] . "</p>";
  echo "<p><strong>Email   :    </strong> " . $row["email"] . "</p>";
  echo "<p><strong>Balance :    </strong> " . $row["balance"] . "</p>";
  
  // Display the transfer button
  echo "<form action='transfer.php' method='post'>";
  echo "<input type='hidden' name='sender_id' value='" . $row["id"] . "'>";
  echo "<button style='text-align:center' type='submit' value='Transfer Money'>Transfer</button>";
  echo "</form>";
} else {
  echo "Customer not found.";
}

// Close the database connection
$stmt->close();
$conn->close();
?>  
</div>
</body>
</html>