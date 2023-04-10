<?php
/**
 * @var string $strSubject
 * @var string $strMessage
 */
$strSubject = "Welcome"; 

$strMessage = "
Welcome,

Your are now a member of the forum {$_SESSION[QT]['site_name']}.

Please find here after your login and password to access the board.
You can change this password in the Profile section.

Login: %s
Password: %s

Regards,
The webmaster of {$_SESSION[QT]['site_name']}
";