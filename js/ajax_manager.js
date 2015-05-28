/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//Ajax


//added by Oliver Chong - April 21, 2015
/**
 * The enumeration of the XML HTTP Request ready state values
 */
var HttpRequestReadyState = {
	"UNITIALIZED" : 0,
	"LOADING" : 1,
	"LOADED" : 2,
	"INTERACTIVE" : 3,
	"COMPLETE" : 4
};


//added by Oliver Chong - April 21, 2015
//for more information on the response code, you can visit:
//https://developer.mozilla.org/en-US/docs/Web/HTTP#HTTP_response_codes
/**
 * The enumeration of the XML HTTP Request server response code values
 */
var HttpRequestResponseCode = {
	//Informational responses
	"CONTINUE" : 100,
	//Success responses
	"OK" : 200,
	"PARTIAL_CONTENT" : 206,
	//Redirection responses
	"MOVED_PERMANENTLY" : 301,
	"FOUND" : 302,
	//Client error responses
	"BAD_REQUEST" : 400,
	"FORBIDDEN" : 403,
	"NOT_FOUND" : 404,
	//Server error responses
	"INTERNAL_SERVER_ERROR" : 500,
	"SERVICE_UNAVAILABLE" : 503
};


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//added by Oliver Chong - April 21, 2015
/**
 * The class that handles Ajax calls from the client
 * @author oliver.chong
 */
function AjaxManager()
{
	if ( window.XMLHttpRequest )
	{
		//IE7+, Firefox, Chrome, Opera, Safari
		this.m_cXmlHttp = new XMLHttpRequest();
	}
	else if ( window.ActiveXObject )
	{
		//IE5, IE6 (old browsers)
		try
		{
			this.m_cXmlHttp = new ActiveXObject( "Msxml2.XMLHTTP" );
		}
		catch ( e )
		{
			try
			{
				this.m_cXmlHttp = new ActiveXObject( "Microsoft.XMLHTTP" );
			}
			catch ( e )
			{
				console.log( "AjaxManager: Error!", e );
				return false;
			}
		}
	}
};


//added by Oliver Chong - April 21, 2015
/**
 * Performs the Ajax call
 * @param boolean bPost : if true, use Ajax POST otherwise use Ajax GET
 * @param string sUrl : the URL of the server call
 * @param string sUrlParams : the parameters to be passed along with the server call URL
 * @param function fCallback: the callback function
 */
AjaxManager.prototype.call = function( bPost, sUrl, sUrlParams, fCallback /* multiple arguments for the callback function */ )
{
	var that = this;

	//get the callback parameters if applicable
	var aCallbackParams = null;
	if ( arguments.length > 4 )
	{
		aCallbackParams = Array.prototype.slice.call( arguments, 4 );
	}

	//upon getting the server response
	this.m_cXmlHttp.onreadystatechange = function() {
		//server response successfully received
		if ( that.m_cXmlHttp.readyState == HttpRequestReadyState["COMPLETE"] )
		{
			if ( that.m_cXmlHttp.status == HttpRequestResponseCode["OK"] )
			{
				if ( typeof( fCallback ) === "function" )
				{
					//var ajaxResponse = JSON.parse( that.m_cXmlHttp.responseText );

					if ( aCallbackParams )
					{
						//put the ajax response as the very first argument for the callback
						aCallbackParams.unshift( that.m_cXmlHttp.responseText );
					}
					else
					{
						aCallbackParams = [ that.m_cXmlHttp.responseText ];
					}

					//invoke the callback function
					fCallback.apply( null, aCallbackParams );
				}
			}
			else
			{
				console.log( "AjaxManager: Error!", "Server responded with status: " + this.m_cXmlHttp.status );
			}
		}
	}

	//send request through POST
	if ( bPost )
	{
		this.m_cXmlHttp.open( "POST", sUrl, true );

		if ( sUrlParams )
		{
			//to post data like an HTML form
			this.m_cXmlHttp.setRequestHeader( "Content-type","application/x-www-form-urlencoded" );
			this.m_cXmlHttp.send( sUrlParams );
		}
		else
		{
			this.m_cXmlHttp.send();
		}
	}
	//send request through GET
	else
	{
		if ( sUrlParams )
		{
			sUrl += "?";
			sUrl += sUrlParams;
			sUrl += "&";
		}
		else
		{
			sUrl += "?"
		}

		this.m_cXmlHttp.open( "GET", ( sUrl + "bust_cache=" + Math.random() ), true );
		this.m_cXmlHttp.send();
	}
};


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
