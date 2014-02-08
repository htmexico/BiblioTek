<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<?php

   $numero_cutter = "";

   $lastname = "";
   $firstname = "";	
   $action = "";

   if( isset($_POST["lastname"]) )
   {
   	   $lastname = $_POST["lastname"];
   }

   if( isset($_POST["firstname"]) )
   {
   	   $firstname = $_POST["firstname"];
   }

   if( isset($_POST["action"]) )
   {
   	   $action = $_POST["action"];
   }

   if( $action == "go" )
   {
   
   /***********
   
Based on LC Cutter Table (No. 3)

After initial Vowels 
  for the second letter    b    d   l-m    n     p     r    s-t   u-y 
  use number               2    3    4     5     6     7     8     9 
After initial letter S                               
  for the second letter    a    ch   e    h-i   m-p    t     u   w-z
  use number               2    3    4     5     6     7     8    9 
After inital letters Qu
  for the third letter     a    e    i     o     r     t     y 
  use number               3    4    5     6     7     8     9 
For initial letters Qa Qt, use 2 29
After other initial Consonants
  for the second letter    a    e    i     o     r     u     y 
  use number               3    4    5     6     7     8     9 
For Expansion
  for the letter          a-d  e-h  i-l   m-o   p-s   t-v   w-z 
  use number               3    4    5     6     7     8     9 
   
    **/
   
   		// calcular número de cutter
		$clastname = strtoupper($lastname);
		
		// primera letra
		$cprimeraletra = substr( $clastname, 0, 1 );  
		$csegundaletra = substr( $clastname, 1, 1 ); 
		
		if( $cprimeraletra == "Q" ) // Ajustar para Qu
		{
			if( $csegundaletra == "U" )
			{
			   $cprimeraletra = "Q";  // p.e. Quinn
			   $csegundaletra = substr( $clastname, 2, 1 ); 
			}
			else if( $csegundaletra >= "A" and $csegundaletra <= "T" )
			{
			   $cprimeraletra .= $csegundaletra;  // p.e. Quarell
			   $csegundaletra = substr( $clastname, 2, 1 );
			}
		}

		if( $cprimeraletra == "C" ) // Ajustar para CH
		{
			if( substr( $clastname, 1, 1 ) == "H" )
			{
			   $cprimeraletra = "C";  // p.e. Schumacher o Schmidth
			   $csegundaletra = substr( $clastname, 2, 1 ); 
			}
		}
		
		if( $cprimeraletra == "A" or 
			$cprimeraletra == "E" or
			$cprimeraletra == "I" or
			$cprimeraletra == "O" or
			$cprimeraletra == "U" )
		{
			// primera letra vocal
			
			$numero_cutter = $cprimeraletra;
			
			if( $csegundaletra == "B" ) $numero_cutter .= "2";
			else if( $csegundaletra == "D" ) $numero_cutter .= "3";
			else if( $csegundaletra >= "L" and $csegundaletra <= "M" ) $numero_cutter .= "4";
			else if( $csegundaletra == "N" ) $numero_cutter .= "5";
			else if( $csegundaletra == "P" ) $numero_cutter .= "6";
			else if( $csegundaletra == "R" ) $numero_cutter .= "7";
			else if( $csegundaletra >= "S"  and $csegundaletra <= "T" ) $numero_cutter .= "8";
			else if( $csegundaletra >= "U"  and $csegundaletra <= "Y" ) $numero_cutter .= "9";
		}
		else if( $cprimeraletra == "S" )
		{
			// primera letra S
			
			$numero_cutter = $cprimeraletra;
			
			if( $csegundaletra == "A" ) $numero_cutter .= "2";
			else if( $csegundaletra == "CH" ) $numero_cutter .= "3";
			else if( $csegundaletra == "E" ) $numero_cutter .= "4";
			else if( $csegundaletra >= "H" and $csegundaletra <= "I" ) $numero_cutter .= "5";
			else if( $csegundaletra >= "M" and $csegundaletra <= "P" ) $numero_cutter .= "6";
			else if( $csegundaletra == "T" ) $numero_cutter .= "7";
			else if( $csegundaletra == "U" ) $numero_cutter .= "8";
			else if( $csegundaletra >= "W"  and $csegundaletra <= "Z" ) $numero_cutter .= "9";		
		}
		else if( $cprimeraletra == "QU" )
		{
			// primeras letras QU
			
			$numero_cutter = $cprimeraletra;
			
			if( $csegundaletra == "A" ) $numero_cutter .= "3";
			else if( $csegundaletra == "E" ) $numero_cutter .= "4";
			else if( $csegundaletra == "I" ) $numero_cutter .= "5";
			else if( $csegundaletra == "O" ) $numero_cutter .= "6";
			else if( $csegundaletra == "R" ) $numero_cutter .= "7";
			else if( $csegundaletra == "T" ) $numero_cutter .= "8";
			else if( $csegundaletra == "Y" ) $numero_cutter .= "9";
		}		
		else if( $cprimeraletra >= "QA" and $cprimeraletra >= "QT" )
		{
		  /*qa  2
		    qá
			qb  3
			qc  4
			qch 5
			qd  6
			qe  7
			qé
			qf  8
			qg  9
			qh  10
			qi  11
			qj  12
			qk  13
			ql  14 
			qll  14 
			qm  15
			qn  16
			qo  17
			qp  18
			qq  19
			qr  20
			qs  21
			qt  22 */
		}
		else if( $cprimeraletra >= "0" and $cprimeraletra <= "9")
		{
			// para numeros
			//	Numbers go in A 12 - 19. Entries beginning with numerals are assigned an A Cutter (.A12 - .A19) and numbered to precede all entries beginning with the letter A. (See G 60, sec. 3.b. , Subject Cataloging Manual: Shelflisting.)
		}
		else
		{
			// para otras consonantes iniciales
			$numero_cutter = $cprimeraletra;
			
			if( $csegundaletra == "A" ) $numero_cutter .= "3";
			else if( $csegundaletra == "E" ) $numero_cutter .= "4";
			else if( $csegundaletra == "I" ) $numero_cutter .= "5";
			else if( $csegundaletra == "O" ) $numero_cutter .= "6";
			else if( $csegundaletra == "R" ) $numero_cutter .= "7";
			else if( $csegundaletra == "U" ) $numero_cutter .= "8";
			else if( $csegundaletra == "Y" ) $numero_cutter .= "9";		

/*			
			// para expansion
			if( $csegundaletra >= "A" and $csegundaletra <= "D" ) $numero_cutter .= "3";
			else if( $csegundaletra >= "E" and $csegundaletra <= "H") $numero_cutter .= "4";
			else if( $csegundaletra >= "I" and $csegundaletra <= "L") $numero_cutter .= "5";
			else if( $csegundaletra >= "M" and $csegundaletra <= "O" ) $numero_cutter .= "6";
			else if( $csegundaletra >= "P" and $csegundaletra <= "S" ) $numero_cutter .= "7";
			else if( $csegundaletra >= "T" and $csegundaletra <= "V" ) $numero_cutter .= "8";
			else if( $csegundaletra >= "W"  and $csegundaletra <= "Z" ) $numero_cutter .= "9";		
	*/	
		}		

   } 

 ?>

<body>
<form name="form1" method="post" action="cutter.php">
  <input type=hidden name="action" value="go">

  <table width="80%" border="0" cellspacing="0" cellpadding="0">
    <tr> 
      <td colspan="3"><h1>C&aacute;lculo del N&uacute;mero de Cutter</h1></td>
    </tr>
    <tr> 
      <td width=200px>Author's Last &amp; First Name</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td width=200px> <input type="text" name="lastname" value="<?php echo $lastname;?>">
        &nbsp;
        <input type="text" name="firstname" value="<?php echo $firstname;?>"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td width=200px>&nbsp;<?php echo $numero_cutter;?></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td width=200px><input type="submit" name="Submit" value="Calcular N&uacute;mero de Cutter"></td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>

</body>
</html>
