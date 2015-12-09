<?php
 // Archivo de funciones básicas 
 // restricciones aplicadas en restricciones.inc.php
 
 /******
   Historial de Cambios
   
   20ene2009: Se crea el archivo basico para funcs.inc.php
   17mar2009: Se agrega función include_language para localización
   26mar2009: se agrega función hilite para resaltar textos
   29mar2009: Se crea función agregar_actividad_de_usuario()
   17jun2009: Las funciones de DB están auxiliadas con parámetros de conexión desde config_db.inc.php
   17jul2009: Se agregan las funciones _JAVASCRIPT_???
   11ago2009: Se agrega la función sum_days()
   13ago2009: Se agrega la función usuario_obtenerinfo_from_id()
   14ago2009: Se agrega remove_scaped_quotes()
   17sep2009: Se agrega funcion current_time()
   08oct2009: Se mueve la función usuario_obtenerinfo_from_id() a marc.php
   19-oct-2009: Se mueve hilight() a opac.inc.php
   
  **/
 
 //$IS_DEBUG = 1;
 //$IS_TECH_STOP = 1;
 
 if( isset($IS_TECH_STOP) )
 {
	echo "<body><br>";
	die( "<center><span style='padding-left:20px; font-size:120%'>Por el momento el servicio está en una parada técnica por mantenimiento. <br>Vuelva a intentar en unos minutos por favor.<br>".
			"<h2>Nos disculpamos por los inconvenientes que esto pudiera causarle</h2>.</span></center><br></body></html>" );
 } 
 
 $version = "1.0";

 $signo_moneda = "$";                // 14 oct 2008
 $nombre_moneda_singular = "PESO";   // 14 oct 2008
 $nombre_moneda_plural   = "PESOS";  // 14 oct 2008
 
 if( issetsessionvar("pais") )
 
 {
	if (getsessionvar("pais") == "ECUADOR") 
	{
		 $signo_moneda = "USD$";                // 28 sep 2010
		 $nombre_moneda_singular = "DOLAR";   	// 28 sep 2010
		 $nombre_moneda_plural   = "DOLARES";  	// 28 sep 2010	
	}
	else if (getsessionvar("pais") == "ARGENTINA") 	
	{
	}

 }
 
 
 // Dentro se inicializaran variables globales
 if ( !issetsessionvar("biblio_firmado") )
 {
    setsessionvar( "biblio_firmado", "NO" );
	
	setsessionvar( "tipousuario", "" );
    setsessionvar( "usuario", "ninguno" );
    setsessionvar( "nombreusuario", "");
	setsessionvar( "usuarioaccesoanterior", "");

	setsessionvar( "isadmin", 0 );
 }

 if ( !issetsessionvar("init_params") )
 { 
	include( "init_params.inc.php" );
	setsessionvar("init_params", 1 );
 }
 //else
	//include( "init_params.inc.php" );

return 0;

 
function GetPublicAppDir( $forzar_ssl=0 )
{
	global $www_dir;
	global $ssl_dir;

	require_once( getsessionvar("ss_physical_dir") . "GLOBAL_CONFIG.php" );
	
	$ret = "";
	
	if( $forzar_ssl == 1 )
	{
		if( isset($ssl_dir) )
			$ret = $ssl_dir;		
	}
	else
	{	
		$ret = $www_dir;
		
		if( isset($ssl_dir) )
			$ret = $ssl_dir;
	}
	
	return( $ret );
}
 
function left($str, $howManyCharsFromLeft)
{
  return substr ($str, 0, $howManyCharsFromLeft);
}

function right($str, $howManyCharsFromRight)
{
  $strLen = strlen ($str);
  return substr ($str, $strLen - $howManyCharsFromRight, $strLen);
}

function issetsessionvar( $varname )
{
   return isset( $_SESSION[ $varname ] );
}

function getsessionvar( $varname )
{
   return $_SESSION[ $varname ];
}

function setsessionvar( $varname, $value )
{
	$_SESSION[ $varname ] = $value;  
	return 1;
}

// Funciones de consulta de base de datos
// Query Statement
function db_query( $query, $con_blob=0, $ascii_blob="<!--none-->", $ascii_blob2="<!--none-->", $hacer_commit = 1 ) 
{
	global $CFG;
	require_once( "config_db.inc.php" );

	if( $CFG->db_type == "mysql" )
	{
		$mylink = mysql_connect($CFG->db_host, $CFG->db_user, $CFG->db_pass) or db_die();

		if ( !mysql_select_db( getsessionvar("db_name"), $mylink ) )
		   echo "ERROR EN database select<br>";

		$qryres = mysql_query( $query ) or db_die();

		if ( preg_match( "/insert|update|delete|create/", $query )) 
		{
		  $qryres = mysql_affected_rows();
		  
		  //if( $hacer_commit == 1 ) mysql_commit();
		}

		return $qryres;
	}
	elseif( $CFG->db_type == "interbase" ) 
	{
		$link = ibase_connect( $CFG->db_host . ":" . $CFG->db_name, $CFG->db_user, $CFG->db_pass, "ISO8859_1" ) ;

		if( $con_blob == 1 )
		{
			if( $obj_blob = ibase_blob_create( $link ) )
			{
				ibase_blob_add( $obj_blob, $ascii_blob );
				$blob_id_str = ibase_blob_close( $obj_blob ); 
				
				if( $ascii_blob2 == "<!--none-->" )
				{
				   // un blob o ninguno
				   $qryres = ibase_query( $link, $query, $blob_id_str ) or die("no se pudo ejecutar la consulta BLOB: $query" );
				}
				else
				{
				   // vienen ambos
				   if( $obj_blob2 = ibase_blob_create( $link ) )
				   {
					   ibase_blob_add( $obj_blob2, $ascii_blob2 ) ;
					   $blob_id2_str = ibase_blob_close( $obj_blob2 );
					   
					   $qryres = ibase_query( $link, $query, $blob_id_str, $blob_id2_str ) or die("no se pudo ejecutar la consulta BLOB: $query" );			
				   }
				   else
					  die( "error creando SWAP space" );
				}
			}
		}
		else
			$qryres = ibase_query( $link, $query ) or die("no se pudo ejecutar la consulta: $query" );

		if ( preg_match('/insert|update|delete|create/', $query )) 
		{
		  $qryres = ibase_affected_rows();
		  //$qryres = 1; // ibase_affected_rows();
		  
		  if( $hacer_commit == 1 ) ibase_commit();
		}

		return $qryres;
	}
}

// fetch row statement
function db_close() 
{
	global $CFG;  // only inside functions
	require_once( "config_db.inc.php" );

	if ( $CFG->db_type == "mysql")
		mysql_close();
}

// fetch row statement
function db_fetch_row ($result) 
{
  global $CFG;  // only inside functions
  require_once( "config_db.inc.php" );
  
  if ($CFG->db_type == "mysql")    {
    return @mysql_fetch_assoc($result);
  }
  elseif ($CFG->db_type == "interbase") {
    return ibase_fetch_assoc($result);
  }

}

// freee result-set statement
function db_free_query( $result )
{
	free_dbquery( $result );
}

function free_dbquery ( $result ) 
{
  global $CFG;
  require_once( "config_db.inc.php" );
  
  if( $CFG->db_type == "mysql")    
  {
    return @mysql_free_result($result);
  }
  elseif ( $CFG->db_type == "interbase") 
  {
    ibase_free_result($result);
  }

}

// Error-Messages
function db_die() 
{
  global $CFG;
  require_once( "config_db.inc.php" );
  
  if     ($CFG->db_type== "mysql") { echo @mysql_error(); }
  elseif ($CFG->db_type == "interbase") { echo ibase_errmsg();}
  
  echo "</body></html>";

  exit;
}

//
// Permite obtener la hora actual
// 
function current_time( $add_secs=1, $incluir_ampm=0 )
{

    $cur_time = getdate();
   
    $hours   = $cur_time["hours"];
    $minutes = $cur_time["minutes"];
    $seconds = $cur_time["seconds"];
	
	if( $incluir_ampm == 1 )
	{
		$ampm = " a.m.";

		if( $hours > 12 ) $ampm = " p.m.";

		if( $hours > 12 ) 
			$hours = $hours - 12;
	}
	else
		$ampm = "";	
	
	if($hours < 10 ) $hours = "0" . $hours;
	if($minutes < 10 ) $minutes = "0" . $minutes;
	if($seconds < 10 ) $seconds = "0" . $seconds;
	
	$result = $hours . ":" . $minutes;
	
	if( $add_secs == 1 )
	{
		$result .= ":"  . $seconds;
	}
	
	return $result . $ampm;
	
}

//
//  Obtiene un string con la fecha y hora actual de
//  Acuerdo al formato aceptado por el RDBMS
//
//  Verificar si es lo mismo que current_dateandtime
//
function current_dbtime( $agregardiaactual=0, $incluir_ampm=0 )
{
    global $CFG;
	require_once( "config_db.inc.php" );
	
	$result = "";
	 
    $cur_time = getdate();
   
    $hours   = $cur_time["hours"];
    $minutes = $cur_time["minutes"];
    $seconds = $cur_time["seconds"];
	 	
    if( $CFG->db_type == "mysql" )
	{
	     if( $agregardiaactual == 1 )
		 {
			 $anio = $cur_time["year"];
			 $mes = $cur_time["mon"];
			 $dia = $cur_time["mday"];	
			 
			 $result = dateasmysql($anio, $mes, $dia) . " ";  
		 }
		 else
		    $result = "";
		
		 if( $incluir_ampm == 1 )
		 {
			 $ampm = " a.m.";
			
			 if( $hours > 12 ) $ampm = " p.m.";
	
			 if( $hours > 12 ) 
				$hours = $hours - 12;
		 }
		 else
		    $ampm = "";
		
		 if($hours < 10 ) $hours = "0" . $hours;
		 if($minutes < 10 ) $minutes = "0" . $minutes;
		 if($seconds < 10 ) $seconds = "0" . $seconds;
		
		 return $result . $hours . ":" . $minutes . ":" . $seconds . $ampm;
    }
    elseif( $CFG->db_type == "interbase" )
    { 
	     if( $agregardiaactual == 1 )
		 {
			 $anio = $cur_time["year"];
			 $mes = $cur_time["mon"];
			 $dia = $cur_time["mday"];	
			 
			 $result = dateasinterbase($anio, $mes, $dia) . " ";  
		 }
		 else
		    $result = "";
		
		 if( $incluir_ampm == 1 )
		 {
			 $ampm = " a.m.";
			
			 if( $hours > 12 ) $ampm = " p.m.";
	
			 if( $hours > 12 ) 
				$hours = $hours - 12;
		 }
		 else
		    $ampm = "";
		
		 if($hours < 10 ) $hours = "0" . $hours;
		 if($minutes < 10 ) $minutes = "0" . $minutes;
		 if($seconds < 10 ) $seconds = "0" . $seconds;
		
		 return $result . $hours . ":" . $minutes . ":" . $seconds . $ampm;
	}
		
}


function current_dbdate()
{
   global $CFG;
   require_once( "config_db.inc.php" );
   
   $cur_date = getdate();
   
   $anio = $cur_date["year"];
   $mes = $cur_date["mon"];
   $dia = $cur_date["mday"];
   
   if( $CFG->db_type == "mysql" )
   { return dateasmysql($anio, $mes, $dia); }
   elseif( $CFG->db_type == "interbase" )
   { return dateasinterbase($anio, $mes, $dia); }
}


function current_dateandtime( $humanreadable = 0 )
{
    global $CFG;
	require_once( "config_db.inc.php" );
	
	$anio = date( "Y", time());
    $mes  = date( "m", time());
	$dia  = date( "d", time());
	
	$hora = date( "H", time());
	$min  = date( "i", time());
	$segs = date( "s", time());
	
	if( $dia < 10 and strlen($dia)==1 ) $dia = "0" . $dia;
	if( $mes < 10 and strlen($mes)==1 ) $mes = "0" . $mes;
	
	if( $CFG->db_type == "interbase" ) 	   
	{
	
	  if( $humanreadable == 1 )
		 $cStr = $dia . "/" . $mes . "/" . $anio;
	  else
	     $cStr = dateasinterbase( $anio, $mes, $dia ); // para base de datos
	  
	  $cStr .= " " . $hora . ":" . $min . ":" . $segs;
	  
	  return $cStr; 
	}
	else 
	{
	
	  if( $humanreadable == 1 )
		 $cStr = $dia . "/" . $mes . "/" . $anio;
	  else
	     $cStr = dateasmysql( $anio, $mes, $dia ); // para base de datos
	  
	  $cStr .= " " . $hora . ":" . $min . ":" . $segs;
	  
	  return $cStr; 
	}
}


