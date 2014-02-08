<?php
	session_start();
	
	/**********
	
	02-jun-2009	Se crea el archivo circ_bandeja.php

	**********/
 
	include ("../funcs.inc.php");
	include "../basic/bd.class.php";
	include "circulacion.inc.php";
	
	include_language( "global_menus" );	
	include_language( "circ_bandeja" );

	check_usuario_firmado(); 

	$id_biblioteca = getsessionvar('id_biblioteca');
	$usuario= getsessionvar('usuario');
	$id_usuario = getsessionvar('id_usuario');
	
	$accion = read_param("accion", 1, 1 );
	$id_titulo = read_param("id_titulo", 0, 1 );
	
	// codigo para insertar un título en la bandeja personal
	if( $accion == 1 or $accion=="add" )
	{
		$existe = 0;
		
		$db = new DB();
		
		$db->Open( "SELECT COUNT(*) AS CUANTOS " . 
				   " FROM usuarios_bandeja WHERE ID_BIBLIOTECA=$id_biblioteca and ID_USUARIO=$id_usuario and ID_TITULO=$id_titulo; " );

		if( $db->NextRow() )
		{ $existe = $db->Field("CUANTOS") > 0; }

		$db->FreeResultset();
		
		if( $existe == 0 )
		{	
			$db->ExecSQL( "INSERT INTO usuarios_bandeja ( ID_BIBLIOTECA, ID_USUARIO, ID_TITULO ) VALUES ( $id_biblioteca, $id_usuario, $id_titulo ); " );
			
			require_once("../actions.inc.php");
			agregar_actividad_de_usuario( USER_ITEM_ADDED_TO_BIN, "", 0, $id_titulo );			
			
			$error = 1;
		}
		else if ( $existe == 1 )
		{

			$error = 2;

		}
	}
	else if( $accion == 2 or $accion=="remove" )
	{
	
		$singular_name = "$LBL_SINGLE_NAME_ITEM";
		
		$query_to_confirm = "SELECT COUNT(*) AS CUANTOS " . 
				   " FROM usuarios_bandeja WHERE ID_BIBLIOTECA=$id_biblioteca and ID_USUARIO=$id_usuario and ID_TITULO=$id_titulo";
		
		ask_user_confirmation( "$MSG_WANT_TO_REMOVE_AN_ITEM", $singular_name, $query_to_confirm,
				"circ_bandeja.php?accion=3&id_titulo=$id_titulo" );	
				
		exit;
	
	}
	else if( $accion == 3 or $accion=="remove_confirmed" )
	{
		$db = new DB();

		$db->ExecSQL( "DELETE FROM usuarios_bandeja WHERE ID_BIBLIOTECA=$id_biblioteca and ID_USUARIO=$id_usuario and ID_TITULO=$id_titulo; " );
		
		require_once("../actions.inc.php");
		agregar_actividad_de_usuario( USER_ITEM_REMOVED_FROM_BIN, "", 0, $id_titulo );			
		
		$error = 1;		

		$db->destroy();
		
		ges_redirect( "../index.php" );
		
	}
	else if( $accion ==4 or $accion=="remove_reserva" )
	{
		$singular_name = "$LBL_SINGLE_NAME_RESERVA";
		$id_reserva = read_param( "id_reserva", 0, 1 );
		
		$query_to_confirm = "SELECT COUNT(*) AS CUANTOS " . 
							" FROM reservaciones_det WHERE ID_BIBLIOTECA=$id_biblioteca and ID_RESERVACION=$id_reserva and ID_TITULO=$id_titulo";
		
		ask_user_confirmation( "$MSG_WANT_TO_REMOVE_RESERVA", $singular_name, $query_to_confirm,
							    "circ_bandeja.php?accion=5&id_reserva=$id_reserva&id_titulo=$id_titulo" );	

		exit;
	}
	else if( $accion == 5 or $accion=="remove_reserva_confirmed" )
	{
		$id_reserva = read_param( "id_reserva", 0, 1 );

		$db = new DB();

		$db->ExecSQL( "DELETE FROM reservaciones_det WHERE ID_BIBLIOTECA=$id_biblioteca and ID_RESERVACION=$id_reserva and ID_TITULO=$id_titulo; " );

		require_once("../actions.inc.php");
		agregar_actividad_de_usuario( USER_ITEM_REMOVED_RESERVA, "", 0, $id_titulo );			

		$error = 1;

		$db->destroy();

		ges_redirect( "../index.php" );
	}	
	
	// Draw an html head
	include ("../basic/head_handler.php");
	HeadHandler( "Bandeja Personal", "../" );	
	
?>

<script type='text/javascript' src='../calend/calend.js'></script>

<SCRIPT language='JavaScript'>

	function BackToHome()
	{
		js_ChangeLocation( "../index.php" );
	}
	
</SCRIPT>

<STYLE>

	#buttonarea { border: 1px solid red;  } 
	
	#nombre_usuario { 
		display: inline; 
		position: absolute;
		left: 32em; 
		width: 25em; 
		border: 1px dotted green; 
		/*background: #FFF; */
	}
	
	#caja_datos {
		width: 130%; 
		height: 100%;
	}
	
	#contenedor {
	background: #FFF;
	}
	

</STYLE>

  <LINK href="../css/screen.css" type="text/css" rel="stylesheet">

<body id="home">
	
	<?php
		display_global_nav();  // barra de navegación superior
	?>

	
	<div id="contenedor" class="contenedor"> 
		<?php 
			display_banner();  // banner
			display_menu('../'); // menu principal			
		?>
		
		<div id="bloque_principal"> 
			<div id="contenido_principal"> <br>
				<div class="caja_datos" id="caja_datos"> 
					<H2><?php printf( $LBL_BIN_HEADER, getsessionvar("nombreusuario") ); ?> </H2>
					<HR>
					
					<?php
						
						if( $error == 1 )
						{
							$item = new TItem_Basic( getsessionvar("id_biblioteca"), $id_titulo );

							echo "<div class=caja_info>";
							echo " <strong>$LBL_BIN_MSG_OK1</strong><br>";
							echo " <strong><img src='../$item->cIcon'>&nbsp;$item->cTitle</strong>";
							echo "</div>";
							
							$records = 0;
							
							$db->Open( "SELECT COUNT(*) AS CUANTOS " . 
									   " FROM usuarios_bandeja WHERE ID_USUARIO=$id_usuario" );

							if( $db->NextRow() )
							{ $records = $db->Field("CUANTOS"); }

							$db->FreeResultset();						

							echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;";
							printf( $LBL_BIN_INFO_LBL1, $records );
							echo "<br>";
						}
						else if( $error == 2 )
						{
							$item = new TItem_Basic( getsessionvar("id_biblioteca"), $id_titulo );
							
							echo "<div class='caja_errores'>";
							echo " <strong>$LBL_BIN_MSG_ERR1</strong><br><br>";
							echo " <strong><img src='../$item->cIcon'>&nbsp;$item->cTitle</strong>";
							echo "</div>";						
						}

					?>					

					<br>

					<input type="submit" class=boton value="<?php echo $BTN_GOBACK; ?>" name="btnBorrar" id="btnBorrar" onClick="javascript:BackToHome();">
					<br>

					<br>
				</div><!-- caja_datos -->
			</div><!-- Contenido principal -->
			<?php  display_copyright();	?>    
		</div><!-- bloque principal -->
	</div>  <!-- contenedor principal -->
</body>
</html>
