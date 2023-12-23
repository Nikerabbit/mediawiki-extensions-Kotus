<?php
/**
 * @author Niklas Laxström
 */

$IN = $argv[1] ?? 'voguli.txt';
$OUT = $argv[2] ?? 'data.json';
process( $IN, $OUT );

function process( string $IN, string $OUT ): void {
	$all = parse( new SplFileObject( $IN ) );

	$json = json_encode( $all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	file_put_contents( $OUT, $json );
	echo "'_,_^\n";
}

function parse( $lineIterator ): array {
	$output = [];

	$index = null;

	foreach ( $lineIterator as $line ) {
		$line = trim( $line );

		if ( trim( $line ) === '' ) {
			continue;
		}

		if ( str_contains( $line, 'text-align:center' ) ) {
			$ps = '~^<div style=".+">(.+)</div>$~';
			$line = preg_replace( $ps, '\1', $line );
			$index = $line;
			continue;
		}

		$ps = preg_quote( '<div style="margin-left:0.1972in;margin-right:0;">_</div>', '~' );
		$ps = str_replace( '_', '(.*)', $ps );
		$line = preg_replace( "~^$ps$~", '\1', $line );
		$line = str_replace( '&nbsp;', ' ', $line );
		$line = str_replace( '{{anchor|GoBack}}', '', $line );
		$line = str_replace( '<nowiki>', '', $line );
		$line = str_replace( '</nowiki>', '', $line );
		$line = str_replace( "''-''", '-', $line );
		$line = str_replace( "'' ''", ' ', $line );
		$line = preg_replace( "~''\(''(.*?)''\)~", '(\1)\'\'', $line );
		$line = rtrim( $line, " '" );

		if ( $line === '' ) {
			continue;
		}

		$ps = "~^(\d )?''([^']+)''(.+)/(.+?)( [0-9, ]+)$~u";
		if ( !preg_match( $ps, $line, $match ) ) {
			echo "Skipping: $line\n";
			continue;
		}

		$expression = $match[2];

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
