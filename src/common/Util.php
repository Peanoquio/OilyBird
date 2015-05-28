<?php

namespace OilyBird\Common;


/**
 * This is the utility class for common functions
 * @author oliver.chong
 *
 */
class Util
{

	/**
	 * Formats the exception
	 *
	 * @param Exception $e : the exception object that contains data about the error
	 * @return string : the formatted exception string
	 */
	public static function formatException( /*Exception*/ $e )
	{
		$szExceptionStr = "Exception code: " . $e->getCode() . " message: " . $e->getMessage() ." in line: " . $e->getLine() . " file: " . $e->getFile();
		$szExceptionStr .= $e->getTraceAsString();

		return $szExceptionStr;
	}


	/**
	 * Checks if the string is a valid JSON
	 *
	 * @param string $sStrText : the string text to be checked
	 * @return boolean : if true, the string is a valid JSON
	 */
	public static function isJSON( $sStrText )
	{
		return is_string( $sStrText ) && is_object( json_decode( $sStrText ) ) && ( json_last_error() == JSON_ERROR_NONE ) ? true : false;
	}

}//end class


?>