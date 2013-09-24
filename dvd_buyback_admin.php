<?php
  // Username and password for authentication
  $username = 
  $password = 
 // The username/password are incorrect so send the authentication headers
  if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_USER'] != $username || ($_SERVER['PHP_AUTH_PW'] != $password)) {
    header("HTTP/1.1 401 Unauthorized");
    header("WWW-Authenticate: Basic realm='DVD BuyBack Admin'");
}

?>
<!DOCTYPE html>
<html>
  <head>
  <!-- I know, putting style tags in the head rather than in a separate css file
  Is not the best "style", but this is a one-off, interior facing admin page which
  won't require much maintainance. -->
  <style>
    table {
      border-spacing: 0;   
    }
    th, td {
      padding: 8px;
      border: 1px solid black;
    }
    button {
      margin-left: 15px;
    } 
  </style>
  <script src="js/libs/jquery-1.7.1.min.js"></script>
  <script>
  $(document).ready(function() {
    $('button').click(function() {
      cell = $(this).parent();
      pk = $(this).attr('pk');
      name = $('#name').text();
      username = $('#username').text();
      email = $('#email').text();
      dvd = $('#dvd').text();
      $.ajax({
        type: "POST",
        url: "/dvd_buyback_update.php",
        data: {pk: pk,
               name: name, 
               username: username, 
               email: email,
               dvd: dvd},
        success: function() { 
          cell.text('Yes');
        }
      });
    });
  });
  </script>
  </head>
  <body>
    <h1>DVD BuyBack Admin</h1>
    <p>Below is a list of all existing members who have enrolled in our DVD BuyBack Program</p>
    <hr>
    <table>
      <tr>
        <th>Member Real Name</th>
        <th>Member UserName</th>
        <th>DVD Returned</th>
        <th>Email</th>
        <th>Date Enrolled</th>
        <th>Confirmation #</th>
        <th>Credited Free Month</th>
      </tr> 
    <?php
      $dbhost = "cs890lan";
      $dbuser = "dvd_buyback_user";
      $dbpass = "J9TCPygVNcvMXMqZwx";
      $dvd_dbname = "dvd_buyback";
      $dvd_tablename = "dvd_buyback";
      $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dvd_dbname);
      $query = "SELECT pk, name, existing_member, dvd, email, submission_date, case when credited_free_month is not null then 'Yes' else 'No' end as credited from $dvd_tablename where existing_member is not null order by pk";
      $result = mysqli_query($conn, $query);
      while ($row = mysqli_fetch_array($result)) {
          echo "<tr><td id='name'>".$row['name']."</td><td id='username'>".$row['existing_member']."</td><td id='dvd'>".$row['dvd']."</td><td id='email'>".$row['email']."</td><td>".$row['submission_date']."</td><td>JXKDKFL".$row['pk']."</td><td>".$row['credited'];
          if ($row['credited'] == 'No')
              echo '<button pk="'.$row['pk'].'">Credited!</button>';
          echo "</td></tr>";
      }
    ?>
      </table>
  </body>
</html>
