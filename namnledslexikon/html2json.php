<?php
/**
 * @author Niklas Laxström
 */

$IN = isset( $argv[1] ) ? $argv[1] : 'ledlex';
$OUT = isset( $argv[2] ) ? $argv[2] : 'data.json';
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
		$parsed = parseHtml( $input );
		if ( $parsed !== [] ) {
			$all[parseIndex( $filename )] = $parsed;
		}
	}

	$json = json_encode( $all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	file_put_contents( $OUT, $json );
}

function parseIndex( $filename ) {
	$index = basename( $filename, '.php' );
	switch ( $index ) {
	case 'A1':
		return 'Ä';
	case 'O1':
		return 'Ö';
	case 'I':
		return 'IJ';
	case 'V':
		return 'VYZ';
	case 'A2':
		return 'Å';
	default:
		return $index;
	}
}

function parseHtml( $string ) {
	$string = mb_convert_encoding( $string, 'UTF-8', 'ISO-8859-15' );
	$string = preg_replace( '~\R~u', "\n", $string );

	$matches = [];
	$re = '~<p>.*ankkuri.*<strong [^>]+>([^<]+)</strong>(.*)</p>(\n<p>(?!<a).*</p>)*~';
	preg_match_all( $re, $string, $matches, PREG_SET_ORDER );

	$output = [];
	foreach ( $matches as $m ) {
		$content = trim( $m[2] );

		if ( isset( $m[3] ) ) {
			$continuations = [];
			preg_match_all( '~^<p>(?!<a name)(.*)</p>$~m', $m[0], $continuations, PREG_SET_ORDER );

			foreach ( $continuations as $c ) {
				$content .= "\n\n" . trim( $c[1] );
			}
		}

		$output[] = [
			trim( $m[1] ),
			$content,
		];
	}

	return $output;
}
