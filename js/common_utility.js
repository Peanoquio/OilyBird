//BROWSER COMPATIBILITY


//added by Oliver Chong - February 28, 2015
/**
 * fallback for console.log not working in IE8 and below
 */
 function EnableConsoleFallbackIE8()
 {
	var alertFallback = false;
	if ( typeof console === "undefined" || typeof console.log === "undefined" )
	{
		console = {};
		if ( alertFallback )
		{
			console.log = function( msg ) {
				alert(msg);
			};
		}
		else
		{
			console.log = function() {};
		}
	}
}


//added by Oliver Chong - February 28, 2015
//this is not my original code
//reference: http://stackoverflow.com/questions/12097994/cancel-drag-selection-event
/**
 * Disables text selection and dragging on IE8 and below
 *
 * @method disableTextSelectAndDragIE8
 */
function DisableTextSelectAndDragIE8()
{
	// Disable text selection.
	document.body.onselectstart = function() {
		return false;
	}

	// Disable dragging.
	document.body.ondragstart = function() {
		return false;
	}
}


//added by Oliver Chong - February 28, 2015
//this is not my original code
//reference: http://stackoverflow.com/questions/2400935/browser-detection-in-javascript
/**
 * Get the browser information
 *
 * @method GetBrowserInfo
 * @return {array} : the array containing the browser name and the version
 */
function GetBrowserInfo()
{
	var ua = navigator.userAgent, tem;
	var M = ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];

	if ( /trident/i.test(M[1]) )
	{
		tem=  /\brv[ :]+(\d+)/g.exec(ua) || [];
		return 'IE '+(tem[1] || '');
	}

	if ( M[1]=== 'Chrome' )
	{
		tem= ua.match(/\b(OPR|Edge)\/(\d+)/);
		if(tem!= null) return tem.slice(1).join(' ').replace('OPR', 'Opera');
	}

	M= M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];

	if((tem= ua.match(/version\/(\d+)/i))!= null) M.splice(1, 1, tem[1]);

	//return M.join(' ');
	return M;
}


var g_aBrowserInfo = GetBrowserInfo();
//for IE8 browser and below
if ( g_aBrowserInfo[ 0 ] == "MSIE" && g_aBrowserInfo[ 1 ] <= 8 )
{
	EnableConsoleFallbackIE8();
	DisableTextSelectAndDragIE8();
}


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * This sets the default value for the variable if none has been provided
 *
 * @method defaultValue
 * @param {object} variable : the variable
 * @param {any type} value : the default value to be assigned to the variable
 * @return {object} : the variable containing the default value if none is provided
 */
function defaultValue( variable, value )
{
	variable = ( typeof( variable ) !== 'undefined' && variable !== null ) ? variable : value;
	return variable;
}


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//COMMON UTIL


//added by Oliver Chong - February 25, 2015
/**
 * The constructor of the common utility class
 *
 * @class CommonUtil
 * @constructor CommonUtil
 */
var CommonUtil = function()
{
};


//added by Oliver Chong - February 25, 2015
/**
 * This format the time in HH:MM:SS format based on the total number of seconds
 *
 * @method FormatTime
 * @param {unsigned} nTotalTimeInSecs : the total time in seconds
 * @return string : a string showing the time in HH:MM:SS format
 */
CommonUtil.FormatTime = function( nTotalTimeInSecs )
{
	var seconds = nTotalTimeInSecs % 60;
	var minutes = Math.floor( nTotalTimeInSecs / 60 );
	var hours = Math.floor( minutes / 60 );
	var minutes = minutes % 60;

	if ( hours == 0 )
	{
		hours = '00';
	}
	else if ( hours < 10 )
	{
		hours = '0' + hours;
	}

	if ( minutes == 0 )
	{
		minutes = '00';
	}
	else if ( minutes < 10 )
	{
		minutes = '0' + minutes;
	}

	if ( seconds == 0 )
	{
		seconds = '00';
	}
	else if ( seconds < 10 )
	{
		seconds = '0' + seconds;
	}

	var formattedTime = hours + ':' + minutes + ':' + seconds;

	return formattedTime;
};


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//INHERITANCE


