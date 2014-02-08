<?php
  global $LBL_HEADER_V1, $LBL_HEADER_V2;
  
  global $LBL_TO_BE_ASIGNED;
  
  global $LBL_LABEL, $LBL_EVENT_TYPE, $LBL_EVENT_LOCATION, $LBL_PUBLISH_FROM, $LBL_PUBLISH_UNTIL;
  global $LBL_EVENT_SCHEDULE, $LBL_FROM_TO_CAPTION;
  global $LBL_EVENT_SCHEDULE_TIME;
  global $LBL_BRIEF_INFO, $LBL_EXTENSE_INFO;
  
  global $VALIDA_MSG_NOLABEL, $VALIDA_MSG_WRONGDATE, $VALIDA_MSG_WRONGDATE_PERIOD, $VALIDA_MSG_NOINFO_AT_BRIEF, $VALIDA_MSG_NOINFO_AT_EXTENSE;
  
  global $SAVE_EDIT_DONE, $SAVE_CREATED_DONE, $DELETE_DONE;
  
  global $MSG_ERROR_SAVING_CHANGES, $MSG_NO_PERSONS_MARKED_TO_DELETE;
  
  global $HINT_LABEL, $HINT_EVENT_TYPE, $HINT_EVENT_LOCATION, $HINT_PUBLISH_FROM, $HINT_PUBLISH_UNTIL, $HINT_SCHEDULE_TIME;
  
  global $ACTION_DESCRIP_CREATE, $ACTION_DESCRIP_EDIT, $ACTION_DESCRIP_DELETE;

  // ESPA&ntilde;OL
  
  // PARA EDITAR 
  $LBL_HEADER_V1 = "Agregar un evento";	
  $LBL_HEADER_V2 = "Modificar un evento";	
   
  $LBL_LABEL 	     = "Etiqueta para mostrar";
  $LBL_EVENT_TYPE    = "Tipo de Evento";
  $LBL_PUBLISH_FROM  = "Publicarse desde";
  $LBL_PUBLISH_UNTIL = "Publicarse hasta";
  
  $LBL_EVENT_LOCATION = "Ubicaci&oacute;n del evento";
  $LBL_EVENT_SCHEDULE = "Agenda del Evento";
  $LBL_FROM_TO_CAPTION = "a";
  $LBL_EVENT_SCHEDULE_TIME = "Horario";
  $LBL_BRIEF_INFO     = "Informaci&oacute;n Breve (p&aacute;gina principal)";
  $LBL_EXTENSE_INFO   = "Informaci&oacute;n Ampliada"; 

  $VALIDA_MSG_NOLABEL = "Se necesita colocar una etiqueta descriptiva para el evento.";
  $VALIDA_MSG_WRONGDATE = "El valor de la fecha es incorrecto";
  $VALIDA_MSG_WRONGDATE_PERIOD = "El periodo de tiempo no es coherente. La fecha final de la publicaci&oacute;n debe ser superior a la fecha inicial.";
  $VALIDA_MSG_NOINFO_AT_BRIEF = "Es necesario colocar un resumen muy breve acerca del evento";
  $VALIDA_MSG_NOINFO_AT_EXTENSE = "No ha colocado informaci&oacute;n ampliada con todos los detalles de su evento, se sugiere hacerlo. ¿Desea continuar sin esta informaci&oacute;n?";

  $SAVE_EDIT_DONE    = "Los datos del evento fueron modificados.";
  $SAVE_CREATED_DONE = "Los datos del evento fueron creados.";
  $DELETE_DONE       = "Se eliminaron los eventos.";

  $MSG_ERROR_SAVING_CHANGES       = "Error al guardar los cambios";
  $MSG_NO_PERSONS_MARKED_TO_DELETE = "Es necesario marcar uno o m&aacute;s personas para poder borrarlas";

  $HINT_LABEL 		  = "Etiqueta descriptiva que se mostrar&aacute; en la p&aacute;gina de v&iacute;nculos";
  $HINT_EVENT_TYPE	  = "Elija el tipo de evento de acuerdo a la lista (proviene de la categor&iacute;a 22 del Tesauro General).";
  $HINT_EVENT_LOCATION  = "Elija la ubicaci&oacute;n donde se desarrollar&aacute; el evento.";
  $HINT_PUBLISH_FROM  = "Fecha a partir de la cual se publicar&aacute;";
  $HINT_PUBLISH_UNTIL = "Fecha hasta la cual se publicar&aacute;";
  $HINT_SCHEDULE_TIME = "Coloque la hora en la que se desarrollar&aacute; el evento en formato hh:mm de 24 hrs.";

  $ACTION_DESCRIP_CREATE  = "Se cre&oacute; el evento ";
  $ACTION_DESCRIP_EDIT    = "Realiz&oacute; cambios al evento ";
  $ACTION_DESCRIP_DELETE  = "Elimin&oacute; eventos: ";
  
 ?>