function dateasmysql( $year, $month, $day )
{
   $month = right( "00" . $month, 2 );	
   $day = right( "00" . $day, 2 );	

   $cStr = $year . "-" . $month . "-" . $day;
  
   return $cStr;
}

function dateasinterbase( $year, $month, $day )
{
   $month = $month + 0;

   if( $month == 01 or $month==1)     { $cmonth = "Jan"; }
   elseif( $month == 02 or $month==2) { $cmonth = "Feb"; }
   elseif( $month == 03 or $month==3) { $cmonth = "Mar"; }
   elseif( $month == 04 or $month==4) { $cmonth = "Apr"; }
   elseif( $month == 05 or $month==5) { $cmonth = "May"; }
   elseif( $month == 06 or $month==6) { $cmonth = "Jun"; }
   elseif( $month == 07 or $month==7) { $cmonth = "Jul"; }
   elseif( $month == 08 or $month==8) { $cmonth = "Aug"; }
   elseif( $month == 09 or $month==9) { $cmonth = "Sep"; }
   elseif( $month == 10 or $month==10) { $cmonth = "Oct"; }
   elseif( $month == 11 or $month==11) { $cmonth = "Nov"; }
   elseif( $month == 12 or $month==12) { $cmonth = "Dec"; }

   $day = right( "00" . $day, 2 );	

   $cStr = $day . "-" . $cmonth . "-" . $year;
  
   return $cStr;
}

// Esta función convierte a un valor de fecha
// listo para usarse en INSERTS y/o UPDATES
// tomando como valor de entrada una fecha CAPTURADA por el USUARIO
function date_for_database_updates( $capture_date )
{
	global $CFG;
	require_once( "config_db.inc.php" );

	$strtime = "";
	
	$pos = strpos( $capture_date, " " );

	if( $pos != 0 )
	{
		// si hay valor de TIME
		// obtener el valor de TIME
		$strtime = substr( $capture_date, $pos+1, 50 );
		$capture_date = substr( $capture_date, 0, $pos);
	}
  
	//                  0123456789
	// fecha en formato dd/mm/AAAA 00:00
	if( (getsessionvar("pais") == "MEXICO") or
		(getsessionvar("pais") == "ECUADOR") or
		(getsessionvar("pais") == "PANAMA") ) 
	{
	  $cdia  = substr( $capture_date, 0, 2 ); 
	  $cmes  = substr( $capture_date, 3, 2 );
	  $canio = substr( $capture_date, 6, 4 );
	}
	else if( getsessionvar("pais") == "USA" )
	{
	  // fecha en formato mm/dd/AAAA 00:00
	  $cmes  = substr( $capture_date, 0, 2 ); 
	  $cdia  = substr( $capture_date, 3, 2 );
	  $canio = substr( $capture_date, 6, 4 );  
	}
	
	if( $CFG->db_type == "mysql" )
		$ret = dateasmysql($canio, $cmes, $cdia); 
	elseif( $CFG->db_type == "interbase" )
		$ret = dateasinterbase($canio, $cmes, $cdia); 
		
	if( $strtime != "" )
		$ret .= " " . $strtime;
		
	return $ret;
}

// Esta función convierte a un valor de hora capturado
// listo para usarse en INSERTS y/o UPDATES
// tomando como valor de entrada una fecha CAPTURADA por el USUARIO
function time_for_database_updates( $capture_time )
{
  global $CFG;
  require_once( "config_db.inc.php" );
  
  //                  01234
  // fecha en formato 00:00
  $horas = substr( $capture_time, 0, 2); 
  $mins  = substr( $capture_time, 3, 2);
  
  $horas = right( "00" . $horas, 2 );	
  $mins = right( "00" . $mins, 2 );	
  
  if( $CFG->db_type == "mysql" )
     return dateasmysql( "0000", "00", "00" ) . " " . $horas . ":" . $mins . ":00" ; 
  elseif( $CFG->db_type == "interbase" )
     return dateasinterbase(1899, 12, 31) . " " . $horas . ":" . $mins . ":00"; 
}

//
// esta funcion toma un valor de fecha DIRECTO de la base de datos
// devuelve un string con formato legible por humamo (dd/mm/aaaa o mm/dd/aaaa)
// NOTA: igual que get_str_datetime()
//
function dbdate_to_human_format( $str_dbdate, $includetime=0, $month_as_str=0, $quitar_palabra_auxiliar = 1 )
{
	return get_str_datetime( $str_dbdate, $includetime, $month_as_str, $quitar_palabra_auxiliar );
}

// Convierte una fecha a HUMAN format>
function get_str_datetime( $strdatetime, $includetime=1, $month_as_str=1, $quitar_palabra_auxiliar = 0 )
{
   global $CFG;
   require_once( "config_db.inc.php" );
   
   if( $strdatetime == "")
   {  $cStr = ""; }
   else
   {
     $pos = strpos($strdatetime, "-");

	 if( $CFG->db_type == "mysql" )
	 {
	   $canio = substr( $strdatetime, 0, 4);
	   $cmes  = substr( $strdatetime, ($pos==0)?4:5, 2);
	   $cdia  = substr( $strdatetime, ($pos==0)?6:8, 2);
	 
	   if( $includetime == 1 )
	   {
	      // se espera que el formato sea aaaa-mm-11 hh:mm:ss	 
	      $chora = substr( $strdatetime, 11, 2);
	      $cmins = substr( $strdatetime, 14, 2);
	      $csegs = substr( $strdatetime, 17, 2);		 
	   }	
		
	 }
	 elseif( $CFG->db_type == "interbase" )
	 {
	   $canio = substr( $strdatetime, 6, 4);
	   $cmes  = substr( $strdatetime, 0, 2);
	   $cdia  = substr( $strdatetime, 3, 2);
	 
	   if( $includetime == 1 )
	   {	 
	      $chora = substr( $strdatetime, 11, 2);
	      $cmins = substr( $strdatetime, 14, 2);
	      $csegs = substr( $strdatetime, 17, 2);
	   }
	 }
	 
	 if( $month_as_str == 1 )
	 {
		 if( $cmes == "01" )     { $cstrmes = "Ene"; }
		 elseif( $cmes == "02" ) { $cstrmes = "Feb"; }
		 elseif( $cmes == "03" ) { $cstrmes = "Mar"; }
		 elseif( $cmes == "04" ) { $cstrmes = "Abr"; }
		 elseif( $cmes == "05" ) { $cstrmes = "May"; }
		 elseif( $cmes == "06" ) { $cstrmes = "Jun"; }
		 elseif( $cmes == "07" ) { $cstrmes = "Jul"; }
		 elseif( $cmes == "08" ) { $cstrmes = "Ago"; }
		 elseif( $cmes == "09" ) { $cstrmes = "Sep"; }
		 elseif( $cmes == "10" ) { $cstrmes = "Oct"; }
		 elseif( $cmes == "11" ) { $cstrmes = "Nov"; }
		 elseif( $cmes == "12" ) { $cstrmes = "Dic"; }
		 elseif( $cmes == "00" ) { $cstrmes = "000"; }
		 else $cstrmes = "";
	 }
	 else
	    $cstrmes = $cmes;
	 
	 $cStr = $cdia . "/" . $cstrmes . "/" . $canio;
	 
	 if ( $includetime == 1 ) 
	 {
  	    if( $chora != "" and $cmins != "" and $csegs != "" )
		{
			$cStr .= (($quitar_palabra_auxiliar == 1) ? " " : " a las ") . $chora . ":" . $cmins . ":" . $csegs ;  
		}
	 }
   }
   
   return $cStr;
}

function get_str_onlytime( $strdatetime, $mostrarsecs=0 )
{
   global $CFG;
   require_once( "config_db.inc.php" );
   
   if( $strdatetime == "")
   {  $cStr = ""; }
   else
   {
 
	 if( $CFG->db_type == "mysql" )
	 {
	   /**$canio = substr( $strdatetime, 0, 4);
	   $cmes  = substr( $strdatetime, 4, 2);
	   $cdia  = substr( $strdatetime, 6, 2); **/
	 
	   $chora = substr( $strdatetime, 11, 2);
	   $cmins = substr( $strdatetime, 14, 2);
	   $csegs = substr( $strdatetime, 17, 2);
	 }
	 elseif( $CFG->db_type == "interbase" )
	 {
	  /* $canio = substr( $strdatetime, 6, 4);
	   $cmes  = substr( $strdatetime, 0, 2);
	   $cdia  = substr( $strdatetime, 3, 2); */
	 
	   $chora = substr( $strdatetime, 11, 2);
	   $cmins = substr( $strdatetime, 14, 2);
	   $csegs = substr( $strdatetime, 17, 2);	 
	 }
	 
    /** if( $cmes == "01" )     { $cstrmes = "Ene"; }
     elseif( $cmes == "02" ) { $cstrmes = "Feb"; }
     elseif( $cmes == "03" ) { $cstrmes = "Mar"; }
     elseif( $cmes == "04" ) { $cstrmes = "Abr"; }
     elseif( $cmes == "05" ) { $cstrmes = "May"; }
     elseif( $cmes == "06" ) { $cstrmes = "Jun"; }
     elseif( $cmes == "07" ) { $cstrmes = "Jul"; }
     elseif( $cmes == "08" ) { $cstrmes = "Ago"; }
     elseif( $cmes == "09" ) { $cstrmes = "Sep"; }
     elseif( $cmes == "10" ) { $cstrmes = "Oct"; }
     elseif( $cmes == "11" ) { $cstrmes = "Nov"; }
     elseif( $cmes == "12" ) { $cstrmes = "Dic"; }
	 **/
	 $cStr = $chora . ":" . $cmins;
	 
	 if( $mostrarsecs==1 )
	    $cStr .= ":" . $csegs;
   }
   
   return $cStr;
}

// esta función debe utilizarse principalmente para
// convertir fechas que vienen de las bases de datos
// y trasladarlas a formatos MEXICANOS
function get_str_date( $strdate, $month_as_str=1 )
{ 
   global $CFG;
   require_once( "config_db.inc.php" );
   
   $char_sep = "/";

   if (strpos($strdate, "-") != 0 ) $char_sep = "-";
   
   if( $strdate == "")
     $cStr = "00$char_sep" . "00" . $char_sep . "0000";
   else
   {
     // La fecha original viene en AAAA-mm-dd para MYSQL
	 // para Interbase viene en mm-dd-AAAA
	 if( $CFG->db_type == "mysql" )
	    $cmonth = substr( $strdate, 5, 2);
	 elseif( $CFG->db_type == "interbase" )
	    $cmonth = substr( $strdate, 0, 2);

	$cmonth = $cmonth + 0;

     if( $month_as_str == 1 )
	 {
		 if( $cmonth == 0 )     $cmonth = "";
		 else if( $cmonth == 1 ) $cmonth = "Ene";
		 else if( $cmonth == 2 ) $cmonth = "Feb";
		 else if( $cmonth == 3 ) $cmonth = "Mar";
		 else if( $cmonth == 4 ) $cmonth = "Abr";
		 else if( $cmonth == 5 ) $cmonth = "May";
		 else if( $cmonth == 6 ) $cmonth = "Jun";
		 else if( $cmonth == 7 ) $cmonth = "Jul";
		 else if( $cmonth == 8 ) $cmonth = "Ago";
		 else if( $cmonth == 9 ) $cmonth = "Sep";
		 else if( $cmonth == 10) $cmonth = "Oct";
		 else if( $cmonth == 11) $cmonth = "Nov";
		 else if( $cmonth == 12) $cmonth = "Dic";
	 }
	 else
	 {
	 	if($cmonth < 10) $cmonth = "0" . $cmonth;
	 }

     if( $CFG->db_type == "mysql" )
	 {
        if( $cmonth == "" ) 
		   $cStr = "30-Dic-1899";
		else 
		   $cStr = substr( $strdate, 8, 2) . $char_sep . $cmonth . $char_sep . substr( $strdate, 0, 4);
	 }
	 elseif( $CFG->db_type == "interbase" )
	 {
        $cStr = substr( $strdate, 3, 2) . $char_sep . $cmonth . $char_sep . substr( $strdate, 6, 4);
	 }
   }
	
   if( ($cStr == "30-Dic-1899") or ($cStr == "30-12-1899"))
	   $cStr = "";
   
   return $cStr;
}

function get_str_time( $strdate )
{
  // La fecha original viene en AAAA-mm-dd 00:00:00
   $time = substr( $strdate, 11, 8);
   
   return $time;
}

function getcurdate_human_format()
{
	$result = "";

	if( getsessionvar("pais") == "USA" )
	{
		$result = strftime("%m/%d/%Y");
	}
	else
	{
		$result = strftime("%d/%m/%Y");
	}
	
	return $result;
}

function getblankdate_human_format()
{
	$result = "";

	if( getsessionvar("pais") == "USA" )
	{
		$result = strftime("00/00/0000");
	}
	else
	{
		$result = strftime("00/00/0000");
	}
	
	return $result;
}