//added by Oliver Chong - February 15, 2015
//this is not my original code
//reference: http://phrogz.net/JS/classes/OOPinJS2.html
/**
 * Overload the native Function prototype with this function to support prototype inheritance
 *
 * @method inheritsFrom
 * @param {object} parentClassOrObject : the parent class or object that your object want to inherit from
 * @return {object} : your object after inheriting the parent class/object
 */
Function.prototype.inheritsFrom = function( parentClassOrObject )
{
	if ( parentClassOrObject.constructor == Function )
	{
		//Normal Inheritance
		this.prototype = new parentClassOrObject;
		this.prototype.constructor = this;
		this.prototype.parent = parentClassOrObject.prototype;
	}
	else
	{
		//Pure Virtual Inheritance
		this.prototype = parentClassOrObject;
		this.prototype.constructor = this;
		this.prototype.parent = parentClassOrObject;
	}

	return this;
}


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//INTERFACE EMULATOR (through duck typing)


//added by Oliver Chong - February 15, 2015
//this is not my original code
//reference: http://jscriptpatterns.blogspot.sg/2013/01/javascript-interfaces.html
/**
 * The constructor of the Interface class that will simulate interfaces that is not natively supported in JavaScript
 *
 * @class Interface
 * @constructor Interface
 * @param {string} sName : the name of the interface class
 * @param {array} aMethods : the array containing the method names of the interface class
 */
var Interface = function( sName, aMethods )
{
    if ( arguments.length != 2 )
	{
        throw new Error("Interface constructor called with " + arguments.length + "arguments, but expected exactly 2.");
    }

    this.m_sName = sName;
    this.m_aMethods = [];

	//store the interface method names
    for ( var i = 0, len = aMethods.length; i < len; i++ )
	{
        if ( typeof aMethods[i] !== 'string' )
		{
            throw new Error("Interface constructor expects method names to be passed in as a string.");
        }

        this.m_aMethods.push( aMethods[i] );
    }//end loop
};


//added by Oliver Chong - February 15, 2015
//this is not my original code
//reference: http://jscriptpatterns.blogspot.sg/2013/01/javascript-interfaces.html
/**
 * Checks if the object implements the specified interface
 *
 * @method EnsureImplements
 * @param {object} object : the object to check
 * @param {arguments} arguments : Interface instances that you want to verify against
 */
Interface.EnsureImplements = function( object /* <subsequent parameters> pass Interface instances that you want to verify against */ )
{
    if ( arguments.length < 2 )
	{
        throw new Error("Function Interface.EnsureImplements called with " + arguments.length + "arguments, but expected at least 2.");
    }

	//the subsequent arguments after the object (first parameter) are the Interface instances that you want to verify against
    for ( var i = 1, len = arguments.length; i < len; i++ )
	{
        var interface = arguments[i];
        if ( interface.constructor !== Interface )
		{
            throw new Error("Function Interface.EnsureImplements expects arguments two and above to be instances of Interface.");
        }

		//go through the methods of the current Interface instance
        for ( var j = 0, methodsLen = interface.m_aMethods.length; j < methodsLen; j++ )
		{
			//check if the interface method was implemented
            var method = interface.m_aMethods[j];
            if ( !object[method] || typeof object[method] !== 'function' )
			{
                throw new Error("Function Interface.EnsureImplements: object " + object.constructor.name + " does not implement the " + interface.m_sName + " interface. Method " + method + " was not found.");
            }
        }//end loop
    }//end loop
};


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//CUSTOM EVENT MANAGER


//added by Oliver Chong - February 15, 2015
//reference: https://developer.mozilla.org/en-US/docs/Web/Guide/Events/Creating_and_triggering_events
/**
 * The constructor of the event manager class that will manage custom event listeners
 *
 * @class EvtMgr
 * @constructor EvtMgr
 */
