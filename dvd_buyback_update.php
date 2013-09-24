<?php
  // Update dvd_buyback database to reflect that a member has 
  // received their free month of TitanMen
  $dbhost = 
  $dbuser = 
  $dbpass = 
  $dvd_dbname = 
  $dvd_tablename = 
  $pk = $_POST['pk'];
  $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dvd_dbname);
  $query = "UPDATE $dvd_tablename SET credited_free_month = 1 where pk = $pk";
  mysqli_query($conn, $query);
  mysqli_close($conn);

  // Send confirmation email
  $to = $_POST['email'];
  $subject = "TitanMen Discount Applied!";
  $dvd = $_POST['dvd'];
  $existing_mem = $_POST['username'];
  $name = $_POST['name'];
  $headers = "From: it@titanmedia.com\r\n";
  $headers .= "Reply-To: it@titanmedia.com\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
  $template = "dvd_buyback/credited.html";
  $message = strtr(file_get_contents($template), array('$name' => $name, 
                                                       '$dvd' => $dvd,
                                                       '$existing_mem' => $existing_mem));
  mail($to, $subject, $message, $headers);
?>