//
// esta funcion toma un valor de fecha capturada por usuario (HUMAN)
// puede estar en dd/mm/aaaa o mm/dd/aaaa
//
// devuelve un array asociativo con 1=dd, 
// valores[d,m,a,hrs,mins,secs];
//
function decodedate( $str_date, $include_time=0 )
{
	$pos = strpos( $str_date, " " );
	
	$strtime = "";
	
	if( $pos != 0 )
	{
		// si hay valor de TIME
		// obtener el valor de TIME
		$strtime = substr( $str_date, $pos+1, 50 );
		$str_date = substr( $str_date, 0, $pos);
	}
	
	$valores = explode( "/", $str_date );
	
	$array_date = Array();
	
	if(( getsessionvar("pais") == "MEXICO" ) or ( getsessionvar("pais") == "PANAMA" ) or ( getsessionvar("pais") == "ECUADOR" ) )
	{
		// fecha en d/m/a
		$array_date = Array( "d" => $valores[0], "m" => $valores[1], "a" =>  $valores[2] );
	}
	else if( getsessionvar("pais") == "USA" )
	{
		// fecha en m/d/a
		$array_date = Array( "d" => $valores[1], "m" => $valores[0], "a" =>  $valores[2] );
	}
	
	if( $include_time == 1 )
	{
		if( $strtime == "" )
		{
			$array_date["hrs"] = 0;
			$array_date["mins"] = 0;
			$array_date["secs"] = 0;
			$array_date["msecs"] = 0;
		}
		else
		{
			$valores_hora = explode( ":", $strtime );
			$array_date["hrs"] = $valores_hora[0];
			$array_date["mins"] = $valores_hora[1];
			$array_date["secs"] = $valores_hora[2];
			
			if( count($valores_hora) > 3 )
				$array_date["msecs"] = $valores_hora[3];
		}
	}
	
	return $array_date;
}

//
// esta funcion toma un valor de fecha capturada por usuario
// puede estar en dd/mm/aaaa o mm/dd/aaaa
//
// devuelve un string con formato legible por humamo desde un unixtimestamp
//
function encodedate_to_human_format( $unixstyle_date, $include_time=0 )
{
	$result = "";
	
	if( getsessionvar("pais") == "USA" )
	{
		$result = date( ($include_time==1) ? "m/d/Y H:i:s" : "m/d/Y", $unixstyle_date );
	}
	else 
	{
		$result = date( ($include_time==1) ? "d/m/Y H:i:s" : "d/m/Y", $unixstyle_date );
	}
	
	return $result;
}

//
// Convierte una fecha en HUMAN style a UNIX style
// la fecha puede incluir valores de H+MIN,SECS
//
function convert_humandate_to_unixstyle( $the_date )
{
	$aValoresFecha = decodedate( $the_date, 1 );
	
	$date_time_stamp = mktime( $aValoresFecha["hrs"], $aValoresFecha["mins"], $aValoresFecha["secs"], $aValoresFecha["m"], $aValoresFecha["d"], $aValoresFecha["a"] );
	
	return $date_time_stamp;
}

//
// SUMAR días a una fecha en formato HUMAN
//
function sum_days( $date_in_human_style, $days_to_sum, $include_time=0 )
{
	$aValoresFecha = decodedate( $date_in_human_style, $include_time );
	
	if( $include_time == 0 )
		$date_time_stamp = mktime( 0, 0, 0, $aValoresFecha["m"], $aValoresFecha["d"], $aValoresFecha["a"] );
	else
		$date_time_stamp = mktime( $aValoresFecha["hrs"], $aValoresFecha["mins"], $aValoresFecha["secs"], $aValoresFecha["m"], $aValoresFecha["d"], $aValoresFecha["a"] );
	
	$date_time_stamp += ($days_to_sum*(24 * 60 * 60));

	return encodedate_to_human_format( $date_time_stamp, $include_time );		
}

//
//  Sirve para remover los slashes antes de un UPDATE
//  debe comportarse correctamente dependiendo el RDBMS
//
function remove_scaped_quotes( $str )
{
	global $CFG;
	
	if( $CFG->db_type == "interbase" )
	{
		// quita 
		$str = str_replace( "\'", "'", $str);
		$str = str_replace( "'", "''", $str);
		return $str;
	}
	else
	{
		return $str; // COMPLEMENTAR PARA OTROS RDBMS
	}
		
}

//
// Checar Privilegio
// y coloca un HINT si el usuario no cuenta con él (OPCIONAL)
//
// Esta rutina solo deberá usarse para usuarios administrativos
//
function verificar_privilegio( $privilegio, $show_back_ifnot=0, $permitir_acceso_a_lectores=0 )
{
	if ( getsessionvar( "isadmin" ) == 1 ) 
	{
		return 1;   // SUPER USUARIO
	}
	
	$mostrar_error = 0;
	
	if( !issetsessionvar("biblio_firmado") ) 
	{
		//return 0;   // no hay sesión
		$mostrar_error = 1;   // no hay sesión
	}
	else if( getsessionvar("biblio_firmado") == "NO" ) 
	{
		//return 0;  // no está firmado
		$mostrar_error = 1;   // no está firmado
	}
	else if( getsessionvar("empleado") != "S" ) 
	{
		if( $permitir_acceso_a_lectores == 1 )
			return 1;
		
		//return 0;  // Cualquier usuario diferente de admvo.
	}  
	
	$retval = 0;
	
	if( $mostrar_error == 0 )
	{
		// no muestra el error por default
		// primero verifica privilegios
	
		$id_biblioteca = getsessionvar( "id_biblioteca" );
		$usuario = getsessionvar( "id_usuario" );
	
		$descrip_priv = "";
	   
		$query = "SELECT a.DESCRIPCION, b.PRIVILEGIO " .
				  "FROM cfgprivilegios a " . 
				  "   LEFT JOIN cfgusuarios_privilegios b ON (b.ID_BIBLIOTECA=$id_biblioteca and b.ID_USUARIO=$usuario and b.PRIVILEGIO=a.PRIVILEGIO) " . 
				  "WHERE a.PRIVILEGIO=$privilegio ";
				  
		$result = db_query( $query );		
		
		if( $row = db_fetch_row($result) )
		{
			if( $row["PRIVILEGIO"] == $privilegio )
				$retval = 1;

			$descrip_priv = $row["DESCRIPCION"];
		} 
		
		free_dbquery( $result );
	}
	
	if( $retval == 0 and $show_back_ifnot == 1 )
	{
		//if( !allow_use_of_popups() )
		echo "<body id='home'>";
		display_global_nav(); 
		
		echo "<!-- contenedor principal -->\n\n";
		echo " <div id='contenedor' class='contenedor'>";
			
		display_banner();      // banner   
		display_menu( "../" ); // menu principal		

		global $MSG_NO_RIGHTS_TITLE, $MSG_NO_RIGHTS_DETAILS;
		
		echo " <div id='bloque_principal'>";
		echo "   <div id='contenido_principal'>";
		echo "      <br><h1>$MSG_NO_RIGHTS_TITLE</h1>";
		echo "      <img src='../images/icons/warning.gif'>&nbsp;" . sprintf( $MSG_NO_RIGHTS_DETAILS, getsessionvar("usuario"), $descrip_priv );
		echo "   </div><br style='clear:both'>";  // contenido principal
		
		display_copyright();
		
		echo "</div>";
	
		echo " </div>";
		echo "</body>";
		die( "" );
	}
	
	return $retval;
}

// Reutilzada de WebGES / ProjectTracker
// Permita sacar un email en una sola línea
// colocando los parámetros adecuados
function enviar_email_notificacion( $email_usuario_creador, $nombre_usuario_creador, $subject, $email, $nombre_destinatario, $cc, $titulo_notificacion, $subtitulo, $body, $end_link="" )
{
	 $header = "Return-Path: root@me.com\r\n"; 
	 $header .= "From: $nombre_usuario_creador <$email_usuario_creador>\r\n"; 
	 
	 if ( $nombre_destinatario != "" )
	 {
	 	if( !getsessionvar("smtp_for_mail") )
	 	 { $email = "$nombre_destinatario <" . $email . ">"; }
	}

	 if( $cc != "" )
	    $header .= "Cc: $cc\r\n";

	 $header .= "Content-Type: text/html; charset=iso-8859-1\r\n";
	 $header .= "Content-Transfer-Encoding: 7bit\r\n";
	
	 $email_bkground = "http://intranet.escolarges.com.mx/images/logoFondoGES.jpg"; // el default
	 
	 if( getsessionvar("mail_background") != "" )
	    $email_bkground = getsessionvar("mail_background");
	
	 $mesg = "<html>\n<body style='font-family:verdana,arial,helvetica; font-size:11px; font-color: black; " .
			 "background: url($email_bkground) no-repeat right top;'> ";
	 
	 // Anexar titulo
	 $mesg .= "<h3><strong>$titulo_notificacion</strong></h3>";

     // Anexar subtitulo
	 if( $subtitulo != "" )
	 {
	 	 $mesg .= "<font size=3>$subtitulo</font><br><br>";
	 }
			 
	 // Anexar cuerpo del mensaje
	 $mesg .= $body;

	 $mesg .= "<br><br>";
		 
     if( $end_link != "" )
	 {
	     $mesg .= "<a href='$end_link'>Para mayor referencia haga \"clic\" en este link: $end_link </a><br>";	
	 }
	
	 $mesg .= "\n<br><hr><div style='display: inline; font-size: 9px; color: #666666;'>Disclaimer: Este e-mail es de interés solo para los individuos mencionados en el mismo. Por lo anterior, no podrá distribuirse ni difundirse bajo ninguna circunstancia. Si Usted no es alguno de los destinatarios y este correo le ha llegado por equivocación se le pide borrarlo inmediatamente.</div>";
	 $mesg .= "</body></html>"; 
	 
	 if( getsessionvar("smtp_for_mail") )
	 {
		require_once 'Mail.php';
		
		// para enviar 
		$smtp = Mail::factory( 'smtp',
		  array ('host' => getsessionvar("smtp_host"),
			'auth' => true,
			'username' => getsessionvar("smtp_user"),
			'password' => getsessionvar("smtp_password") ));
			
		$headers = array ('From' => "$nombre_usuario_creador <$email_usuario_creador>",
		  'Return-Path' => $email_usuario_creador,
		  'To' => $email,
		  'Cc' => $cc,
		  'Subject' => $subject, 
		  'Content-Type' => "text/html; charset=iso-8859-1",
		  'Content-Transfer-Encoding' => "7bit" );		
		
		// el objeto MAIL smtp no maneja el header CC,... por eso el $cc se tiene que añadir al $email
		if( $cc != "" )
		{
			$email = $email . "," . $cc;
		}
		
		$mail = $smtp->send( $email, $headers, $mesg );
		
		if (PEAR::isError($mail)) 
		{
		   echo("<p>" . $mail->getMessage() . "</p>");
		   return 0;
		} 
		else 
		{
		   return 1;
		}			

	 }
	 else
	 {
		 if( mail( $email, $subject, $mesg, $header ) )
			return 1;
		 else
			return 0;
	 } 

}

// Reutilizado de WebGES
// Muestra información dependiendo de la extensión o tipo 
// de un archivo
//
// info = 1  (icono)
// info = 2  (descripción)
//
function obtener_file_info( $mimetype, $info=1 )
{
    $imageicon = "";
	$descripcion = "";
	
    if( strpos($mimetype, "excel" ) !== false )
	{
	   $imageicon = "<IMG SRC='../images/iconos/excel.ico'>";
	   $descripcion = "MS Excel";
	}
    else if( strpos($mimetype, "word") !== false )
	{
	   $imageicon = "<IMG SRC='../images/icons/msword_icon.gif'>";
	   $descripcion = "MS Word";
	}
    else if(  strpos($mimetype, "access") !== false )
	{
	   $imageicon = "<IMG SRC=../images/iconos/access.ico>";
	   $descripcion = "MS Access";
	}
    else if( strpos($mimetype, "powerpoint") !== false )
	{
	   $imageicon = "<IMG SRC=../images/iconos/pwpoint.ico>";
	   $descripcion = "MS PowerPoint";
	}
    else if( strpos($mimetype, "zip") !== false  )
	{
	   $imageicon = "<IMG SRC='../images/iconos/winzip_icon.gif'>";
	   $descripcion = "ZIP file";
	}
    else if( strpos($mimetype, "text/plain") !== false )
	{
	   $imageicon = "<IMG SRC='../images/iconos/text_icon.gif'>";
	   $descripcion = "ASCII";
	}	
    else if( strpos($mimetype, "acrobat") !== false  or strpos($mimetype, "pdf") !== false )
	{
	   $imageicon = "<IMG SRC='../images/iconos/pdf_icon.gif'>";
	   $descripcion = "PDF";
	}
    else if( preg_match("/jpeg|jpg|gif/", $mimetype ) )
	{
	   $imageicon = "<IMG SRC=../images/iconos/graphic_icon.gif>";
	   $descripcion = "Imagen";
	}
    else
	{
	   $imageicon = "<IMG SRC=../images/iconos/app.ico>";
	   $descripcion = "Otro";
	}
	
	return ($info==1) ? $imageicon : $descripcion;
}

