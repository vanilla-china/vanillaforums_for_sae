<?php if (!defined('APPLICATION')) exit();

if(defined('SAE_ACCESSKEY')){

	define('PATH_LOCAL_CONF', 'saekv://conf');
	define('PATH_LOCAL_CACHE', 'saemc://filecache');
	define('PATH_LOCAL_UPLOADS', 'saestor://myasyz');
	define('VANILLA_FILE_PUT_FLAGS', 0);

}
