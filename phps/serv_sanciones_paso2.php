<?php
	session_start();

	/**********
		HISTORIAL DE CAMBIOS
		
		01-Junio-2009	Se crea el archivo serv_sanciones.php
		02-Junio-2009	Se completa informacion de llenado en la forma.
						Se agrego catalogo de sanciones a combo box.
		10-Junio-2009   Se logra obtener valores seleccionados en el combo. Se crean validaciones y concatenacion de cadenas
						dependiendo el tipo de sanción.
		15 Junio-2009	Se agrega función inicializa_valores_default().
		19-Junio-2009	Se crea insert para registrar sancion.

	**********/		

	include ("../funcs.inc.php");
	include ("../basic/bd.class.php");
	include_language( "global_menus" );
	
	check_usuario_firmado(); 

	include_language( "serv_sanciones" ); // archivo de idioma
	
	$id_biblioteca =getsessionvar('id_biblioteca');
	
	// usuario seleccionado en pantalla anterior
	$id_usuario = read_param( "id_usuario", 0, 1 ); // falla si no viene
	$the_action = read_param( "the_action", "" ); // falla si no viene
	
	$usuario = getsessionvar('usuario');  // usuario firmado (operador)
	
	//
	// MOVIMIENTOS A LA BASE DE DATOS
	//
	if ( $the_action == "save" )
	{	
		// datos transaccionales	
		$fecha_reg = current_dateandtime();
		
		// fecha limite
		$fecha_limite = read_param( "fecha_limite", "", 1 ); // falla sino
		$fecha_limite = date_for_database_updates( $fecha_limite );  // convertir a tipo DB
		
		$tipo_sancion = read_param( "tipo_sancion", "", 1 ); // falla sino
		$motivo_sancion = read_param( "txt_motivo", "", 1 );  // falla sino
		
		$monto_sancion = read_param( "txt_monto", "", 1 );
					
		if(!ereg('^[0-9]{1,6}$', $monto_sancion))  //Si el monto de la sancio no es numerico toma el valor la descripcion
		{
			$descrip_sancion = $monto_sancion;
			$monto_total= 0;
		}
		else
		{
			$monto_total= $monto_sancion;
			$descrip_sancion="";
		}
		
		$db = new DB;
		//obtener consecutivo de tabla sanciones.
		$db->Open( "SELECT COUNT(*) AS CUANTOS, MAX(ID_SANCION) AS MAXIMO " . 
				   "FROM sanciones WHERE ID_BIBLIOTECA=" . getsessionvar("id_biblioteca"));
		
		$id_sancion = 0;
		
		if( $db->NextRow() )
		{
			if(  $db->Field("CUANTOS") == 0 )
				$id_sancion = 1;
			else
				$id_sancion = $db->Field("MAXIMO") + 1;
		}
		else
			die( "error on update" );
					
		$db->FreeResultset();
		
		// Agrega registro en la tabla sanciones.
		$db->sql  = "INSERT INTO sanciones (ID_BIBLIOTECA, ID_SANCION, ID_USUARIO, TIPO_SANCION, ID_PRESTAMO, ID_ITEM, FECHA_SANCION, FECHA_CUMPLIDA, FECHA_LIMITE, ";
		$db->sql .= "         MOTIVO, STATUS_SANCION, MONTO_SANCION, MONTO_TOTAL, OBSERVACIONES ) ";
		$db->sql .= " VALUES ( $id_biblioteca, $id_sancion, $id_usuario, $tipo_sancion, 0, 0, '$fecha_reg', NULL, '$fecha_limite', " ;
		$db->sql .= "        '$motivo_sancion', 'N', $monto_total, 0, '$descrip_sancion') ";
			
		$db->ExecSQL();
		
		require_once("../actions.inc.php");
		agregar_actividad_de_usuario( SERV_USERS_SANCTIONS, "" );
				
		ges_redirect( "serv_sanciones_end.php?id_usuario=$id_usuario&id_sancion=$id_sancion" );
	}
	
	// PRIMER PASO:
	//  Validar el usuario que ha sido elegido previamente 
	//
	include ( "../basic/head_handler.php" );  // Coloca un encabezado HTML <head>	
	HeadHandler( $LBL_HEADER, "../" );
	
?>

<body id="home">

<?php
  
  display_global_nav();  // barra de navegación superior
  
 ?>

<!-- contenedor principal -->
<div id="contenedor" class="contenedor">

<?php 
   
   display_banner();    // banner   
   display_menu('../'); // menu principal
      
 ?>
 
</div>
 
</body>

</html>