<?php
$dbhost = "";
$dbuser = "";
$dbpass = "";
$dvd_dbname = "";
$dvd_tablename = "";
$nats_dbname = "";
$nats_tablename = "";
$dvd = $_POST["DVDBuyBack"];
$name = $_POST["fullname"];
$email = $_POST["email"];
$nats_code = $_POST["nats_code"];
$now = date('Y-m-d H-i-s');

// If existing member not passed in, set as null, otherwise set as its value
$existing_mem = (empty($_POST["existing_mem"])) ? "NULL" : "'".$_POST["existing_mem"]."'";

$valid_array = array('valid' => true, 'dvd_error' => false, 'fullname_error' => false,
    'email_error' => false, 'existing_mem_error' => false, 'pk' => null);
//
// Validate data - set errors if any
if ($dvd == "") {
    $valid_array["valid"] = false;
    $valid_array["dvd_error"] = "Please enter a DVD title.";
}
if ($name == "") {
    $valid_array["valid"] = false;
    $valid_array["fullname_error"] = "Please enter your name.";
}
if ($email != "") {
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $valid_array["valid"] = false;
        $valid_array["email_error"] = "Please enter a valid email address.";
    }
} else {
    $valid_array["valid"] = false;
    $valid_array["email_error"] = "Please enter a email address.";
}
// Check the nats db to see if the username passed in exists, and is active
if ($existing_mem != "NULL") {
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $nats_dbname);
    if (mysqli_connect_errno())
        die("Could not connect to database: " . mysqli_error());
    $query = "SELECT username, status from $nats_tablename where username = $existing_mem and siteid in (2, 8)";
    $result = mysqli_query($conn, $query);

    // No record found for that username
    if (mysqli_num_rows($result) == 0) {
        $valid_array["valid"] = false;
        $valid_array["existing_mem_error"] = "Sorry, that username doesn't match" . 
            " our records. Please enter a valid username";
    } else {
        $row = mysqli_fetch_array($result);
        // status 1 == an active member, all other statuses considered invalid
        if ($row['status'] != 1) {
            $valid_array["valid"] = false;
            $valid_array["existing_mem_error"] = "Sorry, that is not an active " .
                "account. Please continue without entering your old account " .
                "information. You will be able to create a new account to receive" . 
                " the discount";
        }
    }
    // Close the connection
    mysqli_close($conn);
}

// Finally, check if the username that is being passed in (assuming 
// they entered one) has already been added to the db (ie, they have 
// already taken advantage of the offer. We only allow 1 per customer).
if ($existing_mem != "NULL") {
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dvd_dbname);
    if (mysqli_connect_errno())
        die("Could not connect to database: " . mysqli_error());
    $query = "SELECT existing_member from $dvd_tablename where existing_member = $existing_mem";
    $result = mysqli_query($conn, $query);

    // They are already in the database
    if (mysqli_num_rows($result) > 0) {
        $valid_array["valid"] = false;
        $valid_array["existing_mem_error"] = "OOPS! Looks like you've already" .
            " taken advantage of our DVD BuyBack Progam. Sorry, but we only allow" .
            " one DVD per customer. Worth a shot though!";
    }
    // Close the connection
    mysqli_close($conn);
}

// If form data is invalid, stop script here and send back errors
if (!$valid_array['valid']) {
    echo json_encode($valid_array);
    exit;
}

// create connection to db
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dvd_dbname);
if (mysqli_connect_errno())
    die("Could not connect to database: " . mysqli_error());
// escape values, construct query
$dvd = mysqli_real_escape_string($conn, $dvd);
$name = mysqli_real_escape_string($conn, $name);
$email = mysqli_real_escape_string($conn, $email);
$query = "INSERT INTO $dvd_tablename (dvd, name, email, submission_date, existing_member, nats_code)" . 
         "VALUES" . "('$dvd', '$name', '$email', '$now', $existing_mem, '$nats_code')";

// insert data in db, exit if failure
if (!mysqli_query($conn, $query))
    die("Could not enter data: " . mysqli_error());

// Get the primary key of last insert (to be used for the 
// confirmation number
$valid_array['pk'] = mysqli_insert_id($conn);

// close connection
mysqli_close($conn);

// Construct email
$to = $_POST['email'];
$subject = "Your TitanMen Discount!";
$headers = "From: it@titanmedia.com\r\n";
$headers .= "Reply-To: it@titanmedia.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
if ($existing_mem == "NULL")
    $template = "dvd_buyback/dvd_email_nonmember.html";
else {
    $template = "dvd_buyback/dvd_email_member.html";
    $headers .= "Bcc: james.sutterfield@gmail.com\r\n";
}

// Open email template, read in contents, and substitute in recipients name
$message = strtr(file_get_contents($template), array('$name' => $name, 
                                                     '$dvd' => $dvd,
                                                     '$nats_code' => $nats_code,
                                                     '$pk' => $valid_array['pk'],
                                                     '$existing_mem' => $existing_mem));

// and off it goes
mail($to, $subject, $message, $headers);

echo json_encode($valid_array);

?>
