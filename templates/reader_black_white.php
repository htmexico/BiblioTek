<style>
	
	/* SE USAN LOS MISMOS NOMBRES DE CLASE QUE EN SCREEN.CSS 
	   PARA CONSERVAR EL ESTILO BASICO... SOLAMENTE SE AGREGAN
	   ALGUNAS PROPIEDADES ESPECIFICAS PARA DARLE EL LOOK DESEADO AL TEMA.
	 */
	.classxyz { props; } 
	
	body 
	{
	background-color:#fff;
	padding-bottom: 5px;
	color: #fff;
	}
	
	#banner h1 {
		
	margin:0;
	width:960px;
	height:100px;
	background-repeat:no-repeat; 
}

	.caja_con_ligas DIV
	{
		font-size: 17px;
	}

	.caja_con_ligas li a 
	{ 
	  
	  font: normal 12px Arial; /*TIPO DE FUENTE DE LAS CAJAS DE TEXTO*/
	  font-style:normal;
	} 

	h1, h3, h4, h5 
{
	font-family:arial; /*FUENTE DE ENCABEZADOS PRINCIPALES*/
}

	
h1 {
	
	color:#CCCCCC; /*COLOR DE FUENTE DE NOMBRE PRINCIPAL*/
	font-size:16px;
	 }

h2 {

	font-size:70%; /*FUENTE ENCABEZADO NOTAS DESTACADAS */
	margin-bottom:5px;
	margin-top:1em; 
	line-height:1.1em; }

/*FUENTES TODAS LAS CAJAS DE TEXTO*/	
p, li {
	font:normal 12px arial;
 	margin:0 0 1em 0;
	line-height:1.5em; }


li { 	
	margin-bottom:.4em; }
/*************
	NAV  
*************/

#global-nav {
			text-align:right;
			width:960px;
			margin:0 auto;
			padding:8px 0 4px;
			color:#CC9900; }/*COLOR DE BARRAS Y NOMBRE DE USUARIO DE CUENTA*/

#global-nav a {

			font-size:80%;
			font-family:Verdana, Arial, sans-serif; 
			color:#CC9900; } /*COLOR TEXTO DE ENCABEZADOS O LIGAS DE PAG. PRINCIPAL*/


/*COLOR DE LIGAS*/
a:link {
	color:#333333;
}

/*COLOR DE LIGAS YA ACCESADAS*/
a:visited {
	color:#999999;
}

.caja_con_ligas h1 { /*COLOR, TAMAÑO ENCABEZADOS DE LIGAS RAPIDAS*/
	font-size: 13px;
	letter-spacing:2px;
	color: #660000;
	 }

.info {
	font: bold 90% Arial; /*TIPO DE FUENTE DE INFO PRINCIPAL*/
	color:#FFFFFF;
	font-style:normal;
	 } 

div.resaltados {
	overflow:auto;
	border: 2px solid #000000; /*COLOR DE BORDE NOTAS DESTACADAS*/
	padding: 10px;
	font-size: 88%;
	color: #000000;
	background: #FFFFFF;
	font:normal 11px arial;
}

div.resaltados h2 
{
	margin:0; 
	color:#660000; /*ENCABEZADO NOTAS DESTACADAS*/
	font: bold 135% Arial;
}

#contenedor {
	border:1px solid #000000;
	background-color:#FFFFFF;
	background-image:url(templates/images/fondo_black.jpg);
	width:960px;
	margin:0 auto;
	position:relative;
	text-align: left;
	color: #666666; /*color de texto de ligas rapidas viñetas*/
}

/*CONTENEDOR DE BUSQUEDA*/
div.caja_datos 
{
	position: relative;
	background-color: #EEEEEE;
	padding:5px; 
	border:1px solid #cccccc; 
	font-family:Arial;  /*TIPOS DE FUENTES QUE MODIFCA CATALOGACION*/
}


/*PROPIEDADES DE TEXTO INGRESAR DATOS DE ACCESSO*/
div.caja_datos h2 
{
	font-size:100%;
	font-family:arial;
	letter-spacing:1.5px;
	margin:	0; 
	color:#666666;
}
	
/*PROPIEDAES DE BOTON DE ACCESO*/	
div.caja_datos input.submit, input.boton
{
	
	font-family: Verdana, Arial, sans-serif;
	background-color:#666666;
	color:#FFFFFF;
	font-size:85%; 
	width:auto;
	padding:2px 5px; 
	letter-spacing:1px; 
	margin-left: 5px;
	left: 11em;	
}



/* menus y submenus */
/* barra del menu (con opciones principales) */
/* menu principal */
#sddm
{	margin: 0;
	padding: 0;
	z-index: 30;
	}

#sddm li
{	margin: 0;
	padding: 0;
	list-style: none;
	float: left;
}

#sddm ul 
{
	margin:0; 
	padding:0;
	list-style:none; 
}

#sddm li a
{	display: block;
	padding: 4px 10px;
	width: auto;
	color: #FFF;

	text-align: center;
	border-top:1px solid #A88E51;
	border-left:1px solid #A88E51;	
	border-right:1px solid #F2F8BE;
	text-decoration: none}

#sddm li a:hover
{	background: #49A3FF;
	padding: -4px -2px -2px -2px;
	background-color:#FCF247;
	text-decoration: underline;
	border-top:1px solid #000;
	border-left:1px solid #000;
	border-right:1px solid #000;	
	color: #000;
}

