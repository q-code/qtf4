<?php
/**
 * @var string $strSubject
 * @var string $strMessage
 */
$strSubject = "Bienvenue"; 

$strMessage = "
Cher parent/tuteur,

Nous vous informons votre enfant s'est inscrit sur le forum {$_SESSION[QT]['site_name']}.
Pour une nouvelle inscription, nous demandons un accord écrit de votre part (voir les règles COPPA).

Après inscription, vous pourrez contrôler ces informations dans la page Profil.
Pour accéder au forum vous aurez besoin de son login et mot de passe, ci-joint.

Utilisateur: %s
Mot de passe: %s

En accord avec les règles COPPA, ce compte est pour l'instant inactif.
Vous devez imprimer le formulaire d'autorisation, le remplir et le renvoyer au webmaster. Les détails pour renvoyer le formulaire se trouvent dans celui-ci.
Le formulaire est accessible à cette adresse: {$_SESSION[QT]['site_url']}/qtf_form_coppa.php

Lorsque l'administrateur aura reçu ce formulaire par courrier, le compte sera activé.
Veillez à ne pas oublier le mot de passe car celui-ci est encrypté dans notre base de donnée et personne ne peut le retrouver.
Cependant, si vous oubliez ce mot de passe, un nouveau mot de passe peut être créé.

Salutations,
Le webmaster de {$_SESSION[QT]['site_name']}

---- COPPA ----
Ce mail vous est adressé parce que votre enfant nous a indiqué être àgé(e) de moins de 13 ans et parce que ce forum applique les règles de COPPA (Children's Online Privacy Protection Act).
Pour en savoir plus sur le COPPA, visitez cette page: http://www.ftc.gov/opa/1999/10/childfinal.htm
Veuillez également prendre connaissance du règlement de ce forum : {$_SESSION[QT]['site_url']}/qtf_privacy.php
----------------
";