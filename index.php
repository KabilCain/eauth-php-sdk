<?
error_reporting(E_ERROR);

/* Eauth implementation. */
require './eauth.php';

/* To create an instance of a Eauth class. */
$eauthAPI = new EauthAPI();

$current_time = time();

/* Check to see if the session has been signed by this user. */
if ((!isset($_SESSION['session_id'])) || (($current_time - $_SESSION['start_time']) > 600)) {
    if (!$eauthAPI->initRequest()) {
        /* If there is problem it will alert the user. */
        die("...");
    }
    $_SESSION['start_time'] = time();
}

/* You must edit this for sure. */
function sayToUser($message) {
    die($message);
}

/* Login request by the user.*/
if (isset($_POST['loginRequest'])) {
    $userName = $_POST['username'];
    $passWord = $_POST['password'];
    if ($eauthAPI->loginRequest($userName, $passWord)) {
        $_SESSION['username'] = $userName;
        header("Location: dashboard.php");
        exit();
    }
    else {
        /* If there is problem it will alert the user. */
        sayToUser($errorMessage);
    }
}

/* Register request by the user.*/
if (isset($_POST['registerRequest'])) {
    $userName = $_POST['username'];
    $emailAddress = $_POST['emailaddress'];
    $passWord = $_POST['password'];
    $licenseKey = $_POST['licensekey'];
    if ($eauthAPI->registerRequest($userName, $emailAddress, $passWord, $licenseKey)) {
        sayToUser("You can login now!");
    }
    else {
        /* If there is problem it will alert the user. */
        sayToUser($errorMessage);
    }
}

/* Upgrade request by the user. */
if (isset($_POST['upgrade'])) {
    $userName = $_POST['accountToUpgrade'];
    $licenseKey = $_POST['keyToRedeem'];
    if ($eauthAPI->upgradeRequest($userName, $licenseKey)) {
        sayToUser("The account has been successfully upgraded.");
    }
    else {
        /* If there is problem it will alert the user. */
        sayToUser($errorMessage);
    }
}

/* Password reset request by the user.*/
if (isset($_POST['reset'])) {
    $emailAddress = $_POST['emailaddress'];
    if ($eauthAPI->resetPasswordRequest($emailAddress)) {
        sayToUser("We have sent you an email. Please also check your spam folder.");
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
</body>
