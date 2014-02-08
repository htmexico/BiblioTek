<?php
	session_start();
	
	/*******
	 Historial de Cambios
		  
	 29 mar 2009: Se agrega registro de acceso en el log de actividades
	 30 mar 2009: Se obtiene el ID_RED para asignar a una var. de sesión
	 18 nov 2009: Se coloca validacion y variables para tipo de servicio
	 */	
	
    include("../funcs.inc.php");	
	include("../actions.inc.php");

	$id_lib_param = "";
	$where_lib_param = "";

	if( isset($_POST["id_lib"]) )
	{
		$id_lib = $_POST["id_lib"];
		$id_lib_param = "id_lib=" . $id_lib . "&init=1";
		$where_lib_param = "a.ID_BIBLIOTECA=" . $_POST["id_lib"] . " and ";
	}

	$nombreusuario = $_POST["nomusr"];
	$password      = $_POST["val_int"];
	
	if( isset($deshabilitar_acceso) )
	{
		/* ACCESO DESHABILIADO */
		if( isset($_POST["paginavalidacion"]) )
			ges_redirect( "Location:" . $HTTP_SERVER_VARS["HTTP_REFERER"] . "?msg=aceptado&deshabilitado=1&lastuser=$nombreusuario" ); 
		else
			ges_redirect( "Location:../biblio.php?page=index" );
		
		die( "" );
	}

	$query = "SELECT a.ID_BIBLIOTECA, a.ID_USUARIO, a.PATERNO, a.MATERNO, a.NOMBRE, a.ULTIMO_INGRESO, a.TEMA_PERSONAL, a.STATUS, a.ADMINISTRADOR, b.ID_RED, b.TEMA, b.ARCHIVO_BANNER, b.IDIOMA, b.PAIS, " . 
			 "  b.USAR_POPUPS, b.CUENTA_ACTIVA, c.USUARIOS_ADMINISTRATIVOS, d.INCLUYE_BASICO, d.INCLUYE_AVANZADO, d.INCLUYE_ESPACIO_LECTORES, d.SOLO_PERSONAL " .
			  "FROM cfgusuarios a" .
			  " LEFT JOIN cfgbiblioteca b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA) " .
			  "   LEFT JOIN cfgusuarios_grupos c ON (c.ID_BIBLIOTECA=a.ID_BIBLIOTECA and c.ID_GRUPO=a.ID_GRUPO) " . 
			  "     LEFT JOIN cfgservicios d ON (d.ID_TIPOSERVICIO=b.ID_TIPOSERVICIO) " .
			  "WHERE $where_lib_param a.USERNAME='" . $nombreusuario . "' and PASSWRD='" . $password . "' ";
			  
	$resultqry = db_query( $query );
	 
	if ( $row = db_fetch_row( $resultqry ) ) 
	{  
	    if( $row["STATUS"] == "A" and $row["CUENTA_ACTIVA"]=="S" )
		{
			// actualizar dato de ultimo acceso
			$fecha_y_hora = current_dbtime(1);

			$query = "UPDATE cfgusuarios SET ULTIMO_INGRESO='$fecha_y_hora' " .
					 "WHERE ID_BIBLIOTECA=" . $row["ID_BIBLIOTECA"] . " and ID_USUARIO='" . $row["ID_USUARIO"] . "' ";
			db_query( $query );

			setsessionvar( "id_biblioteca", $row["ID_BIBLIOTECA"] );
			setsessionvar( "id_red", $row["ID_RED"] );
			setsessionvar( "id_usuario", $row["ID_USUARIO"] );
			setsessionvar( "usuario", $nombreusuario );
			setsessionvar( "nombreusuario", $row["PATERNO"] . " " . $row["MATERNO"] . " " . $row["NOMBRE"]);
			setsessionvar( "usuarioaccesoanterior", $row["ULTIMO_INGRESO"] );
			setsessionvar( "biblio_firmado", "SI" );
			setsessionvar( "skin", $row["TEMA"] );
			
			//setsessionvar( "file_banner", $row["ARCHIVO_BANNER"] );
			setsessionvar( "language_pref", $row["IDIOMA"] );
			
			setsessionvar( "empleado", $row["USUARIOS_ADMINISTRATIVOS"] );
			setsessionvar( "personal_skin", $row["TEMA_PERSONAL"] );
			
			if( $row["ADMINISTRADOR"] == "S" )
				setsessionvar( "isadmin", 1 );
			else
				setsessionvar( "isadmin", 0 );
			
			setsessionvar( "pais", $row["PAIS"] );
			
			setsessionvar( "usar_popups_transactions", $row["USAR_POPUPS"] );
			
			setsessionvar( "__personal_service", $row["SOLO_PERSONAL"] );
			setsessionvar( "__basic_service", $row["INCLUYE_BASICO"] );
			setsessionvar( "__advanced_service", $row["INCLUYE_AVANZADO"] );
			setsessionvar( "__space_for_storage", $row["INCLUYE_ESPACIO_LECTORES"] );
			
			$expire = mktime(0,0,0,1,1,2010); // un mes		
			setcookie( "usuario", $nombreusuario, $expire, "/", "", 0 );

			agregar_actividad_de_usuario( CFG_USER_LOGGED, "" );
						
			ges_redirect( "../index.php") ;
		}
		else if( $row["CUENTA_ACTIVA"] != "S" )
		{			
			ges_redirect( "../main.php?$id_lib_param&error=4&lastuser=$nombreusuario" ); // 03-may-2011
		}		
		else
		{			
			ges_redirect( "../main.php?$id_lib_param&error=3&lastuser=$nombreusuario" );
		}
	}
	else 
	{ 
		unset( $_SESSION["biblio_firmado"] );
		
		ges_redirect( "../main.php?$id_lib_param&error=2&lastuser=$nombreusuario" );
	}
	
   	free_dbquery( $resultqry ); 


  function fechahoraactual()
  {
	global $CFG;
	require_once( "../config_db.inc.php" );
  
    $anio = date( "Y", mktime());
    $mes  = date( "m", mktime());
	$dia  = date( "d", mktime());
	
	$hora = date( "H", mktime());
	$min  = date( "i", mktime());
	$segs = date( "s", mktime());
	
	if( $CFG->db_type == "interbase" )
	{
	  $cStr = dateasinterbase( $anio, $mes, $dia );
	  
	  $cStr .= " " . $hora . ":" . $min . ":" . $segs;
	  
	  return $cStr; 
	}
	else 
	{
      return $anio . $mes . $dia . $hora . $min . $segs; }
  }


?> 
