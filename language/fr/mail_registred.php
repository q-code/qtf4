<?php
/**
 * @var string $strSubject
 * @var string $strMessage
 */
$strSubject = "Bienvenue"; 

$strMessage = "
Bienvenue,

Vous �tes � pr�sent membre du forum {$_SESSION[QT]['site_name']}.

Veuillez trouver ci-apr�s votre login et mot de passe.
Vous pouvez changer ce mot de passe dans votre page Profil.

Utilisateur: %s
Mot de passe: %s

Salutations,
Le webmaster de {$_SESSION[QT]['site_name']}
";