<?php

/** Run each standalone importer with isolated symbols and caches. */
$root = dirname( __DIR__ );
$failed = false;
foreach ( [ 'kirjasanasto', 'namnledslexikon', 'slangipaikannimet', 'sosva', 'vepsä', 'tools' ] as $directory ) {
	foreach ( glob( "$root/$directory/*.php" ) as $file ) {
		$command = [ PHP_BINARY, "$root/vendor/bin/rector", 'process', $file ];
		array_push( $command, ...array_slice( $argv, 1 ) );
		$environment = getenv();
		$environment['KOTUS_RECTOR_FILE'] = $file;
		// Separate processes prevent standalone scripts from sharing global function signatures.
		// phpcs:ignore MediaWiki.Usage.ForbiddenFunctions.proc_open
		$process = proc_open( $command, [ STDIN, STDOUT, STDERR ], $pipes, $root, $environment );
		if ( $process === false || proc_close( $process ) !== 0 ) {
			$failed = true;
		}
	}
}

exit( $failed ? 1 : 0 );
