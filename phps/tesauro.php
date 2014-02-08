<?php

	/*******
	  Historial de Cambios
	  
	  21 abr 2009: Se crea el archivo PHP
	  
	  
     */
 
	function tesauro_obtener_descrip_termino( $dbx, $termino )
	{
		require_once "../basic/bd.class.php";
		
		$res = "";
		
		$id_biblioteca = getsessionvar("id_biblioteca");

		$sql =  "SELECT a.ID_RED, b.ID_TERMINO, b.TERMINO, b.CODIGO_CORTO, b.DESCRIPCION ";
		$sql .= "FROM cfgbiblioteca a ";
		$sql .= "   LEFT JOIN tesauro_terminos b ON (b.ID_RED=a.ID_RED and b.ID_TERMINO=$termino) ";
		$sql .= "WHERE a.ID_BIBLIOTECA=$id_biblioteca ";
		
		$resultqry = $dbx->SubQuery( $sql );
		
		if( $row = $dbx->FetchRecord( $resultqry ) )
		{
			$res = $row["TERMINO"];
		}
		
		$dbx->ReleaseResultset( $resultqry );
		
		return $res;
	}

 ?>