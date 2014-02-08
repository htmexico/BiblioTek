<?php
	session_start();

	/**********
		Sirve para elegir un campo
		
		21-junio-2009	Se crea el archivo "gral_elegir_campo.php"
	 
	 **/

	include ("../funcs.inc.php");
	include ("../basic/bd.class.php");

	check_usuario_firmado(); 

	include ("../basic/head_handler.php");
	
	include_language( "global_menus" );
	include_language( "gral_elegir_campo" );
	
	include "catalog.inc.php";
	
	HeadHandler( "$LBL_HEADER_V1", "../" );

	$db = new DB();
	
?>

<script type="text/javascript" src='..\utils.js'></script>

<script type="text/javascript">
	
		function seleccionar() 
		{
			updateCheckBoxValue();
			
			var valores = document.getElementsByName("valores");

			if( valores.length > 0 )
			{
				if( valores[0].value == "" )
					alert( "<?php echo $MSG_WARNING_1;?>" );
				else
				{
					var control_destino = window.opener.document.getElementsByName( "aux_params" );
					
					if( control_destino.length > 0 )
					{
						// subcampo seleccionado
						control_destino[0].value += "&idcampo=" + valores[0].value;
						
						window.opener.nuevosCampos();
					}
					
					window.close();
				}
			}
		}
		
		function SetMarcHINT( hint )
		{
			var divID = document.getElementsByName("marc_hint");
			
			if( hint == "" ) hint = "&nbsp;";
			
			divID[0].innerHTML = hint;
		}
		
		function updateCheckBoxValue()
		{
			var obj = document.getElementsByTagName( "INPUT" );
			
			if( obj.length > 0 )
			{
				var ret = "";  // genera la cadena con los diversos campos MARCADOS
				var valores = document.getElementsByName( "valores" );
				
				for( var i = 0; i<obj.length; i++ )
					if( obj[i].className == "checkbox" )
						if( obj[i].name.substr(0,8) == "chk_fld_" && obj[i].checked )
						{
							if( ret != "" )
								ret += ":";
								
							ret = ret + obj[i].name.substr(8,25);
						}
				
				if( valores.length > 0 )
					valores[0].value = ret;  // coloca la cadena con los diversos campos MARCADOS
			}
		}		
		
</script>

<body id="home">
<br>
<div id="contenedor"> 

<div id="bloque_principal">

	<div id="contenido_principal" name="contenido_principal" style='width: 750px; border: 0px solid red;'>

	<H2><?php echo $LBL_HEADER_V1;?><br></H2>
	
	<div id="marc_hint" name="marc_hint">&nbsp;</div><br>
	
		<UL class=tabset_tabs style="left:3px; text-align: left;font-size:90%;" name='tabMARCFields' id='tabMARCFields' >
			<LI><a class="active" name="tab1_link" href='javascript:changeTab( "contenido_principal", "tab1" );' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Codificación Básica");'>01x-04x</a></LI>
			<LI><a name="tab2_link" href='javascript:changeTab( "contenido_principal","tab2" );' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Clasificacion");'>05x-08x</a></LI>
			<LI><a name="tab3_link" href='javascript:changeTab( "contenido_principal","tab3" );' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Asiento Principal");'>100x</a></LI>
			<LI><a name="tab4_link" href='javascript:changeTab( "contenido_principal","tab4" );' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Codificación Básica");'>20x-24x</a></LI>
			<LI><a name="tab5_link" href='javascript:changeTab( "contenido_principal","tab5");' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Edición, Impresión, etc.");'>25x-27x</a></LI>
			<LI><a name="tab6_link" href='javascript:changeTab( "contenido_principal","tab6");' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Descripción Física");'>3xx</a></LI>
			<LI><a name="tab7_link" href='javascript:changeTab( "contenido_principal","tab7");' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Mención de Serie");'>4xx</a></LI>
			<LI><a name="tab8_link" href='javascript:changeTab( "contenido_principal","tab8");' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Notas");'>5xx</a></LI>
			<LI><a name="tab9_link" href='javascript:changeTab( "contenido_principal","tab9");' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Materias");'>6xx</a></LI>
			<LI><a name="tab10_link" href='javascript:changeTab( "contenido_principal","tab10");' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Asientos Secundarios");'>7xx</a></LI>
			<LI><a name="tab11_link" href='javascript:changeTab( "contenido_principal","tab11");' onmouseout='javascript:SetMarcHINT("");' onmouseover='javascript:SetMarcHINT("Asientos Secundarios de Serie");'>8xx</a></LI>
		</UL>	
		
		<DIV id="tab1" name="tab1" class="contenedor_tabs" style='height: 370px'><?php insertMARCFields( $db, "010", "049", "contenido_rincipal"); ?></DIV>
		<DIV id="tab2" name="tab2" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "050", "089", "contenido_principal"); ?></DIV>
		<DIV id="tab3" name="tab3" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "100", "199", "contenido_principal"); ?></DIV>
		<DIV id="tab4" name="tab4" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "200", "249", "contenido_principal"); ?></DIV>
		<DIV id="tab5" name="tab5" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "250", "279", "contenido_principal"); ?></DIV>
		<DIV id="tab6" name="tab6" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "300", "399", "contenido_principal");?></DIV>
		<DIV id="tab7" name="tab7" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "400", "499", "contenido_principal");?></DIV>
		<DIV id="tab8" name="tab8" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "500", "599", "contenido_principal");?></DIV>
		<DIV id="tab9" name="tab9" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "600", "699", "contenido_principal");?></DIV>
		<DIV id="tab10" name="tab10" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "700", "799", "contenido_principal");?></DIV>
		<DIV id="tab11" name="tab11" class="contenedor_tabs" style="display:none; height: 370px;"><?php insertMARCFields( $db, "800", "899", "contenido_principal");?></DIV>

		<br>
		
		<INPUT type='hidden' class='hidden' id='valores' name='valores' >
		
		<DIV id='buttonArea'>
		<INPUT class="boton" type="button" value="<?php echo $BTN_SELECT;?>" name="btnBuscar" id="btnBuscar" onClick="javascript:seleccionar();">&nbsp;
		<INPUT class="boton" type="button" value="<?php echo $BTN_CANCEL;?>" name="btnBuscar" id="btnBuscar" onClick="javascript:window.close();">
		</DIV>
		
	</div>

</div>

<?php  
	display_copyright(); 
	$db->destroy();
?>

</div>
<!-- end div contenedor -->
</body>

</html>