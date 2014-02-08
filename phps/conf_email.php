<?php
	session_start();
		
	/*******
	 Permite configurar el formato de avisos al gusto del usuario
	 
	 Historial de Cambios
		  
	 27 oct 2009: Se crea como parte de las funciones de alertas x email.
	 
	 */		
		
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	include "marc.php";
	
	check_usuario_firmado(); 
	
	$descrip = "";

	$id_biblioteca = getsessionvar("id_biblioteca");
	
	$id_cat = read_param( "id_cat", "", 0 );
	$the_action = read_param( "the_action", "", 0 );
		
	include_language( "global_menus" );	
	include_language( "conf_email" );
	
	$info = 0;
	
	$db = new DB();
	
	$next_action = "";
	
	if( $the_action == "save" or $the_action == "update" )
	{
		$txt_subject    = read_param( "custom_subject", "", 0 );
		$txt_body	    = read_param( "body_content", "", 0 );

		$txt_body = str_replace( "\n", "", $txt_body );
		
		$db->ExecSQL( "UPDATE email_config SET CUSTOM_SUBJECT='$txt_subject', CUSTOM_BODY_CONTENT='$txt_body' " .
					  "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_CATEGORIA=$id_cat " );
					  
		$info = 30;
	}
	else if( $the_action == "create_new" )
	{
		$txt_subject    = read_param( "custom_subject", "", 0 );
		$txt_body	    = read_param( "body_content", "", 0 );

		$txt_body = str_replace( "\n", "", $txt_body );

		$db->ExecSQL( "INSERT INTO email_config ( ID_BIBLIOTECA, ID_CATEGORIA, CUSTOM_SUBJECT, CUSTOM_BODY_CONTENT ) " .
					  "VALUES ($id_biblioteca, $id_cat, '$txt_subject', '$txt_body' ); " );
					  
		$info = 40;
		
		
	}

	// Draw an html head
	include "../basic/head_handler.php";
	HeadHandler( $LABEL_HEADER, "../" );
	
	include ("../privilegios.inc.php");
	verificar_privilegio( PRIV_CFG_EMAIL_ALERTS, 1 );

?>

<script type="text/javascript">

 
 function edit_cat( obj )
 {
	var url = "conf_email.php?id_cat=" + obj.value;
	
	js_ChangeLocation( url );
 }
 
 function validar()
 {
	var error = 0;
	
	if( document.edit_form.custom_subject.value == "" )
	{
		alert( "<?php echo $ALERT_SUBJECT_IS_MISSING;?>" );
		document.edit_form.custom_subject.focus();
		error = 1;
	}
	
	if( document.edit_form.body_content.value == "" )
	{
		alert( "<?php echo $ALERT_BODY_IS_MISSING;?>" );
		document.edit_form.body_content.focus();
		error = 1;
	}
	
	if( error == 0 )
	{
		if( confirm("<?php echo $MSG_SAVE;?>") )
		{
			document.edit_form.submit();
		}
	}
	
 }
 
</script>


