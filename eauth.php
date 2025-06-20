<?
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class EauthAPI
{
    /* Required configuration */
    private const APPLICATION_TOKEN = "application_token_here"; // Your application token goes here
    private const APPLICATION_SECRET = "application_secret_here"; // Your application secret goes here;
  
    /* Advanced configuration */
    private const INVALID_REQUEST_MESSAGE = "Invalid request!";
    private const BUSY_SESSIONS_MESSAGE = "Please try again later!";
    private const UNAVAILABLE_SESSION_MESSAGE = "Invalid session.";
    private const USED_SESSION_MESSAGE = "Why did the computer go to therapy? Because it had a case of 'Request Repeatitis' and couldn't stop asking for the same thing over and over again!";
    private const OVERCROWDED_SESSION_MESSAGE = "Session limit exceeded.";
    private const EXPIRED_SESSION_MESSAGE = "Your session has timed out.";
    private const INVALID_USER_MESSAGE = "Incorrect login credentials!";
    private const BANNED_USER_MESSAGE = "Access denied!";
    private const INCORRECT_HWID_MESSAGE = "Hardware ID mismatch. Please try again with the correct device!";
    private const EXPIRED_USER_MESSAGE = "Your subscription has ended. Please upgrade to continue using your account!";
    private const USED_NAME_MESSAGE = "Username already taken. Please choose a different username!";
    private const INVALID_KEY_MESSAGE = "Invalid key. Please enter a valid key!";
    private const UPGRADE_YOUR_EAUTH_MESSAGE = "Upgrade your Eauth plan to exceed the limits!";
    private const INVALID_EMAIL_MESSAGE = "The email you entered is either already in use or unavailable or invalid!";
    private const UNAUTHORIZED_SESSION_MESSAGE = "Unauthorized access!";
    private const RESET_COOLDOWN = "Resetting is currently not allowed. Please try again ";
    private const ALREADY_HWID_RESET = "Your HWID is already null.";
    
    /* Runtime configuration */
    public $errorMessage;
    
    // Function to compute SHA-512 hash
    function compute_sha512($input_string) {
        return hash('sha512', $input_string);
    }
    
    // Function to generate Eauth header
    function generate_Eauth_header($message) {
        $auth_token = self::APPLICATION_SECRET . $message;
        return self::compute_sha512($auth_token);
    }
    
    // Function to generate a random string
    function generate_random_string($length = 18) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $random_string;
    }
    
    // Function to send POST request to Eauth
    function eauthRequest($request_data) {
        $url = 'https://eauth.us.to/api/1.2/';
        
        $headers = array(
            'Content-Type: application/json',
            'User-Agent: ' . self::generate_Eauth_header(json_encode($request_data))
        );
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $response;
    }
    
    function logEauthError($errorTextMessage) {
        global $errorMessage;
        $errorMessage = $errorTextMessage;
    }
    
    function initRequest() {
        $postData = array(
            'type' => "init",
            'token' => self::APPLICATION_TOKEN,
            'pair' => self::generate_random_string()
        );
        
        $dataArray = json_decode(self::eauthRequest($postData), true);
        $message = $dataArray['message'];
        
        if ($message == "init_success") {
            $_SESSION['session_id'] = $dataArray['session_id'];
            $_SESSION['logged_message'] = $dataArray['logged_message'];
            $_SESSION['registered_message'] = $dataArray['registered_message'];
            return true;
        }
        else if ($message == "invalid_request") {
            self::logEauthError(self::INVALID_REQUEST_MESSAGE); // This is usually not the case
            return false;
        }
        else if ($message == "maximum_sessions_reached") {
            self::logEauthError(self::BUSY_SESSIONS_MESSAGE);
            return false;
        }
        else if ($message == "init_paused") {
            self::logEauthError($dataArray['paused_message']);
            return false;
        }
        else if ($message == "user_is_banned") {
            self::logEauthError(self::BANNED_USER_MESSAGE);
            return false;
        }
    }
    
    function logoutRequest() {
        $_SESSION = array();
        session_unset();
        session_destroy();
        
        // Get an array of all cookies
        $cookies = $_COOKIE;
        
        // Loop through each cookie and delete it
        foreach ($cookies as $name => $value) {
            setcookie($name, '', time() - 3600, '/');
        }
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_regenerate_id(true);
        header("Location: .");
        exit();
    }
    
    function loginRequest($userName, $passWord) {
        $postData = array(
            'type' => "login",
            'session_id' => $_SESSION['session_id'],
            'username' => $userName,
            'password' => $passWord,
            'pair' => self::generate_random_string()
        );
        
        $dataArray = json_decode(self::eauthRequest($postData), true);
        $message = $dataArray['message'];
        
        if ($message == "login_success") {
            $_SESSION['hwid'] = $dataArray['hwid'];
            $_SESSION['rank'] = $dataArray['rank'];
            $_SESSION['register_date'] = $dataArray['register_date'];
            $_SESSION['expire_date'] = $dataArray['expire_date'];
            $_SESSION['last_login_date'] = $dataArray['last_login'];
            return true;
        }
        else if ($message == "session_unavailable") {
            self::logEauthError(self::UNAVAILABLE_SESSION_MESSAGE);
            self::logoutRequest();
            return false;
        }
        else if ($message == "invalid_request") {
            self::logEauthError(self::INVALID_REQUEST_MESSAGE); // This is usually not the case
            return false;
        }
        else if ($message == "session_already_used") {
            self::logEauthError(self::USED_SESSION_MESSAGE);
            return false;
        }
        else if ($message == "session_overcrowded") {
            self::logEauthError(self::OVERCROWDED_SESSION_MESSAGE);
            self::logoutRequest();
            return false;
        }
        else if ($message == "session_expired") {
            self::logEauthError(self::EXPIRED_SESSION_MESSAGE);
            self::logoutRequest();
            return false;
        }
        else if ($message == "account_unavailable") {
            self::logEauthError(self::INVALID_USER_MESSAGE);
            return false;
        }
        else if ($message == "user_is_banned") {
            self::logEauthError(self::BANNED_USER_MESSAGE);
            return false;
        }
        else if ($message == "subscription_expired") {
            self::logEauthError(self::EXPIRED_USER_MESSAGE);
            return false;
        }
    }
    
    function registerRequest($userName, $emailAddress, $passWord, $licenseKey) {
        $postData = array(
            'type' => "register",
            'session_id' => $_SESSION['session_id'],
            'username' => $userName,
            'email' => $emailAddress,
            'password' => $passWord,
            'key' => $licenseKey,
            'pair' => self::generate_random_string()
        );
        
        $dataArray = json_decode(self::eauthRequest($postData), true);
        $message = $dataArray['message'];
        
        if ($message == "register_success") {
            return true;
        }
        else if ($message == "session_unavailable") {
            self::logEauthError(self::UNAVAILABLE_SESSION_MESSAGE);
            self::logoutRequest();
            return false;
        }
        else if ($message == "invalid_email") {
            self::logEauthError(self::INVALID_EMAIL_MESSAGE);
            return false;
        }
        else if ($message == "name_already_used") {
            self::logEauthError(self::USED_NAME_MESSAGE);
            return false;
        }
        else if ($message == "key_unavailable") {
            self::logEauthError(self::INVALID_KEY_MESSAGE);
            return false;
        }
        else if ($message == "maximum_users_reached") {
            self::logEauthError(self::UPGRADE_YOUR_EAUTH_MESSAGE);
            return false;
        }
        else if ($message == "invalid_request") {
            self::logEauthError(self::INVALID_REQUEST_MESSAGE); // This is usually not the case
            return false;
        }
        else if ($message == "session_already_used") {
            self::logEauthError(self::USED_SESSION_MESSAGE);
            return false;
        }
        else if ($message == "session_overcrowded") {
            self::logEauthError(self::OVERCROWDED_SESSION_MESSAGE);
            self::logoutRequest();
            return false;
        }
        else if ($message == "session_expired") {
            self::logEauthError(self::EXPIRED_SESSION_MESSAGE);
            self::logoutRequest();
            return false;
        }
        else if ($message == "account_unavailable") {
            self::logEauthError(self::INVALID_USER_MESSAGE);
            return false;
        }
        else if ($message == "user_is_banned") {
            self::logEauthError(self::BANNED_USER_MESSAGE);
            return false;
        }
    }
    
    function upgradeRequest($userName, $licenseKey) {
        $postData = array(
            'type' => "upgrade",
            'session_id' => $_SESSION['session_id'],
            'username' => $userName,
            'key' => $licenseKey,
            'pair' => self::generate_random_string()
        );
        
        $dataArray = json_decode(self::eauthRequest($postData), true);
        $message = $dataArray['message'];
        
        if ($message == "upgrade_success") {
            if (isset($_SESSION['expire_date'])) {
                $_SESSION['expire_date'] = $dataArray['expire_date'];
            }
            return true;
        }
        else if ($message == "session_unavailable") {
            self::logEauthError(self::UNAVAILABLE_SESSION_MESSAGE);
            self::logoutRequest();
            return false;
        }
        else if ($message == "invalid_request") {
            self::logEauthError(self::INVALID_REQUEST_MESSAGE); // This is usually not the case
            return false;
        }
        else if ($message == "session_already_used") {
            self::logEauthError(self::USED_SESSION_MESSAGE);
            return false;
        }
        else if ($message == "session_overcrowded") {
            self::logEauthError(self::OVERCROWDED_SESSION_MESSAGE);
            self::logoutRequest();
            return false;
        }
        else if ($message == "session_expired") {
            self::logEauthError(self::EXPIRED_SESSION_MESSAGE);
            self::logoutRequest();
            return false;
        }
        else if ($message == "account_unavailable") {
            self::logEauthError(self::INVALID_USER_MESSAGE);
            return false;
        }
        else if ($message == "key_unavailable") {
            self::logEauthError(self::INVALID_KEY_MESSAGE);
            return false;
        }
        else if ($message == "subscription_expired") {
            self::logEauthError(self::EXPIRED_USER_MESSAGE);
            return false;
        }
    }
    
    function resetPasswordRequest($emailAddress) {
        $postData = array(
            'type' => "reset_password",
            'session_id' => $_SESSION['session_id'],
            'email' => $emailAddress,
            'pair' => self::generate_random_string()
        );
        
        $dataArray = json_decode(self::eauthRequest($postData), true);
        $message = $dataArray['message'];
        
        if ($message == "sent_email") {
            return true;
        }
        else if ($message == "invalid_email") {
            self::logEauthError(self::INVALID_EMAIL_MESSAGE);
            return false;
        }
        else if ($message == "session_unavailable") {
            self::logEauthError(self::UNAVAILABLE_SESSION_MESSAGE);
            self::logoutRequest();
            return false;
        }
        else if ($message == "invalid_request") {
            self::logEauthError(self::INVALID_REQUEST_MESSAGE); // This is usually not the case
            return false;
        }
        else if ($message == "session_expired") {
            self::logEauthError(self::EXPIRED_SESSION_MESSAGE);
            self::logoutRequest();
            return false;
        }
        else if ($message == "account_unavailable") {
            self::logEauthError(self::INVALID_USER_MESSAGE);
            return false;
        }
        else if ($message == "session_unauthorized") {
            self::logEauthError(self::UNAUTHORIZED_SESSION_MESSAGE);
            return false;
        }
    }
    
    function resetHWIDRequest($userName) {
        $postData = array(
            'type' => "hardware_reset",
            'session_id' => $_SESSION['session_id'],
            'username' => $userName,
            'pair' => self::generate_random_string()
        );
        
        $dataArray = json_decode(self::eauthRequest($postData), true);
        $message = $dataArray['message'];
        
        if ($message == "reset_success") {
            $_SESSION['hwid'] = "0";
            return true;
        }
        else if ($message == "cooldown_not_reached") {
            self::logEauthError(self::RESET_COOLDOWN . $dataArray['estimated_reset_time']);
            return false;
        }
        else if ($message == "session_unavailable") {
            self::logEauthError(self::UNAVAILABLE_SESSION_MESSAGE);
            self::logoutRequest();
            return false;
        }
        else if ($message == "invalid_request") {
            self::logEauthError(self::INVALID_REQUEST_MESSAGE); // This is usually not the case
            return false;
        }
        else if ($message == "session_expired") {
            self::logEauthError(self::EXPIRED_SESSION_MESSAGE);
            self::logoutRequest();
            return false;
        }
        else if ($message == "session_unauthorized") {
            self::logEauthError(self::UNAUTHORIZED_SESSION_MESSAGE);
            return false;
        }
        else if ($message == "invalid_user") {
            self::logEauthError(self::ALREADY_HWID_RESET);
            return false;
        }
    }
    
}
?>
