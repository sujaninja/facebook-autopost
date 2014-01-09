<?php
define("WPSUJAN_PLUGIN_DIR",basename(dirname(__FILE__)) );
spl_autoload_register('autoloadClass');
include_once 'functions.php';
function autoloadClass($class){

	$namespaces=array('Core','MicroblogPoster');

	if(strtolower($class)=='facebook'){
		include_once 'classes/Facebook/facebook.php';
	}else{

		$segments = explode('_', $class);
		if(in_array($segments[0], $namespaces)){
			$class = str_replace($segments[0]."_", '', $class);
			include_once 'classes/'.ucfirst($class).'.php';
		}


	}
	
}