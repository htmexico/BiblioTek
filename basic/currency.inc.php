<?php
	/*******
	  Contiene functiones orientadas para manejo de información
	  monetaria

     */
	
	if( getsessionvar("pais") == "MEXICO" ) 
	{
		$signo_moneda = "$";                // 14 oct 2008
		$nombre_moneda_singular = "PESO";   // 14 oct 2008
		$nombre_moneda_plural   = "PESOS";  // 14 oct 2008		
	}
	else if( getsessionvar("pais") == "USA" )	
	{
		$signo_moneda = "$";                // 14 oct 2008
		$nombre_moneda_singular = "DOLLAR";   // 14 oct 2008
		$nombre_moneda_plural   = "DOLLARS";  // 14 oct 2008		
	}
	else if( getsessionvar("pais") == "BRAZIL" )	
	{
		$signo_moneda = "R";                // 14 oct 2008
		$nombre_moneda_singular = "REAL";   // 14 oct 2008
		$nombre_moneda_plural   = "REALES";  // 14 oct 2008		
	}
	 
	function formato_cantidad( $number )
	{
		global $signo_moneda;
		
		return $signo_moneda . " " . number_format( $number, 2 );
	}

  //**************************************
  // A simple currency function in php to give the same
  // feel as the currency function in asp.
  //	Paste this code in your php page then just
  //	send your variable to it
  //		$yourVar = 1236.1234;
  //		$yourNewVar = currency($yourVar);
  //	and the result would be :
  //	$yourNewVar = $1236.12			
  //**************************************
  
    function currency( $c_f_x )	
    {
		global $signo_moneda;
		
    	$c_f_x = round($c_f_x, 2);	//THIS WILL ROUND THE ACCEPTED PARAMETER TO THE 
    							    //PRECISION OF 2		
									
    	$temp_c_f_variable = strstr(round($c_f_x,2), ".");	//THIS WILL ASSIGN THE "." AND WHAT EVER 
    								//ELSE COMES AFTER IT. REMEMBER DUE TO 
    								//ROUNDING THERE ARE ONLY THREE THINGS
    								//THIS VARIABLE CAN CONTAIN, A PERIOD 
    								//WITH ONE NUMBER, A PERIOD WITH TWO NUMBERS,
    								//OR NOTHING AT ALL
    								//EXAMPLE : ".1",".12",""
									
    	if (strlen($temp_c_f_variable) == 2)	//THIS IF STATEMENT WILL CHECK TO SEE IF 
    						//LENGTH OF THE VARIABLE IS EQUAL TO 2. IF
    						//IT IS, THEN WE KNOW THAT IT LOOKS LIKE 
    						//THIS ".1" SO YOU WOULD ADD A ZERO TO GIVE IT 
    						// A NICE LOOKING FORMAT 
    	{
    		$c_f_x = $c_f_x . "0";
    	}
		
    	if (strlen($temp_c_f_variable) == 0)	//THIS IF STATEMENT WILL CHECK TO SEE IF 
    						//LENGTH OF THE VARIABLE IS EQUAL TO 2. IF
    						//IT IS, THEN WE KNOW THAT IT LOOKS LIKE 
    						//THIS "" SO YOU WOULD ADD TWO ZERO'S TO GIVE IT 
    						// A NICE LOOKING FORMAT
    	{
    		$c_f_x = $c_f_x . ".00";
    	}
		
		$c_f_x = $signo_moneda . $c_f_x;	//THIS WILL ADD THE "$" TO THE FRONT 

    	return $c_f_x;	//THIS WILL RETURN THE VARIABLE IN A NICE FORMAT
    					//BUT REMEMBER THIS NEW VARIABLE WILL BE A STRING 
    					//THEREFORE CAN BE USED IN ANY FURTHER CALCULATIONS
    		
    }	//THIS IS THE END OF THE CURRENCY FUNCTION

//************************************************************* 
// this function converts an amount into alpha words 
// with the words dollars and cents.  Pass it a float. 
// Example:  $3.77 = Three Dollars and Seventy Seven Cents 
// works up to 999,999,999.99 dollars - Great for checks 
//************************************************************* 

