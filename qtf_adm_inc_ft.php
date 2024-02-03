<?php // v4.0 build:20230618
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */

echo '
</div>
';

if ( isset($oDB->stats) ) {
  if ( empty($oDB->stats['end']) ) $oDB->stats['end'] = gettimeofday(true);
  $oH->log[] = sprintf('%d queries. %d rows fetched in %01.4f sec.', $oDB->stats['num'], $oDB->stats['rows'], $oDB->stats['end'] - $oDB->stats['start']);
}

echo '
</div>
';

// Automatic add script {file.php.js} if existing
if ( file_exists($oH->selfurl.'.js') ) $oH->scripts[] = '<script type="text/javascript" src="'.$oH->selfurl.'.js"></script>';

$oH->end();

ob_end_flush();