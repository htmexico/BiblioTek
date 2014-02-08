<?php

	/******
	  Variables heredades de archivo gral_vertitulo.php

	  $user = TUser
	  $titulo = TItemBasic

	***/

?>
	<div>
		<span class='subtitle'><?php echo $LBL_COMMENTS_WRITE_REVIEW;?></span><br><br>
		
		<form name='post_comment' id='post_comment' action='gral_vertitulo.php' method='post'>

<!-- BEGIN - AUX FIELDS -->		
			<input type='hidden' name='id_titulo' id='id_titulo' value='<?php echo $id_titulo;?>'>
			<input type='hidden' name='id_consulta' id='id_consulta' value='<?php echo $id_consulta;?>'>
			<input type='hidden' name='search' id='search' value='<?php echo $search;?>'>
			<input type='hidden' name='marc' id='marc' value='<?php echo $marc;?>'>
			<input type='hidden' name='aacr2' id='aacr2' value='<?php echo $aacr2;?>'>
<!-- END - AUX FIELDS -->			
			
			<input type='hidden' name='id_usuario' id='id_usuario' value='<?php echo $id_usuario;?>'>
			<input type='hidden' name='txt_qualif_value' id='txt_qualif_value' value='0'>
			<input type='hidden' name='the_action' id='the_action' value='save_comments'>
			
			<div style='float:left; display:inline; width:130px; height:20px;'><?php echo $LBL_COMMENTS_YOURNAME;?>&nbsp</div>
			<div style='display:inline;'><strong><?php echo $user->NOMBRE_COMPLETO;?></strong></div><br style='clear:both;'>
			
			<div style='float:left; display:inline; width:130px; height:20px;'><?php echo $LBL_COMMENTS_EMAIL;?>&nbsp</div>
			<div style='display:inline;'><strong><?php echo $user->EMAIL;?></strong></div><br><br>
			
			<!-- resumen -->
			<div style='display:block; height:20px;'><strong><?php echo $LBL_COMMENTS_COMMENTS;?></strong></div>
			<input type='text' class='campo_captura' name='txt_comments' id='txt_comments' size=95>
			
			<!-- comentarios -->
			<div style='display:block; height:20px;'><strong><?php echo $LBL_COMMENTS_SUMMARY;?></strong></div>
			<textarea rows='10' cols='90' name='txt_summary' id='txt_summary'></textarea>
			
			<!-- calificacion -->
			<br><br>
			<div style='display:inline; height:20px;'><strong><?php echo $LBL_COMMENTS_RATE;?></strong></div>
			<div style='display:block;'>
				<div style='display:inline;' name='value_qualif_one' id='value_qualif_one' onClick='javascript:qualify_on(1);'><img src='../images/icons/star_empty.png'></div>
				<div style='display:inline;' name='value_qualif_two' id='value_qualif_two' onClick='javascript:qualify_on(2);'><img src='../images/icons/star_empty.png'></div>
				<div style='display:inline;' name='value_qualif_three' id='value_qualif_three' onClick='javascript:qualify_on(3);'><img src='../images/icons/star_empty.png'></div>
				<div style='display:inline;' name='value_qualif_four'  id='value_qualif_four' onClick='javascript:qualify_on(4);'><img src='../images/icons/star_empty.png'></div>
				<div style='display:inline;' name='value_qualif_five'  id='value_qualif_five' onClick='javascript:qualify_on(5);'><img src='../images/icons/star_empty.png'></div>
				<div style='display:inline; padding-left:5px; padding-right:5px; margin-left:10px; border:1px dotted gray; position:relative; top: -5px;' name='final_value_qualif' id='final_value_qualif'>0.00</div>
			</div>
			
			<br>
			<div style='margin-left: 28em;'>
			<input type='button' class='boton' value="<?php echo $BTN_SAVE_COMMENTS;?>" onClick='javascript:saveComments();'>
			</div>
			<br>
			
			<span>
				<ul>
					<li>Las direcciones de las páginas web y las de correo se convierten en enlaces automáticamente.</li>
					<li>Etiquetas HTML permitidas: &lt;a&gt; &lt;h1&gt; &lt;h2&gt; &lt;h3&gt; &lt;h4&gt; &lt;em&gt; &lt;strong&gt; &lt;cite&gt; &lt;code&gt; &lt;ul&gt; &lt;ol&gt; &lt;li&gt; &lt;dl&gt; &lt;dt&gt; &lt;dd&gt;</li>
					<li>Saltos automáticos de líneas y de párrafos.</li>
					<li>Un par de etiquetas &lt;blockquote&gt; serán trasladadas como un bloque que indica un cita.</li>
					<li>You can use Markdown syntax to format and style the text. Also see Markdown Extra for tables, footnotes, and more.</li>
				</ul><br>
				<!--<a href=''>Más información sobre opciones de formato...</a> -->
			</span>
			<br>
			
		</form>
	</div>

<?php
			
?>