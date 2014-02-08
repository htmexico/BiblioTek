<?php
	session_start();
	
	/*******
	  Historial de Cambios

	  24 sep 2009:  Se crea el archivo anls_series_recep.php
     */
		
	include "../funcs.inc.php";
	include ("../basic/bd.class.php");
	include "../privilegios.inc.php";
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "anls_series_recep" );
	include_language( "anls_suscriptions" );
	
	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";	
	
	$id_biblioteca = getsessionvar("id_biblioteca");
	$validar = read_param( "validar", 0 );
	$id_titulo = read_param( "id_titulo", "" );	
	
	$validando = false;
	$error_validacion = 0;
	
	if( $validar == 1 )
	{
		// verificar
		include "circulacion.inc.php";
		
		$validando = true;
		
		$item = new TItem_Basic( $id_biblioteca, $id_titulo, 0 );
		
		if( $item->NOT_FOUND )
		{
			$error_validacion = 1;
		}
		else
		{
			 if( $item->nIDSerie == 0 )
				$error_validacion = 2;
		}
		
		$item->destroy();
		
		if( $error_validacion == 0 )
		{
			// item correcto
			ges_redirect( "anls_series_recep_paso2.php?id_titulo=$id_titulo" );
		}		
	}

	HeadHandler( $LBL_TITLE, "../" );
	
	verificar_privilegio( PRIV_SERIES_RECEPTION, 1 );	
	
?>

<SCRIPT type="text/javascript" language="JavaScript">

	var validado = false;

	function selectTitle()
	{
		var nwidth = screen.width;
		var nheight = screen.height; 
		window.open( "gral_buscartitulo.php", "buscar_titulos", "WIDTH=" + (nwidth-200) + ",HEIGHT=" + (nheight-330) + ",TOP=50,LEFT=80,resizable=yes,scrollbars=yes,status=yes" );
		window.status='';
				
	}
	
	function goNextPage()
	{
		if( document.frm_existencias_1.txt_id_title.value == "" )
		{
			alert( "<?php echo $MSG_IDTITLE_NEEDED;?>" );
			document.frm_existencias_1.txt_id_title.focus();

			return false;
		}
		else
		{
			if( validado ) 
			{			
				js_ChangeLocation( "anls_existencias_paso2.php?id_titulo=" + document.frm_existencias_1.txt_id_title.value );
			}
			else
			{
				js_ChangeLocation( "anls_series_recep.php?id_titulo=" + document.frm_existencias_1.txt_id_title.value + "&validar=1" );
			}
			return true;
		}
	}	

	function crearserie()
	{
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "create_series", 900, 550 );
	}

	//
	// adaptada para Sirve para hacer un submit cuando se oprime la tecla enter
	function local_blockNonNumbers(obj, e, allowDecimal, allowNegative)
	{
		var keycode;
	
		if (window.event) keycode = window.event.keyCode;
		else if (e) keycode = e.which;
		else return true;
	
		if (keycode == 13)
		{			
			goNextPage();
			return false;	
		}
		else
			return blockNonNumbers(obj, e, allowDecimal, allowNegative)
	}		
	
	window.onload=function()
	{
		prepareInputsForHints();
		document.frm_existencias_1.txt_id_title.focus();
	}		
	

</SCRIPT>

<STYLE>

  #caja_datos1 
  {
    float: none; 
    width: 750px; 
  }
  
  #lbl_id_title
  {
	position: absolute;
	margin-left: 10px;
	margin-top: 0px;
  }
  
  #btnContinuar { position:absolute; left:0em; } 
  
</STYLE>

<body id="home">

<?php
  // barra de navegación superior
  display_global_nav();  
 ?>

<!-- contenedor principal -->
<div id="contenedor">

<?php 
   // banner
   display_banner();  
   
   // menu principal
   display_menu( "../" ); 
   
?>
 
<div id="bloque_principal"> <!-- inicia contenido -->
 
 <div id="contenido_principal">

	<div class=caja_datos id=caja_datos1 >
		<h2><?php echo $LBL_EXISTENCES_HEADER_1;?></h2>
		<hr>
		<br>
		
		<?php
			if( $error_validacion == 1 )
			{
				echo "<div class='caja_errores'>";
				echo sprintf( $MSG_TITLE_NOT_FOUND, $id_titulo );
				echo "</div><br>";
			}
			else if( $error_validacion == 2 )
			{
				echo "<div class='caja_errores'>";
				echo sprintf( $MSG_TITLE_NOT_SERIE, $id_titulo );
				echo "</div><br>";
			}			
		?>

			  <form action="javascript:verif();" method="post" name="frm_existencias_1" id="frm_existencias_1" class="forma_captura">
				
				<dt>
					<label for="txt_id_title"><?php echo $LBL_ID_TITLE;?></label>
				</dt>
				<dd>
					<input type="text" class="campo_captura" id="txt_id_title" name="txt_id_title" style='display: inline;' value="<?php echo $id_titulo;?>" 
						onblur="extractNumber(this,2,false);" onkeypress="return local_blockNonNumbers(this, event, true, false);" >
										
					<input title='<?php echo $TXT_HINT_SEARCH;?>' style="display: inline; top:-1px; left: -5px;" class="boton" type="button" value="?" name="btnBuscar" id="btnBuscar" 
						onClick="javascript:selectTitle();">
					
					<span id=lbl_id_title name=lbl_id_title><?php echo $LBL_TXT_TITLE; ?></span>
					
					<br>
					<span class="sp_hint"><?php echo $HINT_ID_TITLE;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
					
				<br><br>
				
				<div id="buttonarea">
					<input class=boton type=button value="Continuar" id=btnContinuar name=btnContinuar onClick='javascript:goNextPage();'>
				</div>
				<br>

			  </form>
			  
			  <br>
			  
	</div> <!-- caja_datos --> 
	
 </div> <!-- contenido_principal -->

 <div id="contenido_adicional">
	&nbsp;
  </div> <!-- contenido_adicional -->

</div>
<!-- end div bloque_principal -->

<?php  display_copyright(); ?>

</div><!-- end div contenedor -->

</body>

</html>