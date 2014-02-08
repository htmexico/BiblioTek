<?php
	session_start();
	
	/*******
	  - Bitacora de actividades por usuario.
	  - Inicio 09 mayo de 2009.
	  
	    - 07-ago-2009: Se cambia a caracteres ocultos, la captura de contraseñas
		
		PENDIENTES:
		
		ENCRIPTAR las contraseñas en md5 al viajar
	  
     */	
	
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";

	include_language( "global_menus" );
	include_language( "usr_cmb_passwd" ); // archivo de idioma
	
	$id_biblioteca = getsessionvar("id_biblioteca");
	
	$empleado 	= read_param( "empleado", "" );
	$id_usuario	= read_param( "id_usuario", "" );
	$pagina		= read_param( "pagina", 0 );	

	check_usuario_firmado();
	
	$paso1		=0;
	$paso2		=0;
	$passwd_act ="";
	$nvo_passwd ="";
	$cnf_passwd ="";
	$usuario	=false;
	$cambiar	=false;
	$existe		=false;
		
	if( isset($_POST["cambiar"]) )
		$cambiar=$_POST["cambiar"];
		
	if( isset($_POST["passwd_act"]) )
		$passwd_act  =$_POST["passwd_act"];		
		
	if( isset($_POST["nvo_passwd"]) )
		$nvo_passwd  =$_POST["nvo_passwd"];
		
	if( isset($_POST["cnf_passwd"]) )
		$cnf_passwd=$_POST["cnf_passwd"];
		
	if ( ( $empleado=="Si" ) and ( $cambiar ) ) {
		$existe=true;
		$passwd_act="empleado";
	}
	
	if ( empty( $passwd_act ) OR empty( $nvo_passwd ) OR empty( $cnf_passwd ) ) 
		$paso1=1; 
		
	if ( $empleado == "No" ) {
		if ( ( $cambiar ) and ( $paso1 != 1) )
		{		
			$db = new DB();
			$db->Open( "SELECT * FROM cfgusuarios WHERE ID_BIBLIOTECA=$id_biblioteca AND ID_USUARIO = '$id_usuario' AND  PASSWRD = md5('$passwd_act') ");
			
			while( $db->NextRow() )
			{
				if( $db->numRows >= 1 ) {
					$existe=true;
				}
			}
				
			$db->FreeResultset();	
		}
	}
	
	if ( trim( $nvo_passwd ) === trim( $cnf_passwd ) ) 
		$paso2=1;
		
	if ( ( $paso2 ) AND ($existe) ){
		$db = new DB();
		$db->Open( "UPDATE cfgusuarios SET PASSWRD='" . md5($nvo_passwd ) . "' WHERE ID_BIBLIOTECA=$id_biblioteca AND ID_USUARIO = '$id_usuario' ");
		$db->FreeResultset();
		$usuario=true;
		agregar_actividad_de_usuario( CFG_USER_CHANGE_PASSWORD, $login_usuario ); 
	}	
		
	include "../basic/head_handler.php"; // Coloca un encabezado HTML <head>

	HeadHandler( "$LBL_HEADER_V1", "../" );

?>

<script type="text/javascript" src="../calend/calend.js"></script>

<SCRIPT language="JavaScript">

	function go()
	{
		if( document.bit_form.nvo_passwd.value == document.bit_form.cnf_passwd.value )
		{	
			document.bit_form.submit();
		}
		else
		{
			alert( "<?php echo $MSG_PASSWRD_NOT_MATCH;?>" );
		}
	}
	
</SCRIPT>

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
   display_menu(); 
 ?>
 
<div id="bloque_principal"> <!-- inicia contenido -->
	<div id="contenido_principal">
 		<div class=caja_datos id=caja_datos1 style='width: 98%;' >
		
		<?php
		
		echo "<h2>$LBL_HEADER_V1</h2>";
		echo "<HR>";
		
		if( $usuario ) 
		{				
			echo "<div class=caja_info>";
			echo " <strong>$SAVE_PASSWD_DONE</strong>";
			echo "</div><br>";
		}
		
		if ( ( $cambiar ) AND ( $paso1 == 1 ) ) {				
			echo "<div class=caja_errores>";
			echo " <strong>Error: Introduce correctamente los datos. </strong>";
			echo "</div><br>";
		}	
			
		if ( ( $cambiar ) AND ( !$existe ) AND ( $paso1 != 1 ) ) {				
			echo "<div class=caja_errores>";
			echo " <strong>Error: La contraseña actual no es correcta. </strong>";
			echo "</div><br>";
		}
		
		echo "<form action='serv_usr_cmb_passwd.php' method='post' name='bit_form' id='bit_form' class='forma_captura'>";
			echo "<input class='hidden' type='hidden' name='pagina' id='pagina' value='" . $pagina . "' >";	
			echo "<input class='hidden' type='hidden' name='id_usuario' id='id_usuario' value='" . $id_usuario . "' >";
			echo "<input class='hidden' type='hidden' name='cambiar' id='cambiar' value='" . True . "' >";
			echo "<input class='hidden' type='hidden' name='empleado' id='empleado' value='" . $empleado . "' >";
			
			if ( $empleado=="No" ) {
				echo "<br>";
				echo "<label for='passwd_act'>".$LBL_PASSWD_ACT."</label>";
				echo "<input class='campo_captura' type='password' name='passwd_act' id='passwd_act' value='$passwd_act' size='15'/>";
				echo "<br>";
			}
			echo "<br>";
			echo "<label for='nvo_passwd'>".$LBL_NVO_PASSWD."</label>";
			echo "<input class='campo_captura' type='password' name='nvo_passwd' id='nvo_passwd' value='" . $nvo_passwd . "' size='15'/>";
			echo "<br>";
			
			echo "<label for='cnf_passwd'>". $LBL_CONF_PASSWD."</label>";
			echo "<input class='campo_captura' type='password' name='cnf_passwd' id='cnf_passwd' value='" . $cnf_passwd . "' size='15'/>";
			echo "<br>";
			
			echo "<br>";		
			
		?>
			
		<div id="buttonarea">
			<input id="btncambiar" name="btncambiar" class="boton" type="button" value="Cambiar" onClick="this.form.submit()">
			<input id="btnRegresar" name="btnRegresar" class="boton" type="button" value="Regresar" onClick="location.href='serv_usuarios.php?pagina=<?php echo $pagina; ?>'">							
		</div>
			
		</div> <!-- caja datos --> 
		
		</div> <!-- contenido_principal -->
		
	</div><!-- end div bloque_principal -->

<?php  display_copyright(); ?>

</div><!-- end div contenedor -->

</body>

</html>