// Reutilizado de WebGES
// redirecciona a otras paginas
function ges_redirect( $url )
{
  if( getsessionvar("user_header302_relocation") == true )
  {  
     header( "302 Moved" ); 
  }

  if( strpos( $url, "Location" ) === false )
	$url = "Location:" . $url;
  
  header( $url );
}

// Reutilizado de WebGES
// coloca rapidamente un campo de fecha
// para captura de datos
function colocar_edit_date( $idname, $valor_default_fecha, $solo_muestra_valor=0, $on_other_event="" )
{
   if( $solo_muestra_valor )
   {
   		echo $valor_default_fecha;
   }
   else
   {
   
		echo "\n\n<!-- Fecha: $idname -->\n";		
		echo "<div id='div_" . $idname . "' class='div_fecha'>\n";  

		// se removió un style del siguiente input
		echo "<input class='campo_captura' name='$idname' id='$idname' size='15' maxlength='10' value='$valor_default_fecha' style='display:inline'";

		if( $on_other_event != "" )
		{
			echo $on_other_event;
		}
		
		echo " onKeyUp='javascript:verify_keyup( this, event );	'";
		echo ">";

			echo "<div style='display: inline; position: absolute;'><a href='JavaScript:doNothing();' \n";
			echo "      onclick='setDateField(\"$idname\");\n ";
			echo "		         if( !calDateField.disabled ) {top.calendwin = window.open(\"../basic/getdate.php\",\"cal\",\"WIDTH=200px,HEIGHT=230px,TOP=200px,LEFT=300px,status=no\");}'\n  ";		
			echo "		onMouseOver='javascript: window.status = \"Abrir calendario\"; return true;'\n ";
			echo "		onMouseOut='window.status=\"\"; return true;'><img src='../images/icons/calendario.png' style='position:relative; left: 2px; '></a>\n";
			echo "</div>";

		echo "</div>\n\n";
		
   }
}

function colocar_edit_date_v2( $formname, $idname, $valor_default_fecha, $solo_muestra_valor=0, $on_other_event="" )
{
   if( $solo_muestra_valor )
   {
   		echo $valor_default_fecha;
   }
   else
   {
		global $num_date;
		
		if( !isset($num_date) )
			$num_date = 0;
		
		echo "\n\n<!-- Fecha: $idname -->\n";		
		echo "<div id='div_" . $idname . "' class='div_fecha'>\n";  

		// se removió un style del siguiente input
		echo "<input class='campo_captura' name='$idname' id='$idname' size='14' maxlength='10' value='$valor_default_fecha' style='display:inline'";

		if( $on_other_event != "" )
		{
			echo $on_other_event;
		}
		
		echo " onKeyUp='javascript:verify_keyup( this, event );	'";
		echo ">";
		
		$lines = " new tcal( {'formname': '$formname', 'controlname' : '$idname', 'id' : '$num_date' } ); ";			
		SYNTAX_JavaScript( 1, 1, $lines );
			
		$num_date++;
		
		// hint para flags
		echo "<span class='edit_flag' name='edit_flag_$idname' id='edit_flag_$idname'></span>";

		echo "</div>\n\n";
   
   }
}


// 20ene2009
// Coloca un menú de navegación en la parte superior de las opciones
function colocar_navegacion( $home_link, $home_etiqueta, $segundo_link="", $etiqueta_actual, $colocar_link_regresar=0 )
{
	echo "\n\n<!--- NAVEGACION-->\n\n";
	echo "<DIV class=columnNormal style='margin: 5px 5px 3px 3px;'>";
	
	echo "<img src='../images/iconos/home.gif'>";
	
	echo "<A href='$home_link'>$home_etiqueta</a>";
	
	echo "&nbsp;/&nbsp;$etiqueta_actual&nbsp;";
	
	if( $colocar_link_regresar == 1 )
	{
		echo "<a href='javascript:window.history.back();'><img src='../images/iconos/back.gif'></a>";
	}
	
	echo "</DIV>";

	echo "\n\n<!--- FIN NAVEGACION-->\n\n";
	
}

// 20ene2009
// Verifica Usuario Firmado
function check_usuario_firmado( $basedir = "../" )
{
	if ( getsessionvar("biblio_firmado") == "NO" )
	{
		$url_comp = "";
		
		if( issetsessionvar("id_lib") )
		{
			$url_comp = "?id_lib=" . getsessionvar("id_lib");
		}
		
		 ges_redirect( $basedir . "main.php$url_comp" );
	}
}

// 11may2010
// Verifica Usuario Administrador
function check_usuario_empleado( $basedir = "../" )
{
	$redirect = 1;

	if( issetsessionvar("empleado") )
	{
		if ( getsessionvar("empleado") == "SI" or getsessionvar("empleado") == "S" )
		{	
			$redirect = 0;
		}
	}
	
	if( $redirect == 1 )
	{
		$url_comp = "";
		
		if( issetsessionvar("id_lib") )
		{
			$url_comp = "?id_lib=" . getsessionvar("id_lib") . "&init=0";
		}
		
		ges_redirect( $basedir . "main.php$url_comp" );	
	}

}


function display_global_nav()
{	
	global $CONNECTED_AS, $LBL_LOGOUT;
	
	echo '<div id="global-nav">';
	
	if ( getsessionvar("biblio_firmado") == "SI" )
	{
		echo "$CONNECTED_AS (" . getsessionvar("usuario") . ") ";
		
		if( strpos($_SERVER["PHP_SELF"], "phps/" ) )
			$link_4_logout = "../logout.php";
		else
			$link_4_logout = "logout.php";
		
		echo "&nbsp;<a href='$link_4_logout'>&laquo; $LBL_LOGOUT </a>&nbsp;";
	}
	
  	echo '<a href="http://www.ebibliotek.com" target="new"><strong>www.eBiblioTEK.com</strong></a>';
	echo '</div>';
}

// 20ene2009
// Muestra el banner superior con un gráfico o imagen
// 
function display_banner()
{	
//	global $ACCESS_CFG;     
//	require_once( getsessionvar("ss_physical_dir") . "APP_CONFIG.php" );
	
//	echo getsessionvar( "file_banner" );
//	die( $ACCESS_CFG->banner );
	
	$custom_file_banner = "";

	if( issetsessionvar("file_banner") )
	{
		if( getsessionvar("file_banner") != "" )
		{
			$custom_file_banner = getsessionvar("file_banner");
/**
			if( strpos($_SERVER["PHP_SELF"], "phps/" ) )
				$custom_file_banner = "../images/banners/" . $custom_file_banner;
			else
				$custom_file_banner = "images/banners/" . $custom_file_banner;				 **/
		}
	}	
	
	if( $custom_file_banner != "" )
		$file_banner = $custom_file_banner;
	else
	{
		if( strpos($_SERVER["PHP_SELF"], "phps/" ) )
			$file_banner = "../images/banner_rapido.jpg";
		else
			$file_banner = "images/banner_rapido.jpg";
	}
	
	echo "<div id='banner' style='height:100px;'>";
	echo "<img src='$file_banner'>";
	echo "</div>";
	echo "\n";
}

