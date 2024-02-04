<?php
/**
 * @var string $strSubject
 * @var string $strMessage
 */
$strSubject = "Bienvenue";

$strMessage = "
Cher parent/tuteur,

Nous vous informons votre enfant s'est inscrit sur le forum {$_SESSION[QT]['site_name']}.
Pour une nouvelle inscription, nous demandons un accord �crit de votre part (voir les r�gles COPPA).

Apr�s inscription, vous pourrez contr�ler ces informations dans la page Profil.
Pour acc�der au forum vous aurez besoin de son login et mot de passe, ci-joint.

Utilisateur: %s
Mot de passe: %s

En accord avec les r�gles COPPA, ce compte est pour l'instant inactif.
Vous devez imprimer le formulaire d'autorisation, le remplir et le renvoyer au webmaster. Les d�tails pour renvoyer le formulaire se trouvent dans celui-ci.
Le formulaire est accessible � cette adresse: {$_SESSION[QT]['site_url']}/qtf_form_coppa.php

Lorsque l'administrateur aura re�u ce formulaire par courrier, le compte sera activ�.
Veillez � ne pas oublier le mot de passe car celui-ci est encrypt� dans notre base de donn�e et personne ne peut le retrouver.
Cependant, si vous oubliez ce mot de passe, un nouveau mot de passe peut �tre cr��.

Salutations,
Le webmaster de {$_SESSION[QT]['site_name']}

---- COPPA ----
Ce mail vous est adress� parce que votre enfant nous a indiqu� �tre �g�(e) de moins de 13 ans et parce que ce forum applique les r�gles de COPPA (Children's Online Privacy Protection Act).
Pour en savoir plus sur le COPPA, visitez cette page: http://www.ftc.gov/opa/1999/10/childfinal.htm
Veuillez �galement prendre connaissance du r�glement de ce forum : {$_SESSION[QT]['site_url']}/qtf_privacy.php
------
";