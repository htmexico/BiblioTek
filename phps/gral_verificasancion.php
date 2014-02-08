<?php
	session_start();

	/**********

		23-Junio-2009	Se crea el archivo "gral_verificasancion.php"
		24-junio-2009   Se crea funcion Aceptar
						Se agrega archivo de lenguaje
	 **/

	include ("../funcs.inc.php");
	include ("../basic/bd.class.php");

	check_usuario_firmado(); 

	// Draw an html head
	include ("../basic/head_handler.php");
	include_language( "gral_verificasancion" ); // archivo de idioma
	HeadHandler( $LBL_HEADER, "../" );

		
	$return_type = read_param( "return_type", "MONTO", 1 );
	
    if( isset($_GET["numero"]) )
        $nosancion = $_GET["numero"];
	else
		$nosancion = "";
		
	if( isset($_GET["sancion"]) )
        $idsancion = $_GET["sancion"];
	else
		$idsancion = "";
		
	if( isset($_GET["id_interno"]) )
        $idinterno = $_GET["id_interno"];
	else
		$idinterno = "";
	
	
?>

	
<script type="text/javascript">
	
		function Aceptar() 
		{
			var words = new String( document.verifica.txt_sancion.value );
			var words2 = new String( document.verifica.txt_id_interno.value );
			var valores = words.split(";");
			var valores2 = words2.split(";");
			var temp;
			
			temp = "txt_sancion_org_"+document.verifica.txt_id_interno.value;
			
			var monto_final = window.opener.document.getElementsByName( temp );
			var monto_final2 = window.opener.document.getElementsByName( "no_sancion" );
			
					if( monto_final.length > 0 )
					{					
						<?php 
					  
							echo " monto_final[0].value = valores[0];";
							echo " monto_final2[0].value = valores2[0];";

					  
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

	<H2><?php echo $LBL_HEADER; ?></H2><HR>
	<br>

		<form action="gral_verificasancion.php" method="get" enctype="multipart/form-data" name="verifica" target="_self" id="verifica"  class="forma_captura">
			
			<!--<input class="hidden" type="hidden" value="" name="usuario" id="usuario">
			<input class="hidden" type="hidden" value="<?php echo $return_type;?>" name="return_type" id="return_type"> -->
			
			<label for="txt_sancion"><?php echo $LBL_MONTO; ?></label>
			<input class=campo_captura type="text" name="txt_sancion" id="txt_sancion" value="<?php echo $nosancion;?>" size=50>
			<input class="boton" type="button" value="<?php echo $BTN_OK;?>" name="btnAceptar" id="btnAceptar" onClick="javascript:Aceptar();"><br>
			
			<input class=campo_captura type="hidden" name="txt_id_sancion" id="txt_id_sancion" value="<?php echo $idsancion;?>" size=50>
			<input class=campo_captura type="hidden" name="txt_id_interno" id="txt_id_interno" value="<?php echo $idinterno;?>" size=50>
			
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