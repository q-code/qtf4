<?php
/**
 * @var string $strSubject
 * @var string $strMessage
 */
$strSubject = 'Welkom';

$strMessage = "
Welkom,

U bent nu een lid van het forum {$_SESSION[QT]['site_name']}.

Gelieve te vinden hier na uw login en wachtwoord om tot het forum {$_SESSION[QT]['site_name']} toegang te hebben.
U kunt dit wachtwoord in de sectie Profiel veranderen.

Gebruikersnaam: %s
Wachtwoord: %s

Vriendelijke groeten,
Webmaster van {$_SESSION[QT]['site_name']}
";