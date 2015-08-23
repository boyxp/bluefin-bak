<?php
$classmap
->set('dispatcher', array(
	'interface' => 'component\dispatcher',
	'impls'     => array(
		'default' => 'component\dispatcher\_default',
	),
))
->set('injector', array(
	'interface' => 'component\injector',
	'impls'     => array(
		'default' => 'component\injector\_default',
	),
))
->set('registry', array(
	'interface' => 'component\registry',
	'impls'     => array(
		'default' => 'component\registry\apc',
		'apc'     => 'component\registry\apc',
	),
))
->set('request', array(
	'interface' => 'component\request',
	'impls'     => array(
		'default' => 'component\request\_default',
	),
))
->set('response', array(
	'interface' => 'component\response',
	'impls'     => array(
		'default' => 'component\response\_default',
	),
))
->set('router', array(
	'interface' => 'component\router',
	'impls'     => array(
		'default' => 'component\router\_default',
	),
))
->set('redis', array(
	'interface' => 'library\redis',
	'impls'     => array(
		'default' => 'library\redis\_default',
	),
))
;
