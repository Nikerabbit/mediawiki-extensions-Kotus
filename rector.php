<?php

declare( strict_types = 1 );

use Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector;
use Rector\Config\RectorConfig;

$file = getenv( 'KOTUS_RECTOR_FILE' );
if ( !$file ) {
	throw new RuntimeException( 'Use composer rector to isolate standalone scripts.' );
}

return RectorConfig::configure()
	->withPaths( [ $file ] )
	->withCache( cacheDirectory: sys_get_temp_dir() . '/kotus-rector-' . sha1( __DIR__ . $file ) )
	->withPhpSets()
	->withComposerBased()
	->withSkip( [
		// Keep consecutive class member declarations together.
		NewlineBetweenClassLikeStmtsRector::class,
	] )
	->withPreparedSets(
		deadCode: true,
		codeQuality: true,
		codingStyle: true,
		typeDeclarations: true,
		privatization: true,
	);
