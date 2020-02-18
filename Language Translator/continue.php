<?php
require_once "login.php";
session_start();

echo <<<_END
<html><head><title>Online Virus Check</title></head><body>
<form method="post" action="continue.php" enctype="multipart/form-data">
Upload Translation File:<input type="file" name="file">
<input type="submit" name="translationFile" value="Upload"> </br>
Enter text to translate:<input type="text" name="translateWord">
<input type="submit" name="translateKey" value="Translate"> </br>
<input type="submit" name="logout" value="Logout"></br>
</form>
_END;

$_SESSION['ua'] = $_SERVER['HTTP_USER_AGENT'];
if ($_SESSION['ua'] != $_SERVER['HTTP_USER_AGENT']) different_user();

$_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
if ($_SESSION['check'] != hash('ripemd128', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])) different_user();

if (!isset($_SESSION['initiated'])) {
    session_regenerate_id();
    $_SESSION['initiated'] = 1;
}

if (!isset($_SESSION['count'])) $_SESSION['count'] = 0;
else ++$_SESSION['count'];

function mysql_fix_string($connection, $string)
{
    if (get_magic_quotes_gpc()) $string = stripslashes($string);
    return $connection->real_escape_string($string);
}

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
start_session($connection);
logout();
function start_session($connection)
{

    if (isset($_SESSION['Username'])) {
        $username = $_SESSION['Username'];
        // $password = $_SESSION['Password'];
        echo "Logged in as $username.<br />";
        echo "Please search or upload the translations<br />";
        $handle = fopen($_FILES['file']['tmp_name'], "r");
        if (!empty($handle)) {
            if (isset($_POST['translationFile'])) {
                if ($_FILES['file']['name']) {
                    $filename = explode('.', $_FILES['file']['name']);
                    if (end($filename) == "txt") {
                        $i = 0;
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            if ($i > 0) {
                                $stmt = $connection->prepare("INSERT INTO $username VALUES(?,?)");
                                $data0 =  mysql_fix_string($connection, $data[0]);
                                $data1 = mysql_fix_string($connection, $data[1]);
                                $stmt->bind_param('ss', $data0, $data1);
                                $stmt->execute();
                            }
                            $i = 1;
                        }
                    } else {
                        echo "Please select text files only";
                    }
                }
            } else {
                "File Field is empty choose a file to upload";
            }
            $stmt->close();
        }

        if (($_POST['translateKey'])) {
            $translateWord = mysql_fix_string($connection, $_POST['translateWord']);
            //$words = explode(' ', $translateWord);

            if (!empty($translateWord)) {
                $query = "SELECT * FROM $username WHERE English IN ('$translateWord')";
                $result = $connection->query($query);
                if (mysqli_num_rows($result) > 0) {
                    if (!$result) die("Database access failed: " . $connection->error);
                    $rows = $result->num_rows;
                    //echo "<table><tr><th>Translation</th></tr>";
                    for ($j = 0; $j < $rows; ++$j) {
                        $result->data_seek($j);
                        $row = $result->fetch_array(MYSQLI_ASSOC);

                        echo "<tr>";
                        echo "<td>" . "Translation word is ---->" . $row['Translation'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "You don't have " . $translateWord . " in the database</br>";
                    echo "If your database is empty, please search keyword in English such as Hello and How to see the translation</br>";
                    echo "If you would like to insert your own translation please upload the translation</br>";
                    $query = "SELECT * FROM DefaultTranslation WHERE English IN ('$translateWord')";
                    $result = $connection->query($query);
                    if (!$result) die("Database access failed: " . $connection->error);
                    $rows = $result->num_rows;
                    //echo "<table><tr><th>Translation</th></tr>";
                    for ($j = 0; $j < $rows; ++$j) {
                        $result->data_seek($j);
                        $row = $result->fetch_array(MYSQLI_ASSOC);

                        echo "<tr>";
                        echo "<td>" . "Defalut Translation word is ---->" . $row['Translation'] . "</td>";
                        echo "</tr>";
                    }
                }
            }
        } else {
            echo "Search field is empty";
        }
        
    } else echo "Please <a href=Userlogin.php>click here</a> to login";
    $result->close();
    $connection->close();
}

function logout()
{
    if (isset($_POST['logout'])) {
        destroy_session_and_data();
        header("loaction: Userlogin.php");
    }
}

function different_user()
{
    destroy_session_and_data();
    header("location: Userlogin.php");
}
function destroy_session_and_data()
{
    $_SESSION = array();
    if (session_id() != "" || isset($_COOKIE[session_name()]))
        setcookie(session_name(), '', time() - 2592000, '/');
    session_destroy();
}
?>