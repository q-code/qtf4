<?php
/**
 * @var string $strSubject
 * @var string $strMessage
 */
$strSubject = "Profile mis � jour";

$strMessage = "
Cher parent/tuteur,

Nous vous informons votre enfant (login: %s) a chang� son profil sur le forum {$_SESSION[QT]['site_name']}. Vous pouvez contr�ler ces informations dans la page Profil.

Pour acc�der au forum vous aurez besoin de son login et mot de passe qui vous a �t� communiqu� dans un mail pr�c�dent.

---- COPPA ----
Ce mail vous est adress� parce que votre enfant nous a indiqu� �tre �g�(e) de moins de 13 ans et parce que ce forum applique les r�gles de COPPA (Children's Online Privacy Protection Act).
Pour en savoir plus sur le COPPA, visitez cette page: http://www.ftc.gov/opa/1999/10/childfinal.htm
Veuillez �galement prendre connaissance du r�glement de ce forum : {$_SESSION[QT]['site_url']}/qtf_privacy.php
------

Salutations,
Le webmaster de {$_SESSION[QT]['site_name']}
";