<STYLE type="text/css">
 
  #datos_generales 
  {
	font-size: 90%;
  }

 #contenido_principal {
   float: left;
   width: 70%;
  }

 #contenido_adicional {
   float: right; 
   width: 25%;
   border: 1px dotted gray;
   padding: 4px;
   margin-top: 50px;
  } 
  
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
	<h1><?php echo $LABEL_HEADER;?></h1>
	
    <h2><?php echo $LABEL_INTRO_V2;?>&nbsp;&nbsp;</h2>
	<br>
	
	<?php	
		if( $info == 30 )
		{
			echo "<div class=caja_info>";
			echo " <strong>$SAVE_DONE</strong>";			
			echo "</div>";
		}
		else if( $info == 40 )
		{
			echo "<div class=caja_info>";
			echo " <strong>$CREATE_DONE</strong>";			
			echo "</div>";
		}
		
		$codes = "";
	?>

	<div class="caja_con_ligas">
	
		<form action='conf_email.php' method='post' name='edit_form' id='edit_form' class='forma_captura'>

		  
		  <?php
		  
			$db->Open( "SELECT a.ID_CATEGORIA, a.DESCRIPCION, a.DESCRIPCION_ENG, a.DESCRIPCION_PORT " .
					   "FROM email_categorias a " .
					   "ORDER BY a.ID_CATEGORIA;" );
					   
			echo "<dt>";
			echo "<label for='txt_user_login_name'><strong>$LABEL_CHOOSE_CAT</strong></label>";
			echo "</dt>";
			echo "<dd>";
			echo "<SELECT class='select_captura' id='cmb_categorias' name='cmb_categorias' onChange='javascript:edit_cat(this)'>\n\n";
			echo " <OPTION value='-1'> --- " . $LABEL_CHOOSE_ONE. " -- </OPTION>\n"; 
			
			$nombre_cat  = "";
				
			while( $db->NextRow() ) 
			{
				$str_selected = "";
				
				if( $db->row["ID_CATEGORIA"] == $id_cat )
				{
					$str_selected = "SELECTED";
				}
					
				echo " <OPTION value='" . $db->row["ID_CATEGORIA"] ."' $str_selected>" . get_translation( 
																							$db->row["DESCRIPCION"],
																							$db->row["DESCRIPCION_ENG"],
																							$db->row["DESCRIPCION_PORT"] ) . "</OPTION>\n"; 
			}
					   
			echo "</SELECT>\n\n";
			echo "</dd>";
			
			$db->Close();

			if( $id_cat != 0 and $id_cat != -1 )
			{			
				$db->Open( "SELECT a.FIELDS_LIST, b.CUSTOM_SUBJECT, b.CUSTOM_BODY_CONTENT " . 
						   "FROM email_categorias a " . 
						   "   LEFT JOIN email_config b ON (b.ID_BIBLIOTECA=$id_biblioteca and b.ID_CATEGORIA=a.ID_CATEGORIA) " .
						   "WHERE a.ID_CATEGORIA=$id_cat; " );
						   
				if( $db->NextRow() )
				{
					$subject = $db->row["CUSTOM_SUBJECT"];
					$codes = htmlentities( $db->GetBLOB( $db->row["FIELDS_LIST"] ) );
					$body_content = $db->GetBLOB( $db->row["CUSTOM_BODY_CONTENT"], 1 );
							
					if( trim($subject) == "" and trim($body_content) == "" )
					{
						$next_action = "create_new";
					}
					else
					{
						$next_action = "save";
					}
				}
				else
				{
					$subject = "";
					$body_content = "";
				}
				
				$db->Close();
				
				echo "<br style='clear:all'>";
				
			    echo "<input class='hidden' type='hidden' name='the_action' id='the_action' value='$next_action'>";
			    echo "<input class='hidden' type='hidden' name='id_cat' id='id_cat' value='$id_cat'>";								
				
				$body_content = str_replace( "\r\n", "\n\n", $body_content );
				$codes = str_replace( "\r\n", "<br>", $codes );
				
				echo "<dt>";
				echo "<label for='custom_subject'><strong>$LBL_SUBJECT</strong></label>";
				echo "</dt>";
				echo "<dd>";
				echo "<input id='custom_subject' name='custom_subject' value='$subject' size=90 maxlength=150>";
				echo "<span class='sp_hint'>$HINT_SUBJECT<span class='hint-pointer'>&nbsp;</span></span>";
				echo "</dd>";
				
				echo "<dt>";
				echo "<label for='body_content'><strong>$LBL_BODY</strong></label>";
				echo "</dt>";
				echo "<dd>";
				echo "<textarea id='body_content' name='body_content' rows=15 cols=90>$body_content</textarea>";
				echo "<span class='sp_hint'>$HINT_BODY<span class='hint-pointer'>&nbsp;</span></span>";
				echo "</dd>";
				
				echo "<br>";

				echo '<div id="buttonarea">';
				echo "<input id='btnGuardar' name='btnGuardar' class='boton' type='button' value='$BTN_SAVE' onClick='javascript:validar();'>";
			    echo '</div>';

			}
		  
		   ?>
        
		</form>
		
	</div><!-- caja_con_ligas -->	
	
 </div> <!-- contenido_principal -->

 <div id="contenido_adicional" <?php if( $id_cat == 0 or $id_cat == -1 ) echo " style='border:0px;' ";?>> 
  <?php
	if( $id_cat != 0 and $id_cat != -1 )
	{
		echo "<strong>$LBL_CODES_AVAIL</strong><hr>";
		
		echo "<br>$codes.<br><br>";
	}
   ?>
 </div> <!-- contenido_adicional -->

</div>
<!-- end div bloque_principal -->

<?php  display_copyright(); 

  $db->destroy();

?>

</div><!-- end div contenedor -->

</body>

</html>
