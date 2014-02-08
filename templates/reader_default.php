<style>
	.classxyz { props; } 

/***
  COLOCAR EN SCREEN.CSS
 */
/* The hint to Hide and Show */
.sp_hint {
   	display: none;
    position: absolute;
    right: 10px;
    width: 200px;
    margin-top: -4px;
    border: 1px solid #c93;
    padding: 10px 12px;
    /* to fix IE6, I can't just declare a background-color,
    I must do a bg image, too!  So I'm duplicating the pointer.gif
    image, and positioning it so that it doesn't show up
    within the box */
    background: #ffc url(../images/pointer.gif) no-repeat -10px 5px;
}

/* The pointer image is hadded by using another span */
.sp_hint .hint-pointer {
    position: absolute;
    left: -10px;
    top: 5px;
    width: 10px;
    height: 19px;
    background: url(templates/bullets/pointer.gif) left top no-repeat;
}
/*VIÑETAS O BULLETS*/	
.mini_bullet {
 
float: left; 
width: 20px; 
height: 13px; 
background: url(templates/bullets/mini_bullet.png) left top no-repeat; 
} 	
</style>

<div id="contenido_principal">

<?php

	require_once( "phps/opac.php" );
	global $db;
	global $user;

 ?>

<h1>
	<?php echo getsessionvar("nombreusuario");?>
</h1>

	<p><strong><?php echo $user->NOMBRE_GRUPO; ?></strong></p><br>
	
	<?php display_personal_info();?>

<?php
	
	$id_consulta = obtener_consulta_default_x_usuario( $db, getsessionvar("id_biblioteca") );

 	if( $id_consulta != 0 )
	{	
		echo "<br>";
		muestra_consulta( $db, getsessionvar("id_biblioteca"), $id_consulta );
	}

?>

<div class='caja_con_ligas'>

	<div class='lista_elementos_indexada' style='width:49%'>
		<h1><?php echo $RECENT_USED_ITEMS;?></h1>

<?php
    
    USER_HISTORY_RecentUsedItems( $db, getsessionvar("id_biblioteca"), getsessionvar("id_usuario") );
	
?>

	</div>

	<div class='lista_elementos_indexada' style='width:48%'>
		<h1><?php echo $MOST_FRECUENT_SUBJECTS;?></h1>

<?php	
	
	USER_HISTORY_MostFrequentlyViewed( $db, getsessionvar("id_biblioteca"), getsessionvar("id_usuario") );

?>
	
	</div>
	
	<br clear='both'><br>
	
	<div class='lista_elementos_indexada' style='width:48%'>
		<h1><?php echo $RECENT_CONTRIBUTIONS;?></h1>
	
<?php	
	
	USER_HISTORY_RecentContributions( $db, getsessionvar("id_biblioteca"), getsessionvar("id_usuario") );

?>

	</div>
	
	<div class='lista_elementos_indexada' style='width:48%'>
		<h1><?php echo $RECENT_ACTIVITIES;?></h1>

		<?php	
			USER_HISTORY_RecentActions( $db, getsessionvar("id_biblioteca"), getsessionvar("id_usuario") );
		?>

	</div>

</div><!-- caja_con_ligas -->

</div> <!-- contenido_principal -->

<div id='contenido_adicional'>

	<div class='resaltados'>
		<h2><?php echo $VIRTUAL_STORAGE_SPACE;?></h2><br>
<?php

	include_once "phps/bandeja.inc.php";
	
	paperbin_show_themes_for_user();
	echo "<br>";
	paperbin_show_contents();
	echo "<br>";
		
	display_personal_links( $db );
			
?>

	</div>

</div> <!-- contenido_adicional -->	