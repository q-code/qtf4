<?php // v4.0 build:20240210
/**
 * @var CHtml $oH
 * @var CDatabase $oDB (always if isset)
 */

// END PAGE SITE
echo CHtml::pageEntity('/', 'site');

if ( isset($oDB->stats) ) {
  if ( empty($oDB->stats['end']) ) $oDB->stats['end'] = gettimeofday(true);
  $oH->log[] = sprintf('%d queries. %d rows fetched in %01.4f sec.', $oDB->stats['num'], $oDB->stats['rows'], $oDB->stats['end'] - $oDB->stats['start']);
}

echo CHtml::pageEntity('/', 'page layout');
echo CHtml::pageEntity('/', 'page admin');

// Automatic add script {file.php.js} if existing and formsafe
if ( file_exists($oH->selfurl.'.js') )
$oH->scripts[] = '<script type="text/javascript" src="'.$oH->selfurl.'.js"></script>';

$oH->end();

ob_end_flush();