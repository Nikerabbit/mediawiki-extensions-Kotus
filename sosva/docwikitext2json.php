<?php
/**
 * @author Niklas Laxström
 */

$IN = isset( $argv[1] ) ? $argv[1] : 'voguli.txt';
$OUT = isset( $argv[2] ) ? $argv[2] : 'data.json';
process( $IN, $OUT );

function process( $IN, $OUT ) {
	$all = [];

	$all = parse( new SplFileObject( $IN ) );

	file_put_contents( $OUT, json_encode( $all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
	echo "'_,_^\n";
}

function parse( $lineIterator ) {
	$output = [];

	$index = null;

	foreach ( $lineIterator as $line ) {
		$line = trim( $line );

		if ( trim( $line ) === '' ) {
			continue;
		}

		if ( strpos( $line, 'text-align:center' ) !== false ) {
			$ps = '~^<div style=".+">(.+)</div>$~';
			$line = preg_replace( $ps, '\1', $line );
			$index = $line;
			continue;
		}

		$ps = preg_quote( '<div style="margin-left:0.1972in;margin-right:0in;">_</div>', '~' );
		$ps = str_replace( '_', '(.*)', $ps );
		$line = preg_replace( "~^$ps$~", '\1', $line );
		$line = str_replace( '&nbsp;', ' ', $line );
		$line = str_replace( '{{anchor|GoBack}}', '', $line );
		$line = str_replace( '<nowiki>', '', $line );
		$line = str_replace( '</nowiki>', '', $line );
		$line = rtrim( $line, " '" );

		if ( $line === '' ) {
			continue;
		}

		$ps = "~^(\d )?''(.+)''(.+)/(.+)( \d+)$~u";
		if ( !preg_match( $ps, $line, $match ) ) {
			echo "Skipping: $line\n";
			continue;
		}

		$expression = str_replace( "'", '', $match[2] );

		$output[] = [
			$index,
			trim( $match[1] ),
			trim( $expression ),
			trim( $match[3] ),
			trim( $match[4] ),
			trim( $match[5] ),
		];
	}

	return $output;
}
