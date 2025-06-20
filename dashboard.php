<?
error_reporting(E_ERROR);

/* To create an instance of a Eauth class. */
require './eauth.php';

/* Check if user is already logged in or not. */
if (!isset($_SESSION['expire_date'])) {
  header("Location: index.php");
  exit();
}

$current_time = time();

/* To create an instance of a Eauth class. */
$eauthAPI = new EauthAPI();

/* Check to see if the session has been signed by this user. */
/* It also check if 10 minutes have passed. You can freely change the duration to whatever your users can stay logged in for. */
if ((!isset($_SESSION['session_id'])) || (($current_time - $_SESSION['start_time']) > 10 * 60)) {
    $eauthAPI->logoutRequest();
}

/* logout request by the user.*/
if (isset($_POST['logoutAccount'])) {
    $eauthAPI->logoutRequest();
}

/* You must edit this for sure. */
function sayToUser($message) {
    die($message);
}

/* Reset HWID of the user account. */
if (isset($_POST['resetHWID'])) {
    $userName = $_SESSION['username'];
    if ($eauthAPI->resetHWIDRequest($userName)) {
        sayToUser("Your HWID was successfully reset.");
        }
    else {
        /* If there is problem it will alert the user. */
        sayToUser($errorMessage);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Meta tags -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />

     <!-- Favicon and title -->
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="icon" href="./pyim-icon.png">
    <title>Eauth PHP SDK</title>
    <meta name="description" content="PyImmunity.us.to is Your Powerful Python Obfuscator, Ultimately providing you with the most secure benefits and easy to use on an obfuscator.">
  </head>
<body>
  <h1>Examples of all the functions you may need are provided in the PHP code.</h1>
  <p>Simply use the POST method to call any of them with the required fields.</p>
  <br>
  <p>Name: <? echo $_SESSION['username']; ?></p>
  <p>HWID: <? echo $_SESSION['hwid']; ?></p>
  <p>Register Date: <? echo $_SESSION['register_date']; ?></p>
  <p>Expire Date: <? echo $_SESSION['expire_date']; ?></p>
</body>
