<?php
/**
 * @var string $strSubject
 * @var string $strMessage
 */
$strSubject = 'Welkom';

$strMessage = "
Beste ouder/beschermer,

Wij delen u mee dat uw kinderen op het forum {$_SESSION[QT]['site_name']} heeft geregistreerd.
Voor een nieuwe registratie, zullen wij uw overeenkomst nodig hebben (zie de regels COPPA).

Na registratie, zult u zijn informatie in de pagina Profiel kunnen herzien gebruikend zijn/haar wachtwoord.

Gebruikersnaam: %s
Wachtwoord: %s

Overeenkomstig de COPPA regels is deze rekening momenteel inactief. U moet de toestemmingsvorm invullen en per post of e-mail versturen terug naar de webmaster. De details zijn op de vorm zelf.

De vorm kan door deze pagina worden betreden: {$_SESSION[QT]['site_url']}/qtf_form_coppa.php

Zodra de beheerder deze vorm via e-mail of regelmatige post heeft ontvangen zal de rekening worden geactiveerd.
Gelieve niet te vergeten dat het wachtwoord is gecodeerd en wij kunnen het niet terugvinden.
Nochtans, als uw wachtwoord vergeet, kunnen we een nieuwe watchwoord voor u maken.

Vriendelijke groeten,,
Webmaster van {$_SESSION[QT]['site_name']}

---- COPPA ----
Deze e-mail is verzonden naar u omdat uw kinderen heeft verklaard dat hij/ze jonger is dan 13 jaar oud en dit forum overeenkomstig het Akte is van de Bescherming van de Privacy van de Kinderen Online (COPPA). Om meer over COPPA te weten te komen, gelieve deze pagina te bezoeken http://www.ftc.gov/opa/1999/10/childfinal.htm. Gelieve ook de Privacy pagina van het Forum te lezen: {$_SESSION[QT]['site_url']}/qtf_privacy.php
------
";