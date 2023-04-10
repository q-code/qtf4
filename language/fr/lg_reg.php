<?php

$L['Agree']='J\'ai lu ce règlement et j\'accepte de le suivre.';
$L['Proceed']='S\'enregister';

// coppa
$L['I_am_child']='J\'ai moins de 13 ans';
$L['I_am_not_child']='J\'ai 13 ans ou plus';
$L['Child']='Enfant';
$L['With_parent_agree']='Avec accord d\'un parent/tuteur';
$L['Without_parent_agree']='Sans accord d\'un parent/tuteur';
$L['Rules_not_agreed']='Vous n\'avez pas accepté les règles de ce forum.<br>La procédure d\'enregistrement ne peut continuer sans cet accord.';

// registration
$L['User_del']='Effacer l\'utilisateur';
$L['Not_your_account']='Ceci n\'est pas votre compte';
$L['Choose_password']='Choisissez un mot de passe';
$L['Old_password']='Ancien mot de passe';
$L['New_password']='Nouveau mot de passe';
$L['Confirm_password']='Confirmez le mot de passe';
$L['Password_updated']='Mot de passe modifié';
$L['Password_by_mail']='Un mot de passe temporaire sera envoyé à votre adresse e-mail.';
$L['Your_mail']='Votre e-mail';
$L['Parent_mail']='Parent/tuteur e-mail';
$L['Security']='Securité';
$L['Reset_pwd']='Réinitialiser le mot de passe';
$L['Reset_pwd_help']='L\'application va envoyer par e-mail un nouveau mot de passe.';
$L['Type_code']='Copiez le code que vous voyez.';
$L['Unregister']='Désinscription';
$L['H_Unregister']='En vous désinscrivant, vous n\'aurez plus accès à cette application en tant que membre. Votre profil sera effacé et ne sera plus visible dans la liste des membres. Vos messages resteront visibles. Si des utilisateurs tentent d\'accéder à votre profil, ils verront un profil anonyme "Visiteur".<br><br>Entrez votre mot de passe pour confirmer votre désinscription...';
$L['Unregister_staff']=' est membre du Staff. Pour désincrire un membre du staff, un administrateur doit d\'abord changer son role en Utilisateur ou utiliser la fonction Effacer.';

// login and profile

$L['Welcome']='Bienvenue';
$L['Goodbye']='Vous êtes déconnecté. Au revoir...';
$L['Remember']='Se souvenir de moi';
$L['Forgotten_pwd']='Mot de passe perdu';
$L['Change_password']='Changer de mot de passe';
$L['Change_picture']='Changer de photo';
$L['Picture_thumbnail'] = 'L\'image est trop grande.<br>Pour définir votre photo, tracez un carré dans la grande image.';
$L['Picture_updated']='Photo changée';
$L['Delete_picture']='Effacer la photo';
$L['Picture_deleted']='Photo effacée';
$L['Change_signature']='Changer de signature';
$L['Change_role']='Changer de rôle';
$L['Change_ban']='Changer le bannissement';
$L['H_no_signature']='Votre signauture s\'affiche en bas de vos messages. Pour effacer votre signature, sauvez un texte vide ci-après.';
$L['H_ban']='Durée du bannissement';
$L['Ban']='Bannir';
$L['Is_banned']='Est banni';
$L['Is_banned_since']='est banni %s depuis le last message';
$L['Is_banned_nomore']='<h2>Bienvenue à nouveau...</h2><p>Votre compte est à présent ré-ouvert.<br>Vous pouvez maintenant vous re-connecter...</p>';
$L['Since']='depuis';
$L['Retry_tomorrow']='Ré-essayez demain ou contactez l\'Administrateur du forum.';

// Error
$L['No_parental_confirm']='Autorisation du parent/tuteur n\'est pas encore arrivée. Veuillez patienter.';

// Secret question
$L['Secret_question']='Question secrète';
$L['H_Secret_question']='Cette question vous sera posée si vous avez oublié votre mot de passe.';
$L['Update_secret_question']='Votre profil doit être mis à jour...<br><br>Afin d\'améliorer la sécurité, nous vous demandons de définir, votre "Question secrète". Cette question vous sera posée si vous avez oublié votre mot de passe.';
$L['Secret_q']['What is the name of your first pet?']='Quel est le nom de votre premier chien/chat ?';
$L['Secret_q']['What is your favorite character?']='Quel est votre personnage préféré ?';
$L['Secret_q']['What is your favorite book?']='Quel est votre livre préféré ?';
$L['Secret_q']['What is your favorite color?']='Quelle est votre couleur préférée ?';
$L['Secret_q']['What street did you grow up on?']='Dans quelle rue avez-vous grandi ?';

// Help
$L['Reg_help']='<p>Veuillez remplir ce formulaire afin de compléter votre inscription.</p>
<p>Le nom d\'utilisateur et le mot de passe doivent avoir au moins 4 caractères et être sans espace au début et à la fin.</p>
<p>L\'adresses e-mail sert à vous renvoyer un nouveau mot de passe en cas d\'oubli. Elle n\'est visible  que pour les membres enregistrés. Vous pouvez la rendre invisible dans votre profil.</p>
<p>Si vous êtes malvoyant ou que vous ne voyez pas le code de sécurité, contactez l\'<a href="mailto:'.$_SESSION[QT]['admin_email'].'">Administrateur</a>.</p>';
$L['Reg_mail']='Vous allez recevoir par e-mail un mot de passe temporaire.<br><br>Vous êtes invité à vous connecter et à changer votre mot de passe dans la page Profil.';
$L['Reg_pass']='Réinitialisation du mot de passe.<br><br>Si vous avez oublié votre mot de passe, veuillez d\'abord entrer votre nom d\'utilisateur.';
$L['Reg_pass_reset']='Nous pouvons vous envoyer un nouveau mot de passe si vous savez répondre à votre question secrète.';