<?php
	session_start();
	
	/*
	  
	  - Comentarios de Usuarios
	  - Inicio: 11-nov-2009
	     
	*/	
		
	include "../funcs.inc.php";
	include "../basic/bd.class.php";
	include "../privilegios.inc.php";
	include "circulacion.inc.php";
	include_language( "global_menus" );

	check_usuario_firmado(); 
	
	$id_biblioteca = getsessionvar("id_biblioteca");

	include_language( "gral_vertitulo" ); // archivo de idioma

	$id_usuario = read_param( "id_usuario", 0, 1 );
	$the_action = read_param( "the_action", "" );
	
	$flag = 0;
	
	include ("../basic/head_handler.php");  // Coloca un encabezado HTML <head>
	HeadHandler( "$LBL_COMMENTS_PER_USER", "../" );
	
	//verificar_privilegio( PRIV_USERS, 1 );

?>

<script type="text/javascript" src="../basic/md5.js"></script>

<SCRIPT type="text/javascript" language="JavaScript">

</SCRIPT>

<STYLE>

	#info_general 
	{
		width: 850px;
	}
	
	#buttonArea
	{
		margin-bottom: 8px;
	}

</STYLE>

<body id="home">

<?php

  display_global_nav();  // barra de navegación superior
  
 ?>

<!-- contenedor principal -->

<div id="contenedor">

<?php 
   display_banner();  // banner   
   display_menu('../'); // menu principal
 ?>
 
   <div id="bloque_principal"> 
      <div id="contenido_principal">
	   
	  <h1><?php echo $LBL_COMMENTS_PER_USER;?></h1>
	  
	  <?php 
		$db = new DB();
		
		$user = new TUser( $id_biblioteca, $id_usuario, $db );
	   ?>
	  
	  <h2><?php echo $user->NOMBRE_COMPLETO;?></h2>
	  <h3><?php echo $user->NOMBRE_GRUPO;?></h3>
	  <br>
	  
       <div id="info_general" class="caja_datos">
		
	   <?php 
	   
			$user->destroy();
			
			echo "<div id='buttonArea' align=right>";				
			echo "</div>"; 	   
			
			$db->sql = "SELECT a.* " . 
				       "FROM acervo_titulos_califs a " . 
					   "WHERE a.ID_BIBLIOTECA=" . getsessionvar("id_biblioteca") . " and a.ID_USUARIO=$id_usuario " .
					   "ORDER BY a.ID_USUARIO ";
				
			// crear el paginador		
			$paginador = new Pager( $db, "N", 6 );
				
			if( isset( $_GET["page"] ) )
				$paginador->page = $_GET["page"];
				
			$paginador->Calculate_Ranges();
			$db->SetPage( $paginador->start_from, $paginador->Range, $paginador );  // se agrega $paginador
			
			$paginador->Language( getsessionvar("language") );
				
			$db->Open();
				
			while( $db->NextRow() )
			{
				echo "<div class='caja_comentarios'>";
				
				$item = new TItem_Basic( $id_biblioteca, $db->row["ID_TITULO"], 0, $db );
				
				echo "<div class='perfil_usuario'>";
				echo "<h1>$item->cTitle</h1><br>";
				
				$item->destroy();
				
				echo "<h3>" . dbdate_to_human_format( $db->row["FECHA_OPINION"], 1, 1 ) . "</h3>";
				
				echo "<br><br>";

				$str_calif = "";
				
				for( $j=1; $j<=((int) $db->row["CALIFICACION"]); $j++ )
				{
					$str_calif .= "<img src='../images/icons/star_full.png'>";
				}
				
				if( $db->row["CALIFICACION"] > (int) $db->row["CALIFICACION"] )
				{
					$str_calif .= "<img src='../images/icons/star_half.png'>";
				}
				
				echo "<h2>$LBL_USER_RATE</h2><br>";
				echo $str_calif;

				echo "<br><br>";
				
				echo "</div>";  // perfil_usuario
				
				echo "<div class='detalles'>";
				
				$opinion = $db->GetBLOB( $db->row["OPINION"] );
				$opinion = str_replace( "\r\n", "<br>", $opinion );
				
				echo "<h1>" . $db->row["COMENTARIO"] . "</h1><br>" ;
				echo "$opinion<br><br>";
				
				echo "</div>";
				echo "</div>";
				
				echo "<br style='clear:both;'><br>";

			} //fin while						
			
			$db->Close();
			
			$paginador->DrawPages();
		
		?>		
		
       </div> <!-- - caja datos -->	   
	   
	   <br>
		<input id='btnRegresar' name='btnRegresar' class='boton' type='button' value='<?php echo $BTN_GOBACK;?>' onclick="window.history.back();">
	   
	   
      </div>  <!-- contenido pricipal -->	
		
<?php  display_copyright(); ?>

   </div> <!--bloque principal-->
</div>    <!--bloque contenedor-->
       
</body>
</html>