// 20ene2009
// Muestra el menú de navegación
// 
function display_menu( $basedir = "" )
{
	global $MENU_OPT_1, $MENU_OPT_15;
	
	echo "\n";
	
	echo "<div id='menu'>";
	echo "<ul id='css3menu1' class='topmenu'>";
	
	echo "<li class='topfirst'>";
	
	if( issetsessionvar("biblio_firmado") )
	{	
		if( getsessionvar("biblio_firmado") == "SI" )
			echo "<a href='$basedir" . "index.php' style='height:14px;line-height:14px;'>$MENU_OPT_1</a>";
		else
		{
			$url_extras = "";
			echo "<a href='$basedir" . "main.php?$url_extras' style='height:14px;line-height:14px;'>$MENU_OPT_1</a>";
		}
	}
	else
		echo "<a href='$basedir" . "main.php' style='height:14px;line-height:14px;'>$MENU_OPT_1</a>";
	
	echo '</li>';

	$menu_icon_none = '<h2>&nbsp;</h2>';
	
	if( getsessionvar("biblio_firmado") == "SI" )
	{
		global $MENU_OPT_3, $MENU_OPT_5, $MENU_OPT_7, $MENU_OPT_9, $MENU_OPT_11, $MENU_OPT_13;

		global $MENU_3_ITEM1, $MENU_3_ITEM3, $MENU_3_ITEM5, $MENU_3_ITEM7;

		global $ANLSMENU_ITEM1, $ANLSMENU_ITEM2, $ANLSMENU_ITEM3, $ANLSMENU_ITEM4, $ANLSMENU_ITEM5, $ANLSMENU_ITEM6, $ANLSMENU_ITEM7, $ANLSMENU_ITEM11, $ANLSMENU_ITEM13, $ANLSMENU_ITEM15;
		global $CIRMENU_ITEM1, $CIRMENU_ITEM3, $CIRMENU_ITEM5, $CIRMENU_ITEM6, $CIRMENU_ITEM7, $CIRMENU_ITEM9, $CIRMENU_ITEM11;
		global $SRVMENU_ITEM1, $SRVMENU_ITEM3, $SRVMENU_ITEM5, $SRVMENU_ITEM7, $SRVMENU_ITEM8, $SRVMENU_ITEM9;
		global $INF_GRAL_STATISTICS, $INF_DAILY_STATISTICS, $INF_MOST_VIEWED_TITLES, $INF_LOANS, $INF_LOANS_ON_DUE, $INF_CIRCULATION_REPTS, $INF_SANCTIONS, $INF_OPAC, $INF_STATISTICS_CATALOG;
		global $CFGMENU_ITEM1, $CFGMENU_ITEM2, $CFGMENU_ITEM3, $CFGMENU_ITEM4, $CFGMENU_ITEM6, $CFGMENU_ITEM7, $CFGMENU_ITEM8, $CFGMENU_ITEM9, $CFGMENU_ITEM11, $CFGMENU_ITEM12, $CFGMENU_ITEM13, $CFGMENU_ITEM15, $CFGMENU_ITEM17;
		global $CFGMENU_CONTENTS;
		
		setsessionvar( "last_url", $_SERVER["REQUEST_URI"] );  // utilizada para el F5 o Refresh Key desde home.php 
		
		if( getsessionvar("empleado") == "S" )
		{
			// submenu de Adquisiciones
			if( getsessionvar("__advanced_service") == "S" )
			{
				echo '<li class="topmenu"><a href="#" style="height:14px;line-height:14px;">', $MENU_OPT_3, '</a>';
					echo '<ul>'.
						 '<li><a href="#">', $menu_icon_none, $MENU_3_ITEM1 . '</a></li>' .  // Requisitions
						 '<li><a href="#">', $menu_icon_none, $MENU_3_ITEM3 . '</a></li>' .  // Entradas
						 '<li class="item_sep"><a href="#">', $menu_icon_none, $MENU_3_ITEM5 . '</a></li>' .  // Cancelations
						 '<li><a href="#">', $menu_icon_none, $MENU_3_ITEM7 . '</a>' .  // Informs of Adcquisitions
						 '</ul>';
				echo '</li>';
				echo "\n";
			}

			// submenu Análisis /Analysis
			echo '<li class="topmenu"><a href="#" style="height:14px;line-height:14px;">' . $MENU_OPT_5 .  '</a>';		
				echo '<ul>'.
					 '<li class="item_sep"><a href="'. $basedir . 'phps/anls_catalogacion.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_catalogacion.png"></h1>' . $ANLSMENU_ITEM1 . '</a></li>'.  // Cataloging
					 //'<a href="'. $basedir . 'phps/anls_tematizacion.php">' . $ANLSMENU_ITEM2 . '</a>'.
					 '<li><a href="'. $basedir . 'phps/gral_elegir_item.php?the_action=existencias"><h1><img src="' . $basedir . 'images/menu_icons/icon_inventario.png"></h1>' . $ANLSMENU_ITEM3 . '</a></li>'.
					 '<li class="item_sep"><a href="'. $basedir . 'phps/anls_descartes.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_expurgo.png"></h1>' . $ANLSMENU_ITEM4 . '</a></li>'.
					 '<li><a href="'. $basedir . 'phps/anls_series.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_publicaciones.png"></h1>' . $ANLSMENU_ITEM5 . '</a></li>'.
					 '<li class="item_sep"><a href="'. $basedir . 'phps/anls_series_recep.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_recepcion_publi.png"></h1>' . $ANLSMENU_ITEM6 . '</a></li>'.
					 '<li class="item_sep"><a href="'. $basedir . 'phps/anls_consultatitulos.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_consul.png"></h1>' . $ANLSMENU_ITEM7 . '</a></li>'.
					 '<li><a href="'. $basedir . 'phps/anls_barcodes.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_impresion.png"></h1>' . $ANLSMENU_ITEM11 . '</a></li>'.
					 '<li><a href="'. $basedir . 'phps/anls_print_catalogs.php">', $menu_icon_none, $ANLSMENU_ITEM13 . '</a></li>'.
					 '<li><a href="'. $basedir . 'phps/anls_print_cards.php">', $menu_icon_none, $ANLSMENU_ITEM15 . '</a></li>'.
					 /* '<span></span>'.
					 '<a href="#">Importar Catálogo</a>'. */
					 '</ul>';
			echo '</li>';		
			echo "\n";

			// submenu Circulation/Circulation 
			if( getsessionvar("__basic_service") == "S" )
			{
				echo '<li class="topmenu"><a href="#" style="height:14px;line-height:14px;">' . $MENU_OPT_7 . '</a>';
					echo '<ul>'.
						 '<li><a href="'. $basedir . 'phps/gral_elegir_usuario.php?the_action=prestamos"><h1><img src="' . $basedir . 'images/menu_icons/icon_prestamos.png"></h1>' . $CIRMENU_ITEM1 . '</a></li>'.
						 '<li><a href="'. $basedir . 'phps/gral_elegir_usuario.php?the_action=devoluciones"><h1><img src="' . $basedir . 'images/menu_icons/icon_devoluciones.png"></h1>' . $CIRMENU_ITEM5 . '</a></li>'.
						 '<li><a href="'. $basedir . 'phps/gral_elegir_item.php?the_action=devoluciones"><h1><img src="' . $basedir . 'images/menu_icons/icon_devoluciones.png"></h1>' . $CIRMENU_ITEM6 . '</a></li>' .
						 '<li class="item_sep"><a href="'. $basedir . 'phps/gral_elegir_usuario.php?the_action=renovaciones"><h1><img src="' . $basedir . 'images/menu_icons/icon_renovacion.png"></h1>' . $CIRMENU_ITEM7 . '</a></li>' .
						 '<li class="item_sep"><a href="'. $basedir . 'phps/gral_elegir_usuario.php?the_action=reservas"><h1><img src="' . $basedir . 'images/menu_icons/icon_reservaciones.png"></h1>' . $CIRMENU_ITEM9 . '</a></li>'.
						 '<li><a href="'. $basedir . 'phps/gral_elegir_item.php?the_action=tracking"><h1><img src="' . $basedir . 'images/menu_icons/icon_rastreo.png"></h1>' . $CIRMENU_ITEM11 . '</a></li>'.
						 '</ul>';		
				echo '</li>';
				echo "\n";
								 
				// Servicios
				echo '<li class="topmenu"><a href="#" style="height:14px;line-height:14px;">' . $MENU_OPT_9 . '</a>';
					echo '<ul>'.
						 '<li class="item_sep"><a href="'. $basedir . 'phps/serv_usuarios.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_usuarios.png"></h1>' . $SRVMENU_ITEM3 . '</a></li>'.
						 '<li><a href="'. $basedir . 'phps/gral_elegir_usuario.php?the_action=sanciones"><h1><img src="' . $basedir . 'images/menu_icons/icon_sanciones.png"></h1>'.  $SRVMENU_ITEM7 . '</a></li>'.
						 '<li class="item_sep"><a href="'. $basedir . 'phps/gral_elegir_usuario.php?the_action=sanciones_cumplidas"><h1><img src="' . $basedir . 'images/menu_icons/icon_cumplimiento_sanc.png"></h1>' . $SRVMENU_ITEM8 . '</a></li>'.
						 '<li><a href="'. $basedir . 'phps/gral_elegir_usuario.php?the_action=restricciones"><h1><img src="' . $basedir . 'images/menu_icons/icon_restricciones.png"></h1>' . $SRVMENU_ITEM9 . '</a></li>';
					
					if( getsessionvar("id_biblioteca") == 11 )
					{
						echo '<li class="item_sep"><a href="'. $basedir . 'phps/gral_importar_acervo.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_restricciones.png"></h1>IMPORTAR ACERVO</a></li>';
					}
										 
					echo '</ul>';			
				echo '</li>';
				echo "\n";
			}
			else
			{	
				if( getsessionvar("__personal_service") == "S" )
				{
					// Servicios para usuarios personales
					echo '<li class="topmenu"><a href="#" style="height:14px;line-height:14px;">' . $MENU_OPT_9 .'</a>';
						echo '<ul>'.
							 '<li><a href="'. $basedir . 'phps/serv_usuarios.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_usuarios.png"></h1>' . $SRVMENU_ITEM3 . '</a></li>'.
							 '</ul>';			
					echo '</li>';
					echo "\n";					
				}
			}
			
			// submenú Reportes y Estadísticas
			echo '<li  class="topmenu"><a href="#" style="height:14px;line-height:14px;">' . $MENU_OPT_11 .'</a>';
				echo '<ul>'.
					 '<li><a href="' . $basedir .'phps/gral_rango_fechas.php?the_action=est_general"><h1><img src="' . $basedir . 'images/menu_icons/icon_grafica.png"></h1>' . $INF_GRAL_STATISTICS . '</a></li>';
					 
				if( getsessionvar("__basic_service") == "S" )		 
					echo '<li><a href="' . $basedir .'phps/gral_rango_fechas.php?the_action=prest_otorgados">',$menu_icon_none, $INF_LOANS . '</a></li>';
					 
				echo '<li class="item_sep"><a href="' . $basedir .'phps/gral_rango_fechas.php?the_action=est_catalog">',$menu_icon_none, $INF_STATISTICS_CATALOG . '</a></li>' .
//					 '<li><a href="#">' . $INF_DAILY_STATISTICS . '</a></li>' .
					 '<li><a href="' . $basedir .'phps/gral_rango_fechas.php?the_action=titulos_freq"><h1><img src="' . $basedir . 'images/menu_icons/icon_reportes.png"></h1>' . $INF_MOST_VIEWED_TITLES . '</a></li>';
					 
			if( getsessionvar("__basic_service") == "S" )		 
			{
				echo '<li><a href="' . $basedir .'phps/gral_rango_fechas.php?the_action=prest_vencidos">',$menu_icon_none, $INF_LOANS_ON_DUE . '</a></li>'.
					  '<li class="item_sep"><a href="' . $basedir .'phps/gral_rango_fechas.php?the_action=est_circulacion">',$menu_icon_none, $INF_CIRCULATION_REPTS . '</a></li>';
				echo  '<li><a href="' . $basedir .'phps/gral_rango_fechas.php?the_action=est_sanciones"><h1><img src="' . $basedir . 'images/menu_icons/icon_reportes.png"></h1>' . $INF_SANCTIONS . '</a></li>';
			}

			echo '<li><a href="' . $basedir .'phps/gral_rango_fechas.php?the_action=est_opac">',$menu_icon_none, $INF_OPAC . '</a></li>' .
				 '</ul>';
			echo '</li>';
			echo "\n";
			
			// submenú Configuración
			
			$isadmin = getsessionvar( "isadmin" ) == 1;
			
			echo '<li  class="topmenu"><a href="#" style="height:14px;line-height:14px;">' . $MENU_OPT_13 .'</a>';
				echo '<ul>'.
					 '<li><a href="'. $basedir . 'phps/conf_system.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_datos_ent_usu.png"></h1>' . $CFGMENU_ITEM1 . '</a></li>'.
					 '<li class="item_sep"><a href="'. $basedir . 'phps/conf_contents.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_cambio_biblio.png"></h1>' . $CFGMENU_CONTENTS . '</a></li>' .
					 '<li><a href="'. $basedir . 'phps/conf_thesaurus.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_registro_activ.png"></h1>' . $CFGMENU_ITEM2 . '</a></li>'.
					 '<li><a href="'. $basedir . 'phps/conf_templates.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_plantillas_capt.png"></h1>' . $CFGMENU_ITEM3 . '</a></li>'.					 
					 '<li><a href="'. $basedir . 'phps/conf_personas.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_personas.png"></h1>' . $CFGMENU_ITEM4 . '</a></li>'.
					 '<li><a href="'. $basedir . 'phps/conf_sanciones.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_cat_sanciones.png"></h1>' . $CFGMENU_ITEM6 . '</a></li>'.
					 '<li class="item_sep"><a href="'. $basedir . 'phps/conf_restricciones.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_cat_restricciones.png"></h1>' . $CFGMENU_ITEM7 . '</a></li>'.
					 '<li><a href="'. $basedir . 'phps/conf_cat_params.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_reglas_cat_autor.png"></h1>' . $CFGMENU_ITEM8 . '</a></li>'.
					 '<li><a href="'. $basedir . 'phps/conf_consultas.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_consul.png"></h1>' . $CFGMENU_ITEM9 . '</a></li>'.
					 '<li><a href="'. $basedir . 'phps/conf_email.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_alertas_email.png"></h1>' . $CFGMENU_ITEM12 . '</a></li>'.
					 '<li class="item_sep"><a href="'. $basedir . 'phps/serv_usuariosgrupos.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_usuarios.png"></h1>' . $CFGMENU_ITEM11 . '</a></li>'.					 
					 '<li class="item_sep"><a href="'. $basedir . 'phps/serv_usr_bitacora.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_bitacora.png"></h1>'. $CFGMENU_ITEM13 . '</a></li>'.
					// '<li><a href="'. $basedir . 'phps/conf_cambiar.php">' . $menu_icon_none . $CFGMENU_ITEM15 . '</a></li>'.
					// '<li><a href="'. $basedir . 'phps/conf_cambiar_idioma.php"><h1><img src="' . $basedir . 'images/menu_icons/icon_idioma.png"></h1>' . $CFGMENU_ITEM17 . '</a></li>'.
					 '</ul>';
			echo '</li>';
			echo "\n";
			
			echo '<li class="toplast"><a href="' . $basedir . 'help/es/index.html" target="new"  style="height:14px;line-height:14px;">' . $MENU_OPT_15 . '</a></li>';
			echo "\n";
		}
	}
	
	echo "</ul>";
	
	echo "</div>";
	//echo "<br>";
}

// 20ene2009
// Muestra en la parte inferior una leyenda de Derechos Reservados
function display_copyright()
{
	global $NOTES_COMMENTS, $CONTACT_US;
	
    echo "\n\n<!-- INICIA mención de copyright -->\n\n";
	echo "<div class='block_copyright'>";
	echo "  <p>&copy;2009-2012, <a href='http://www.ebibliotek.com'>Escolar Hi-TECH Alta Tecnolog&iacute;a para la Gesti&oacute;n de la Educaci&oacute;n</a>.<br>";
	echo "  <p><strong>$NOTES_COMMENTS</strong> <a href='http://www.ebibliotek.com/comentarios.php'>$CONTACT_US</a>.</p>";
	echo "</div>";
}

//
// 09dic2009
//  25mar2011 - Se agrega el conteno de restricciones
// Muestra información personal de préstamos, sanciones, etc.
//
function display_personal_info()
{
	global $HINT_ITEMS_IN_LOAN_NOW, $HINT_ITEMS_RESERVED, $HINT_USER_SANCTIONS, $HINT_USER_RESTRICTIONS;
	global $user;
	
	if( isset($user) )
	{
		$items_en_prestamo = $user->ObtenerNumItemsPrestados();
		$items_actualmente_reservados =  $user->ObtenerNumItemsReservados();
		$sanciones_sin_cumplir =  $user->ObtenerNumSanciones();
		
		$restricciones = $user->ObtenerNumRestricciones(0);
	
		if( $items_en_prestamo > 0 or  ($items_actualmente_reservados > 0 ) or ($sanciones_sin_cumplir>0) or ($restricciones>0) )
		{
			echo "<p class='info'><strong>Resumen de su cuenta:</strong>&nbsp;";
			
			if( $items_en_prestamo > 0 )
				echo sprintf( $HINT_ITEMS_IN_LOAN_NOW, $items_en_prestamo ) . "&nbsp;";
			
			if( $items_actualmente_reservados > 0 )
				echo sprintf( $HINT_ITEMS_RESERVED, $items_actualmente_reservados ) . "&nbsp;";
				
			if( $sanciones_sin_cumplir > 0 )
				echo sprintf( $HINT_USER_SANCTIONS, $sanciones_sin_cumplir ) . "&nbsp;";
				
			if( $restricciones > 0 )
				echo sprintf( $HINT_USER_RESTRICTIONS, $restricciones ) . "&nbsp;";				
				
			echo "</p>";
		}
	}

}

