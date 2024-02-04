<?php
/**
 * @var string $strSubject
 * @var string $strMessage
 */
$strSubject = "Nieuwe watchwoord";

$strMessage = "
Beste ouder/beschermer,

Wij delen u mee dat uw kinderen zijn/haar wachtwoord op het forum {$_SESSION[QT]['site_name']} heeft veranderd.

Gebruikersnaam: %s
Wachtwoord: %s

---- COPPA ----
Deze e-mail is verzonden naar u omdat uw kinderen heeft verklaard dat hij/ze jonger is dan 13 jaar oud en dit forum overeenkomstig het Akte is van de Bescherming van de Privacy van de Kinderen Online (COPPA). Om meer over COPPA te weten te komen, gelieve deze pagina te bezoeken http://www.ftc.gov/opa/1999/10/childfinal.htm. Gelieve ook de Privacy pagina van het Forum te lezen: {$_SESSION[QT]['site_url']}/qtf_privacy.php
------

Vriendelijke groeten,
Webmaster van {$_SESSION[QT]['site_name']}
";