function makewords($numval, $decs) 
{ 
  global $nombre_moneda_singular;
  global $nombre_moneda_plural;
  
  $moneystr = ""; 

  // handle the millions 
  $milval = (integer)($numval / 1000000); 

  if($milval > 0) 
  {  $moneystr = getwords($milval) . " MILLONES";  } 

  // handle the thousands 
  $workval = $numval - ($milval * 1000000); // get rid of millions 
  $thouval = (integer) ($workval / 1000); 
  
  if($thouval > 0) 
  { 
    $workword = getwords($thouval); 
	
    if ($moneystr == "") 
    { 
      $moneystr = $workword . " mil"; 
    } 
    else 
    { 
      $moneystr .= " " . $workword . " mil"; 
    } 
  } 

 // handle all the rest of the dollars 
  $workval = $workval - ($thouval * 1000); // get rid of thousands 
  $tensval = (integer)($workval); 
  
  if ($moneystr == "") 
  { 
      $moneystr = "";
	  
	  if ($tensval > 0) 
        $moneystr .= getwords($tensval); 
      else 
        $moneystr .= "Cero"; 
  } 
  else // non zero values in hundreds and up 
  { 
    $workword = getwords($tensval); 
    $moneystr .= " " . $workword; 
  } 

  // plural o singular
  $workval = (integer)($numval); 
  
  if ($workval == 1) 
  { 
    $moneystr .= " $nombre_moneda_singular con "; 
  } 
  else 
  { 
    $moneystr .= " $nombre_moneda_plural "; 
  } 

  // do the pennies - use printf so that we get the 
  // same rounding as printf 
  $workstr = sprintf("%3." . $decs . "f", $numval ); // convert to a string 
  $intstr = substr($workstr, strlen($workstr) - $decs, $decs); 
  $workint = (integer)($intstr); 
    
  $moneystr .= $intstr;
  
  $moneystr .= "/100 M.N.";

//  if ($workint == 1) 
//  { 
//    $moneystr .= " centavo"; 
//  } 
//  else 
//  { 
//    $moneystr .= " centavos"; 
//  } 

// done - let's get out of here! 
  $moneystr = strtoupper($moneystr);
 
return $moneystr; 
} 

//************************************************************* 
// this function creates word phrases in the range of 1 to 999. 
// pass it an integer value 
//************************************************************* 
function getwords($workval) 
{ 
$numwords = array( 
  1 => "un", 
  2 => "dos", 
  3 => "tres", 
  4 => "cuatro", 
  5 => "cinco", 
  6 => "seis", 
  7 => "siete", 
  8 => "ocho", 
  9 => "nueve", 
  10 => "diez", 
  11 => "once", 
  12 => "doce", 
  13 => "trece", 
  14 => "catorce", 
  15 => "quince", 
  16 => "dieciseis", 
  17 => "diecisiete", 
  18 => "dieciocho", 
  19 => "diecinueve", 
  20 => "veinte", 
  30 => "treinta", 
  40 => "cuarenta", 
  50 => "cincuenta", 
  60 => "sesenta", 
  70 => "setenta", 
  80 => "ochenta", 
  90 => "noventa"); 
  
$hundreds = array( 
  1 => "ciento", 
  2 => "doscientos", 
  3 => "trescientos", 
  4 => "cuatrocientos", 
  5 => "quinientos", 
  6 => "seiscientos", 
  7 => "setecientos", 
  8 => "ochocientos", 
  9 => "novecientos" );

// handle the 100's 
$retstr = ""; 
$hundval = (integer)($workval / 100); 

if ($hundval > 0) 
  { 
  $retstr = $hundreds[$hundval]; 
  } 

// handle units and teens 
$workstr = ""; 
$tensval = $workval - ($hundval * 100); // dump the 100's 

if (($tensval < 20) && ($tensval > 0))// do the teens 
  { 
     $workstr = $numwords[$tensval]; 
  } 
else // got to break out the units and tens 
  { 
     $tempval = ((integer)($tensval / 10)) * 10; // dump the units 
     
     $unitval = $tensval - $tempval; // get the unit value 
  
     if ($unitval > 0) 
     { 
        if( ($tempval == 20) and ($unitval == 1) )
		{
		  $workstr .= "veintiun";
		}		
        elseif( ($tempval == 20) and ($unitval == 2) )
		{
		  $workstr .= "veintidos";
		}		
		else
		{
		  $workstr .= $numwords[$tempval] . " y " . $numwords[$unitval]; 
		}
     } 
	 else
	 {
  	    if( $tempval <> 0 )
		   $workstr = $numwords[$tempval]; // get the tens 
	  }
  } 

// join all the parts together and leave 
if ($workstr != "") 
  { 
  if ($retstr != "") 
    { 
    $retstr .= " " . $workstr; 
    } 
  else 
    { 
    $retstr = $workstr; 
    } 
  } 
return $retstr; 
} 

?>