<?php
/**
 * @var string $strSubject
 * @var string $strMessage
 */
$strSubject = "Profile updated";
$strMessage = "
Dear parent/gardian,

We inform you that your children (login: %s) has changed his/her profile on the forum {$_SESSION[QT]['site_name']}.
You can review this information in the Profile page.

To access the forum you will need his/her login and password send to you in a previous mail.

---- COPPA ----
This email has been sent to you because your children has stated that he/she is younger than 13 years of age and this forum is in compliance with the Children's Online Privacy Protection Act (COPPA).
To find out more about COPPA, please visit this page: http://www.ftc.gov/opa/1999/10/childfinal.htm
Please read the Community Forum Privacy Statement also: {$_SESSION[QT]['site_url']}/qtf_privacy.php
------

Regards,
The webmaster of {$_SESSION[QT]['site_name']}
";