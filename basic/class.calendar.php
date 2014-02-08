<?php
//
// By Ricardo Costa - ricardo@icorp.com.br - 2002
// Classe para exibiçao de calendário
//
//  calendar
//    +---- calendar()
//    +---- show()
//
//

class calendar {
   var $day; 
   var $month;
   var $year;
   
   var $picking_date;  // true cuando el control sirve para elegir una fecha
   
   var $avoid_saturday=0;
   var $avoid_sunday=0;
   
   var $startonmonday=0;  // NO empieza en Lunes
   
   var $content;   // Conteudo HTML formatado
   var $page;      // Página para link
   var $month_name;   // Nombre del mes
   var $year_bgcolor = "E9EBF1"; // Cor de fundo do ano
   var $month_bgcolor = "CCCCCC"; // Cor de fundo do mes
   var $days_bgcolor = "8D9ABA"; // Cor de fundo dos dias da semana
   var $day_color = "E9EBF1"; // Cor de fundo dos dias
   var $day_today_color = "FF9999"; // Cor de fundo de hoje
   var $font_color = "4C5B7D"; // Cor da fonte
   var $bg_color = "E9EBF1"; // Cor de fundo
   var $event_bgcolor = "FFCC99"; // Cor de fundo dos compromissos
   var $events = array(); // Array de eventos
   var $events_hint = array(); // Array com a descrição dos eventos
   
   var $day_height_pxls; 
   
   var $activities_for_today;
   var $link_url;
   var $link_extra_params;
   
   var $link_mask_substitute;
   
   var $calendar_width_pxls;

   function calendar( $initday, $initmonth, $inityear, $pick_date=0 ) 
   {
      $this->page = $_SERVER['PHP_SELF']; ;

      $this->year  = $inityear;
	  $this->month = $initmonth;
      $this->day   = $initday;
	  $this->day_height_pxls = 20;
	  
	  $this->calendar_width_pxls = 180;
	  
	  $this->picking_date = $pick_date;

      if ( $this->month == 0) 
	  {
         $this->year--;
         $this->month = 12;
      }
      elseif ($this->month == 13) 
	  {
         $this->year++;
         $this->month = 1;
      }

	  global $month_year;
	  
      $this->month_name = $month_year;
      $this->month_name = $this->month_name[ $this->month ];
	  
	  $this->link_mask_substitute = "";
	  
   }

