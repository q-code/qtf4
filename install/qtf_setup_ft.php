<?php // v4.0 build:20230205

/**
* @var string $strPrev
* @var string $strNext
* @var string $urlPrev
* @var string $urlNext
*/

echo '
</div>
';

$strPrev = empty($strPrev) ? L('Back') : $strPrev;
$strNext = empty($strNext) ? L('Next') : $strNext;
$urlPrev = empty($urlPrev) ? '<span class="button">'.$strPrev.'</span>' : '<a class="button" href="'.$urlPrev.'">'.$strPrev.'</a>';
$urlNext = empty($urlNext) ? '<span class="button">'.$strNext.'</span>' : '<a class="button" href="'.$urlNext.'">'.$strNext.'</a>';

echo '
<div class="banner">
<div class="flex-sp">
<div style="color:white;font-size:8pt">powered by <a style="color:white" href="http://www.qt-cute.org">QT-cute</a></div>
<div style="text-align:right">'.$urlPrev.$urlNext.'</div>
</div>
</div>
';

echo '
</div>
</body>
</html>';