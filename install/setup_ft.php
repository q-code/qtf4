<?php // v4.0 build:20230618

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
<nav class="flex-sp">
<p>powered by <a style="color:white" href="http://www.qt-cute.org" target="_blanck">QT-cute</a></p>
<p>'.$urlPrev.$urlNext.'</p>
</nav>
';

echo '
</main>
</body>
</html>';