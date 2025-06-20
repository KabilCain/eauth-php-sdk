What is Eauth?
==============

https://eauth.us.to/ - Your #1 software login and authentication system, providing you with the most secure, flexible, and easy-to-use solutions.

Functions
-------------

```php
function initRequest();
```
```php
function logoutRequest();
```
```php
function loginRequest($userName, $passWord);
```
```php
function registerRequest($userName, $emailAddress, $passWord, $licenseKey);
```
```php
function upgradeRequest($userName, $licenseKey);
```
```php
function resetPasswordRequest($emailAddress);
```
```php
function resetHWIDRequest($userName);
```

Configuration
-------------

Navigate to `./eauth.php`, and fill these lines of code:

```php
/* Required configuration */
private const APPLICATION_TOKEN = "application_token_here"; // Your application token goes here
private const APPLICATION_SECRET = "application_secret_here"; // Your application secret goes here;
```