// 17nov2009
// Muestra información personal y links para usuarios
//
//  25mar2011 - Se verifican restricciones en RESERVAS y RENOVACIONES
//
function display_personal_links( $db )
{
	global $LINK_MY_FILES, $LINK_USER_ACTIVITY, $LINK_USER_RESERVAS, $LINK_USER_RENEWALS, $LINK_USER_REMOVE_RESERVA;
	global $user;
	
	if( getsessionvar("__space_for_storage") == "S" )
	{
		echo "	<a href=''>$LINK_MY_FILES</a><br><br>";	
	}
	
	echo "	<strong><a href='phps/serv_usr_bitacora.php?id_usuario=" . getsessionvar("id_usuario") . "'>$LINK_USER_ACTIVITY</a></strong><br><br>";
	
	// 
	// Reservas
	//
	if( isset($user) )
	{
		if( $user->GRUPO_MAX_RESERVACIONES > 0 and (!$user->EXIST_RESTRICTION_RESERVA) )
		{
			global $LBL_GROUP_RESERVAS;
			
			echo "<hr>";
			echo "<h2>$LBL_GROUP_RESERVAS</h2><br>";
			
			echo "	<strong><a href='phps/circ_reservaciones.php?id_usuario=" . getsessionvar("id_usuario") . "'>$LINK_USER_RESERVAS</a></strong><br>";
			
			$items_actualmente_reservados = $user->ObtenerNumItemsReservados();
			
			if( ($items_actualmente_reservados) > 0 )
			{
				global $HINT_ITEMS_RESERVED; 
				
				echo "<br>";
				echo "<strong>Actualmente tiene " . sprintf( $HINT_ITEMS_RESERVED, $items_actualmente_reservados ) . "&nbsp;</strong><br><br>";
				
				//
				// Verificar las reservaciones Bloqueadas - Retenidas (para Entrega)  o Pendientes de procesar
				//
				$db->Open( "SELECT a.ID_RESERVACION, b.ID_TITULO, b.STATUS_RESERVACION " .
						   " FROM reservaciones_mst a " .
						   "	 LEFT JOIN reservaciones_det b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_RESERVACION=a.ID_RESERVACION) " .
						   " WHERE (a.ID_BIBLIOTECA=$user->nIDBiblioteca and a.ID_USUARIO=$user->nIDUser) and (b.STATUS_RESERVACION='B' or b.STATUS_RESERVACION='P'); " );

				//Complementar el despliegue de informacion
				//con datos respecto a la modalidad de reservacion y el día que será procesada
				
				while( $db->NextRow() )
				{
					$item = new TItem_Basic( $user->nIDBiblioteca, $db->row["ID_TITULO"], 0, $db );
					
					$id_titulo = $db->row["ID_TITULO"];
					$id_reserva = $db->row["ID_RESERVACION"];
					
					// Remove this item from personal bin
					$delete_reserva_item_link = "<a href='phps/circ_bandeja.php?accion=4&id_titulo=$id_titulo&id_reserva=$id_reserva' title='$LINK_USER_REMOVE_RESERVA'><img src='../images/icons/cut.gif'></a>";
					echo "<div class='mini_bullet' style='float:left; display:block; width:35px;'>&nbsp;&nbsp;&nbsp;&nbsp;$delete_reserva_item_link</div>";
					
				    $class_hilite = "hilite_odd";
				   
				    if( $db->numRows % 2 == 1 )
					   $class_hilite = "hilite_even";					
					
					echo "<div class='$class_hilite' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$class_hilite\";' " . 
						 "style='float: left; display:block; width:200px; '>[" . $item->Material_ShortCode() . "]&nbsp;$item->cTitle_ShortVersion";
					
					if( $db->row["STATUS_RESERVACION"] == "E" )
					{
						echo "&nbsp;<img src='images/icons/warning.gif'>(Retenida para Ud.)";
					}
					else if ( $db->row["STATUS_RESERVACION"] == "P" )
					{
						echo "&nbsp;(En lista de espera desde ...)"; // PENDIENTE colocar icono
					}
					
					echo ".</div><br style='clear:both'>";
					
					$item->destroy();
				}
						   
				$db->Close();
			}
		}
		
		//
		// Renovaciones
		//		
		if( $user->GRUPO_MAX_RENOVACIONES > 0 and (!$user->EXIST_RESTRICTION_RENOVACION)  )
		{
			if( $user->ObtenerNumItemsPrestados() > 0 )
			{
				global $LBL_GROUP_RENEWALS;

				echo "<hr>";
				echo "<h2>$LBL_GROUP_RENEWALS</h2><br>";

				echo "	<strong><a href='phps/circ_renovaciones.php?id_usuario=" . getsessionvar("id_usuario") . "'>$LINK_USER_RENEWALS</a></strong><br><br>";
			}
		}
	}
}

//
// 17mar2009
// Permite manejar archivos de localización
//
function include_language( $base_php_filename, $base_dir = "" )
{

	if( !issetsessionvar("language") )
	{
		if( isset( $_COOKIE["language"] ) )
		{
			setsessionvar( "language", $_COOKIE["language"] );
		}
		else
		{
			setsessionvar( "language", 1 );  // por default Spanish

			if( issetsessionvar("language_pref") )
			{
				
				//die( "" );
				// si hay una variable de idioma predefinido
				if( getsessionvar("language_pref")=="Spanish" )
					setsessionvar( "language", 1 );
				else if( getsessionvar("language_pref")=="English")
					setsessionvar( "language", 2 );
				else if( getsessionvar("language_pref")=="Portuguese")
					setsessionvar( "language", 3 );
					
				//echo "<!--  "  . getsessionvar("language_pref") . "<!-- >";
			}
				
		}
		
		
	}
	else
	   { }
	
	if( $base_php_filename == "" )
		die( "coloque el nombre del archivo base" );
	else
	{
		if( getsessionvar("language") == 1 )
			$lang_file = $base_dir . "languages/spanish";
		else if( getsessionvar("language") == 2 )
			$lang_file = $base_dir . "languages/english";
		else if( getsessionvar("language") == 3 )
			$lang_file = $base_dir . "languages/portuguese";
		else 
			$lang_file = $base_dir . "languages/spanish";
			
		/*else
			die( "No language definitions for " . getsessionvar("language") );
		*/	
		require_once "$lang_file/$base_php_filename" . "_def.php";
	}
	
}

// 
// 29-mar-2009
// Agrega un log a la bitácora de usuarios
//
function agregar_actividad_de_usuario( $id_accion, $observaciones, $id_item=0, $id_titulo=0, $caux1="" )
{
    $id_biblioteca = getsessionvar("id_biblioteca");
	$id_usuario    = getsessionvar("id_usuario");
	
	if (getenv("HTTP_X_FORWARDED_FOR")) 
		$ip_addr = getenv("HTTP_X_FORWARDED_FOR");
	else 
		$ip_addr = getenv("REMOTE_ADDR");	
	
	$fecha = current_dateandtime();
	
	$query = "INSERT INTO usuarios_bitacora_eventos ( ID_BIBLIOTECA, ID_USUARIO, ID_ACCION, FECHA,  OBSERVACIONES, IP_ESTACION, ID_ITEM, ID_TITULO, CAUX1 ) " . 
			 "VALUES ( $id_biblioteca, $id_usuario, $id_accion, '$fecha', '$observaciones', '$ip_addr', $id_item, $id_titulo, '$caux1' ) ";
			 
	db_query( $query );
	
}

function allow_use_of_popups()
{
	if( issetsessionvar("usar_popups_transactions") )
	{
		if( getsessionvar("usar_popups_transactions") == true )
			return true;
		else
			return false;
	}
	else
		return false;
}

function back_function()
{
	if( allow_use_of_popups() )
		echo "javascript:window.close();";
	else
		echo "javascript:window.history.back();";
}

function read_param( $nombre, $default_value, $abortar=0 )
{	
	if( isset($_GET[ $nombre ]) )
		return $_GET[ $nombre ];
	else if( isset($_POST[ $nombre ]) )
		return $_POST[ $nombre ];
	else
	{
		if( $abortar == 1 )
		{
			global $IS_DEBUG;
			
			if( isset($IS_DEBUG) )
				die( "Error en la llamada [$nombre] / Error on caller function" );
			else		
				die( "Error en la llamada / Error on caller function" );
		}
		else	
			return $default_value;
	}
}

//
// Copyright 2000 David L. Weiner <davew@webmast.com>. All Rights Reserved
// Convertir numeros Arábigos a ROMANOS
//
function to_roman($num) {

	// Function to convert an arabic number ($num) to a roman numeral. $num must be between 0 and 9,999
	if ($num < 0 || $num > 9999) { return -1; } // out of range

	$r_ones = array(1=> "I", 2=>"II", 3=>"III", 4=>"IV", 5=>"V", 6=>"VI", 7=>"VII", 8=>"VIII", 9=>"IX");
	$r_tens = array(1=> "X", 2=>"XX", 3=>"XXX", 4=>"XL", 5=>"L", 6=>"LX", 7=>"LXX", 8=>"LXXX", 9=>"XC");
	$r_hund = array(1=> "C", 2=>"CC", 3=>"CCC", 4=>"CD", 5=>"D", 6=>"DC", 7=>"DCC", 8=>"DCCC", 9=>"CM");
	$r_thou = array(1=> "M", 2=>"MM", 3=>"MMM", 4=>"MMMM", 5=>"MMMMM", 6=>"MMMMMM", 7=>"MMMMMMM", 8=>"MMMMMMMM", 9=>"MMMMMMMMM");

	$ones = $num % 10;
	$tens = ($num - $ones) % 100;
	$hundreds = ($num - $tens - $ones) % 1000;
	$thou = ($num - $hundreds - $tens - $ones) % 10000;

	$tens = $tens / 10;
	$hundreds = $hundreds / 100;
	$thou = $thou / 1000;

	$rnum = "";
	
	if ($thou) { $rnum .= $r_thou[$thou]; }
	if ($hundreds) { $rnum .= $r_hund[$hundreds]; }
	if ($tens) { $rnum .= $r_tens[$tens]; }
	if ($ones) { $rnum .= $r_ones[$ones]; }

	return $rnum;
} // function to_roman($num)

//
// permite desplegar toda una página de verificación antes de borrar algo
//
//  17jun2009
//
// buttons = 1 (permite colocar OK o CANCEL)
//
function ask_user_confirmation( $cTitleOrCaption, $singular_name, $cQueryToConfirm, $cUrlToSend, $buttons=1, $displaynavbar=1, $displaybanner=1, $displaymenu=1 )
{
	// ya debía existir el <head> y todo lo anterior
	include "../basic/head_handler.php";
	global $TITLE_CONFIRMATION_NEEDED,  $MSG_CONFIRMATION_NEEDED;
	HeadHandler( $TITLE_CONFIRMATION_NEEDED, "../" );
	
	echo "<script type='text/javascript' language='javascript'>";
	echo " function confirmar() ";
	echo " { var confirm_box = document.getElementsByName('confirmacion');";
	echo "   if( !confirm_box[0].checked ) { alert( '$MSG_CONFIRMATION_NEEDED') }  ";
	echo "	 else {";
	echo "	   location.href = '$cUrlToSend'; ";
	echo "	 }";
	echo " }";
	echo "</script>";
	
	echo "<body id='home'>";

	if( $displaynavbar == 1) display_global_nav(); 

	echo "<!-- contenedor principal -->";
	echo "<div id='contenedor'>";

	if( $displaybanner == 1) display_banner();  
	if( $displaymenu == 1)   display_menu( "../" ); 	

		echo "<div id='bloque_principal'><!-- inicia contenido -->\n";

			echo "<div id='contenido_principal'>";

			echo "<h2>$cTitleOrCaption<HR></h2><br>";

			$result = db_query( $cQueryToConfirm );

			if( $row = db_fetch_row($result) )
			{
				if( $row["CUANTOS"]==0 )
				{
					global $MSG_CONFIRMATION_ON_ZERO;
					echo " $MSG_CONFIRMATION_ON_ZERO $singular_name.<br><br>";
				}
				else if( $row["CUANTOS"]>0 )
				{
					global $MSG_CONFIRMATION_ON_MANY;
					echo sprintf( $MSG_CONFIRMATION_ON_MANY, $row["CUANTOS"],  $singular_name );
					echo "<br><br>";
				}
			} 

			global $CHK_CONFIRMATION_BOX;
			echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type=checkbox id=confirmacion name=confirmacion>&nbsp;&nbsp;$CHK_CONFIRMATION_BOX";
			echo "<br>";
			
			free_dbquery( $result );

			echo "<br>";

			echo "<div id='buttonarea'>";

			if( $buttons == 1 )
			{
				global $BTN_CONTINUE, $BTN_CANCEL;

				echo "	<input id='btnContinuar' name='btnContinuar' class='boton' type='button' value='$BTN_CONTINUE' onClick='javascript:confirmar();'>&nbsp;";
				echo "	<input id='btnCancelar' name='btnCancelar' class='boton' type='button' value='$BTN_CANCEL' onClick='javascript:window.history.back();'>";
			}

			echo "</div>&nbsp;";
			echo "<br>";
			echo "<br>";
			echo "<br> <!-- for IE -->";

			echo "</div>";

		echo "</div>";

	echo "</div>";  // contenedor

	echo "</body>";
	echo "</html>";
}


