<?php
/****
  están generando errores los BLOB en versiones 5.2.2 > (y mayores)
 **/
class ibase_blob {
        var $blobid;
        var $length;
        var $numseg;
        var $maxseg;
        var $stream;
        var $isnull;
        var $data;
		
		var $mylink;

        function ibase_blob( $db_already_opened_link, $blobid ) 
		{
            $this->mylink = $db_already_opened_link;
			$this->blobid = $blobid;
        }
		
        function destroy() 
		{
                $this->data = "";
        }

        function retrieve_all() 
		{
                $this->retrieve_info();
                $this->retrieve_data($this->length);
        }

        function retrieve_info() 
		{
		//	if( phpversion() >= "5.2.3" )
			//{
				list($this->length,$this->numseg,
                	$this->maxseg,$this->stream,
                	$this->isnull)
                	=ibase_blob_info( $this->mylink, $this->blobid );
			//}
			//else
			//{
			//	list($this->length,$this->numseg,
              //  	$this->maxseg,$this->stream,
                	//$this->isnull)
                	//=ibase_blob_info($this->blobid);
			//}
        }
        
        function retrieve_data( $size='1024') 
		{
               // if( phpversion() >= "5.2.3" )
			//	{
					$bl=ibase_blob_open($this->mylink, $this->blobid);
				//}
				//else
				  //  $bl=ibase_blob_open($this->blobid);
				
                if ($bl) 
				{
                        while($buf = ibase_blob_get($bl, $size)) 
						{
                                $this->data .= $buf;
                        }
						
                        ibase_blob_cancel($bl);
                }
        }
}

?>