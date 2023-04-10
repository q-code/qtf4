<?php

echo '<p class="bold">Application operated by</p>
<p>'.$_SESSION[QT]['site_name'].'</p>
<p>Webmaster: <a href="mailto:'.$_SESSION[QT]['admin_email'].'">'.$_SESSION[QT]['admin_email'].'</a></p>
<p>Contact: '.$_SESSION[QT]['admin_name'].' '.$_SESSION[QT]['admin_addr'].' '.$_SESSION[QT]['admin_phone'].'</p>
<br>
<p class="bold">Application created by</p>
<p>QT-cute (www.qt-cute.org) version '.VERSION.'</p>
<br>
<p class="bold">Application license</p>
<p><img src="bin/css/vgplv3.png" width="88" height="31" alt="GPL" title="GNU General Public License"/></p>
<p>See the <a href="license.txt">Application License</a> and the <a href="license_gpl.txt">GNU General Public License</a> for more details.</p>
<br>
<p class="bold">Application compliance</p>
';