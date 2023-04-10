<?php

$L['Agree']='I have read, and agree to abide by these rules.';
$L['Proceed']='Proceed to registration';

// coppa
$L['I_am_child']='I am under 13 years of age';
$L['I_am_not_child']='I am over or exactly 13 years of age';
$L['Child']='Child';
$L['With_parent_agree']='With parent/guardian agreement';
$L['Without_parent_agree']='Without parent/guardian agreement';
$L['Rules_not_agreed']='You do not agree with the forum participation rules.<br>The registration procedure can\'t continue without your agreement.';

// registration
$L['User_del']='Delete user';
$L['Not_your_account']='This is not your account';
$L['Choose_password']='Choose a password';
$L['Old_password']='Old password';
$L['New_password']='New password';
$L['Confirm_password']='Confirm the password';
$L['Password_updated']='Password updated';
$L['Password_by_mail']='Temporary password will be send to your e-mail address.';
$L['Your_mail']='Your e-mail';
$L['Parent_mail']='Parent/guardian e-mail';
$L['Security']='Security';
$L['Reset_pwd']='Reset password';
$L['Reset_pwd_help']='The application will send by e-mail a new single-use access password key.';
$L['Type_code']='Type the code you see.';
$L['Unregister']='Unregister';
$L['H_Unregister']='By unregistering, you will stop having access to this application as a member. Your profile will be deleted and your account will no more be visible in the memberlist. Your messages will remain visible. If other users try to access your profile, they will see the Visitor profile (empty).<br><br>Enter your password to confirm unregistration...';
$L['Unregister_staff']=' is Staff member. To unregister a staff member, an administrator must first change role to User or use the delete function.';

// Login and profile

$L['Welcome']='Welcome';
$L['Goodbye']='You are disconnected. Goodbye...';

$L['Remember']='Remember me';
$L['Forgotten_pwd']='Forgotten password';

$L['Change_password']='Change password';
$L['Change_picture']='Change picture';
$L['Picture_thumbnail'] = 'The uploaded image is too large.<br>To define your picture, draw a square in the large image.';
$L['Picture_updated']='Picture updated';
$L['Delete_picture']='Delete picture';
$L['Picutre_deleted']='Picture deleted';
$L['Change_signature']='Change signature';
$L['Change_role']='Change role';

$L['Change_ban']='Change ban';
$L['H_no_signature']='Your signature is displayed at the bottom of your messages. If you don\'t want signature, save an empty text here.';
$L['H_ban']='Select the ban duration';
$L['Ban']='Ban';
$L['Is_banned']='Is banned';
$L['Is_banned_since']='is banned %s since the last message';
$L['Is_banned_nomore']='<h2>Welcome back...</h2><p>Your account has been re-opened.<br>Re-try login now...</p>';
$L['Since']='since';
$L['Retry_tomorrow']='Try again tomorrow or contact the forum Administrator.';

// Error

$L['No_parental_confirm']='Agreement from your parent/guardian not yet received. Please wait.';

// Secret question

$L['Secret_question']='Secret question';
$L['H_Secret_question']='This question will be asked if you forget your password.';
$L['Update_secret_question']='Your profile must be updated...<br><br>To improve security, we request you to define your own "Secret question". This question will be asked if you forget your password.';
$L['Secret_q']['What is the name of your first pet?']='What is the name of your first pet?';
$L['Secret_q']['What is your favorite character?']='What is your favorite character?';
$L['Secret_q']['What is your favorite book?']='What is your favorite book?';
$L['Secret_q']['What is your favorite color?']='What is your favorite color?';
$L['Secret_q']['What street did you grow up on?']='What street did you grow up on?';

// Help

$L['Reg_help']='<p>Please fill in this form to complete your registration.</p>
<p>Username and password must be at least 4 characters without trailing spaces.</p>
<p>E-mail address will be used to send you a new password if you forgot it. It is visible for registrered members only. To make it invisible, change your privacy settings in your profile.</p>
<p>If you are visually impaired or cannot otherwise read the security code please contact the <a href="mailto:'.$_SESSION[QT]['admin_email'].'">Administrator</a> for help.</p>';
$L['Reg_mail']='You will receive an email shortly including a temporary password.<br><br>You are invited to login and edit your profile to define your own password.';
$L['Reg_pass']='If you have forgotten your password, please first enter your username.';
$L['Reg_pass_reset']='We can send you a new password if you can answer your secret question.';