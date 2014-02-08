<?php
  global $LBL_LOAN_HEADER, $LBL_LOAN_HEADER_SUB;
  
  global $LBL_ID_USER, $LBL_DATE_LOAN, $LBL_ID_ITEM;
  global $LBL_TABLE_TITLE0, $LBL_TABLE_TITLE1, $LBL_TABLE_TITLE2;
  
  global $LBL_ITEM_ID, $LBL_CALL_NUMBER;
  
  global $BTN_VALIDATE, $BTN_ADD_LOAN, $BTN_DELETE_ITEM, $BTN_LOAD_BLOCKED_ITEMS;
  
  global $INFO_STEP_ONE;
  
  global $HINT_TYPE_CURRENT_DATE, $HINT_TYPE_MATERIAL_CODE;
  
  global $HINT_MAX_ITEMS_4_LOAN, $HINT_MAX_ITEMS_ALREADY_HAD, $HINT_ITEMS_BLOCKED;
  global $HINT_DELETE_ITEM;

  global $ALERT_ON_LIMIT_MAX, $ALERT_WRONG_STATE_CHANGED;
  global $ALERT_WRONG_ADD_ITEM,$ALERT_WRONG_DELETE,$ALERT_WRONG_ADD_IDUSER, $ALERT_WRONG_ADD_ITEM;
  global $ALERT_WRONG_ALL_DATE,$ALERT_USER_NOT_ALLOW_LOANS,$ALERT_WRONG_USER_STATUS,$ALERT_WRONG_USER_SANCTION,$ALERT_WRONG_USER_RESTRICTION;
  global $ALERT_WRONG_USER_NOT_FOUND,$ALERT_WRONG_ERROR_DATE,$ALERT_WRONG_ERROR_DATE_2;
  global $ALERT_WRONG_ERROR_DATE_FORMAT;
  
  global $ALERT_WRONG_ITEM_NOT_FOUND, $ALERT_WRONG_ITEM_DATES_OP0, $ALERT_WRONG_ITEM_DATES_OP1, $ALERT_WRONG_ITEM_DATES_OP2;
  global $ALERT_WRONG_DEV_DATE;
  global $ALERT_WRONG_DUPLICATION;
  
  global $ALERT_ERRORS_IN_LIST, $ALERT_ERRORS_NOT_ITEMS_IN_LIST;
  
  global $MSG_CONFIRM_BEFORE_LOAN;
  
  global $LBL_BEFORE_TIME;
  
  global $MSG_LOAN_COMPLETED, $MSG_LOAN_COMPLETED_HINT;
  global $BTN_NEW_LOAN, $BTN_CHANGE_USER;

  // ESPA&ntilde;OL
  
  $LBL_LOAN_HEADER 		= "Pr&eacute;stamos";	
  $LBL_LOAN_HEADER_SUB 	= "Introduzca los datos requeridos para registrar el pr&eacute;stamo";

  /* columnas titulos */
  $LBL_ID_USER  			 = "Usuario";
  $LBL_DATE_LOAN 		     = "Fecha de Pr&eacute;stamo";  
  $LBL_ID_ITEM				 = "Material a Prestar";
  $LBL_TABLE_TITLE0		     = "Imagen";
  $LBL_TABLE_TITLE1			 = "Nombre del material a Prestar";
  $LBL_TABLE_TITLE2			 = "Fecha de devoluci&oacute;n";
  
  $LBL_ITEM_ID     = "C&oacute;digo del material";
  $LBL_CALL_NUMBER = "Signatura Topogr&aacute;fica";
  
  /*Botones*/
  $BTN_VALIDATE = "Validar";
  $BTN_ADD_LOAN    	= "Registrar el Pr&eacute;stamo";
  $BTN_DELETE_ITEM  = "Borrar Item";  
  $BTN_LOAD_BLOCKED_ITEMS = "Cargar Items Apartados";
  
  /* Mensajes de confirmacion y de error */
  $INFO_STEP_ONE					= "PASO 1: Deber&aacute; elegir el usuario que recibir&aacute; el pr&eacute;stamo.";
  
  $HINT_TYPE_CURRENT_DATE 			= "Indique la fecha de hoy, en la cual se registrar&aacute; el pr&eacute;stamo.";
  $HINT_TYPE_MATERIAL_CODE          = "Indique el c&oacute;digo del material.";
  
  $HINT_MAX_ITEMS_4_LOAN		    = "Max. Items por prestar: %s";
  $HINT_MAX_ITEMS_ALREADY_HAD	    = "%s Items actualmente prestados";
  $HINT_ITEMS_BLOCKED				= "El usuario tiene %s items reservados y apartados.";
  
  $HINT_DELETE_ITEM				    = "Eliminar este item";
  
  $ALERT_ON_LIMIT_MAX				= "Ahora se ha alcanzado el l&iacute;mite de items. Solo se pueden prestar %s items a este usuario.";
  $ALERT_WRONG_STATE_CHANGED		= "ERROR: El status de un item ha cambiado de &uacute;ltimo momento.";
  $ALERT_WRONG_DELETE				= "Debe seleccionar un libro para borrar";
  $ALERT_WRONG_ADD_IDUSER			= "Introduzca un identificador de usuario";
  $ALERT_WRONG_ADD_ITEM 			= "Favor de seleccionar material";
  $ALERT_WRONG_ALL_DATE				= "Favor de ingresar todas las fechas requeridas";
  $ALERT_USER_NOT_ALLOW_LOANS	    = "El usuario no admite pr&eacute;stamo de material";
  $ALERT_WRONG_USER_STATUS			= "El Usuario ha sido encontrado pero no esta en Status Activo";
  $ALERT_WRONG_USER_SANCTION		= "Se han encontrado sanciones a este usuario y no se puede continuar con el pr&eacute;stamo";
  $ALERT_WRONG_USER_RESTRICTION		= "Se han encontrado restricciones a este usuario y no se puede continuar con el pr&eacute;stamo";
  $ALERT_WRONG_USER_NOT_FOUND		= "El Usuario NO HA SIDO encontrado.";
  $ALERT_WRONG_ERROR_DATE			= "Fecha inv&aacute;lida";
  $ALERT_WRONG_ERROR_DATE_2			= "Favor de ingresar una fecha posterior a la fecha de pr&eacute;stamo";
  $ALERT_WRONG_ERROR_DATE_FORMAT	= "Formato de fecha no v&aacute;lido";
  
  $ALERT_WRONG_ITEM_NOT_FOUND  = "El material no fue encontrado";
  
  $ALERT_WRONG_ITEM_DATES_OP0  = "Este material no est&aacute; disponible para el d&iacute;a del pr&eacute;stamo %s.";
  $ALERT_WRONG_ITEM_DATES_OP1  = "El material %s no est&aacute; disponible para pr&eacute;stamo.";
  $ALERT_WRONG_ITEM_DATES_OP2  = "El material s&oacute;lo est&aacute; disponible para pr&eacute;stamo en los d&iacute;as %s.";
  
  $ALERT_WRONG_DEV_DATE = "Por favor modifique la fecha de devoluci&oacute;n";
  $ALERT_WRONG_DUPLICATION = "El ITEM %s ya est&aacute; en la lista";
  
  $ALERT_ERRORS_IN_LIST = "Uno o m&aacute;s items tienen errores y no ser&aacute;n procesados. Se requiere acci&oacute;n del Usuario.";
  $ALERT_ERRORS_NOT_ITEMS_IN_LIST = "No hay items en la lista. No se puede registrar el pr&eacute;stamo";
  
  $MSG_CONFIRM_BEFORE_LOAN    = "¿ Desea continuar con el registro del pr&eacute;stamo ?";
  
  $LBL_BEFORE_TIME = "Antes de las ";
  
  /* Mensajes de confirmacion */
  $MSG_LOAN_COMPLETED = "Pr&eacute;stamo completado";
  $MSG_LOAN_COMPLETED_HINT = "Se han prestado %d items, con el n&uacute;mero de Pr&eacute;stamo %s."; 
    
  $BTN_NEW_LOAN = "Realizar Nuevo Pr&eacute;stamo";    
  $BTN_CHANGE_USER = "Cambiar de Usuario";
  
 ?>