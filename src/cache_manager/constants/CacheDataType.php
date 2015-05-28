<?php

namespace OilyBird\CacheMgr\Constants;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * This is the enumeration for the supported data types in the cache
 * @author oliver.chong
 *
 */
abstract class CacheDataType
{
	const STRING = "string";
	const LISTS = "list";
	const HASH = "hash";
	const SET = "set";
	const SORTED_SET = "zset";
}

?>