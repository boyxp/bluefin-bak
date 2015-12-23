<?php
$classmap
->set('dispatcher', array(
	'interface' => 'component\dispatcher',
	'impls'     => array(
		'default' => 'component\dispatcher\_default',
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
->set('view', array(
	'interface' => 'component\view',
	'impls'     => array(
		'default' => 'component\view\json',
		'html'    => 'component\view\html',
		'xml'     => 'component\view\xml',
		'json'    => 'component\view\json',
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
		'mongo'   => 'library\orm\connection\mongo',
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
		'mongo'   => 'library\orm\query\mongo',
	),
))
->set('table', array(
	'interface' => 'library\orm\table',
	'impls'     => array(
		'default' => 'library\orm\table\_default',
		'pdo'     => 'library\orm\table\_default',
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
->set('resize', array(
	'interface' => 'library\image\resize',
	'impls'     => array(
		'default' => 'library\image\resize\gd',
		'gd'      => 'library\image\resize\gd',
	),
))
->set('session', array(
	'interface' => 'library\session',
	'impls'     => array(
		'default' => 'library\session\redis',
		'redis'   => 'library\session\redis',
	),
))
->set('file', array(
	'interface' => 'library\file\info',
	'impls'     => array(
		'default' => 'library\file\info\_default',
	),
))
->set('logWriter', array(
	'interface' => 'library\log\writer',
	'impls'     => array(
		'default' => 'library\log\writer\file',
	),
))
->set('logReader', array(
	'interface' => 'library\log\reader',
	'impls'     => array(
		'default' => 'library\log\reader\file',
	),
))
;
