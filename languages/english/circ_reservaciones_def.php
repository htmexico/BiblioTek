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
  
  $LBL_LOAN_HEADER = "Reservations";	
  $LBL_LOAN_HEADER_SUB = "Enter all required data for registering the operation";

  /* columnas titulos */
  $LBL_USER  			 = "User Information";
  $LBL_DATE_RESERV		 = "Reservation Date";  
  $LBL_ID_ITEM			 = "Title's ID";
  
  $LBL_TABLE_RESERVE	 = "Reserv";
  $LBL_TABLE_TITLE1		 = "Material Description<br>";
  $LBL_TABLE_TITLE2		 = "Mode of Reservation";
  
  $LBL_ITEM_ID     = "C&oacute;digo del material";
  $LBL_CALL_NUMBER = "Signatura Topogr&aacute;fica";  
  
  $ERROR_MSG_ITEM_NOT_FOUND = "T&iacute;tulo no encontrado";
  
  $HINT_MAX_ITEMS_4_RESERVA   = "Max. Items allowed for reservas: %s";
  $HINT_MAX_ITEMS_ALREADY_HAD = "%s Item(s) currently reserved";
  
  /*Botones*/
  $BTN_ADD_TITULOS     = "Add Title";
  $BTN_DELETE_TITULOS  = "Delete Title";
  
  $BTN_ADD_THIS_TITLE = "Agregar este t&iacute;tulo";
  
  $BTN_SAVE_RESERVA = "Save Reservation";
  
  $BTN_HIDE_BIN	    = "Hide Personal bin";
  $BTN_SHOW_BIN	    = "Show Pesonal bin";
  
  $BTN_CHANGE_USER = "Change of user";
  
  /* HINTS */
  $HINT_DELETE_TITLE = "Remove this title";
  $HINTS_ITEMS_MARKED = "item(s) marked for reservs.";
  $HINTS_ITEMS_MARKED_BIN = " in Bin.";
  
  /*Mensajes de confirmacion y de error*/
  $ALERT_WRONG_MAX_ITEMS_1	= "The allowed items for reservation are ";
  $ALERT_WRONG_MAX_ITEMS_2  = "items for this user. ";
 
  $ALERT_WRONG_ITEM_RESERVA		="Error: It's been detected a not AVAILABLE title on requested dates";
  
  $ALERT_WRONG_ADD_ITEM			="Please enter an item ID or perform a full search";
  $ALERT_WRONG_DELETE			="You must select an item to Remove";
  $ALERT_WRONG_ADD_IDUSER		="Please enter an user name";
  $ALERT_WRONG_ADD_ITEM 		="Please enter the material ID";
  $ALERT_WRONG_DUPLICATE_ITEM	="This title's ID is already on your list";
  $ALERT_WRONG_ALL_DATE			="Please enter the requested dates";
  $ALERT_WRONG_USER_STATUS		="The user has been found but his/her status is not ACTIVE";
  
  $ALERT_WRONG_USER_SANCTION	="They're some sanctions for this user and it's impossible to continue with the reservation process";
  $ALERT_WRONG_USER_RESTRICTION= "They're some restrictions for this user and it's impossible to continue with the reservation process";
  
  $ALERT_WRONG_ERROR_DATE		= "Invalid date";
  $ALERT_WRONG_ERROR_DATE_2		= "Favor de ingresar una fecha posterior a la fecha de Reservacion";
  $ALERT_WRONG_ERROR_DATE_3		= "Favor de ingresar primero la fecha de entrega";
  $ALERT_WRONG_ERROR_DATE_4		= "Favor de ingresar una fecha posterior a la fecha de Prestamo";
  $ALERT_WRONG_ERROR_DATE_FORMAT	="This date format is invalid (mm/dd/aaaa)";
  
  $ALERT_NOITEMS_MARKED  = "There's no marked items";
  $ALERT_NOITEMS_TO_SHOW = "NO items to be shown and marked";
  $ALERT_NO_MORE_ITEMS	= "The user has reached the maximun amount of items in his/her reservation list. You can't reserve any more.";
  
  /* alertas de fechas incorrectas */
  $ALERT_WRONG_ITEM_DATES_OP0  = "This material is not available for the requested reservation day %s.";
  $ALERT_WRONG_ITEM_DATES_OP1  = "The material marked as %s is't available for reservations.";
  $ALERT_WRONG_ITEM_DATES_OP2  = "The material is only available for reservations on these days %s.";
  
  $ALERT_NO_COPIES = "No hay existencias de este t&iacute;tulo o copias disponibles para reservar. Imposible reservar.";
  
  $ALERT_ERRORS_IN_LIST = "Uno o m&aacute;s t&iacute;tulos en la lista tienen errores y no ser&aacute;n procesados. El Usuario deber&aacute; quitarlos o desmarcarlos.";
  
  /* Mensajes de confirmacion */
  $MSG_RESERVA_COMPLETED = "Reservation was completed";
  $MSG_RESERVA_COMPLETED_HINT = "It's has been reserved  %d items, with the following reservation number: %s."; 
  
  $BTN_NEW_RESERVA = "Make new reservation";
  
  global $LBL_RESERVATION_MODE, $LBL_MODE_WAITINGLIST, $LBL_MODE_ONCERTAINDATE;
  $LBL_RESERVATION_MODE = "Mode";
  
  $LBL_MODE_WAITINGLIST = "On wating list" ;
  $LBL_MODE_ONCERTAINDATE = "On a specific date";  
  
 ?>