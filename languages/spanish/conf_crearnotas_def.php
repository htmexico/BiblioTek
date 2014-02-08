<?php
  global $LBL_HEADER_V1, $LBL_HEADER_V2;
  global $LBL_TO_BE_ASIGNED;
  
  global $LBL_LABEL, $LBL_PUBLISH_FROM, $LBL_PUBLISH_UNTIL;
  global $LBL_BRIEF_INFO;
  
  global $VALIDA_MSG_NOLABEL, $VALIDA_MSG_WRONGDATE, $VALIDA_MSG_WRONGDATE_PERIOD, $VALIDA_MSG_NOINFO_AT_BRIEF;
  
  global $SAVE_EDIT_DONE, $SAVE_CREATED_DONE, $DELETE_DONE;
  global $MSG_ERROR_SAVING_CHANGES, $MSG_NO_PERSONS_MARKED_TO_DELETE;
  
  global $HINT_LABEL, $HINT_PUBLISH_FROM, $HINT_PUBLISH_UNTIL;
  global $ACTION_DESCRIP_CREATE, $ACTION_DESCRIP_EDIT, $ACTION_DESCRIP_DELETE;

  // ESPA&ntilde;OL
  
  // PARA EDITAR 
  $LBL_HEADER_V1 = "Agregar una nota";	
  $LBL_HEADER_V2 = "Modificar una nota";	
   
  $LBL_LABEL 	     = "Etiqueta para mostrar";
  $LBL_PUBLISH_FROM  = "Publicarse desde";
  $LBL_PUBLISH_UNTIL = "Publicarse hasta";
  
  $LBL_BRIEF_INFO     = "Informaci&oacute;n Breve (p&aacute;gina principal)";
  $LBL_EXTENSE_INFO   = "Informaci&oacute;n Ampliada"; 

  $VALIDA_MSG_NOLABEL = "Se necesita colocar una etiqueta descriptiva para la nota.";
  $VALIDA_MSG_WRONGDATE = "El valor de la fecha es incorrecto";
  $VALIDA_MSG_WRONGDATE_PERIOD = "El periodo de tiempo no es coherente. La fecha final de la publicaci&oacute;n debe ser superior a la fecha inicial.";
  $VALIDA_MSG_NOINFO_AT_BRIEF = "Es necesario colocar el resumen respecto al tema de la nota";

  $SAVE_EDIT_DONE    = "Los datos de la nota fueron modificados.";
  $SAVE_CREATED_DONE = "Los datos de la nota fueron creados.";
  $DELETE_DONE       = "Se eliminaron las notas.";

  $MSG_ERROR_SAVING_CHANGES        = "Error al guardar los cambios";
  $MSG_NO_PERSONS_MARKED_TO_DELETE = "Es necesario marcar uno o m&aacute;s personas para poder borrarlas";

  $HINT_LABEL 		    = "Etiqueta descriptiva que se mostrar&aacute; en la p&aacute;gina de notas. Puede incluir c&oacute;digo HTML.";
  $HINT_PUBLISH_FROM    = "Fecha a partir de la cual se publicar&aacute;";
  $HINT_PUBLISH_UNTIL   = "Fecha hasta la cual se publicar&aacute;";

  $ACTION_DESCRIP_CREATE = "Se cre&oacute; la nota ";
  $ACTION_DESCRIP_EDIT   = "Realiz&oacute; cambios la nota ";
  $ACTION_DESCRIP_DELETE = "Elimin&oacute; notas: ";
  
 ?>