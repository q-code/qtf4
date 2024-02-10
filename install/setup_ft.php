<?php // v4.0 build:20240210

/**
* @var string $strPrev
* @var string $strNext
* @var string $urlPrev
* @var string $urlNext
*/

echo '</div>
'.( empty($aside) ? '' : '<aside>'.$aside.'</aside>').'
</main>
';

$strPrev = empty($strPrev) ? L('Back') : $strPrev;
$strNext = empty($strNext) ? L('Next') : $strNext;
$urlPrev = '<a class="button'.(empty($urlPrev) ? ' disabled' : '').'" href="'.(empty($urlPrev) ? 'javascript:void(0)' : $urlPrev).'">'.$strPrev.'</a>';
$urlNext = '<a class="button'.(empty($urlNext) ? ' disabled' : '').'" href="'.(empty($urlNext) ? 'javascript:void(0)' : $urlNext).'">'.$strNext.'</a>';

echo '<nav>
<p>powered by <a style="color:white" href="http://www.qt-cute.org" target="_blanck">QT-cute</a></p>
<p>'.$urlPrev.$urlNext.'</p>
</nav>
';

echo '</body>
</html>';