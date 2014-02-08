<?php
	session_start();

	/**********

		19-junio-2009	Se crea el archivo "gral_elegir_subcampo.php"
	 
	 **/

	include ("../funcs.inc.php");
	include ("../basic/bd.class.php");

	check_usuario_firmado(); 

	include ("../basic/head_handler.php");
	
	include_language( "global_menus" );
	include_language( "gral_elegir_subcampo" );
	
	HeadHandler( "$LBL_HEADER_V1", "../" );

	$lista_subcampos = "";
	
	$id_plantilla = read_param( "id_plantilla", 0 );
	$id_campo = read_param( "id_campo", "", 1 );
	
	$descrip_campo = "";
	
    if( $id_campo != "")
	{
		// Consultar productos a modificar
		$db = new DB();

		$db->Open( "SELECT a.* " . 
				   "FROM marc_codigo21 a " . 
				   "WHERE a.ID_CAMPO='$id_campo' and a.NIVEL_MARC=1" );

		if ( $db->NextRow() ) 
		{
			if( getsessionvar( "language_pref" ) == "Spanish" )
				$descrip_campo = $db->row["DESCRIPCION"];
			else
				$descrip_campo = $db->row["DESCRIPCION_ORIGINAL"];
		}
		
		echo 
				   
		$db->FreeResultset();
		
		
		$db->Open( "SELECT a.*, b.DATO " . 
				   "FROM marc_codigo21 a " . 
				   "  LEFT JOIN cfgplantillas b ON (b.ID_BIBLIOTECA=" . getsessionvar("id_biblioteca"). " and b.ID_PLANTILLA=$id_plantilla and b.ID_CAMPO=a.ID_CAMPO and b.DATO=a.CODIGO) " .	
				   "WHERE a.ID_CAMPO='$id_campo' and a.NIVEL_MARC=9 and not(a.OBSOLETO='S') " .
				   "ORDER BY a.CODIGO " );
				   
		$lista_subcampos = "<TABLE>";
		
		while( $db->NextRow() ) 
		{
			$id_subcampo = $db->row["CODIGO"];
			$descripcion = $db->row["DESCRIPCION"];
			$descrip_original = $db->row["DESCRIPCION_ORIGINAL"];
			
			if( $id_subcampo >= '$a' )
			{
				$check_control = "";
				$hilite_row = "";
				
				if( $db->row["DATO"] != "" )
				{					
					$check_control = "<input type='checkbox' disabled checked class='checkbox'>";
				}				
				else
				{
					$hilite_row = "onMouseOver='javascript:Hilite(this)' onMouseOut='javascript:UnHilite(this)'";
					$check_control = "<input type='checkbox' class='checkbox' onClick='javascript:check_value();'id='subcampo_" . $id_subcampo . "' name='subcampo_" . $id_subcampo . "'>";
				}
				
				$lista_subcampos .= "<TR $hilite_row>" . 
									"<TD>$check_control&nbsp;&nbsp;</TD><TD class='column cuadricula' width=50px>&nbsp;&nbsp;$id_subcampo</TD><TD width='350px' class='column cuadricula'>$descripcion</TD>" . 
									"</TR>\n";
			}
		}
		
		$lista_subcampos .= "</TABLE>";
		
		$db->FreeResultset();
		$db->destroy();
	}

?>

<script type="text/javascript">
	
	function check_value()
	{
		var aChecks = document.getElementsByTagName('input');
		var valores = document.getElementsByName("valores");
		var res = "";
		
		for( var i = 0; i<aChecks.length; i++ )
		{
			if( aChecks[i].className == "checkbox" )				
			{
				if( aChecks[i].checked )
				{
					var xname = aChecks[i].name;
					if( res != "" ) res += ":";
					res += aChecks[i].name.substr(9,10);
				}
			}
		}
		
		if( valores.length > 0 )
		{
			valores[0].value = res;
		}
	}
	
	function seleccionar() 
	{
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
					control_destino[0].value += "&idsubcampo=" + valores[0].value;
					
					//alert( control_destino[0].value );
					
					window.opener.nuevosSubCampos();
				}
				
				window.close();
			}
		}
	}
		
</script>

<body id="home">
<br>
<div id="contenedor"> 

<div id="bloque_principal">

	<div id="contenido_principal" style='width: 90%;'>

	<H2><?php echo $LBL_HEADER_V2;?><br><br>
	
	<?php echo $id_campo . " " . $descrip_campo;?>
	</H2>

	<HR>

		<?php echo $lista_subcampos; ?>

		<br>
		
		<INPUT type='hidden' class='hidden' id='valores' name='valores' >
		
		<DIV id='buttonArea'>
		<INPUT class="boton" type="button" value="<?php echo $BTN_SELECT;?>" name="btnBuscar" id="btnBuscar" onClick="javascript:seleccionar();">&nbsp;
		<INPUT class="boton" type="button" value="<?php echo $BTN_CANCEL;?>" name="btnBuscar" id="btnBuscar" onClick="javascript:window.close();">
		</DIV>
		
	</div>
				
	<br>

</div>

<?php  display_copyright(); ?>

</div>
<!-- end div contenedor -->

</body>

</html>