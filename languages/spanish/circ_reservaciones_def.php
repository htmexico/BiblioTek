<?php
  global $LBL_LOAN_HEADER, $LBL_LOAN_HEADER_SUB;
  
  global $LBL_USER, $LBL_DATE_RESERV, $LBL_ID_ITEM;
  global $LBL_TABLE_RESERVE, $LBL_TABLE_TITLE1, $LBL_TABLE_TITLE2;
  
  global $LBL_ITEM_ID, $LBL_CALL_NUMBER;
  
  global $ERROR_MSG_ITEM_NOT_FOUND;
  
  global $HINT_MAX_ITEMS_4_RESERVA, $HINT_MAX_ITEMS_ALREADY_HAD;
  
  global $BTN_ADD_TITULOS, $BTN_DELETE_TITULOS, $BTN_ADD_THIS_TITLE, $BTN_SAVE_RESERVA, $BTN_HIDE_BIN, $BTN_SHOW_BIN;
  
  global $BTN_CHANGE_USER;
  
  global $HINT_DELETE_TITLE, $HINTS_ITEMS_MARKED, $HINTS_ITEMS_MARKED_BIN;
  
  global $ALERT_WRONG_MAX_ITEMS_1, $ALERT_WRONG_MAX_ITEMS_2, $ALERT_WRONG_ITEM_RESERVA;
  global $ALERT_WRONG_ADD_ITEM,$ALERT_WRONG_DELETE,$ALERT_WRONG_ADD_IDUSER, $ALERT_WRONG_ADD_ITEM, $ALERT_WRONG_DUPLICATE_ITEM;
  global $ALERT_WRONG_ALL_DATE,$ALERT_WRONG_USER_STATUS,$ALERT_WRONG_USER_SANCTION,$ALERT_WRONG_USER_RESTRICTION;
  global $ALERT_WRONG_ERROR_DATE,$ALERT_WRONG_ERROR_DATE_2;
  global $ALERT_WRONG_ERROR_DATE_FORMAT,$ALERT_WRONG_ERROR_DATE_3,$ALERT_WRONG_ERROR_DATE_4;
  
  global $ALERT_NOITEMS_MARKED, $ALERT_NOITEMS_TO_SHOW, $ALERT_NO_MORE_ITEMS;
  
  global $ALERT_WRONG_ITEM_DATES_OP0, $ALERT_WRONG_ITEM_DATES_OP1, $ALERT_WRONG_ITEM_DATES_OP2;
  
  global $ALERT_NO_COPIES, $ALERT_ERRORS_IN_LIST;
  
  global $MSG_RESERVA_COMPLETED, $MSG_RESERVA_COMPLETED_HINT;
  
  global $BTN_NEW_RESERVA;

  // ESPA&ntilde;OL
  
  $LBL_LOAN_HEADER = "Reservaci&oacute;n de Material";	
  $LBL_LOAN_HEADER_SUB = "Introduzca los datos requeridos para registrar la reservacion";

  /* columnas titulos */
  $LBL_USER  			 = "Usuario";
  $LBL_DATE_RESERV		 = "Fecha de Reservacion";  
  $LBL_ID_ITEM			 = "ID del T&iacute;tulo";
  
  $LBL_TABLE_RESERVE	 = "Reservar";
  $LBL_TABLE_TITLE1		 = "Nombre del material a Reservar<br>";
  $LBL_TABLE_TITLE2		 = "Modalidad de reservaci&oacute;n";
  
  $LBL_ITEM_ID     = "C&oacute;digo del material";
  $LBL_CALL_NUMBER = "Signatura Topogr&aacute;fica";  
  
  $ERROR_MSG_ITEM_NOT_FOUND = "T&iacute;tulo no encontrado";
  
  $HINT_MAX_ITEMS_4_RESERVA   = "Max. Items que puede reservar: %s";
  $HINT_MAX_ITEMS_ALREADY_HAD = "%s Item(s) actualmente reservados";
  
  /*Botones*/
  $BTN_ADD_TITULOS     = "Agregar t&iacute;tulo";
  $BTN_DELETE_TITULOS  = "Borrar T&iacute;tulos";
  
  $BTN_ADD_THIS_TITLE = "Agregar este t&iacute;tulo";
  
  $BTN_SAVE_RESERVA = "Guardar Reservaci&oacute;n";
  
  $BTN_HIDE_BIN	    = "Ocultar Bandeja";
  $BTN_SHOW_BIN	    = "Mostrar Bandeja";
  
  $BTN_CHANGE_USER = "Cambiar de usuario";
  
  /* HINTS */
  $HINT_DELETE_TITLE = "Quitar este t&iacute;tulo";
  $HINTS_ITEMS_MARKED = "item(s) marcados para reservar.";
  $HINTS_ITEMS_MARKED_BIN = "en Bandeja.";
  
  /*Mensajes de confirmacion y de error*/
  $ALERT_WRONG_MAX_ITEMS_1	= "Solo se pueden reservar ";
  $ALERT_WRONG_MAX_ITEMS_2  = "items para el usuario. ";
 
  $ALERT_WRONG_ITEM_RESERVA		="ERROR: Se ha detectado un t&iacute;tulo como NO DISPONIBLE en las fechas solicitadas";
  
  $ALERT_WRONG_ADD_ITEM			="Favor de introducir un Identificador de item";
  $ALERT_WRONG_DELETE			="Debe seleccionar un item para borrar";
  $ALERT_WRONG_ADD_IDUSER		="Introduzca un identificador de usuario";
  $ALERT_WRONG_ADD_ITEM 		="Favor de indicar el ID del material";
  $ALERT_WRONG_DUPLICATE_ITEM	="El t&iacute;tulo con este ID ya est&aacute; en la lista";
  $ALERT_WRONG_ALL_DATE			="Favor de ingresar todas las fechas requeridas";
  $ALERT_WRONG_USER_STATUS		="El Usuario ha sido encontrado pero no esta en Status ACTIVO";
  
  $ALERT_WRONG_USER_SANCTION	="Se han encontrado sanciones a este usuario y no se puede continuar con la reservaci&oacute;n";
  $ALERT_WRONG_USER_RESTRICTION	="Se han encontrado restricciones a este usuario y no se puede continuar con la reservaci&oacute;n";
  
  $ALERT_WRONG_ERROR_DATE		="Fecha inv&aacute;lido";
  $ALERT_WRONG_ERROR_DATE_2		="Favor de ingresar una fecha posterior a la fecha de Reservacion";
  $ALERT_WRONG_ERROR_DATE_3		="Favor de ingresar primero la fecha de entrega";
  $ALERT_WRONG_ERROR_DATE_4		="Favor de ingresar una fecha posterior a la fecha de Prestamo";
  $ALERT_WRONG_ERROR_DATE_FORMAT	="Formato de fecha no v&aacute;lido (dd/mm/aaaa)";
  
  $ALERT_NOITEMS_MARKED  = "No hay items marcados.";
  $ALERT_NOITEMS_TO_SHOW = "No hay items por mostrar y marcar.";
  $ALERT_NO_MORE_ITEMS	= "El usuario ha alcanzado el n&uacute;mero de Items reservados. No se pueden reservar m&aacute;s items.";
  
  /* alertas de fechas incorrectas */
  $ALERT_WRONG_ITEM_DATES_OP0  = "Este material no est&aacute; disponible para el d&iacute;a de la reserva %s.";
  $ALERT_WRONG_ITEM_DATES_OP1  = "El material %s no est&aacute; disponible para reservaci&oacute;n.";
  $ALERT_WRONG_ITEM_DATES_OP2  = "El material s&oacute;lo est&aacute; disponible para reservaci&oacute;n en los d&iacute;as %s.";
  
  $ALERT_NO_COPIES = "No hay existencias de este t&iacute;tulo o copias disponibles para reservar. Imposible reservar.";
  
  $ALERT_ERRORS_IN_LIST = "Uno o m&aacute;s t&iacute;tulos en la lista tienen errores y no ser&aacute;n procesados. El Usuario deber&aacute; quitarlos o desmarcarlos.";
  
  /* Mensajes de confirmacion */
  $MSG_RESERVA_COMPLETED = "Reservaci&oacute;n completada";
  $MSG_RESERVA_COMPLETED_HINT = "Se han reservado %d items, con el n&uacute;mero de Reservaci&oacute;n %s."; 
  
  $BTN_NEW_RESERVA = "Realizar Nueva Reservaci&oacute;n";
  
  global $LBL_RESERVATION_MODE, $LBL_MODE_WAITINGLIST, $LBL_MODE_ONCERTAINDATE;
  $LBL_RESERVATION_MODE = "Modalidad";  
  
  $LBL_MODE_WAITINGLIST = "En lista de espera" ;
  $LBL_MODE_ONCERTAINDATE = "En una fecha específica";    
  
 ?>