//
// 18-sep-2009
// Muestra un mensaje de advertencia sobre alguna acción
// que impide desarrollar un proceso de forma adecuada
// es una rutina macro que debe llamarse antes de cualquier otro despliegue en pantalla
//
function display_stop_message( $cTitleOrCaption, $message, $cUrlToSend, $buttons=1, $displaynavbar=1, $displaybanner=1, $displaymenu=1 )
{
	// ya debía existir el <head> y todo lo anterior
	include "../basic/head_handler.php";
	global $TITLE_NOTIFICATION_SENT;
	HeadHandler( $TITLE_NOTIFICATION_SENT, "../" );
	
	echo "<script type='text/javascript' language='javascript'>";
	echo " function relocate() ";
	echo " { ";
	echo "	   location.href = '$cUrlToSend'; ";
	echo " }";
	echo "</script>";	
	
	echo "<body id='home'>";

	if( $displaynavbar == 1) display_global_nav(); 

	echo "<!-- contenedor principal -->";
	echo "<div id='contenedor'>";

	if( $displaybanner == 1) display_banner();  
	if( $displaymenu == 1)   display_menu( "../" ); 	

		echo "<div id='bloque_principal'><!-- inicia contenido -->\n";

			echo "<div id='contenido_principal'>";

			echo "<h1>$cTitleOrCaption<HR></h1>";
		
			echo $message;

			echo "<br><br>";

			echo "<div id='buttonarea' style='left:1em;'>";

			if( $buttons == 1 )
			{
				global $BTN_CONTINUE;

				echo "	<input id='btnContinuar' name='btnContinuar' class='boton' type='button' value='$BTN_CONTINUE' onClick='javascript:relocate();'>&nbsp;";
			}
			else if( $buttons == 2 )
			{
				global $BTN_CANCEL;

				echo "	<input id='btnCancel' name='btnCancel' class='boton' type='button' value='$BTN_CANCEL' onClick='javascript:relocate();'>&nbsp;";
			}
			
			echo "</div>&nbsp;";
			echo "<br>";
			echo "<br>";
			echo "<br> <!-- for IE --> ";

			echo "</div>";
			
			display_copyright();

		echo "</div>";

	echo "</div>";  // contenedor

	echo "</body>";
	echo "</html>";
}


// se movió de verfichamedia.php
function ICON_DisplayYESNO( $value, $hideNO=false )
{
   $onoff = 0;
	
   if( $value == 'S' or $value == "Y" )
      $onoff = 1;
   if( $value == 1 )
      $onoff = 1;

	if( $onoff == 1 )
		return "<img src='../images/icons/yes.png'>";		
	else if( !$hideNO )
		return "<img src='../images/icons/no.png'>";
}

function SYNTAX_BEGIN_JavaScript()
{
	echo "\n<script type='text/javascript' language='Javascript'>\n";
}

function SYNTAX_CLOSE_JavaScript()
{
	echo "\n</script>\n";
}

function SYNTAX_JavaScript( $begin=0, $close=0, $quick_line )
{
	if( $begin == 1 ) echo "\n<script type='text/javascript' language='Javascript'>\n";
	echo $quick_line;
	if( $close == 1 ) echo "\n</script>\n";
}

function USER_SHOW_QuickLinks( $id_biblioteca, $id_usuario )
{
	global $ANLSMENU_ITEM1, $ANLSMENU_ITEM3, $ANLSMENU_ITEM7;
	global $CIRMENU_ITEM1, $CIRMENU_ITEM6 ;
	global $SRVMENU_ITEM3;
	
	echo "\n\n<!-- QUICK LINKS FOR USER -->\n";
	echo "	  <ol>";
	
	echo "		<li><a href='phps/anls_catalogacion.php'>$ANLSMENU_ITEM1</a></li>";
	echo "		<li><a href='phps/anls_consultatitulos.php'>$ANLSMENU_ITEM7</a></li>";
	echo "		<li><a href='phps/gral_elegir_item.php?the_action=existencias'>$ANLSMENU_ITEM3</a></li>";
	
	echo "		<li><a href='phps/gral_elegir_usuario.php?the_action=prestamos'>$CIRMENU_ITEM1</a></li>";
	echo "		<li><a href='phps/gral_elegir_item.php?the_action=devoluciones'>$CIRMENU_ITEM6</a></li>";
	
	echo "		<li><a href='phps/serv_usuariosgrupos.php'>$SRVMENU_ITEM3</a></li>";
	
	echo "	  </ol>";
}

function USER_HISTORY_RecentActions( $dbx, $id_biblioteca, $id_usuario, $num_actions = 8 )
{
	global $CFG;
	require_once( "config_db.inc.php" );
	
	if( $CFG->db_type == "mysql" )
	{
	}
	else if( $CFG->db_type == "interbase" )
	{
		$sql = "SELECT FIRST $num_actions DISTINCT(a.ID_ACCION), b.DESCRIPCION, b.DESCRIPCION_ENG, b.DESCRIPCION_PORT " .
			   "FROM usuarios_bitacora_eventos a " .
			   "INNER JOIN cfgacciones b ON (b.ID_ACCION=a.ID_ACCION and b.MOSTRAR_EN_RECIENTES='S') " .
			   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_USUARIO=$id_usuario " .
			   "ORDER BY FECHA DESC ";
	}

   	$dbx->Open( $sql );
	
	echo "\n\n<!-- RECENT USED ACTIONS -->\n";
	echo "	  <ul>\n";
	
	$total = 0;
	
	while( $dbx->NextRow(1) )
	{
		$total++;
		echo "	<li>" . get_translation( $dbx->row["DESCRIPCION"], $dbx->row["DESCRIPCION_ENG"], $dbx->row["DESCRIPCION_PORT"] ) . "</li>";
	} 
	
	echo "	  </ul>\n";
	
	if( $total == 0 )
	{
		global $MSG_NO_LOG_OF_RECENT_ACTIVITIES;
		echo "<br>$MSG_NO_LOG_OF_RECENT_ACTIVITIES";
	}
	
	$dbx->Close();
}

function USER_HISTORY_MostFrequentlyViewed( $dbx, $id_biblioteca, $id_usuario, $num_themes = 5 )
{
	global $CFG;
	require_once( "config_db.inc.php" );

	$temas = Array();
	
	$sub_field = '$' . "a";

	if( $CFG->db_type == "mysql" )
	{
	}
	else if( $CFG->db_type == "interbase" )
	{
		$sql = "SELECT FIRST 20 DISTINCT(a.ID_TITULO), b.VALOR " .
			   "FROM usuarios_bitacora_eventos a " .
			   "  INNER JOIN acervo_catalogacion b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_TITULO=a.ID_TITULO and b.ID_CAMPO='650' and b.CODIGO = '$sub_field') " .
			   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_USUARIO=$id_usuario and a.ID_ACCION=212 and a.ID_TITULO<>0 " .
			   "ORDER BY FECHA DESC ";
	}

   	$dbx->Open( $sql );
	
	echo "\n\n<!-- THEMES MOST FREQUENTLY VIEWED -->\n";
		
	$filter_4_titulos = "";
	
	while( $dbx->NextRow(1) )
	{
		$temas[] = Array( "theme" => $dbx->row["VALOR"], "eventos" => 1 );		
	} 
	
	echo "	  <ul>\n";
	
	for( $i=0; $i<count($temas); $i++ )
	{
		echo "	<li>" . $temas[$i]["theme"] . "</li>";
	}
	
	echo "	  </ul>\n";
	
	if( count($temas) == 0 )
	{
		global $MSG_NO_LOG_OF_FREQUENT_THEMES;
		echo "<br>$MSG_NO_LOG_OF_FREQUENT_THEMES";
	}
	
	$dbx->Close();
}

//
// 11-ago-2009:  MUESTRA contribuciones recientes
//
function USER_HISTORY_RecentContributions( $dbx, $id_biblioteca, $id_usuario, $num_tasks = 5 )
{
	global $CFG;
	
	require_once( "config_db.inc.php" );
	
	if( $CFG->db_type == "mysql" )
	{
	}
	else if( $CFG->db_type == "interbase" )
	{
		$sql = "SELECT FIRST $num_tasks DISTINCT(a.ID_TITULO) " .
			   "FROM acervo_titulos_califs a " .
			   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_USUARIO=$id_usuario  " .
			   "ORDER BY FECHA_OPINION DESC ";
	}	
	
  	$dbx->Open( $sql );
	
	$tasks = Array();
	
	echo "\n\n<!-- LATEST CONTRIBUTIONS -->\n";
		
	$filter_4_titulos = "";
	
	// Task
	// 1 = Comentario
	
	while( $dbx->NextRow(1) )
	{
		$tasks[] = Array( "titulo" => $dbx->row["ID_TITULO"], "task" => 1 );
	} 
	
	echo "	  <ul>\n";
	
	for( $i=0; $i<count($tasks); $i++ )
	{
		$item = new TItem_Basic( $id_biblioteca, $tasks[$i]["titulo"], 0, $dbx );
		
		$link = "<a href='phps/gral_vertitulo.php?id_titulo=" . $item->nIDTitulo . "'>";
		
		echo "	<li>$link" . $item->cTitle . "</a></li>";
		
		$item->destroy();
	}
	
	echo "	  </ul>\n";
	
	if( count($tasks) == 0 )
	{
		global $MSG_NO_LOG_OF_CONTRIBUTIONS;
		echo "<br>$MSG_NO_LOG_OF_CONTRIBUTIONS";
	}
	
	$dbx->Close();
	
	
}

//
// 11-ago-2009:  MUESTRA Items recientemente utilizados
//
function USER_HISTORY_RecentUsedItems( $dbx, $id_biblioteca, $id_usuario, $num_themes = 5 )
{
	global $CFG;

	require_once( "config_db.inc.php" );
	require_once( "phps/circulacion.inc.php" );

	$items= Array();
		
	echo "\n\n<!-- ITEMS RECENTLY USED (borrowed or reserved) -->\n";
	$total = 0;
	
	//
	// PRIMERO PRESTAMOS
	//
	if( $CFG->db_type == "mysql" )
	{
	}
	else if( $CFG->db_type == "interbase" )
	{
		$dbx->sql = "SELECT FIRST 10 DISTINCT(b.ID_ITEM) " .
				    "FROM prestamos_mst a " .
				    "  LEFT JOIN prestamos_det b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO) " .
				    "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_USUARIO=$id_usuario and (b.ID_ITEM is NOT NULL) " .
				    "ORDER BY FECHA_PRESTAMO DESC ";
	}
	
   	$dbx->Open();
	
	$titulos = Array();
	
	while( $dbx->NextRow(1) )
	{
		$total++;
		
		$item = new TItem_Basic( $id_biblioteca, $dbx->row["ID_ITEM"], 1, $dbx );
		
		if( !in_array( $item->nIDTitulo, $titulos ) ) 
		{		
			$titulos[] = $item->nIDTitulo;
			
			if( strlen($item->cTitle_ShortVersion) > 75 ) 
			{
				$item->cTitle_ShortVersion = substr( $item->cTitle_ShortVersion, 0, 75 ) . "...";
			}
			
			$items[] = Array( "item" => "<a href='phps/gral_vertitulo.php?id_titulo=" . $item->nIDTitulo . "'><img src='$item->cIcon'>&nbsp;[" . $item->Material_ShortCode() . "] " . $item->cTitle_ShortVersion . ".</a>",
							  "id_titulo" => $item->nIDTitulo );

		}
		
		$item->destroy();
		unset( $item );
		
	} 
	
	$dbx->Close();
	
	//
	// PASO 2 - Reservaciones
	//
	if( $CFG->db_type == "mysql" )
	{
	}
	else if( $CFG->db_type == "interbase" )
	{
		$dbx->sql = "SELECT FIRST 10 DISTINCT(b.ID_TITULO) " .
				   "FROM reservaciones_mst a " .
				   "  LEFT JOIN reservaciones_det b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_RESERVACION=a.ID_RESERVACION) " .
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_USUARIO=$id_usuario and b.STATUS_RESERVACION<>'F'" .
				   "ORDER BY FECHA_RESERVO DESC ";
	}
	
	//echo $sql;

   	$dbx->Open();
	
	while( $dbx->NextRow(1) )
	{
		$total++;
		
		$item = new TItem_Basic( $id_biblioteca, $dbx->row["ID_TITULO"], 0, $dbx );
		
		if( !in_array( $dbx->row["ID_TITULO"], $titulos ) ) 
		{		
			$titulos[] = $dbx->row["ID_TITULO"];

			if( strlen($item->cTitle_ShortVersion) > 75 ) 
			{
				$item->cTitle_ShortVersion = substr( $item->cTitle_ShortVersion, 0, 75 ) . "...";
			}
			
			$items[] = Array( "item" => "<a href='phps/gral_vertitulo.php?id_titulo=" . $item->nIDTitulo . "'><img src='$item->cIcon'>&nbsp;[" . $item->Material_ShortCode() . "] " . $item->cTitle_ShortVersion . ".</a>",
							  "id_titulo" => $item->nIDTitulo );
			
			$item->destroy();
		}
		
		unset( $item );
	} 
	
	$dbx->Close();
	
	// display the list
	echo "	  <ul>\n";
	
	//print_r( $items );
	
	for( $i=0; $i<count($items); $i++ )
	{
		echo "	<li>" . $items[$i]["item"] . "&nbsp;";
		
		$comment_needed = true;
		$id_titulo = $items[$i]["id_titulo"];
		
		$dbx->Open( "SELECT COUNT(*) AS CUANTOS FROM acervo_titulos_califs WHERE ID_BIBLIOTECA=$id_biblioteca and ID_USUARIO=$id_usuario and ID_TITULO=$id_titulo;" );
		
		if( $dbx->NextRow() )
		{
			if( $dbx->row["CUANTOS"] > 0 )
				$comment_needed = false;
		}
		
		$dbx->Close();
		
		if( $comment_needed )
		{
			global $HINT_PLEASE_LEAVE_COMMENT;
			echo "<a title='$HINT_PLEASE_LEAVE_COMMENT' href='phps/gral_vertitulo.php?id_titulo=$id_titulo#comentarios'><img src='images/icons/comments.gif'></a>";
		}
		echo "</li>";
	}
	
	echo "	  </ul>\n";
	
	if( $total == 0 )
	{
		global $MSG_NO_LOG_OF_RECENT_ISSUED_ITEMS;
		echo "<br>$MSG_NO_LOG_OF_RECENT_ISSUED_ITEMS";
	}

}

