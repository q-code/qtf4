<?php // v4.0 build:20230205 allows app impersonation [qt f|i ]

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php'; if ( SUser::role()!=='A' ) die('Access is denied');
include translate('lg_adm.php');

// INITIALISE

$oH->selfurl = APP.'_adm_smtp.php';
$oH->selfname = 'SMTP test';
$oH->selfparent = L('Board_info');

if ( isset($_GET['h']) ) $_SESSION[QT]['smtp_host'] = QTdb($_GET['h']);
if ( isset($_GET['p']) ) $_SESSION[QT]['smtp_port'] = QTdb($_GET['p']);
if ( isset($_GET['u']) ) $_SESSION[QT]['smtp_username'] = QTdb($_GET['u']);
if ( isset($_GET['w']) ) $_SESSION[QT]['smtp_password'] = QTdb($_GET['w']);

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // register value used
  $_SESSION[QT]['smtp_host'] = QTdb($_POST['smtphost']);
  $_SESSION[QT]['smtp_port'] = QTdb($_POST['smtpport']);
  $_SESSION[QT]['smtp_username'] = QTdb($_POST['smtpusr']);
  $_SESSION[QT]['smtp_password'] = QTdb($_POST['smtppwd']);
  if ( !QTismail($_POST['mailto']) ) die(L('Email').' '.L('invalid'));

  // send mail
  qtMail($_POST['mailto'],$_POST['subject'],$_POST['message'],'iso-8859-1','1');

  // exit
  $oH->exiturl = APP.'_adm_smtp.php';
  $oH->exitname = 'SMTP test';
  $oH->pageMessage('', 'Process completed...<br><br>If you have changed the smtp settings during the test, go to the Administration page and SAVE your new settings!', 'admin');
}

// --------
// HTML BEGIN
// --------

const HIDE_MENU_TOC=true;

$oH->links['cssIcons']=''; // remove webicons
include APP.'_adm_inc_hd.php';

// CONTENT

echo '<br>
<form method="post" action="',$oH->selfurl,'">
<h2 class="config">SMTP Settings</h2>
<table class="t-conf">
<tr>
<th><label for="smtphost">Smtp host</label></th>
<td>
<input type="text" id="smtphost" name="smtphost" size="30" maxlength="64" value="'.qtAttr($_SESSION[QT]['smtp_host']).'"/>
<br><span class="small">Use prefix to activate SSL or TLS connection e.g.</span> <span class="small" style="color:#4444ff">ssl://smtp.domain.com</span>
</td>
</tr>
<tr>
<th><label for="smtphost">Port</label></th>
<td>
<input type="text" id="smtpport" name="smtpport" size="5" maxlength="6" value="'.(isset($_SESSION[QT]['smtp_port']) ? qtAttr($_SESSION[QT]['smtp_port']) : '25').'"/>
</td>
</tr>
<tr>
<th><label for="smtpusr">Smtp username</label></th>
<td><input type="text" id="smtpusr" name="smtpusr" size="30" maxlength="64" value="'.qtAttr($_SESSION[QT]['smtp_username']).'"/></td>
</tr>
<tr>
<th><label for="smtppwd">Smtp password</label></th>
<td><input type="text" id="smtppwd" name="smtppwd" size="30" maxlength="64" value="'.qtAttr($_SESSION[QT]['smtp_password']).'"/></td>
</tr>
</table>
';
echo '<h2 class="config">Test ',L('Email'),'</h2>
<table class="t-conf">
<tr>
<th><label for="mailto">SEND TO</label></th>
<td><input type="email" id="mailto" name="mailto" size="30" maxlength="64" /></td>
</tr>
<tr>
<th>From</th>
<td>',$_SESSION[QT]['admin_email'],'</td>
</tr>
<tr>
<th><label for="subject">Subject</label></th>
<td><input type="text" id="subject" name="subject" size="30" maxlength="64" value="Test smtp"/></td>
</tr>
<tr>
<th><label for="message">Message</label></th>
<td><input type="text" id="message" name="message" size="30" maxlength="64" value="Test mail send by smtp server"/></td>
</tr>
</table>
';
echo '<p class="submit"><button type="submit" name="ok" value="send">',L('Send'),'</button></p>
</form>
';

echo '<br>
<h2 class="config">Setting examples</h2>
<div class="scroll">
<p class="bold">Example for gmail</p>
<p>
Host <span style="color:#4444ff">tls://smtp.gmail.com</span><br>
Port <span style="color:#4444ff">587</span><br>
Username <span style="color:#4444ff">yourusername@gmail.com</span><br>
Password <span style="color:#4444ff">your google account password</span><br>
<br>
<span class="small">Note: using ssl or tls requires that your webhost opens these transport sockets in the php configuration. When this is not possible or if the test failed, you can use standard mail function (in the administration page Site & contact, don\'t use external smtp server).</span>
</p>
';
echo '<p class="bold">Example for pop3 instead of smtp</p>
<p>
Host <span style="color:#4444ff">pop3.yourdomain.com</span><br>
Port <span style="color:#4444ff">110</span><br>
Username <span style="color:#4444ff">your username</span><br>
Password <span style="color:#4444ff">your password</span><br>
</p>
</div>
';


// HTML END

include APP.'_adm_inc_ft.php';