function EvtMgr()
{
};


//associative array of custom events
EvtMgr.m_aEvents = {};


//added by Oliver Chong - February 15, 2015
/**
 * Creates the custom event
 *
 * @method CreateEvent
 * @param {string} sEventName : the event name
 * @param {array} aOptions : the associative array containing data that will be added to the event object in this format
 * @param {boolean} bBubbles : if true, events will bubble to ancestors of the element which fired the event
 * @param {boolean} bCancelable : if true, events can be canceled using the event object’s stopPropagation() method
 */
EvtMgr.CreateEvent = function( sEventName, aOptions, bBubbles, bCancelable )
{
	aOptions = defaultValue( aOptions, {} );
	bBubbles = defaultValue( bBubbles, false );
	bCancelable = defaultValue( bCancelable, false );

	var event = null;

	//for IE8 browser and below
	if ( g_aBrowserInfo[ 0 ] == "MSIE" && g_aBrowserInfo[ 1 ] <= 8 )
	{
		event = jQuery.Event( sEventName );
		event.detail = aOptions;
	}
	else
	{
		//create the custom event that can support options
		//if the user did not pass bubbles and cancelable properties, by default, their values will be set to false
		//event = new CustomEvent( sEventName, { "detail" : aOptions, "bubbles" : bBubbles, "cancelable" : bCancelable } );

		// Create the event.
		event = document.createEvent( "Event" );
		// Define the event name, bubbles flag, cancelable flag
		event.initEvent( sEventName, bBubbles, bCancelable );
		event.detail = aOptions;
	}

	if ( event )
	{
		EvtMgr.m_aEvents[ sEventName ] = event;
	}
};


//added by Oliver Chong - February 15, 2015
/**
 * Updates the detail of the custom event
 *
 * @method UpdateEventDetails
 * @param {string} sEventName : the event name
 * @param {string} sDetailParam : the detail parameter name
 * @param {variable} vDetailParamVal : the detail parameter value
 */
EvtMgr.UpdateEventDetails = function( sEventName, sDetailParam, vDetailParamVal )
{
	EvtMgr.m_aEvents[ sEventName ].detail[ sDetailParam ] = vDetailParamVal;
};


//added by Oliver Chong - February 15, 2015
/**
 * Emits/dispatches the custom event
 *
 * @method EmitEvent
 * @param {string} sElementName : the element name
 * @param {string} sEventName : the event name
 */
EvtMgr.EmitEvent = function( sElementName, sEventName )
{
	//for IE8 browser and below
	if ( g_aBrowserInfo[ 0 ] == "MSIE" && g_aBrowserInfo[ 1 ] <= 8 )
	{
		$( "#"+sElementName ).trigger( EvtMgr.m_aEvents[ sEventName ] );
	}
	else
	{
		var cElement = document.getElementById( sElementName );

		// target can be any Element or other EventTarget.
		cElement.dispatchEvent( EvtMgr.m_aEvents[ sEventName ] );
	}
};


//added by Oliver Chong - February 15, 2015
/**
 * This will add an event listener to the specified DOM element
 *
 * @method AddEventListener
 * @param {object} cElement : the DOM element
 * @param {string} sElementName : the element name
 * @param {string} sEventName : the event name
 * @param {function} fEventHandlerFunc : the event handler function
 */
EvtMgr.AddEventListener = function( cElement, sElementName, sEventName, fEventHandlerFunc )
{
	if ( typeof( cElement ) !== 'undefined' && cElement !== null )
	{
		//this is left blank for now
	}
	else
	{
		cElement = document.getElementById( sElementName );
	}

	if ( cElement.addEventListener )
	{
		cElement.addEventListener( sEventName, fEventHandlerFunc, false );
	}
	//for IE9 and below
	else
	{
		//cElement.attachEvent( 'on'+sEventName, fEventHandlerFunc );

		$( "#"+sElementName ).on( sEventName, fEventHandlerFunc );
	}
};