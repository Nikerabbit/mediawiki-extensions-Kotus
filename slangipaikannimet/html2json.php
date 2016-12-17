<?php

$IN = isset( $argv[1] ) ? $argv[1] : 'koulukeruu/html';
$OUT = isset( $argv[2] ) ? $argv[2] : 'koulukeruu.json';
process( $IN, $OUT );

function process( $IN, $OUT ) {
	$all = [];

	$iter = new DirectoryIterator( $IN );
	foreach ( $iter as $entry ) {
		if ( !$entry->isFile() ) {
			continue;
		}

		$filename = $entry->getFilename();
		$input = file_get_contents( "$IN/$filename" );
		$all = array_merge( $all, parseHtml( $input ) );
		echo ".";
	}

	file_put_contents( $OUT, json_encode( $all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );

	echo "\n";
}

function parseHtml( $string ) {
	$output = [];

	$matches = [];
	preg_match_all( '~<td>(.+)</td><td>(.*)</td>~sU', $string, $matches, PREG_SET_ORDER );

	while ( ( $set = array_splice( $matches, 0, 6 ) ) !== [] ) {
		$keys = $values = [];
		foreach ( $set as $k => $v ) {
			$keys[$k] = $v[1];
			$values[$k] = html_entity_decode( $v[2] );
		}

		$output[] = $values;
	}

	return $output;
}
