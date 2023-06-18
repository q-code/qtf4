<?php // v4.0 build:20230618
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */

echo '
</div>
';

if ( isset($oDB->stats) )
{
  $end = (float)vsprintf('%d.%06d', gettimeofday());
  $str = '';
  if ( isset($oDB->stats['num']) ) $str .= $oDB->stats['num'].' query. ';
  if ( isset($oDB->stats['rows']) ) $str .= $oDB->stats['rows'].' rows fetched. ';
  if ( isset($oDB->stats['start']) ) $str .= 'End queries in '.round($end-$oDB->stats['start'],4).' sec. ';
  if ( isset($oDB->stats['pagestart']) ) $str .= 'End page in '.round($end-$oDB->stats['pagestart'],4).' sec. ';
  $oH->log[] = $str;
}

echo '
</div>
';

// Automatic add script {file.php.js} if existing
if ( file_exists($oH->selfurl.'.js') ) $oH->scripts[] = '<script type="text/javascript" src="'.$oH->selfurl.'.js"></script>';

$oH->end();

ob_end_flush();