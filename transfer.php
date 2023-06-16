<?php
// Replace with your MySQL connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bankrecords";

// Create a new MySQLi object
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$senderID = $recipientID = $amount = "";
$errorMsg = "";

// Process the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["sender_id"])) {
        $senderID = $_POST["sender_id"];
    }
    if (isset($_POST["recipient_id"])) {
        $recipientID = $_POST["recipient_id"];
    }
    if (isset($_POST["amount"])) {
        $amount = $_POST["amount"];
    }

    // Validate form fields
    if (empty($senderID) || empty($recipientID) || empty($amount)) {
        $errorMsg = "Please fill in all the fields.";
    } else if ($senderID == $recipientID) {
        $errorMsg = "You cannot send money to yourself.";
    } else if (!is_numeric($amount) || $amount <= 0) {
        $errorMsg = "Please enter a valid positive amount.";
    } else {
        // Retrieve the sender's details
        $senderSQL = "SELECT name, balance FROM customers WHERE id = $senderID";
        $senderResult = $conn->query($senderSQL);
        $senderRow = $senderResult->fetch_assoc();

        if ($senderRow) {
            // Retrieve the recipient's details
            $recipientSQL = "SELECT name, balance FROM customers WHERE id = $recipientID";
            $recipientResult = $conn->query($recipientSQL);
            $recipientRow = $recipientResult->fetch_assoc();

            if ($recipientRow) {
                // Check if the sender has sufficient balance
                if ($amount <= $senderRow["balance"]) {
                    // Perform the transfer
                    $newSenderBalance = $senderRow["balance"] - $amount;
                    $newRecipientBalance = $recipientRow["balance"] + $amount;

                    // Update the sender's balance in the database
                    $updateSenderSQL = "UPDATE customers SET balance = $newSenderBalance WHERE id = $senderID";
                    $conn->query($updateSenderSQL);

                    // Update the recipient's balance in the database
                    $updateRecipientSQL = "UPDATE customers SET balance = $newRecipientBalance WHERE id = $recipientID";
                    $conn->query($updateRecipientSQL);

                    // Insert transaction details into the transfer table
                    $insertTransactionSQL = "INSERT INTO transfers (sender_name, receiver_name, amount) VALUES ('".$senderRow["name"]."', '".$recipientRow["name"]."', $amount)";
                    $conn->query($insertTransactionSQL);

                    // Redirect to the success page
                    header("Location: transactions.php");
                    exit();
                } else {
                    $errorMsg = "Insufficient balance. Please enter a lower amount.";
                }
            } else {
                $errorMsg = "Recipient not found.";
            }
        } else {
            $errorMsg = "Sender not found.";
        }
    }
}

// Retrieve the list of customers excluding the sender
$customersSQL = "SELECT id, name FROM customers WHERE id != $senderID";
$customersResult = $conn->query($customersSQL);

// Retrieve the sender's details
$senderSQL = "SELECT id, name, balance FROM customers WHERE id = $senderID";
$senderResult = $conn->query($senderSQL);
$senderRow = $senderResult->fetch_assoc();

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transfer Money</title>
    <style>
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
        p,h2 {
            text-shadow:1px 1px 4px rgba(51,62,139,255);
            font-size:25px;
            margin: 2px 30px;
            padding: 5px;
        }
        form {
            text-shadow:1px 1px 4px rgba(51,62,139,255);
            font-size:25px;
            margin: 2px 50px;
            padding: 5px;
        }
        .error-message {
            color: red;
            text shadow:0px 0px;
            font-size:12px;
            margin:2px 50px;
            padding:5px;
        }
        button {
            border-radius:4px;
            box-shadow:1px 1px 1px rgba(51,62,139,255);
            color:white;
            margin-top:15px;
            margin-bottom:15px;
            padding:5px;
            font-size:20px;
            background:rgba(51,62,139,255);
            position: relative;
        }
    </style>
</head>
<body>
<ul class="navbar">
    <li><a href="index.php">Home</a></li>
    <li><a href="customers.php">Customers</a></li>
    <li><a href="transactions.php">Transactions</a></li>
  </ul>
    <div>
    <h1>Transfer Money</h1>

    <?php if (!empty($errorMsg)) : ?>
        <p class="error-message"><?php echo $errorMsg; ?></p>
    <?php endif; ?>

    <?php if (isset($senderRow) && !empty($senderRow)) : ?>
        <h2>Sender: <?php echo $senderRow["name"]; ?></h2>
        <h2>Balance: <?php echo $senderRow["balance"]; ?></h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="sender_id" value="<?php echo htmlspecialchars($senderID); ?>">
            <label for="recipient">Recipient  :</label>
            <select name="recipient_id" id="recipient">
                <?php while ($customerRow = $customersResult->fetch_assoc()) : ?>
                    <option value="<?php echo htmlspecialchars($customerRow["id"]); ?>"><?php echo htmlspecialchars($customerRow["name"]); ?></option>
                <?php endwhile; ?>
            </select>
            <br>
            <label for="amount">Amount  :</label>
            <input type="number" name="amount" id="amount" step="0.01" min="0" required>
            <br>
            <button style='text-align:center' type='submit' value='Transfer Money'>Transfer</button>
        </form>
    <?php endif; ?>
                </div>
</body>
</html>
