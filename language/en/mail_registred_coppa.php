<?php
/**
 * @var string $strSubject
 * @var string $strMessage
 */
$strSubject = "Welcome"; 

$strMessage = "
Dear parent/guardian,

We inform you that your child has registered on the forum {$_SESSION[QT]['site_name']}.
For a new registration, we will need your agreement (see the COPPA rules).

After registration, you will be able to review his information in the Profile page using his/her password.

Login: %s
Password: %s

In compliance with the COPPA act this account is currently inactive.
You must print out the permission form, fill it in and mail it back to the webmaster. Details about how to return the form are on the form itself.
The form can be accessed through this page: {$_SESSION[QT]['site_url']}/qtf_form_coppa.php

Once the administrator has received this form via regular mail the account will be activated.
Please do not forget the password as it has been encrypted in our database and we cannot retrieve it for you.
However, should you forget your password you can request a new one.

Thank you for registering.

Regards,
The webmaster of {$_SESSION[QT]['site_name']}

---- COPPA ----
This email has been sent to you because your children has stated that he/she is younger than 13 years of age and this forum is in compliance with the Children's Online Privacy Protection Act (COPPA).
To find out more about COPPA, please visit this page: http://www.ftc.gov/opa/1999/10/childfinal.htm
Please read the Community Forum Privacy Statement also: {$_SESSION[QT]['site_url']}/qtf_privacy.php
---------------
";