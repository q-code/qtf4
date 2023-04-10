<?php
/**
 * @var string $strSubject
 * @var string $strMessage
 */
$strSubject = "Nouveau mot de passe"; 

$strMessage = "
Cher parent/tuteur,

Nous vous informons votre enfant a changé son mot de passe sur le forum {$_SESSION[QT]['site_name']}.

Utilisateur: %s
Mot de passe: %s

---- COPPA ----
Ce mail vous est adressé parce que votre enfant nous a indiqué être àgé(e) de moins de 13 ans et parce que ce forum applique les règles de COPPA (Children's Online Privacy Protection Act).
Pour en savoir plus sur le COPPA, visitez cette page: http://www.ftc.gov/opa/1999/10/childfinal.htm
Veuillez également prendre connaissance du règlement de ce forum : {$_SESSION[QT]['site_url']}/qtf_privacy.php
----------------

Salutations,
Le webmaster de {$_SESSION[QT]['site_name']}
";