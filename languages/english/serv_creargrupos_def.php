<?php
  global $PAGE_TITLE_GRUPOS;
  global $LBL_HEADER_V1, $LBL_HEADER_V2;
  
  global $LBL_TO_BE_ASIGNED;
  
  global $LBL_ID_GRUPO;
  global $LBL_NOMBRE_GRUPO;
  
  global $LBL_PERMITIR_PRESTAMOS, $LBL_MAX_DIAS_PRESTAMO, $LBL_MAX_ITEMS_PRESTADOS, $LBL_PERMITIR_PRESTAMOS_RETRASOS, $LBL_PERMITIR_PRESTAMOS_SANCIONES;
  
  global $LBL_MAX_RESERVACIONES, $LBL_PERMITIR_RESERVAS_SANCIONES;
  
  global $LBL_MAX_RENOVACIONES, $LBL_DIAS_RENOVACION_DEFAULT, $LBL_PERMITIR_RENOVA_RETRASOS, $LBL_PERMITIR_RENOVA_SANCIONES;
  global $LBL_SANCION_ECONOMICA, $LBL_SANCION_HORAS, $LBL_SANCION_ESPECIE, $LBL_SANCION_X_RETRASO_DEV, $LBL_PERMITIR_COMENTARIOS, $LBL_USUARIOS_ADMVOS, $HINT_NO_SANCTION;
  
  global $LBL_EMAIL_NOTIFICATIONS;
  global $LBL_NOTIFY_ON_RESERVAS, $LBL_NOTIFY_ON_LOANS, $LBL_NOTIFY_ON_RENEWALS, $LBL_NOTIFY_ON_DELAYS, $LBL_NOTIFY_ON_DEVOLUTIONS, $LBL_NOTIFY_ON_RESTRICTIONS, $LBL_NOTIFY_ON_SANCTIONS;
  
  global $VALIDA_MSG_21;
  
  global $SAVE_EDIT_DONE, $SAVE_CREATED_DONE, $DELETE_DONE;
  
  global $MSG_ERROR_SAVING_CHANGES, $MSG_NO_GROUPS_MARKED_TO_DELETE;
  
  global $HINT_NOMBRE_GRUPO, $HINT_MAX_DIAS_PRESTAMO, $HINT_MAX_ITEMS_PRESTADOS, $HINT_MAX_RESERVACIONES;
  global $HINT_MAX_RENOVACIONES, $HINT_DIAS_RENOVACION_DEFAULT, $HINT_SANCION_ECONOMICA, $HINT_SANCION_HORAS, $HINT_SANCION_ESPECIE;
  
  global $ACTION_DESCRIP_CREATE, $ACTION_DESCRIP_EDIT, $ACTION_DESCRIP_DELETE;

  // ENGLISH
  $PAGE_TITLE_GRUPOS = "Setting up Groups of Users";
  
  // PARA EDITAR 
  $LBL_HEADER_V1 = "Create new users group";	
  $LBL_HEADER_V2 = "Modify Group";	
  
  $LBL_TO_BE_ASIGNED = "[To be asigned]";
  
  $LBL_ID_GRUPO  	        = "Group ID";
  $LBL_NOMBRE_GRUPO         = "Group Name";  
  $LBL_USUARIOS_ADMVOS	= "Marque esta casilla si los usuarios son administrativos (operadores del sistema)";
  
  $LBL_PERMITIR_PRESTAMOS   = "Permitir acceder a pr&eacute;stamos";
  $LBL_MAX_DIAS_PRESTAMO    = "Max. loan days";
  $LBL_MAX_ITEMS_PRESTADOS	= "Max. loan items";
  $LBL_PERMITIR_PRESTAMOS_RETRASOS  = "Permitir pr&eacute;stamos con Retraso";
  $LBL_PERMITIR_PRESTAMOS_SANCIONES = "Permitir pr&eacute;stamos con sanciones";
  
  $LBL_MAX_RESERVACIONES	= "Max. Reservations";
  $LBL_PERMITIR_RESERVAS_SANCIONES = "Permitir Reservas con Sanciones";
  
  $LBL_MAX_RENOVACIONES     = "Max. Renovations";
  $LBL_DIAS_RENOVACION_DEFAULT = "D&iacute;as de renovaci&oacute;n Default";
  $LBL_PERMITIR_RENOVA_RETRASOS = "Permitir renovaci&oacute;n con Retraso";
  $LBL_PERMITIR_RENOVA_SANCIONES = "Permitir renovaci&oacute;n con Retraso";
  
  $LBL_PERMITIR_COMENTARIOS = "Permitir a los usuarios realizar comentarios a alg&uacute;n material";
  
  $LBL_SANCION_ECONOMICA    = "Money fine";
  $LBL_SANCION_HORAS        = "Time/Service fine";
  $LBL_SANCION_ESPECIE	 	= "Permitir Sanci&oacute;n en Especie";
  $LBL_SANCION_X_RETRASO_DEV = "Sanci&oacute;n que se aplicar&aacute; por retraso en devoluci&oacute;n de material";  
  
  $LBL_EMAIL_NOTIFICATIONS    = "Notificaciones Autom&aacute;ticas por Email";
  $LBL_NOTIFY_ON_RESERVAS     = "Al hacer una Reservaci&oacute;n";
  $LBL_NOTIFY_ON_LOANS        = "Al obtener un Pr&eacute;stamos";
  $LBL_NOTIFY_ON_RENEWALS     = "Al renovar un Pr&eacute;stamo";
  $LBL_NOTIFY_ON_DELAYS       = "Al retrasarse en devolver alg&uacute;n material";
  $LBL_NOTIFY_ON_DEVOLUTIONS  = "Al realizar una devoluci&oacute;n";
  $LBL_NOTIFY_ON_RESTRICTIONS = "Al registrar una Restricci&oacute;n";
  $LBL_NOTIFY_ON_SANCTIONS    = "Al registrar una Sanci&oacute;n";

  $HINT_NO_SANCTION = "No aplicar&aacute; Sanci&oacute;n";  

  $VALIDA_MSG_21 = "Group Name is needed";
  
  $SAVE_EDIT_DONE    = "Los datos del grupo de usuarios fueron modificados.";
  $SAVE_CREATED_DONE = "Los datos del grupo de usuarios fueron creados.";
  $DELETE_DONE       = "Se eliminaron los grupos.";
  
  $MSG_ERROR_SAVING_CHANGES       = "Error al guardar los cambios";
  $MSG_NO_GROUPS_MARKED_TO_DELETE = "Es necesario marcar uno o m&aacute;s grupos para poder borrarlos";
  
  $HINT_NOMBRE_GRUPO        = "Nombre que se asignar&aacute; al grupo de personas.";
  
  $HINT_MAX_DIAS_PRESTAMO   = "Maximo n&uacute;mero de d&iacute;as permitidos para un pr&eacute;stamo.";
  $HINT_MAX_ITEMS_PRESTADOS = "M&aacute;ximo n&uacute;mero de items que podr&aacute;n tener en pr&eacute;stamo simult&aacute;neamente.";
  $HINT_MAX_RESERVACIONES   = "M&aacute;ximo n&uacute;mero de items que podr&aacute;n tener reservados.";
  $HINT_MAX_RENOVACIONES    = "M&aacute;ximo n&uacute;mero de renovaciones que podr&aacute;n hacer por cada item prestado.";
  $HINT_DIAS_RENOVACION_DEFAULT = "Indique los d&iacute;as por default que podr&aacute;n extender como pr&oacute;rroga al hacer una renovaci&oacute;n";
  
  $HINT_SANCION_ECONOMICA   = "Marcar esta casilla si los integrantes del grupo pueden hacerse acreedores a sanciones econ&oacute;micas";
  $HINT_SANCION_HORAS       = "Marcar esta casilla si los integrantes del grupo pueden hacerse acreedores a sanciones en horas de servicio"; 
  $HINT_SANCION_ESPECIE	    = "Marcar esta casilla si los integrantes del grupo pueden hacerse acreedores a sanciones en especie";
  
  $ACTION_DESCRIP_CREATE  = "Cre&oacute; Grupo";
  $ACTION_DESCRIP_EDIT    = "Cambios a";
  $ACTION_DESCRIP_DELETE  = "Elimin&oacute; grupos:";
  
 ?>