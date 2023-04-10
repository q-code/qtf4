<?php
/**
 * @var string $strSubject
 * @var string $strMessage
 */
$strSubject = "Profiel verandering"; 

$strMessage = "
==================================
Autmatische e-mail - antwoord niet
==================================

Beste ouder/beschermer,

Wij delen u mee dat uw kinderen (login: %s) zijn/haar profiel op het forum {$_SESSION[QT]['site_name']} heeft veranderd.
U kunt deze informatie in de pagina Profiel herzien.

Om tot het forum toegang te hebben zult u zijn/haar login en wachtwoord nodig hebben (verzendt naar u in een vorige post).

---- COPPA ----
Deze e-mail is verzonden naar u omdat uw kinderen heeft verklaard dat hij/ze jonger is dan 13 jaar oud en dit forum overeenkomstig het Akte is van de Bescherming van de Privacy van de Kinderen Online (COPPA). Om meer over COPPA te weten te komen, gelieve deze pagina te bezoeken http://www.ftc.gov/opa/1999/10/childfinal.htm. Gelieve ook de Privacy pagina van het Forum te lezen: {$_SESSION[QT]['site_url']}/qtf_privacy.php
---------------

Vriendelijke groeten,
Webmaster van {$_SESSION[QT]['site_name']}
";