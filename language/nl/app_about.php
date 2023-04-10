<?php

echo '<p class="bold">Applicatie eigenaar</p>
<p>'.$_SESSION[QT]['site_name'].'</p>
<p>Webmaster: <a href="mailto:'.$_SESSION[QT]['admin_email'].'">'.$_SESSION[QT]['admin_email'].'</a></p>
<p>Contact: '.$_SESSION[QT]['admin_name'].' '.$_SESSION[QT]['admin_addr'].' '.$_SESSION[QT]['admin_phone'].'</p>
<br>
<p class="bold">Applicatie gemaakt door</p>
<p>QT-cute (www.qt-cute.org) versie '.VERSION.'</p>
<br>
<p class="bold">Vergunning (engels)</p>
<p><img src="bin/css/vgplv3.png" width="88" height="31" alt="GPL" title="GNU General Public License"/></p>
<p>Zie documenten <a href="license.txt">Application License</a> en <a href="license_gpl.txt">GNU General Public License</a> voor meer informatie.</p>
<br>
<p class="bold">Naleving</p>
';