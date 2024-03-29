<?php
/**
 * @author Niklas Laxström
 */

$IN = $argv[1] ?? 'koulukeruu/html';
$OUT = $argv[2] ?? 'koulukeruu.json';
process( $IN, $OUT );

function process( string $IN, string $OUT ): void {
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

function parseHtml( string $string ): array {
	$output = [];

	$matches = [];
	preg_match_all( '~<td>(.+)</td><td>(.*)</td>~sU', $string, $matches, PREG_SET_ORDER );

	while ( true ) {
		$set = array_splice( $matches, 0, 6 );
		if ( $set === [] ) {
			break;
		}

		$values = [];
		foreach ( $set as $k => $v ) {
			$values[$k] = html_entity_decode( $v[2] );
		}

		$output[] = $values;
	}

	return $output;
}
