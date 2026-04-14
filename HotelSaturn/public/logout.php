<?php
session_start();
// Vide le tableau de session
$_SESSION = array();
// Supprime le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
// Détruit la session
session_destroy();
header("Location: index.php");
exit();
?>