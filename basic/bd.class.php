<?php 
	/*******
	  Historial de Cambios
	  
	  Esta clase reutiliza las funciones de funcs.inc.php (se elimina la reutilización  25sep2009)
	  
	  29 mar 2009: Se crea la clase PHP para gestionar conexiones
	               y la funcionalidad para base de datos.
	  01 abr 2009: Se agrega un contador $numRows
	  24 abr 2009: Se modifica la sentence DebuSQL()
	  19 ago 2009: Se coloca validación en Calculate_Ranges() para cuando no hay registros
	  25 sep 2009: Se independiza de funcs.inc.php la tendencia es eliminar las funciones de DB en funcs.inc.php
	  01 nov 2009: se implementa GetBLOB
	  01 jun 2011: El pager ahora muestra el rango de páginas

	  */		

	if(strpos($_SERVER['SCRIPT_FILENAME'], "/phps/" ) != 0) 
	{
		$local_base_dir = dirname( $_SERVER['SCRIPT_FILENAME'] );
		
		if( strpos( $local_base_dir, "/phps" ) != 0 )
			$local_base_dir = str_replace( "/phps", "", $local_base_dir );
			
		if( strpos( $local_base_dir, "/phps/" ) != 0 )
			$local_base_dir = str_replace( "/phps/", "", $local_base_dir );
			
		require_once( $local_base_dir . "/config_db.inc.php" );
	}
	else
	{
	   require_once( "config_db.inc.php" );	
	}
	
	//
	// 
	// 
	class DB
	{ 	
		var $last_sql_executed;

		var $sql;
		var $resultset;
		var $row;

		var $rowsAffected;
		var $numRows;

		var $class_display_even;
		var $class_display_odd;

		var $class_for_display;
		
		var $db_link;
	
		function DB( $exec_sql = "" )
		{
			unset($this->resultset);
			unset($this->row);
			
			// Abrir una conexión con la BD
			global $CFG;

			if( $CFG->db_type == "mysql" )
			{
				$this->db_link = mysql_connect($CFG->db_host, $CFG->db_user, $CFG->db_pass) or die( "Error connecting to DB");

				if ( !mysql_select_db( $CFG->db_name, $this->db_link ) )
				   echo "ERROR on selecting database<br>";
			}
			elseif( $CFG->db_type == "interbase" ) 
			{
				$this->db_link = ibase_connect( $CFG->db_host . ":" . $CFG->db_name, $CFG->db_user, $CFG->db_pass, "ISO8859_1" ) or die( "Error connecting to FB DB") ;
			}

			$this->rowsAffected = 0;
			$this->numRows = 0;
			
			$this->class_display_even = "";
			$this->class_display_odd = "";
		
			$this->class_for_display = "";
			
			$this->last_sql_executed = "";
			
			if( $exec_sql != "" )
				$this->Open( $exec_sql );
		}
		
		// Cierra el dataset que pudo estar abierto
		// Cierra la conexión con la DB
		function destroy()
		{
			global $CFG;
			
			$this->FreeResultset();
			
			//echo "destroying a BD object";
			
			if ( $CFG->db_type == "mysql")
				mysql_close( $this->db_link );
			elseif ( $CFG->db_type == "interbase" ) 
				ibase_close( $this->db_link );
			
			unset( $this->row );
		}		
		
		function ReleaseResultset( $resultset )
		{
			global $CFG;
			
			if( $CFG->db_type == "mysql")    
			{
				@mysql_free_result( $resultset );
			}
			elseif ( $CFG->db_type == "interbase") 
			{
				ibase_free_result( $resultset ) or die( "freeing resultset error");
			}	
			
		}
		
		//
		// Libera el link
		// libera el array asociativo del ROW
		//
		function FreeResultset()
		{
			if( isset($this->row) )
				unset( $this->row );
				
			if( isset($this->resultset) )
			{
				$this->ReleaseResultset( $this->resultset );
				
				unset( $this->resultset );			
			}
		}
		
		// Mismo que FreeResulset
		function Close()
		{
			$this->FreeResultset();
		}
		
		function ExecCommand( $sqlcommand, $con_blob=0, $do_the_commit=1 )
		{
			global $CFG;

			if( $CFG->db_type == "mysql" )
			{
				$qryres = mysql_query( $this->db_link, $sqlcommand  ) or db_die();

				if ( eregi('insert|update|delete|create', $sqlcommand )) 
				{
				  $qryres = mysql_affected_rows();				  
				  mysql_commit();
				}

				return $qryres;
			}
			elseif( $CFG->db_type == "interbase" ) 
			{
				if( $con_blob == 1 )
				{
					if( $obj_blob = ibase_blob_create( $this->db_link ) )
					{
						$ascii_blob = $do_the_commit;
						$ascii_blob2 = "<!--none-->";
						
						ibase_blob_add( $obj_blob, $ascii_blob );
						$blob_id_str = ibase_blob_close( $obj_blob ); 
						
						if( $ascii_blob2 == "<!--none-->" )
						{
						   // un blob o ninguno
						   $qryres = ibase_query( $this->db_link, $sqlcommand, $blob_id_str ) or die("no se pudo ejecutar la consulta BLOB: $sqlcommand" );
						}
						else
						{
						   // en Bibliotek no se soportan 2 blobs
						   // vienen ambos
						   /**if( $obj_blob2 = ibase_blob_create( $link ) )
						   {
							   ibase_blob_add( $obj_blob2, $ascii_blob2 ) ;
							   $blob_id2_str = ibase_blob_close( $obj_blob2 );
							   
							   $qryres = ibase_query( $link, $sqlcommand, $blob_id_str, $blob_id2_str ) or die("no se pudo ejecutar la consulta BLOB: $sqlcommand" );			
						   }
						   else
							  die( "error creando SWAP space" );
						**/
						}
					}
				}
				else
					$qryres = ibase_query( $this->db_link, $sqlcommand ) or die("no se pudo ejecutar la consulta: $sqlcommand" );

				if ( eregi('insert|update|delete|create', $sqlcommand )) 
				{
				  $qryres = ibase_affected_rows();				  
				  
				  if( $do_the_commit == 1 )
					ibase_commit( $this->db_link );
				}

				return $qryres;
			}			
		}
		
		function Open( $newsql = "" )
		{
			$this->FreeResultset();

			if( $newsql != "" )
				$theSQL = $newsql;
			else
				$theSQL = $this->sql;
			
			$this->rowsAffected = 0;
			$this->numRows = 0;
			
			if ( preg_match("/insert|update|delete|create/i", $theSQL ) ) 
			{
				$this->rowsAffected = $this->ExecCommand( $theSQL );
				$this->last_sql_executed = $theSQL;
				
				return $this->rowsAffected;
			}
			else
			{
				$this->resultset = $this->ExecCommand( $theSQL );

				$this->last_sql_executed = $theSQL;
				
				return $this->resultset;
			}
		}
		
		function SubQuery( $sql )
		{
			return $this->ExecCommand( $sql );
		}
	
		function DebugSQL( $bDie=0 )
		{
			if( $bDie==1 )
			   die( ($this->sql=="") ? $this->last_sql_executed : $this->sql );
			else if( $bDie==2 )
			{
				echo "<script language='javascript'>";
				echo " alert( \" " . (($this->sql=="") ? $this->last_sql_executed : $this->sql) . "\" ); ";
				echo "</script>";
				die( "ENDING...");
			}
			else
			{
			   echo "<br><em>";
			   echo ($this->sql=="") ? $this->last_sql_executed : $this->sql;
			   echo "</em><br>";
			   echo "<br>";
			}
		}
	
		// Execute directly a SQL statement, either of execution or queries
		function ExecSQL( $newsql = "" )
		{
			return $this->Open( $newsql );
		}
				
		// Specify Hilite clases por each row of the resultset
		function SetClassesForDisplay( $class_4_odd, $class_4_even )
		{
			$this->class_display_odd = $class_4_odd;
			$this->class_display_even = $class_4_even;
		}
		
		// Calculate wich class will be used for content display
		function CalculateDisplayClass()
		{
			$this->class_for_display = "";
			
			if( $this->class_display_even != "" )
			{
				 $this->class_for_display = $this->class_display_odd;
				   
				 if( $this->numRows % 2 == 1 )
					$this->class_for_display = $this->class_display_even;
			}
		}
		
		function FetchRecord( $resultset, $convert_to_html_compatible=0 )
		{
			global $CFG;

			if ($CFG->db_type == "mysql")    
			{
				$row = @mysql_fetch_assoc( $resultset );
			}
			elseif ($CFG->db_type == "interbase") 
			{
				$row = ibase_fetch_assoc( $resultset );
			}
			
			if( $convert_to_html_compatible == 1 and ($row))
			{
				foreach ($row as $key => $value)
				{
					$value = $this->ConvertToHTML( $value );	
							
					$row[ $key ] = $value;
				}
			}			
			
			return $row;
		}
		
		//WebEscolar.NET 05-may-2010
		function ConvertToHTML( $result )
		{
			$result = str_replace( "á", "&aacute;", $result );
			$result = str_replace( "Á", "&Aacute;", $result );
			$result = str_replace( "é", "&eacute;", $result );
			$result = str_replace( "É", "&Eacute;", $result );
			$result = str_replace( "í", "&iacute;", $result );
			$result = str_replace( "Í", "&Iacute;", $result );
			$result = str_replace( "ó", "&oacute;", $result );
			$result = str_replace( "Ó", "&Oacute;", $result );
			$result = str_replace( "ú", "&uacute;", $result );
			$result = str_replace( "Ú", "&Uacute;", $result );
			
			$result = str_replace( "ñ", "&ntilde;", $result );			
			$result = str_replace( "Ñ", "&Ntilde;", $result );
			
			//$result = str_replace( " ", "&nbsp;", $result );
								
			$result = str_replace( "\n", "<br>", $result );			
			
			$result = str_replace( "¿", "&iquest;", $result );
			
			return $result;
		}		
		
		// WeEscolar.NET  15-abr-2010
		function GetRecordAsArrayFields( $translate_to_html=1 )
		{
			$flds = array();
			
			foreach ($this->row as $key => $value)
			{
				if( $translate_to_html == 1 )
					$value = $this->ConvertToHTML( $value );	
						
				$flds[ $key ] = $value;
			}
				
			return $flds;
		}		
		
		function NextRow( $convert_to_html_compatible = 1 )
		{
			if( !isset($this->resultset) )
				return false;
				
			if( $this->row = $this->FetchRecord( $this->resultset, $convert_to_html_compatible ) )
				$this->numRows++;

			$this->CalculateDisplayClass();
			
			return ($this->row);
		}
				
		function Field( $fieldname )
		{
			return $this->row[ $fieldname ];
		}

		function BeginTransaction()
		{
		}
		
		function Commit()
		{
		}
		
		function Rollback()
		{
		}
		
		function GetBLOB( $field_str, $translate_to_html=0, $translate_from_unicode=0 ) 
		{
			global $CFG;
			
			if ( $CFG->db_type == "interbase" )
			{
				require_once( "ibaseblob.class.php" );
				
				$result = "";
				
				if( $field_str != "" )
				{
					$bl_img = new ibase_blob( $this->db_link, $field_str );
					$bl_img->retrieve_data();
					$result = $bl_img->data;			
					
					$bl_img->destroy();
				}
				
				if( $translate_to_html == 1 )
				{
					$result = str_replace( "á", "&aacute;", $result );
					$result = str_replace( "é", "&eacute;", $result );
					$result = str_replace( "í", "&iacute;", $result );
					$result = str_replace( "ó", "&oacute;", $result );
					$result = str_replace( "ú", "&uacute;", $result );
										
					$result = str_replace( "\n", "<br>", $result );
					
				}
				
				if( $translate_from_unicode == 1 )
				{
					$result = str_replace( "Ã¡", "&aacute;", $result );
					
					$result = str_replace( "Ã©", "&Eacute;", $result );
					
					
					$result = str_replace( "Ã­", "&iacute;", $result );
					$result = str_replace( "Ã³", "&oacute;", $result );
					
					$result = str_replace( "Ãº", "&uacute;", $result );			
					$result = str_replace( "Ãº", "&uacute;", $result );	
					
					$result = str_replace( "Ã±", "&ntilde;", $result );			
					
					

				}
				
				return $result;
			}
			else
			{
				return $field_str;
			}
		}
		
		function SetPage( $from, $range, $pager, $char_init_variants="" )
		{
			global $CFG;
			
			if( $pager->style == "A" )			
			{
				$posSELECT = strpos( $this->sql, "ORDER" );
				
				$tmpval = $pager->page;
				
				if( substr($tmpval,0,1) == "!" )
					$tmpval = substr( $tmpval, 1, strlen($tmpval) );
				
				$primera_opcion = $tmpval;
				$alternativo = strtolower( $tmpval );
				
				$variantes = "";
				
				if( $char_init_variants != "" )
				   $variantes = " or ($pager->Field_for_Pager LIKE '$char_init_variants$primera_opcion%') or ($pager->Field_for_Pager LIKE '$char_init_variants$alternativo%')";
				
				if( $posSELECT >= 0 )
				{
					// hay ORDER
					$this->sql = substr( $this->sql, 0, $posSELECT ) . 
								" and (($pager->Field_for_Pager LIKE '$primera_opcion%') or  ($pager->Field_for_Pager LIKE '$alternativo%') $variantes) " .
								substr( $this->sql, $posSELECT, 256 );
				}
				else
				{
					// NO HAY ORDER solo agregar
					$this->sql .= " and ($pager->Field_for_Pager LIKE '%" . $pager->page . "') ";
				}
				
				//echo $this->sql;
			}
			else
			{				
				if( $CFG->db_type == "interbase" )
				{
					$posSELECT = strpos( $this->sql, "SELECT" );
					
					if( $posSELECT >= 0 )
					{
						$insertFILTER = " FIRST $range SKIP $from ";

						$this->sql = "SELECT "  . $insertFILTER . " " . substr( $this->sql, $posSELECT + 7, 2048 );
					}
				}
				else if ( $CFG->db_type == "mysql" )
				{
					$this->sql .= " ASC LIMIT $from, $range ";
						
					echo $this->sql;
					die( "PENDIENTE...");
				
				}
			}
		}
		
	}
	
	//
	// Clase que colocará paginadores en un "browse" de SQL
	//
	class Pager
	{
		var $TotalPages;
		var $Rows;
		
		var $Range;
		
		var $page;
		
		var $start_from;
		var $style;
		
		var $Field_for_Pager;
		
		var $remove_words;
		
		var $lang;   // 1 - Spanish,  
					 // 2 - English
					 // 3 - Portuguese
					 
		var $db;
		
		//
		// Style N por NUMEROS
		// Style A por ALFABETICO
		//
		function Pager( $dbx, $style="N", $division )
		{
			$this->TotalPages = 0;
			$this->page = 0;
			
			$this->style = $style;
			$this->Range = $division;
			
			$this->lang = "Spanish";
			
			$sql = $dbx->sql;
			
			if( $posFROM = strpos( $sql, "FROM" ) )
			{
				$sql = substr( $sql, $posFROM, 2000 );
				$sql = "SELECT COUNT(*) AS CUANTOS " . $sql;
				
				if( $posORDER = strpos( $sql, "ORDER" ) )
				{
					$sql = substr( $sql, 0, $posORDER );
				}
			}
			
			$resultset = $dbx->SubQuery( $sql );
			
			if( $row = $dbx->FetchRecord( $resultset ) )
			{
				$this->Rows = $row["CUANTOS"];
			}
	
			if( $this->Rows > 0 )
			{
				$this->page = 1;
				
				if( $this->style == "A" )
					$this->page = "A";

				$this->TotalPages = $this->Rows / $division;
			
				if( ($this->Rows % $division) > 0 )
					$this->TotalPages++;			
			}
	
			$dbx->ReleaseResultset( $resultset );
			unset( $resultset );
			
			$this->remove_words = Array();
	
		}

		//
		//
		//
		function Language(  $new_lang )
		{
			$this->lang = $new_lang;
		}
		
		
		//
		// Permite remover parámetros que se colocan en la lista de páginas
		//
		function RemoveParameters( $cParam )
		{
			$this->remove_words[] = $cParam;
		}

		
		//
		// Dibuja la lista de paginas
		//
		function DrawPages( $link="" )
		{
			if( $link == "" )
			{
				$link = $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"];
				
				if( $pos_PAGE = strpos( $link, "&page=" ) )
				{
					$link = substr( $link, 0, $pos_PAGE );
				}
			}

			// usando el array remove_words
			// ejecuta la eliminación de posibles parametros
			for( $ij=0; $ij<count($this->remove_words); $ij++ )
			{
				$link = str_replace( $this->remove_words[$ij], "", $link );
			}

			echo "<br>";

			$str_page = "P&aacute;ginas";
			
			if( $this->lang != 1 )
			{
				if( $this->lang == 2 )
					$str_page = "Pages";
			}
			
			echo "<div style='float:left; width:74%;'>";
			echo "<table align='center' border='0'>";
			echo "<tr><td align='center' class='columna'>" . (($this->TotalPages==0) ? "" : "$str_page") . "&nbsp;";

			if( $this->page > 1 )
			   echo "&nbsp;<a href='$link&page=" . ($this->page-1) . "'><img src='../images/back_btn.gif' alt='Anterior'></a>";		

			$since = 1;
			$to = $this->TotalPages;

			if( $this->TotalPages > 20 )
			{
				$since = $this->page;
				$to    = $since + 20;

				if( $to > $this->TotalPages )
				{
					$to = $this->TotalPages;
				}
			}

			if( $this->TotalPages > 20 and $this->style=="N")
			{
				echo "&nbsp;<a href=''>...</a>";	
			}

			$index_page = $this->page;

			if( $this->style == "A" )
			{
				$since = 1;
				$to    = 26;

				if( substr($this->page,0,1) == "!" )
				{
					$this->page = substr($this->page, 1, strlen($this->page));
					$this->page = 26 + $this->page;
					
					$index_page = $this->page;
				}
				else
				{
					// convertir la página Alfabetica a un INDICE
					$index_page = (ord($this->page) - 64);
				}
			}

			for($i=$since; $i<=$to; $i++ )
			{
				if( $i == $index_page )
				{
					echo "<b><font size=+1>";
					
					if( $this->style == "A" )
					{
						$str = chr(64+$i);
						echo $str;
					}
					else
						echo $i;					

					echo "</font></b>&nbsp;";
				}
				else
				{
					if( $this->style == "A" )
						$str = chr(64+$i);
					else
						$str = $i;

					echo "<a href='" . $link . "&page=$str'>";
					echo (($i==$this->page-1 or $i==$this->page+1) ? "<font size=+0>" : "");

					echo $str;

					echo (($i==$this->page-1 or $i==$this->page+1) ? "</font>" : "");
					echo "</a>&nbsp;";
				}
			}

			if( $this->style == "A" )
			{
				for($i=0; $i<=9; $i++ )
				{
					if( 26+$i == $index_page )
					{
						echo "<strong><font size=+1>";
						
						$str = chr(48+$i);
						echo $str;
	
						echo "</font></strong>&nbsp;";
					}
					else
					{
						$str = chr(48+$i);
	
						echo "<a href='" . $link . "&page=!$str'>";
						echo ((26+$i==$this->page-1 or 26+$i==$this->page+1) ? "<font size=+0>" : "");
	
						echo $str;
	
						echo ((26+$i==$this->page-1 or 26+$i==$this->page+1) ? "</font>" : "");
						echo "</a>&nbsp;";
					}
				}
			
			}

			if( $this->TotalPages > 20  and $this->style=="N" )
			{
				echo "&nbsp;<a href=''>...</a>";	
			}

			if ($this->page<($this->TotalPages-1))
			  echo "&nbsp;<a href='$link&page=" . ($this->page+1) . "'><img src='../images/forward_btn.gif' alt='Siguiente'></a>";	

			echo "</td></tr>";
			echo "</table>";
			echo "</div>";
			
			global $pr;
			
			if( isset($pr) )
			{
				global $LBL_RECS_X_PAGE;
				echo "<div style='float:right; width:25%; text-align:right;' class='x-pageRanges'>$LBL_RECS_X_PAGE ";
				echo "	<span " .  (($pr==10) ? "class='current' " : "") . "><a href='" . $_SERVER["PHP_SELF"] . "?pr=10'>10</a></span>&nbsp; ";
				echo "	<span " . (($pr==15) ? "class='current' " : "") . "><a href='" . $_SERVER["PHP_SELF"] . "?pr=15'>15</a></span>&nbsp;";
				echo"	<span " . (($pr==20) ? "class='current' " : "") . "><a href='" . $_SERVER["PHP_SELF"] . "?pr=20'>20</a></span>&nbsp;";
				echo " </div>";
			}
			
			echo "<br style='clear:both'>";
		}

		function Calculate_Ranges()
		{
			$this->start_from = ($this->page-1) * $this->Range;	

			// cuando $this->page = 0 generalmente indica que no hay registros que mostrar
			if( $this->start_from < 0 )  $this->start_from = 0;
		}

	}

?>