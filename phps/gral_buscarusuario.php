<?php
	session_start();

	/**********

		03-abril-2009	Se crea el archivo "gral_buscarusuario.php"
		06-junio-2009   Se agregó parámetro para determinar el tipo de valor que devolverá
	 
	 **/

	include ("../funcs.inc.php");
	include ("../basic/bd.class.php");

	include_language( "global_menus" );
	
	check_usuario_firmado(); 
	
	include_language( "gral_buscar_usuario" );

	// Draw an html head
	include ("../basic/head_handler.php");
	HeadHandler( "$LBL_HEADER", "../" );

	$lista_usuarios = "";
	
	$return_type = read_param( "return_type", "ID_USUARIO", 1 );
	$txt_buscar = read_param( "txt_buscar", "", 0 );
	
    if( isset($_GET["txt_buscar"]) )
	{
        $txt_buscar = $_GET["txt_buscar"];
		
		if( $txt_buscar != "" )
		{
			// Consultar productos a modificar
			$db = new DB();

			// conversiones
			$txt_search_lcase = strtolower( $txt_buscar );  // todo en minusculas
			$txt_search_fcase = strtoupper( substr($txt_search_lcase,0,1) ) . substr($txt_search_lcase, 1, 256 );  // primera letra mayúscula
			$txt_search_ucase = strtoupper( $txt_buscar );  // todo en mayúsculas			

			$db->Open( "SELECT a.ID_USUARIO, a.USERNAME, a.PATERNO, a.MATERNO, a.NOMBRE, a.ID_GRUPO, a.E_MAIL, b.NOMBRE_GRUPO, b.USUARIOS_ADMINISTRATIVOS " . 
					 "FROM cfgusuarios a " . 
					 " LEFT JOIN cfgusuarios_grupos b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_GRUPO=a.ID_GRUPO) " .	
					 "WHERE a.ID_BIBLIOTECA=" . getsessionvar("id_biblioteca") . " and  " . 
					 "  ((a.PATERNO LIKE '%$txt_search_lcase%' or a.PATERNO LIKE '%$txt_search_fcase%' or a.PATERNO LIKE '%$txt_search_ucase%') or " .
					 "   (a.MATERNO LIKE '%$txt_search_lcase%' or a.MATERNO LIKE '%$txt_search_fcase%' or a.MATERNO LIKE '%$txt_search_ucase%') or " . 
					 "   (a.NOMBRE LIKE '%$txt_search_lcase%' or a.NOMBRE LIKE '%$txt_search_fcase%' or a.NOMBRE LIKE '%$txt_search_ucase%') )" );
					 
			$lista_usuarios = "";
			
			while( $db->NextRow() ) 
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
				
				$val = "$id_usuario;$username;";
				
				$lista_usuarios .= "<input type='radio' class='radio' id='sel_usuario' name='sel_usuario' onClick='javascript:setID(\"$val\");'>&nbsp;&nbsp;$username&nbsp;&nbsp;<strong>$nombre</strong>&nbsp;$nombre_grupo<br>";
			}
			
			$lista_usuarios .= "<br>";
			
			$lista_usuarios .= "<input type='button' class='boton' value='$BTN_CONTINUE' onClick='javascript:seleccionar();'>";
			
			$db->FreeResultSet();
		}
	}

?>

	
<script type="text/javascript">
	
		function buscar() 
		{
		
			if( document.busqueda.txt_buscar.value == "" )
			{
				alert( "Debe especificar un valor para búsquedas." );
				document.busqueda.txt_buscar.focus();
			}		
			else
			{
			    document.busqueda.submit();
			}
		
		}

		function setID( val )
		{
			document.busqueda.usuario.value = val;
		}
		
		function seleccionar() 
		{
				
				if( document.busqueda.usuario.value == "" )
				{
					alert( "<?php echo $VALIDA_MSG_NO_USER_SELECTED;?>" );
					document.busqueda.usuario.focus();
				}		
				else
				{
					var words = new String( document.busqueda.usuario.value );
					var valores = words.split(";");
					
					var control_destino = window.opener.document.getElementsByName( "txt_id_usuario" );
					
					if( control_destino.length > 0 )
					{
					
						<?php 
					   if( $return_type == "ID_USUARIO" ) 
					   {
							echo " control_destino[0].value = valores[0];";
							
					   }
					   else if( $return_type == "USERNAME" ) 
					   {
							echo " control_destino[0].value = valores[1];";
							echo " window.opener.Continuar( valores[0] ); ";
					   }
					   ?>
					}
					
					window.close();
				}
		}
		
		function init()
		{
			prepareInputsForHints();
			
			document.busqueda.txt_buscar.focus();
		}
		
</script>

<style type="text/css">

#contenido_principal 
{
	width: 900px;
	float: none;
}

</style>

<body id="home" onLoad="javascript:init();">
<br>

<!-- contenedor principal -->
<div id="contenedor">

 <div id="bloque_principal">
	<div id="contenido_principal">
	
	<div id="info_general" class="caja_datos">
	
	<H2><?php echo $LBL_SUBTITLE_V1;?></H2><HR>
	<br>

		<form  class="forma_captura" action="gral_buscarusuario.php" method="get" enctype="multipart/form-data" name="busqueda" id="busqueda" target="_self" >
			
			<input class="hidden" type="hidden" value="" name="usuario" id="usuario">
			<input class="hidden" type="hidden" value="<?php echo $return_type;?>" name="return_type" id="return_type">
			
			<dt>
				<label for="txt_buscar"><?php echo $LBL_CAPTION_V1;?></label>
			</dt>
			<dd>
				<input class="campo_captura" type="text" name="txt_buscar" id="txt_buscar" value="<?php echo $txt_buscar;?>" size='50' style='display:inline'>
				<input class="boton" type="button" value="Buscar" name="btnBuscar" id="btnBuscar" onClick="javascript:buscar();" style='display:inline'><br>
				
				<span class="sp_hint"><?php echo $HINT_TYPE_USERNAME;?><span class="hint-pointer">&nbsp;</span></span>
				
			</dd>
			
			<br>
			
		</form>
	
	</div>
	
	<?php
		
		if( $lista_usuarios != "" )
		{					
			echo "<br>";
			echo "<div id='resultados_usuarios' name='resultados_usuarios' style='position: relative; left:14em; width: 650px;'>";
			echo "<strong>Elija a un usuario</strong><br><br>";
			echo $lista_usuarios;
			echo "</div>";
			
		}
		
	 ?>
		
	</div> <!-- contenido pricipal -->
	
	<br style='clear:both'>

<?php  display_copyright(); ?>


 </div><!--bloque principal-->

</div><!--bloque contenedor-->
       
</body>
</html>