/* submenus */
	#sddm div
	{	position: absolute;
		visibility: hidden;
		margin: 0;
		padding: 0;
		background-color: #E8E364;
		border: 1px solid #000; /*6B6A5A;*/
		font-size:90%;
	}

	#sddm div a
	{	position: relative;
		display: block;
		margin: 0;
		padding: 5px 10px;
		width: auto;
		white-space: nowrap;
		text-align: left;
		text-decoration: none;
		background-color: #F1F0D0;
		color: #000; 
		font-size: 11px;
		border: 0px solid black;
	}

	#sddm div a:hover
	{	
		border: 0px solid black;
		background-color:#FCF247;
	}

	#sddm div img 
	{	
		background: url(../images/separador_menus.gif); 
		background-color: #F1F0D0;
		height: 3px;
	}

.highlight   
{   
background: #F8DCB8;   
}   
  
  div.lista_elementos_indexada
	{
	
	background: url(templates/images/idex_black.png) no-repeat left top;
	font-size: 14px;
	font:normal 12px arial;
		
	}

/****
  COLOCAR EN SCREEN.CSS
 */
/* The hint to Hide and Show */
.sp_hint {
   	display: none;
    position: absolute;
    right: 10px;
    width: 200px;
    margin-top: -4px;
    border: 1px solid #000000;
    padding: 10px 12px;
	color: #000000;
	/* to fix IE6, I can't just declare a background-color,
    I must do a bg image, too!  So I'm duplicating the pointer.gif
    image, and positioning it so that it doesn't show up
    within the box */
    background: #e8e3e4 url(templates/bullets/pointer_black.gif) no-repeat -10px 5px;
	font:normal 11px Arial;
}

/* The pointer image is hadded by using another span */
.sp_hint .hint-pointer {
    background: url(templates/bullets/pointer_black.gif) left top no-repeat;
}

/*VIÑETAS O BULLETS*/	
.mini_bullet {
 
float: left; 
width: 20px; 
height: 13px; 
background: url(templates/bullets/bullet_black.png) left top no-repeat; 
} 	 

</style>

<div id="contenido_principal">

<h1><img src="templates/iconos/Finder_black.png" width="32" height="32" /><?php echo getsessionvar("nombreusuario");?></h1>
<p class='info'><?php echo $BIBLIO_TEK_WELCOME_USERS;?></p>
<?php
	require_once( "phps/opac.php" );

	global $db;

	$id_consulta = obtener_consulta_default_x_usuario( $db, getsessionvar("id_biblioteca") );


 	if( $id_consulta != 0 )
	{	echo "<br>";
		muestra_consulta( $db, getsessionvar("id_biblioteca"), $id_consulta );
	}

	//echo "<p>&nbsp;<a href=''>Reservaciones</a></p>";

?>

<div class='caja_con_ligas'>

	<div class='lista_elementos_indexada' style='width:49%'>
		<h1><img src="templates/iconos/Bin.png"><?php echo $RECENT_USED_ITEMS;?></h1>

<?php
	 USER_HISTORY_RecentUsedItems( $db, getsessionvar("id_biblioteca"), getsessionvar("id_usuario") );
	 
?>
	</div>

	<div class='lista_elementos_indexada' style='width:48%'>
		<h1><img src="templates/iconos/Folder_black.png"><?php echo $MOST_FRECUENT_SUBJECTS;?></h1>

<?php	
	
	USER_HISTORY_MostFrequentlyViewed( $db, getsessionvar("id_biblioteca"), getsessionvar("id_usuario") );
	
	// muestra_temas_mas_consultados();
?>
	
	</div>
	
	<br clear='both'><br>
	
	<div class='lista_elementos_indexada' style='width:48%'>
		<h1><img src="templates/iconos/Download_black.png" width="32" height="32" /><?php echo $RECENT_CONTRIBUTIONS;?></h1>
	
<?php	
	
	USER_HISTORY_RecentContributions( $db, getsessionvar("id_biblioteca"), getsessionvar("id_usuario") );
	
	// muestra_contribuciones();
	
?>

	</div>
	
	<div class='lista_elementos_indexada' style='width:48%'>
		<h1><img src="templates/iconos/Billiards.png" width="32" height="32" /><?php echo $RECENT_ACTIVITIES;?></h1>

        <?php	
	USER_HISTORY_RecentActions( $db, getsessionvar("id_biblioteca"), getsessionvar("id_usuario") );
	
	// muestra_ultimas_actividades_pedagogicas();
?>

	</div>

</div><!-- caja_con_ligas -->

</div> <!-- contenido_principal -->

<div id='contenido_adicional'>

	<div class='resaltados'>
		<h2><img src="templates/iconos/Messenger.png" width="40" height="40" /><?php echo $VIRTUAL_STORAGE_SPACE;?></h2>
		<br>
<?php

	include_once "phps/bandeja.inc.php";
	paperbin_show_themes_for_user();
	echo "<br>";
	paperbin_show_contents();
	echo "<br>";
	
	echo "	<a href=''>Mis archivos...</a><br><br>";
	
	echo "	<a href=''>Historial de Actividades...</a><br><br>";
			
?>

  </div>

</div> <!-- contenido_adicional -->	