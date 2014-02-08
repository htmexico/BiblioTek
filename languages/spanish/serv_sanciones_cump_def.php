<?php
	global $LBL_HEADER, $LBL_SUB_HEADER, $LBL_HEADER_COMPLETED;
	global $LBL_IDUSUARIO;
	global $LBL_TIPO_SANCION;
	global $LBL_DATE_ACOMPLISH;
	global $LBL_DATE_LIMITE;
	global $LBL_MONTO;
	global $LBL_MOTIVO;
	global $VALOR;

	global $LBL_HEADER_COL_0, $LBL_HEADER_COL_1, $LBL_HEADER_COL_2, $LBL_HEADER_COL_3, $LBL_HEADER_COL_4, $LBL_HEADER_COL_5;
	global $LBL_SANCION;
	global $LBL_ECONOMICA;
	global $LBL_SOCIAL;
	global $LBL_ESPECIE;
	global $LBL_DETAILS_ACOMPLISH;
	
	global $HINT_MAX_ITEMS_ALREADY_HAD;
	
	global $BTN_DONE;
	global $BTN_NEW_SANCTION;

	global $VALIDA_MSG_STATUS;
	global $VALIDA_MSG_NOFOUND;
	global $VALIDA_MSG_REGISTER;
	global $VALIDA_MSG_NOSANCION;
	global $VALIDA_MSG_IFGROUP;
	
	global $VALIDA_MSG_WRONGDATE;
		
	global $MSG_SANCTIONS_ACOMP_COMPLETED_HINT;
		
	// ESPA&ntilde;OL
	
	$LBL_HEADER = "Cumplimiento de Sanciones";
	$LBL_SUB_HEADER = "Introduzca  los datos requeridos para registrar cumplimiento de sanci&oacute;n:";
	$LBL_HEADER_COMPLETED = "Se ha registrado el cumplimiento de una sanci&oacute;n";
	$LBL_IDUSUARIO = "Nombre de usuario:";
	$LBL_TIPO_SANCION = "Selecciona el tipo de sanci&oacute;n:";
	$LBL_DATE_ACOMPLISH = "Fecha cumplimiento:";
	$LBL_DATE_LIMITE = "Fecha l&iacute;mite a cumplir:";
	$LBL_MONTO = "Sanci&oacute;n Econ/Hrs/Esp:";
	$LBL_MOTIVO = "Motivo de la sanci&oacute;n:";
	$VALOR = "valor_monto";
	$LBL_ECONOMICA = " \$MXN ";
	$LBL_SOCIAL = "Horas";
	$LBL_ESPECIE = "En especie";
	
	$HINT_MAX_ITEMS_ALREADY_HAD = "%s Items actualmente prestados.";
	
	$LBL_DETAILS_ACOMPLISH = "Detalles:";
		
	$LBL_HEADER_COL_0 = "Sanci&oacute;n";
	$LBL_HEADER_COL_1 = "Motivo sanci&oacute;n";
	$LBL_HEADER_COL_2 = "Fecha sanci&oacute;n";
	$LBL_HEADER_COL_3 = "Fecha limite";
	$LBL_HEADER_COL_4 = "Monto sancion";
	$LBL_HEADER_COL_5 = "Registrar Cumplimiento";
	
	$BTN_DONE = "Cumplimiento";
	$BTN_NEW_SANCTION = "Registrar otro cumplimiento";
	
	$VALIDA_MSG_STATUS = "El Usuario ha sido encontrado pero no esta en status activo";
	$VALIDA_MSG_NOFOUND = "El Usuario NO HA SIDO encontrado";	
	$VALIDA_MSG_REGISTER = "Sanci&oacute;n registrada"; 	
	$VALIDA_MSG_NOSANCION = "El Usuario NO TIENE sanciones";
	$VALIDA_MSG_IFGROUP = " Este usuario pertenece a un grupo al que no se le registran sanciones ";	
	
	$VALIDA_MSG_WRONGDATE = "La fecha de cumplimiento debe ser igual o superior a la fecha de sanci&oacute;n";
	
	$MSG_SANCTIONS_ACOMP_COMPLETED_HINT = "Se ha registrado el cumplimiento de la sanci&oacute;n No. %s;<br> El usuario ahora solamente tiene %s sanci&oacute;n(es)."; 
	
?>
