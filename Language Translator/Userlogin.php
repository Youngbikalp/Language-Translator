<?php
require_once "login.php";
function mysql_fatal_error($msg, $connection)
{
    $msg2 = mysqli_error($connection);
    echo <<< _END
    <html> <body> <IMG SRC = "https://tenor.com/view/im-sorry-bow-puppy-eyes-apologize-gif-8326897.gif" </body></html>
_END;
}
if ($connection->connect_error) {
    die(mysql_fatal_error('Failed to connect', $connection));
}

function mysql_fix_string($connection, $string)
{
    if (get_magic_quotes_gpc()) $string = stripslashes($string);
    return $connection->real_escape_string($string);
}
function mysql_entities_fix_string($connection, $string)
{
    return htmlentities(mysql_fix_string($connection, $string));
}
Adminlogin($connection);
function Adminlogin($connection)
{
    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        $un_temp = mysql_entities_fix_string($connection, $_SERVER['PHP_AUTH_USER']);
        $pw_temp = mysql_entities_fix_string($connection, $_SERVER['PHP_AUTH_PW']);
        $query = "SELECT * FROM Final WHERE Username = '$un_temp'";
        $result = $connection->query($query);
        if (!$result) die($connection->error);
        elseif ($rows = $result->num_rows) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $result->close();
            $salt1 = $row['Salt1'];
            $salt2 = $row['Salt2'];
            $token = hash('ripemd128', "$salt1$pw_temp$salt2");
            if ($token == $row[Password]) {
                session_start();
                $_SESSION['Username'] = $un_temp; 
                $_SESSION['Password'] = $pw_temp;
                echo "Welcome $row[Username] </br>";
                header("location: continue.php");
            }
            else{
                die ("<p>Invalid Unsername or Password. Please Sign up if you have not.<a href=Final.php> SignUp</a></p>");
            }
        } else die("Invalid Username or Password </br>");
    } else {
        header("WWW-Authenticate: Basic realm=\"Restricted Section\"");
        header("HTTP\ 1.0 401 Unauthorized");
        die("Please enter your username and password </br>");
    }
    $connection->close();
}
?>