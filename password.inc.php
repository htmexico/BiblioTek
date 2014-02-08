<?php

			function patch_username( $login_name )
			{
				$login_name = str_replace( "Ñ", "n", $login_name );
				$login_name = str_replace( "ñ", "n", $login_name );
				$login_name = str_replace( " ", "_", $login_name );
				
				return $login_name;
			}
	
			/* funciones originales en javascript */
			function generaNumAleatorio($hasta,$llamada)
			{
				$num = rand();
				
				if( ($llamada==1)&&($num==0) )
				{
					$num='';
					generaNumAleatorio($hasta,$llamada);		
				}
				
				return $num;
			}		
			
			function generadorPassword( $maxDigitos, $maxNumAleatorios, $nLlamada, $no_usar )
			{
				$numRandom;
				
				if( $maxDigitos>0)
				{
					$nLlamada++;
					$numRandom=generaNumAleatorio($maxNumAleatorios,$nLlamada);				
					
					$maxDigitos--;					
					$password=generadorPassword($maxDigitos,$maxNumAleatorios,$nLlamada,"")+$numRandom;
				}
				else
					$password = "";
				
				return $password;				
			}
			/* funciones originales en javascript */
			
?>