   //
   //
   // $hide_links_for_changing ... evita que se muestren los links para cambiar mes y año en el calendario
   //
   function show($showyear = 1, $showmonth = 1, $showtoday = 1, $hide_links_for_changing=0 ) 
   {
		global $today_str, $days_week;
	  
		if( $this->startonmonday == 1 )
			$days_week = array(0 => "Lunes", 1 => "Martes", 2 => "Miércoles", 3 => "Jueves", 4 => "Viernes", 5 => "Sábado", 6 => "Domingo");
		else
			$days_week = array(0 => "Domingo", 1 => "Lunes", 2 => "Martes", 3 => "Miércoles", 4 => "Jueves", 5 => "Viernes", 6 => "Sábado");
	      
		$this->activities_for_today = 0;
	  
		require_once( "../funcs.inc.php" );
	  
		$this->content = "<div align='center'><table width='$this->calendar_width_pxls' border='0' cellspacing='0' cellpadding='0'>";

		if ($showyear == 1) 
		{
			if( $hide_links_for_changing == 1 )
				// coloca mes y año
				$this->content .= "<tr align='center'>
									 <td colspan='7' class='columna columnaEncabezado' height='".$this->day_height_pxls."'><b>" . $this->month_name . " " . $this->year . "</b></td>
								   </tr>";		
			else
				$this->content .= "<tr align='center'>
									 <td width='17%' class='columna columnaEncabezado' height='".$this->day_height_pxls."'><b><a href='".$this->page . "?" . $this->link_extra_params . "&nmonth=" . $this->month . "&nyear=" . ($this->year - 1) . "&nday=" . $this->day . "' ><IMG SRC='../images/prev_year.gif' " . (($this->calendar_width_pxls>300) ? "height=25 width=25" : "") . "></a></b></td>
									 <td colspan='5' class='columna columnaEncabezado' height='".$this->day_height_pxls."'><b>" . $this->year . "</b></td>
									 <td width='17%' class='columna columnaEncabezado' height='".$this->day_height_pxls."'><b><a href='".$this->page . "?" . $this->link_extra_params . "&nmonth=" . $this->month . "&nyear=" . ($this->year + 1) . "&nday=" . $this->day . "'><IMG SRC='../images/next_year.gif' " . (($this->calendar_width_pxls>300) ? "height=25 width=25" : "") . "></a></b></td>
								   </tr>";
		} 
    
	  if ($showmonth == 1) 
	  {
	    // = (($this->show_big==1) ? "<font size=+1>" : "");
		
		if( $hide_links_for_changing == 0 )
			$this->content .= "<tr align='center'>
							   <td style='left-padding:0px;' width='14%' bgcolor='#".$this->month_bgcolor."' height='$this->day_height_pxls'><b><a href='".$this->page."?" . $this->link_extra_params . "&nmonth=" . ($this->month - 1) . "&nyear=" . $this->year . "&nday=" . $this->day . "'><IMG SRC='../images/prev_month.gif' " . (($this->calendar_width_pxls>300) ? "height=25 width=25" : "") . "></a></b></td>
							   <td style='left-padding:0px;' colspan='5' bgcolor='#".$this->month_bgcolor."' height='$this->day_height_pxls'><strong>" . $this->month_name . "</strong></td>
							   <td style='left-padding:0px;' width='14%' bgcolor='#".$this->month_bgcolor."' height='$this->day_height_pxls'><b><a href='".$this->page."?" . $this->link_extra_params . "&nmonth=" . ($this->month + 1) . "&nyear=" . $this->year . "&nday=" . $this->day . "'><IMG SRC='../images/next_month.gif' " . (($this->calendar_width_pxls>300) ? "height=25 width=25" : "") . "></a></b></td>
							   </tr>";
	  }
	  
      $this->content .= "<tr align='center' bgcolor='#".$this->days_bgcolor."'>\n";

	  //
      // desplegar los días de la semana
	  //
	  for ($l = 0; $l <= 6; $l++)
	  { 
			$okday = 1;
			
			if( $this->avoid_saturday == 1 && $l == 5 ) $okday = 0;
			if( $this->avoid_sunday == 1 && $l == 6 ) $okday = 0;

			$this->content .= "<td class='cuadricula columnaEncabezado' width='14%' height='$this->day_height_pxls' align=middle>"  .
			       "<strong>" . (($okday==0) ? "<font color=gray>" : "") . $days_week[$l][0] . (($okday==0)? "</font>" : "") . "</strong></td>"; 
	        //$this->content .= "</font>";				   
	  }
	  
      $this->content .= "</tr>";

      $cont_day = 1;
      
	  // weeks
	  for( $l = 1; $l <= 6; $l++) 
	  {
         $this->content .= "<tr>";
		 
		 // days
         for($c = 0; $c <= 6; $c++)
		 {
           // armar una fecha del mes
		   $xday = date("w",mktime (0,0,0, $this->month, $cont_day, $this->year));

		   $bg=$this->day_color;
	   
		   if( $this->startonmonday == 1 ) 
		   {
		      if( $c==6) $tmpday = 0;
			  else $tmpday = $c+1;
		   }
		   else $tmpday = $c;
			
			if ( checkdate( $this->month, $cont_day, $this->year ) & $xday == $tmpday) 
			{
               $okday = 1;
			   
			   if( $this->avoid_saturday == 1 && $c == 5 ) $okday = 0;
			   if( $this->avoid_sunday == 1 && $c == 6 ) $okday = 0;
			   
			   if( $okday== 1) 
			   {
				   $date_time_stamp = mktime( 0, 0, 0, $this->month, $cont_day, $this->year );
				   
				   $the_date_in_human_format = encodedate_to_human_format( $date_time_stamp );
				   
				   if (in_array( $the_date_in_human_format, $this->events ) )
				   {
					  if ($cont_day == $this->day)
					  {
						$activities_for_today = 1;   
						$bg = $this->event_bgcolor;
					  }
					  else
						$bg = $this->event_bgcolor;
						
				   }
				   else 
				   { 
					  if (($cont_day == $this->day) and ($GLOBALS["month"] == $this->month) and ($GLOBALS["year"] == $this->year) )
						 $bg = $this->day_today_color;
				   }
	
					if( $this->picking_date == 1 )
					{
					   // cuando se esté seleccionando una fecha 
					   $frm_date = '"' . ($cont_day<10?"0":"") . $cont_day . "/" . ($this->month<10?"0":"") . $this->month . "/" . $this->year . '"';
					
					   $this->content .= "<td align='center' height='$this->day_height_pxls' class='columna cuadricula'>". 
										 "<a href='javascript:parent.opener.returnDate( " . $frm_date . " )' >".$cont_day."</a></td>";
					}
					else
					{ 
					    if (in_array( $the_date_in_human_format, $this->events ) )
					    {
							$icon = "";
							$hint = "";
							
							$event_number = -1;
							
							for( $xyz=0; $xyz<count($this->events); $xyz++ )
							{
								if( $this->events[$xyz] == $the_date_in_human_format )
								{
									$icon = $this->events_hint[$xyz]["icon"];
									$hint = $this->events_hint[$xyz]["hint"];
									
									break;
								}
							}
							
							$onMouseEnterEvent = "";
							$onMouseLeaveEvent = "";
							
							if( $this->link_mask_substitute != "" )
							{
								$onMouseEnterEvent = " onMouseOver='javascript:HiliteDay(this);' ";
								$onMouseLeaveEvent = " onMouseOut='javascript:UnHiliteDay(this);' ";
								$str_link_open  = "<a onClick='$this->link_mask_substitute' title='$hint'>";
								
								$str_link_open = str_replace( "%d", $cont_day, $str_link_open );
								$str_link_open = str_replace( "%a", $this->year, $str_link_open );
								$str_link_open = str_replace( "%m", $this->month, $str_link_open );
								$str_link_open = str_replace( "%s", $hint, $str_link_open );
								
								$unixstyle_date = mktime( 0, 0, 0, $this->month, $cont_day, $this->year );
								$fecha = encodedate_to_human_format( $unixstyle_date );
								
								$str_link_open = str_replace( "%fecha", $fecha, $str_link_open );
								
								$str_link_close = "</a>";
							}
							else
							{
								$str_link_open  = "<a href='" . $this->link_url . "?" . $this->link_extra_params . "&nmonth=" . $this->month . 
														"&nyear=" . $this->year . "&nday=" . $cont_day . "&wday=" . $c ."' title='$hint' target='_new'>";
								$str_link_close = "</a>";
							}
							
							$this->content .= "<td $onMouseEnterEvent $onMouseLeaveEvent align='center' height='$this->day_height_pxls' class='columna cuadricula'>".
							  "$str_link_open $icon" . $cont_day . "$str_link_close".
							  "</td>";
						}
					    else 
							$this->content .= "<td align='center' height='$this->day_height_pxls' class='columnaError cuadricula' >".$cont_day."</td>";
					}
				}
				else
				   // día deshabilitado (sunday o saturday)
				   $this->content .= "<td align='center' height='$this->day_height_pxls' bgcolor=silver><font color=gray>$cont_day</font></td>";
				    
			    $cont_day++;
			}
			else 
			   $this->content .= "<td align='center' height='$this->day_height_pxls' bgcolor=silver>&nbsp;</td>";

		   
	     }
    
         $this->content .= "</tr>";

         if ( !checkdate( $this->month, $cont_day, $this->year)) break;
      }
   
	  if ($showtoday == 1) 
	  {
	    $this->content .= "</table>
                           <table class='columna' width='140' border='0' cellspacing='0' cellpadding='0' >
                           <tr><td align='center' bgcolor=#".$this->year_bgcolor."><b><a href='".$this->page."?nmonth=".date("n")."&nyear=".date("Y")."&nday=".date("d")."' class='calendar_font'>$today_str</a></b></td></tr>
                           <tr height='10'><td></td></tr></table>";
      }
	  
	  $this->content .= "</table></div>";
     
      print($this->content);
	  
	  return $this->activities_for_today;

   }

} 
?>