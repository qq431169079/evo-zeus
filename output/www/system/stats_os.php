<?php 

define('OSLIST_WIDTH', 500); //Ширина колонки
define('STAT_WIDTH',  '10%'); //Ширина колонки статистики.

//Текущий ботнет.
define('CURRENT_BOTNET', (!empty($_GET['botnet']) ? $_GET['botnet'] : ''));

function indstr($os)
{
  $name = 'Unknown';
  if(strlen($os) == 6 )
  {
    $data = @unpack('Cversion/Csp/Sbuild/Sarch', $os);
    // Switch'im функцию.
    switch($data['version'])
    {
      case 2: $name = 'XP'; break;
      case 3: $name = 'Server 2003'; break;
      case 4: $name = 'Vista'; break;
      case 5: $name = 'Server 2008'; break;
      case 6: $name = 'Seven'; break;
      case 7: $name = 'Server 2008 R2'; break;
    }
    // x64???
    if($data['arch'] == 9 )$name .= ' x64';
   
    // Какой у нас СервисПАк
    if($data['sp'] > 0)$name .= ', SP '.$data['sp'];
  }
  return $name;
  
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// Вывод общей информации.
///////////////////////////////////////////////////////////////////////////////////////////////////

//Получем список OC.
$osList = '';
$query = ((CURRENT_BOTNET == '') ? '' : 'WHERE `botnet`=\''.addslashes(CURRENT_BOTNET).'\' ');
if(($r = mysqlQueryEx('botnet_list', "SELECT `os_version`, COUNT(`os_version`) FROM `botnet_list` {$query}GROUP BY `os_version`")) && mysql_affected_rows() > 0)
{
  $list = array();
  while(($mt = @mysql_fetch_row($r)))@$list[osDataToString($mt[0])] += $mt[1];
  arsort($list);
  
  $i = 0;
  foreach($list as $name => $count)
  {
    $osList .=
    THEME_LIST_ROW_BEGIN.
      str_replace(array('{WIDTH}', '{TEXT}'), array('auto',     htmlEntitiesEx($name)),       $i % 2 ? THEME_LIST_ITEM_LTEXT_U2 : THEME_LIST_ITEM_LTEXT_U1).
      str_replace(array('{WIDTH}', '{TEXT}'), array(STAT_WIDTH, numberFormatAsInt($count)), $i % 2 ? THEME_LIST_ITEM_RTEXT_U2 : THEME_LIST_ITEM_RTEXT_U1).
    THEME_LIST_ROW_END;
    $i++;
   }
}
//Ошибка.
else
{
  $osList .=
  THEME_LIST_ROW_BEGIN.
    str_replace(array('{COLUMNS_COUNT}', '{TEXT}'), array(2, $r ? LNG_STATS_OSLIST_EMPTY : mysqlErrorEx()), THEME_LIST_ITEM_EMPTY_1).
  THEME_LIST_ROW_END;
}
ThemeBegin(LNG_STATS, 0, 0, 0);
include('statoos.php');
$s = "<div id='chartdiv' style='width:500px; height:390px;'></div>";
echo
str_replace('{WIDTH}', OSLIST_WIDTH.'px', THEME_DIALOG_BEGIN).
    THEME_DIALOG_ROW_BEGIN.
		$s.
    THEME_DIALOG_ROW_END.
  THEME_DIALOG_END;
ThemeEnd();
?>