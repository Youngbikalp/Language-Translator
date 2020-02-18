<?php
require_once 'login.php';
echo <<<_END
<html><head><title>Lame Translator</title> <style>.signup { border: 1px solid #999999;
    font: normal 14px helvetica; color:#444444; }</style>
    <script>
    function validate(form) {
    fail = validateUsername(form.username.value) 
    fail += validatePassword(form.password.value)
    if (fail == "") return true
    else { alert(fail); return false }
    } 
    </script>
    </head>
    <body>
    <table class="signup" border="0" cellpadding="2" cellspacing="5" bgcolor="#eeeeee">
    <th colspan="2" align="center">Signup Form</th> <form method="post" action="Final.php"
    onSubmit="return validate(this)"> 

    </tr><tr><td>Username</td>
    <td><input type="text" maxlength="32" name="username" /></td> </tr>
    <tr><td>Password</td>
    <td><input type="text" maxlength="32" name="password" /></td>
    <tr><td>Re-enter Password</td>
    <td><input type="text" maxlength="32" name="repassword" /></td>
    </tr><tr><td colspan="2" align="center"><input type="submit" name="signup"
    value="Signup" /></td> </tr>
    <tr><td>Already have an account?<p><a href=Userlogin.php>Login</a></p> </td></tr>
    </form>
    </table>
    <script src="validate.js"></script>
_END;

function mysql_fatal_error($msg, $connection)
{
    $msg2 = mysqli_error($connection);
    echo <<< _END
    <html> <body> <IMG SRC = "https://tenor.com/view/im-sorry-bow-puppy-eyes-apologize-gif-8326897.gif" </body></html>
_END;
}

// Check connection
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
register_user($connection);
//Adminlogin($connection);
function random_salt()
{
    $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()_+';
    return substr(
        str_shuffle($str_result),
        0,7);
}

function register_user($connection)
{
    if (isset($_POST['signup'])) {
        $salt1 = random_salt();
        $salt2 = random_salt();
        $username = mysql_fix_string($connection, $_POST['username']);
        $password = mysql_fix_string($connection, $_POST['password']);
        $repassword = mysql_fix_string($connection, $_POST['repassword']);
        $query = "CREATE TABLE `$username` ( `English` VARCHAR(100) NOT NULL , `Translation` VARCHAR(100) NOT NULL );";
        if ($password == $repassword) {
            $token = hash('ripemd128', "$salt1$password$salt2");
            $stmt = $connection->prepare("INSERT INTO Final VALUES(?,?,?,?)");
            $stmt->bind_param('ssss', $username, $salt1, $salt2, $token);
            $stmt->execute();
            printf("Row inserted.\n", $stmt->affected_rows);

            $result = $connection->query($query);
            if (!$result) die($connection->error);
        } else {
            echo "Password do not match please re-enter again.";
        }
    }
    $stmt->close();
    $result->close();
    $connection->close();
}
?>