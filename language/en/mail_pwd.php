<?php
/**
 * @var string $strSubject
 * @var string $strMessage
 */
$strSubject = "New password"; 

$strMessage = "
Please find here after your login and password to access the board {$_SESSION[QT]['site_name']}.
You can change this password in the Profile section.

Login: %s
Password: %s

Regards,
The webmaster of {$_SESSION[QT]['site_name']}
";