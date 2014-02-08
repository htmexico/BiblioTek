<?php
	session_start();

	/**********

		03-abril-2009	Se crea el archivo "gral_buscarusuario.php"
		06-junio-2009   Se agregó parámetro para determinar el tipo de valor que devolverá
	 
	 **/

	include ("../funcs.inc.php");
	include ("../basic/bd.class.php");
	include ("circulacion.inc.php" );
	
	check_usuario_firmado(); 

	// Draw an html head
	include ("../basic/head_handler.php");
	HeadHandler( "Elegir un material de la Bandeja", "../" );

	$lista_usuarios = "";
	
	
	$return_type = read_param( "return_type", "ID_USUARIO", 1 );
	
	$id_user = read_param( "id_user", "ID_USER", 1 );
	
	$txt_buscar = read_param( "txt_buscar", "", 0 );
	

			// Consultar productos a modificar
			$db = new DB();


			$db->Open( "SELECT ID_BIBLIOTECA, ID_USUARIO, ID_TITULO " . 
					 "FROM USUARIOS_BANDEJA " . 
					 "WHERE ID_BIBLIOTECA=" . getsessionvar("id_biblioteca") . " and ID_USUARIO=".$id_user);
			
			$lista_usuarios = "<TABLE>";
			
			while( $db->NextRow() ) 
			{
				$id_titulo = new TItem_Basic( getsessionvar("id_biblioteca"), $db->row["ID_TITULO"], 1 );
				//$id_titulo = $db->row["ID_TITULO"];

				$lista_usuarios .= "<TR><TD><input type='radio' class='radio' id='sel_usuario' name='sel_usuario' onClick='javascript:setID(\"$id_titulo->nIDTitulo\")'></TD><TD class='column cuadricula' width=200px>&nbsp;&nbsp;$id_titulo->nIDTitulo&tipoimagen=PORTADA</TD></TR>\n";
			}
			
			$lista_usuarios .= "<TR><TD></TD><TD><br>";
			$lista_usuarios .= '<input class="boton" type="button" value="Continuar" name="btnBuscar" id="btnBuscar" onClick="javascript:seleccionar();">';
			$lista_usuarios .= "</TD></TR>";
			$lista_usuarios .= "</TABLE>";
			
			$db->FreeResultSet();

?>

	
<script type="text/javascript">
	
		function setID( val )
		{
			document.busqueda.usuario.value = val;
		}
		
		function seleccionar() 
		{
					var words = new String( document.busqueda.usuario.value );
					var valores = words.split(";");
					
					var control_destino = window.opener.document.getElementsByName( "txt_id_title" );
					
					if( control_destino.length > 0 )
					{
						<?php 
							echo " control_destino[0].value = valores[0];";
					   ?>
					}
					window.opener.agregar_form.submit(); 
					window.close();
		}
		
</script>

<body id="home">
<br>
<div id="contenedor"> 

<div id="bloque_principal">

	<div id="contenido_principal" style='width: 90%;'>

	<H2>Usuario: <?php echo $return_type;?></H2><HR>
	<br>

		<form action="gral_buscarusuario.php" method="get" enctype="multipart/form-data" name="busqueda" target="_self" id="busqueda"  class="forma_captura">
			
			<input class="hidden" type="hidden" value="" name="usuario" id="usuario">
			<input class="hidden" type="hidden" value="" name="return_type" id="return_type">
			<?php
				
				if( $lista_usuarios != "" )
				{					
					echo "<label>Materiales Disponibles</label>";
					echo $lista_usuarios;
						
				}
				
			 ?>
				
		</form>
		
		<br>

	</div>
				
	<br>

</div>

<?php  display_copyright(); ?>

</div>
<!-- end div contenedor -->

</body>

</html>