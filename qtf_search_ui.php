<?php
/**
* @var string $s
* @var string $st
*/
echo '<div id="searchcmd" class="nav">';
echo '<a id="btn-recent" data-s="'.$s.'" data-fs="'.$fs.'" class="button" href="'.url(APP.'_items.php').'?q=last" onclick="addHrefData(this,[`s`,`fs`])">'.asImg( QT_SKIN.'img/topic_t_0.gif', 'alt=T|class=btn-prefix' ).L('Recent_items').'</a>';
echo '<a id="btn-news" data-s="'.$s.'" data-fs="'.$fs.'" class="button" href="'.url(APP.'_items.php').'?q=news" onclick="addHrefData(this,[`s`,`fs`])">'.asImg( QT_SKIN.'img/topic_a_0.gif', 'alt=N|class=btn-prefix' ).L('All_news').'</a>';
if ( SUser::id()>0 ) echo '<a id="btn-my" data-s="'.$s.'" data-fs="'.$fs.'" class="button" href="'.url(APP.'_items.php').'?q=user&fw='.SUser::id().'&fv='.urlencode(SUser::name()).'" onclick="addHrefData(this,[`s`,`fs`])">'.qtSvg('user', 'class=btn-prefix').''.L('All_my_items').'</a>';
echo '</div>';