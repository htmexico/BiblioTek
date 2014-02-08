<?php
	global $LBL_HEADER, $LBL_HEADER_SUB, $LBL_HEADER_COMPLETED;
	
	global $LBL_USUARIO;
	global $LBL_TIPO_SANCION;
	global $LBL_DATE_REGISTRO;
	global $LBL_DATE_LIMITE;
	global $LBL_MONTO;
	global $LBL_MOTIVO;	
	
	global $LBL_HEADER_COL_1;
	global $LBL_HEADER_COL_2;
	global $LBL_SANCION;
	global $LBL_FORMA_ECO;
	global $LBL_FORMA_SOC;
	global $LBL_FORMA_ESP;
	
	global $HINT_MAX_ITEMS_ALREADY_HAD;
	
	global $ALERT_WRONG_USER_NOT_FOUND;
	
	global $ALERT_DATERECORD_WRONGFORMAT, $ALERT_DATELIMIT_WRONGFORMAT, $ALERT_DATES_WRONG, $ALERT_CURRENCY_VALUE_NEEDED, $ALERT_DETAILS_NEEDED;
	
	global $VALIDA_MSG_STATUS, $VALIDA_MSG_NOSANCION, $VALIDA_MSG_IFGROUP;
	
	global $MSG_WANT_TO_SAVE;
	
	global $BTN_REGISTER, $BTN_NEW_SANCTION;
	
	global $HINT_DATE_REGISTRO, $HINT_DATE_LIMITE, $HINT_SELECT_SANCTION, $HINT_TYPE_AMOUNT, $HINT_CAUSE_SANCTION;
	
	global $HINT_SANCTION_CREATED;
	global $MSG_SANCTIONS_COMPLETED_HINT;

	// ESPA&ntilde;OL
    $LBL_HEADER 		= "Sanciones";	
    $LBL_HEADER_SUB 	= "Introduzca los siguientes datos para registrar una sanci&oacute;n";	
	
	$LBL_HEADER_COMPLETED = "La sanci&oacute;n se ha completado";
	
	$LBL_USUARIO = "Nombre de usuario:";
	$LBL_TIPO_SANCION = "Selecciona el tipo de sanci&oacute;n:";
	$LBL_DATE_REGISTRO = "Fecha de registro:";
	$LBL_DATE_LIMITE = "Fecha l&iacute;mite a cumplir:";
	$LBL_MONTO = "Sanci&oacute;n ";
	$LBL_MOTIVO = "Motivo de la sanci&oacute;n:";
	
	$LBL_HEADER_COL_1 = "Nombre del material en Pr&eacute;stamo";
	$LBL_HEADER_COL_2 = "Fecha de Entrega";
	$LBL_SANCION = "Entrega Atrasada de Material";
	$LBL_FORMA_ECO = "Cantidad a pagar :";
	$LBL_FORMA_SOC = "Horas por cubrir :";
	$LBL_FORMA_ESP = "Material solicitado :";
	
	$HINT_MAX_ITEMS_ALREADY_HAD	    = "%s Items actualmente prestados";
	
	$ALERT_WRONG_USER_NOT_FOUND = "El Usuario NO HA SIDO encontrado";	
	
	$ALERT_DATERECORD_WRONGFORMAT = "La fecha de registro est&aacute; escrita en un formato incorrecto";
	$ALERT_DATELIMIT_WRONGFORMAT = "La fecha l&iacute;mite est&aacute; escrita en un formato incorrecto";
	$ALERT_DATES_WRONG = "La fecha l&iacute;mite debe ser superior a la fecha de registro de la sanci&oacute;n.";
	$ALERT_CURRENCY_VALUE_NEEDED = "Este valor debe ser num&eacute;rico o monetario";
	$ALERT_DETAILS_NEEDED = "Es necesario cantidad/horas y motivo";
	
	$VALIDA_MSG_STATUS = "El Usuario ha sido encontrado pero no esta en status activo";
	$VALIDA_MSG_IFGROUP = " Este usuario pertenece a un grupo que no admite sanciones ";
	
	$VALIDA_MSG_NOSANCION = "El Usuario NO TIENE sanciones";
	
	$MSG_WANT_TO_SAVE = "¿ Desea proceder a registrar la sanci&oacute;n ?";
	
	$BTN_REGISTER = "Registrar sanci&oacute;n";
	$BTN_NEW_SANCTION = "Registrar Nueva Sanci&oacute;n";
	
	$HINT_DATE_REGISTRO   = "Indique la fecha en la que se est&aacute; registrando la sanci&oacute;n";
	$HINT_DATE_LIMITE     = "Indique la fecha l&iacute;mite para el cumplimiento o pago de la sanci&oacute;n.";
	$HINT_SELECT_SANCTION = "Elija el tipo de sanci&oacute;n";
	$HINT_TYPE_AMOUNT     = "Indique monto/horas/material para la sanci&oacute;n.";
	$HINT_CAUSE_SANCTION  = "Indique el motivo o causa que origin&oacute; la sanci&oacute;n.";
	
	$HINT_SANCTION_CREATED = "Sanci&oacute;n registrada";
	$MSG_SANCTIONS_COMPLETED_HINT = "Se ha registrado la sanci&oacute;n No. %s; El usuario ahora tiene %s sanci&oacute;n(es)."; 
	
?>
