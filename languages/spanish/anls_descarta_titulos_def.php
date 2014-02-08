<?php
  global $LBL_CREATE_EXIST_V1, $LBL_CREATE_EXIST_V2, $LBL_CREATE_EXIST_V3, $LBL_CREATE_EXIST_V4;
  
  global $LBL_TO_BE_ASIGNED;
  
  global $LBL_ID_ITEM, $LBL_ID_MATERIAL, $LBL_MATERIAL, $LBL_MOTIVATION, $LBL_LOCATION, $LBL_PHYSICAL_ST, $LBL_FECHA, $LBL_FECHA_AUTORIZA, $LBL_USUARIO_REGISTRA, $LBL_USUARIO_AUTORIZA;
  
  global $LBL_DETAILS;
  
  global $BTN_AUTH;
  global $SAVE_CREATED_DONE, $SAVE_AUTH_DONE;
  
  global $VALIDA_MSG_1, $VALIDA_MSG_2, $VALIDA_MSG_3, $VALIDA_MSG_4;
  
  global $NOTES_HELP_INSIDE;
  global $NOTES_AUTORIZACIONES_1;

  // ESPA&ntilde;OL
  
  $LBL_CREATE_EXIST_V1 = "Registrar un descarte";
  $LBL_CREATE_EXIST_V2 = "Editar un descarte";
  $LBL_CREATE_EXIST_V3 = "Autorizar un descarte";
  $LBL_CREATE_EXIST_V4 = "Consultar un descarte";
  
  $LBL_TO_BE_ASIGNED = "Por asignar";

  /* columnas titulos */
  $LBL_ID_ITEM  	= "ID Descarte";
  $LBL_ID_MATERIAL  = "ID del Material";
  $LBL_FECHA 		= "Fecha del Descarte";
  $LBL_FECHA_AUTORIZA = "Fecha de Autorizaci&oacute;n";

  $LBL_MOTIVATION   = "Motivo del Descarte";
  
  $LBL_MATERIAL 	= "Material por descartar";  
  
  $LBL_USUARIO_REGISTRA	= "Usuario que descarta";  
  $LBL_USUARIO_AUTORIZA	= "Usuario que Autoriza";
  
  $LBL_LOCATION = "Ubicaci&oacute;n";
  $LBL_PHYSICAL_ST = "Estado f&iacute;sico";
  
  $LBL_DETAILS = "Detalles por Ejemplar";
  
  $BTN_AUTH	   = "Autorizar";

  $SAVE_CREATED_DONE = "El descarte fue registrado, solicite autorizaci&oacute;n para aplicarlo a los ejemplares.";
  $SAVE_AUTH_DONE = "El descarte fue autorizado.";
  
  $VALIDA_MSG_1 = "Fecha incorrecta";
  $VALIDA_MSG_2 = "No se puede iniciar el descarte sin ejemplares seleccionados";
  $VALIDA_MSG_3 = "El ejemplar ya ha sido descartado";
  $VALIDA_MSG_4 = "No se puede descartar ahora";
  
  $NOTES_HELP_INSIDE = "<strong>NOTAS:</strong><br><br> Deber&aacute; indicar la fecha del descarte y el Motivo.<br><br>Adicionalmente podr&aacute; indicar alg&uacute;n detalle extra por cada ejemplar." .
						"<br><br><strong>IMPORTANTES:</strong><br><br>El descarte solo quedar&aacute; aplicado cuando sea autorizado por un supervisor.";
  
  $NOTES_AUTORIZACIONES_1 = "El usuario tiene privilegios de autorizaci&oacute;n de descartes. El descarte ser&aacute; aplicado de inmediato sin requerir autorizaci&oacute;n. ";
  
 ?>