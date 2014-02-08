<?php
	session_start();

	/**********

		27-mayo-2009	Se crea el archivo.
	 
	 **/

	include ("../funcs.inc.php");

	check_usuario_firmado(); 

	// Draw an html head
	include ("../basic/head_handler.php");
	include ("../basic/bd.class.php");
	
	include_language( "global_menus" );
	include_language( "gral_indicadores" );

	HeadHandler( "$LBL_CONSULT_HEADER", "../" );
	
	$campo = read_param( "campo", "", 1 );	
	$id = read_param( "id", 0 );	
	$control = read_param( "control", "", 1 );	
	$val = read_param( "val", "" );
	
	if( $campo != "" )
	{
		$campo = str_replace( "\\'", "", $campo );
	}
	
	$db = new DB();

	$descrip_campo = "";
	$descrip_indicador = "";

	//
	// obtener la descripcion del campo
	//
	$db->Open("SELECT a.DESCRIPCION, a.DESCRIPCION_ORIGINAL FROM marc_codigo21 a WHERE a.ID_CAMPO=" . $campo . " and a.NIVEL_MARC=1");
	
	
	if( $db->NextRow() )
	{	
		if( getsessionvar("language") == 1 )
			$descrip_campo = $db->row["DESCRIPCION"];
		else if( getsessionvar("language") == 2 )
			$descrip_campo = $db->row["DESCRIPCION_ORIGINAL"];
		
	}
	
	$db->FreeResultset();

	$codigo_id = (($id==1) ? "X1" : "X2" );
	
	//
	// obtener la descripcion del indicador
	//
	$db->Open("SELECT a.DESCRIPCION, a.DESCRIPCION_ORIGINAL FROM marc_codigo21 a WHERE a.ID_CAMPO=" . $campo . " and a.CODIGO='$codigo_id' and a.NIVEL_MARC=5");
	
	if( $db->NextRow() )
	{	
		if( getsessionvar("language") == 1 )
			$descrip_indicador = $db->row["DESCRIPCION"];
		else if( getsessionvar("language") == 2 )
			$descrip_indicador = $db->row["DESCRIPCION_ORIGINAL"];
	}
	
	$db->Close();

?>
	
<script type="text/javascript">	

	function SelectCodigo()
	{	
		//var id_control = window.opener.document.getElementsByName( "<?php echo $control;?>" );
		var subcodigo = document.getElementsByName( "rad_subcodigo" );
		
		if( subcodigo.length > 0 )
		{
			var value_selected = "";
			
			for( var i=0; i<subcodigo.length; i++ )
			{
				if( subcodigo[i].checked )
				{
					value_selected = subcodigo[i].value;
					
					if( value_selected == "UNDEF" ) value_selected = "#";

					break;
				}
			}
			
			if( value_selected == "" )
			{
				alert( "Debe elegir un valor" );
			}
			else
			{			
				window.returnValue = value_selected;
				window.close();
					
				//if( id_control.length > 0 )
				//{
				//	  id_control[0].value = value_selected;
				//}
			}
		}

		// verificar si hay control de radio
		subcodigo = document.getElementsByName( "sel_subcodigo" );
		
		if( subcodigo.length > 0 )
		{
			if( subcodigo[0].value == "" )
			{
				alert( "Debe elegir un valor" );
			}
			else
			{			
				window.returnValue =subcodigo[0].value
				window.close();
			}
		}
		
	}
	
</script>

<body>

<br>

<div id="contenedor" style='width:700px'> 

<div id="bloque_principal">

  <div id="contenido_principal" style='width: 90%;'>

	<H2><?php echo str_replace( "'", "", $campo ) . " - " . $descrip_campo;?></H2><HR>
	
		<?php
			echo "\n";
			if( $id != 0 )
			{
				$db->sql =  "SELECT a.* " . 
							"FROM marc_codigo21 a " .
							"WHERE a.ID_CAMPO=$campo and a.CODIGO='$codigo_id' and a.NIVEL_MARC=6" . 
							"ORDER BY a.SUBCODIGO";
				$db->Open();
				
				while( $db->NextRow() )
				{	
					if( $db->numRows == 1 )
					{
						echo "<div class='caja_datos' id='caja_datos1'>";
						echo "<h1> " . $codigo_id . " : " . $descrip_indicador . "</h1>";
						echo  "<br>";
					}
					
					echo  "&nbsp;&nbsp;&nbsp;";
					
					$descrip_label = "";
					
					if( getsessionvar("language") == 1 )
						$descrip_label = $db->row["DESCRIPCION"];
					else if( getsessionvar("language") == 2 )
						$descrip_label = $db->row["DESCRIPCION_ORIGINAL"];
					
					if( strpos( $db->row["SUBCODIGO"], ":" ) != 0 ) 
					{
						$str_options = "";
						
						$array_tmp = explode( ":", $db->row["SUBCODIGO"] );
						
						for( $j=$array_tmp[0]; $j<=$array_tmp[1]; $j++ )
						{
							$str_options .= "<option value='$j' " . (($j==$val) ? "selected" : "" ) . ">" . $j . "</option>";
						}

						echo "<select name='sel_subcodigo'>$str_options</select>&nbsp;&nbsp;$descrip_label<br>";
					}
					else
					{					
						$valsubcodigo = $db->row["SUBCODIGO"];
						
						if( $valsubcodigo == "#" ) $valsubcodigo = "UNDEF"; 
						echo  "<input type='radio' name='rad_subcodigo' " . (($db->row["SUBCODIGO"]==$val) ? "checked" : "" ). " value='$valsubcodigo'>&nbsp;&nbsp;" . $db->row["SUBCODIGO"] . " - $descrip_label<br>";
					}

					echo "\n";
					
				}
				
				$db->FreeResultset();			
			
				echo "<br>";
			
				if( $db->numRows > 0 )
				{
					//echo "</table><br>\n";
					echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=button class='boton' value='$BTN_SELECT' onClick='javascript:SelectCodigo();'><br><br>";
					echo "</div><!-- caja_datos1 -->\n\n";
				}
			}
		
		 ?>		
	
  </div>  <!-- contenido_principal -->

</div>

<?php  display_copyright(); ?>

</div>
<!-- end div contenedor -->

</body>

</html>