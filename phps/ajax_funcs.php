<?php 
   /***
     Historial de cambios:
     
     - 06-jun-2011 :  Se implementan funciones para agilizar la operación.
   */
	session_start();
		
	include ("../funcs.inc.php");
	include ("../basic/bd.class.php");

	
	if( isset($IS_DEBUG) )	
		ini_set("display_errors","on" ); 
	else
		ini_set("display_errors","off" ); 
		
		ini_set("display_errors","on" ); 

	include_language( "global_menus" );

	$info  = read_param( "info", "", 1 );
	
	$id_biblioteca = getsessionvar( "id_biblioteca" );
	
	if( $info == "search_users" )
	{
		$buscar = read_param( "search", "", 1 );
		
		if( $buscar )
		{
			$db = new DB();

			// conversiones
			$txt_search_lcase = strtolower( $buscar );  // todo en minusculas
			$txt_search_fcase = strtoupper( substr($txt_search_lcase,0,1) ) . substr($txt_search_lcase, 1, 256 );  // primera letra mayúscula
			$txt_search_ucase = strtoupper( $buscar );  // todo en mayúsculas			

			$db->sql = "SELECT a.ID_USUARIO, a.USERNAME, a.PATERNO, a.MATERNO, a.NOMBRE, a.ID_GRUPO, a.E_MAIL, b.NOMBRE_GRUPO, b.USUARIOS_ADMINISTRATIVOS " . 
					 "FROM cfgusuarios a " . 
					 " LEFT JOIN cfgusuarios_grupos b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_GRUPO=a.ID_GRUPO) " .	
					 "WHERE a.ID_BIBLIOTECA=" . getsessionvar("id_biblioteca") . " and  " . 
					 "  ((a.PATERNO LIKE '%$txt_search_lcase%' or a.PATERNO LIKE '%$txt_search_fcase%' or a.PATERNO LIKE '%$txt_search_ucase%') or " .
					 "   (a.MATERNO LIKE '%$txt_search_lcase%' or a.MATERNO LIKE '%$txt_search_fcase%' or a.MATERNO LIKE '%$txt_search_ucase%') or " . 
					 "   (a.NOMBRE LIKE '%$txt_search_lcase%' or a.NOMBRE LIKE '%$txt_search_fcase%' or a.NOMBRE LIKE '%$txt_search_ucase%') or " . 
					 "   (a.USERNAME LIKE '%$txt_search_lcase%' or a.USERNAME LIKE '%$txt_search_fcase%' or a.USERNAME LIKE '%$txt_search_ucase%') )";
			
			$db->sql .= "ORDER BY a.USERNAME";
			
			$db->Open();

			$lista_usuarios = "";
	
			while ( $db->NextRow() )
			{
				$id_usuario = $db->row["ID_USUARIO"];
				$username = $db->row["USERNAME"];
				$nombre   	= $db->row["PATERNO"];

				if( $db->row["MATERNO"] != "" )
					$nombre .= " " . $db->row["MATERNO"];
					
				$nombre .= ", " . $db->row["NOMBRE"];
				$email		= $db->row["E_MAIL"];
				
				$nombre_grupo = $db->row["NOMBRE_GRUPO"];
				$empleado	= $db->row["USUARIOS_ADMINISTRATIVOS"];
				
				$lista_usuarios .= "<input type='radio' class='radio' id='sel_usuario' name='sel_usuario' onClick='javascript:setID($id_usuario, \"$username\");'>&nbsp;<img src='../images/icons/user.gif'>&nbsp;$username&nbsp;&nbsp;<strong>$nombre</strong>&nbsp;$nombre_grupo<br>";
			}
	
			if ( $db->numRows == 0 )
				echo "<div class='caja_errores'>$MSG_NO_RECORDS_FOUND</div>";
			else
			{
				echo $lista_usuarios;

				echo "<br style='clear:both'><div style='float:left;'><input class='boton' type='button' align='center' onClick='javascript:Continuar()' value='$BTN_CONTINUE'></div>";
			}

			$db->Close();
		}		
	}
	else if( $info == "display_library_consult" )
	{
		include_language( "anls_consultatitulos" );
		
		require( "opac.php" );
		
		$id_consulta = read_param( "id_consulta", 0, 0 );
		
		$db = new DB();
		
		if( $id_consulta == 0 )
			$id_consulta = obtener_consulta_default_admva( $db, $id_biblioteca );
		
		if( $id_consulta <> 0 )
		{
			muestra_consulta( $db, $id_biblioteca, $id_consulta, "", "", "gral_buscartitulo.php", "" ); 	
			echo "<div id='embeded_search' name='embeded_search' style='text-align:left; max-height:300px; overflow: auto;'>&nbsp;</div>";
		}
		else		
		{
			echo "<br>";
			echo "$NO_ADMIN_SEARCHES_FOUND";			
		}
		
		
	}
	else if( $info == "library_search" )
	{
		$id_consulta = read_param( "id_consulta", 0, 0 );
		
		$rad_searchBy = read_param( "type", "" );
		$txt_search = read_param( "search", "" );
		
		$ver_copias=read_param( "ver_copias", 0 );
		$elegir_prestados=read_param( "elegir_prestados", 0 );		

		$func = "";

		if( $ver_copias==0 )
		{
			// SOLO se puede seleccionar un TITULO
			
			// ? ID Titulo
			// @ Descripción
			// % icono
			$func = "javascript:setIDTITULO(?);";
		}				
		else if( $ver_copias == 1 )
		{
			// Se permite seleccionar un ejemplar/copia
			
			// ? ID Titulo
			// # ID Material (Copia/Ejemplar)
			// & ID del ITEM
			// @ Descripción
			$func = "javascript:setIDITEM(?,#,&);";
		}
		
		require( "opac.php" );
		
		$db = new DB();
		
		inserta_resultados_consulta( $db, $id_biblioteca, 0, $rad_searchBy, $txt_search, 0, $func, $ver_copias, $elegir_prestados );
	}
	else
		echo "Sin info. por procesar";

		
?>