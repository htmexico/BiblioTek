<?php
$month_year = array( 1 => "Enero", 
                     2 => "Febrero", 
					 3 => "Marzo", 
					 4 => "Abril", 
					 5 => "Mayo", 
					 6 => "Junio", 
					 7 => "Julio", 
					 8 => "Agosto", 
					 9 => "Septiembre", 
					 10 => "Octubre", 
					 11 => "Noviembre", 
					 12 => "Diciembre" );

// set the "today" string
$today_str = "Hoy";

// array con la fecha actual
$arr_day = getdate();

// definición de variables
$sec = $arr_day["minutes"];
$mi = $arr_day["seconds"];
$hour = $arr_day["hours"];
$day = $arr_day["mday"];
$day_week = $arr_day["wday"];
//$day_week_ext = $days_week[$arr_day["wday"]];
$month = $arr_day["mon"];
$month_ext = $month_year[$month];
$year = $arr_day["year"];

function mformat($zeros,$num) {
  for($i = 1; $i <= $zeros - strlen($num); $i++) $num = "0".$num;
  return $num;
}

function mno_zero($num) {
  if (substr($num,0,1) == "0")
    return substr($num,1,1);
  else
    return $num;
}

function mdia_semana($formato,$data) {
  $d = date("w",mktime (0,0,0,mno_zero(substr($data,5,2)),mno_zero(substr($data,8,2)),substr($data,0,4)));
  $arr_d = $GLOBALS["days_week"];
  if ($formato == "t") return $arr_d[$d]; else return $d-1;
}


function mdata_atual($sep) {
  return $GLOBALS["year"].$sep.mformat(2,$GLOBALS["month"]).mformat(2,$sep.$GLOBALS["day"]);
}


function mdata_ext($data) {
  $arr_m = $GLOBALS["month_year"];
  return mdia_semana("t",$data).", ".substr($data,8,2)." de ".$arr_m[date("n",mktime (0,0,0,mno_zero(substr($data,5,2)),mno_zero(substr($data,8,2)),substr($data,0,4)))]." de ".substr($data,0,4);
}


function mdata_br($data,$sep) {
  if($data != "0000-00-00" && $data != "0000-00-00 00:00:00" && $data != "") return substr($data,8,2).$sep.substr($data,5,2).$sep.substr($data,0,4);
}


function mdata_mysql($data) {
  if($data != "00/00/0000" && $data != "") return substr($data,6,4)."-".substr($data,3,2)."-".substr($data,0,2);
}
?>