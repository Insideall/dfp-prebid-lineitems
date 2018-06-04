<?php

putenv("HOME=".dirname(__DIR__));

spl_autoload_register(function ($class){	
	$root = dirname(__DIR__)."/";
	$file = $root.str_replace('\\', '/', lcfirst($class)).".php";	
	if(is_readable($file)) { require $file; }
});