//
// 25-ago-2009:  MUESTRA sanciones recientes
//
function USER_HISTORY_Sanctions( $id_biblioteca, $id_usuario )
{
	global $CFG;
	require_once( "config_db.inc.php" );
	require_once( "basic/currency.inc.php" );

	echo "\n\n<!-- USER'S sanctions -->\n";
	$total = 0;
	
	//
	// PRIMERO PRESTAMOS
	//
	if( $CFG->db_type == "mysql" )
	{
	}
	else if( $CFG->db_type == "interbase" )
	{
		$sql =  "SELECT a.*, b.DESCRIPCION AS DESCRIP_SANCION " .
				" FROM sanciones a " .
				"  LEFT JOIN cfgsanciones b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.TIPO_SANCION=a.TIPO_SANCION) " . 
				"WHERE (a.ID_BIBLIOTECA=$id_biblioteca and a.ID_USUARIO=$id_usuario and a.STATUS_SANCION='N') " . 
				"ORDER BY a.FECHA_SANCION";
	}
	
   	$result = db_query( $sql );	
	
	while( $row = db_fetch_row($result) )
	{
		$total++;
		
		$item = new TItem_Basic( $id_biblioteca, $row["ID_ITEM"], 1 );
		
		if( strlen($item->cTitle_ShortVersion) > 75 ) 
		{
			$item->cTitle_ShortVersion = substr( $item->cTitle_ShortVersion, 0, 75 ) . "...";
		}
		
		$descrip_datos = "";
		
		if( $row["MONTO_SANCION"] > 0 )
		{
			$descrip_datos .= " " . formato_cantidad( $row["MONTO_SANCION"] );
		}
		
		echo "&nbsp;<img src='images/icons/warning.gif'>&nbsp;" . dbdate_to_human_format($row["FECHA_SANCION"]) . "&nbsp;" . $row["DESCRIP_SANCION"] . " $descrip_datos <br>";
		//[" . $item->Material_ShortCode() . "] " . $item->cTitle_ShortVersion . ".<br>";
		
		$item->destroy();
		unset( $item );
	} 	

	free_dbquery( $result );
	
}

//
// Obtiene la descripción traducida de una categoria
// debe traer "n" argumentos con base en los idiomas soportados
//
function get_translation()
{
	$ret = func_get_arg( getsessionvar("language")-1 );
	
	if( $ret == "" ) { $ret = func_get_arg(0); }
	
	return $ret;
}

function transform_text( $full_text, $aTransformValues )
{

	for( $i=0; $i<count($aTransformValues); $i++ )
	{
		$cFields = $aTransformValues[$i][0];
		
		$full_text = str_replace( $cFields, $aTransformValues[$i][1], $full_text );
	}
	
	return $full_text;
}


//
// Funcion para obtener la descripción de la periodicidad
//
function obtener_descripcion_periodicidad( $codigo )
{
	$ret = "";

	if( $codigo == "S" ) 
		$ret = "Semanal";
	else if( $codigo == "W" ) 
		$ret = "Weekly";
	
	return $ret;
}

// 
// permite colocar un select 
// con el contenido de una categoría del tesauro
// 
function combo_from_tesauro( $combo_name, $id_red, $id_categoria, $init_value, $maxlength=0 )
{
	//global $CFG;
	require_once( "config_db.inc.php" );
	
	$sql = "SELECT a.*, b.ID_TERMINO, b.TERMINO, b.CODIGO_CORTO, b.DESCRIPCION, b.TERMINO_PADRE " . 
			"FROM tesauro_terminos_categorias a " .
			"  LEFT JOIN tesauro_terminos b ON (b.ID_RED=a.ID_RED and b.ID_TERMINO=a.ID_TERMINO) " .
			"WHERE a.ID_RED=$id_red and a.ID_CATEGORIA=$id_categoria and b.MACROTERMINO='N' " . 
			"ORDER BY b.TERMINO";	
			
	$result = db_query( $sql );	
	
	$total = 0;
	
	echo "\n<select class='select_captura' name='$combo_name' id='$combo_name'>\n";

	while( $row = db_fetch_row($result) )
	{	
		$str_selected = "";
		if( $row["ID_TERMINO"] == $init_value )
			$str_selected = "SELECTED";
			
		$str = $row["DESCRIPCION"];
		
		if( $maxlength > 0 )
		{
			if( strlen($str)>$maxlength )
				$str = substr( $str, 1, $maxlength ) . "...";
		}
		
		$str .= " (" . $row["CODIGO_CORTO"] . ")";
			
		echo "<option value='" . $row["ID_TERMINO"] . "' $str_selected>$str</option>\n";
		
		$total++;
	}

	free_dbquery( $result );
	
	echo "\n</select>\n";

}

//
// 22-jan-2010:  MUESTRA eventos vigentes
//
function LIBRARY_Display_Events(  $dbx, $id_biblioteca, $show_events=1, $show_links=0 )
{
	require_once getsessionvar("local_base_dir") . "basic/bd.class.php";

	echo "\n\n<!-- LIBRARY events -->\n";
	$total = 0;
	
	$dbx->sql = "SELECT a.ID_RECURSO, a.SUMARIO, a.INFORMACION_BREVE, a.INFORMACION_AMPLIADA, a.URL, a.FECHA_DESDE, a.FECHA_HASTA, a.HORA_DESDE, a.HORA_HASTA " .
				"FROM recursos_contenido a " .
				"  LEFT JOIN cfgubicaciones b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_UBICACION=a.ID_UBICACION) " .
				"WHERE (a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TIPORECURSO=3) " . 
				"ORDER BY a.PUBLICARSE_DESDE, a.ID_RECURSO; ";
	
	$dbx->Open();	
	
	if( $show_events = 1 and $show_links == 0 )
		$size_col = "100%";
	else
		$size_col = "50%";
	
	echo "<div class='lista_elementos_indexada' style='width:$size_col'>\n";
	
	while( $dbx->NextRow() )
	{
		if( $total == 0 )
		{
			echo "<h2><strong>Eventos</strong></h2>\n";
			echo "<ol>";
		}

		$link_on = "<a href='phps/gral_verevento.php?id_lib=$id_biblioteca&id=" . $dbx->row["ID_RECURSO"] . "'>";
		$link_off = "</a>";

		$tempstr = "";
		$temp_time = "";

		$desde  = get_str_date( $dbx->row["FECHA_DESDE"] );
		$hasta  = get_str_date( $dbx->row["FECHA_HASTA"] );
		
		if( $desde == $hasta )
			$tempstr = $desde;
		else 
		{
			$tempstr = "Del día $desde al $hasta";
		}
		
		$hora_desde = get_str_onlytime( $dbx->row["HORA_DESDE"], 0 );
		$hora_hasta = get_str_onlytime( $dbx->row["HORA_HASTA"], 0 );
		
		if( $hora_desde == $hora_hasta )
			$temp_time = $hora_desde;
		else
		{
			$temp_time = "de las $hora_desde al $hora_hasta";
		}

		echo "<li>$link_on<strong>" . $dbx->row["SUMARIO"] . "</strong>$link_off.<br>$tempstr $temp_time.</li>\n";
	
		$total++;

	} 	
	
	if( $total > 0 )
		echo "</ol>";
	
	$dbx->Close();
	
	echo "</div>\n";
	
	// verificar ligas
	if( $show_links == 1 )
	{
		$dbx->sql = "SELECT a.ID_RECURSO, a.SUMARIO, a.URL " .
					"FROM recursos_contenido a " .
					"WHERE (a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TIPORECURSO=1) " . 
					"ORDER BY a.PUBLICARSE_DESDE, a.ID_RECURSO; ";
		
		$dbx->Open();	
		
		echo "<div class='lista_elementos_indexada' style='width:$size_col'>\n";
		
		while( $dbx->NextRow(1) )
		{
			if( $dbx->numRows == 1 )
			{
				echo "<h2><strong>Ligas</strong></h2>\n";
				echo "<ol>";
			}

			$url = $dbx->row["URL"];
			
			if( substr($url,0,5) != "http:" )
				$url = "http://" . $url;
			
			$link_on = "<a href='$url' target='_new'>";
			$link_off = "</a>";

			echo "<li>$link_on<strong>" . $dbx->row["SUMARIO"] . "</strong>$link_off.</li>\n";
		
			$total++;
		} 	
		
		if( $total > 0 )
			echo "</ol>";
		
		$dbx->Close();
		
		echo "</div>\n";	
	}
	
}


//
// 21-jan-2010:  MUESTRA notas destacadas
//
function LIBRARY_Display_Notes(  $dbx, $id_biblioteca )
{
	require_once getsessionvar("local_base_dir") . "basic/bd.class.php";

	echo "\n\n<!-- LIBRARY notes -->\n";
	$total = 0;
	
	$dbx->sql =  	"SELECT a.SUMARIO, a.INFORMACION_BREVE, a.INFORMACION_AMPLIADA, a.URL " .
					" FROM recursos_contenido a " .
					"WHERE (a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TIPORECURSO=2) " . 
					"ORDER BY a.PUBLICARSE_DESDE, a.ID_RECURSO; ";
	
	$dbx->Open();	
	
	while( $dbx->NextRow(1) )
	{
		if( $total == 0 )
		{
			echo "<div class='resaltados'>\n";
			echo "<h2>Notas</h2>\n";
			echo "<ul>";
		}

		$link_on = "";
		$link_off = "";
		
		if( $dbx->row["URL"] != "" )
		{
			$link_on = "<a href='" . $dbx->row["URL"] . "'>";
			$link_off = "</a>";
		}
		
		echo "<li>$link_on<strong>" . $dbx->row["SUMARIO"] . ".</strong>$link_off";
		
		$info_breve = $dbx->GetBLOB( $dbx->row["INFORMACION_BREVE"], 1, 1 );
		
		if( $info_breve != "" )
		{
			echo "<br><span>$info_breve</span><br>";
		}
		
		echo "</li>\n";
	
		$total++;
	} 	
	
	if( $total > 0 )
	{
		echo "</ul>";
		echo "</div>\n";
	}
	
	$dbx->Close();
	
}

function DisplayChangeNotice()
{
	global $HINT_CHANGES_ALERT, $HINT_CHANGES_APPLIED_HERE;
	
	echo "<div class='caja_info'><img src='../images/some_info.gif'>&nbsp;<strong>$HINT_CHANGES_ALERT</strong>&nbsp;$HINT_CHANGES_APPLIED_HERE</div>";
}

?>
