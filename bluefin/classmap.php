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
->set('connection', array(
	'interface' => 'library\orm\connection',
	'impls'     => array(
		'default' => 'library\orm\connection\pdo',
		'pdo'     => 'library\orm\connection\pdo',
	),
))
->set('resultset', array(
	'interface' => 'library\orm\resultset',
	'impls'     => array(
		'default' => 'library\orm\resultset\_default',
	),
))
->set('record', array(
	'interface' => 'library\orm\record',
	'impls'     => array(
		'default' => 'library\orm\record\_default',
	),
))
->set('query', array(
	'interface' => 'library\orm\query',
	'impls'     => array(
		'default' => 'library\orm\query\pdo',
		'pdo'     => 'library\orm\query\pdo',
	),
))
->set('table', array(
	'interface' => 'library\orm\table',
	'impls'     => array(
		'default' => 'library\orm\table\pdo',
		'pdo'     => 'library\orm\table\pdo',
	),
))
->set('pool', array(
	'interface' => 'library\orm\pool',
	'impls'     => array(
		'default' => 'library\orm\pool\_default',
	),
))
->set('handle', array(
	'interface' => 'library\image\handle',
	'impls'     => array(
		'default' => 'library\image\handle\gd',
		'gd'      => 'library\image\handle\gd',
	),
))
->set('rotate', array(
	'interface' => 'library\image\rotate',
	'impls'     => array(
		'default' => 'library\image\rotate\gd',
		'gd'      => 'library\image\rotate\gd',
	),
))
->set('flip', array(
	'interface' => 'library\image\flip',
	'impls'     => array(
		'default' => 'library\image\flip\gd',
		'gd'      => 'library\image\flip\gd',
	),
))
->set('session', array(
	'interface' => 'library\session',
	'impls'     => array(
		'default' => 'library\session\redis',
		'gd'      => 'library\session\redis',
	),
))
;
