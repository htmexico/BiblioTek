function CreateAjaxObject() 
{
	 try {
			 objetus = new ActiveXObject("Msxml2.XMLHTTP");
	} catch ( e) {
			 try {
					 objetus= new ActiveXObject ("Microsoft.XMLHTTP");
			 } catch (E) {
					  objetus= false;
			}
	}
	if (! objetus && typeof XMLHttpRequest!= 'undefined') {
			 objetus = new XMLHttpRequest();
	}
	
	return objetus
}

var Request;  // avoid reporting errors on Mozilla Firebug

// class
Request = function()
{
	Request = window.XMLHttpRequest ? new XMLHttpRequest() : window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : false;

	Request.fn = function(fn)
	{
		if(typeof fn=='string')return new Function(fn);
		if(typeof fn=='function')return fn;
		if(typeof fn=='undefined')return new Function();
		
		return null;
	};

	Request.config = 
	{
		'xml' : false,
		'404' : 'The File on the request was not found. Please report this to your DBAdmin'
	};

	Request.success = false;
	Request.loading = false;
	Request.error = false;

	Request.submit = function(obj)
	{
		if(Request)	
		{
			Request.onreadystatechange = function()
			{
				if(Request.readyState==4)
				{
					if ( Request.status == 200 )
					{
						var Response = Request.config['xml'] ? Request.responseXml : Request.responseText;
						if(Request.success)
						{
							Request.fn(Request.success).call(this, Response, Request);
						} 
						else 
						{
							Request.fn(obj.success).call(this, Response, Request);
						}
					}
					else 
					{
						if(Request.success)
						{
							if( Request.error )							
								Request.fn(Request.error).call(this, Request.config['404']);
							else
								alert( Request.config['404'] + "sicc" );		
						}  
						else 
						{
							if( obj.Error )
								Request.fn(obj.error).call(this, Request.config['404']);
							else
								alert( Request.config['404'] );
						}

					}					
				} 
				else 
				{
					if (/Firefox[\/\s](\d+\.\d+)/.test(navigator.userAgent))
					{
						if(console) console.log('loading '+obj.url+'...');
					}
					
					if(Request.success)
					{
						if( Request.loading )
							Request.fn(Request.loading).call(this, Request);
					} 
					else 
					{
						if( obj.loading )
							Request.fn(obj.loading).call(this, Request);
					}

				}


			}

			var str_method = "GET";
			var str_params = "";
			
			if( obj.params )	
				str_params = obj.params;
				
			if( obj.method )
			{
				str_method = obj.method;
			}
			
			//if(console) console.log( str_method );
			
			if( str_method == "GET" )
			{ 	
				Request.open("GET", obj.url + "?" +str_params, true);
				Request.send(null);
			}
			else			
			{
				Request.open( "post", obj.url, true);
				
				if(/Safari/.test(navigator.userAgent))
				{
					//Send the proper header information along with the request
					Request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				}				
				else
				{
					//Send the proper header information along with the request
					Request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
					Request.setRequestHeader("Content-length", str_params.length );
					Request.setRequestHeader("Connection", "close");
				}
				
				Request.send(str_params);
				
				if(/Safari/.test(navigator.userAgent))
				{
					//alert( Request );
				}
			}
			
		} 
		else 
			obj.error.call(this, Request);

	}

	return Request;

}
