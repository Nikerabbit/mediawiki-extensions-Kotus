<?php
/**
 * @author Niklas Laxström
 */

ini_set( 'memory_limit', '1G' );

$IN = isset( $argv[1] ) ? $argv[1] : 'data.json';
$OUT = isset( $argv[2] ) ? $argv[2] : 'entrypages';
process( $IN, $OUT );

function parseEntry( array $entry ) {
	$name = implode( ', ', (array)$entry['expression'] );
	// Three items have [aa]
	if ( strpos( $name, '[') !== false ) {
		return [];
	}

	// One item (toikkailla) has references hidden on separate lines!
	$name = preg_replace( '~\n.*~s', '', $name );

	$refValues = [];
	$refKeys = ['ref_a', 'ref_y', 'ref_n'];
	foreach ( $refKeys as $key ) {
		if ( isset( $entry[$key] ) ) {
			$refValues[] = $entry[$key];
		}
	}

	$refPages = '';
	if ( isset( $entry['ref_p'] ) ) {
		$p = implode( ', ', (array)$entry['ref_p'] );
		$refPages = "|$p";
	}

	$refText = trim( implode( ' ', $refValues ) );
	$ref = "{{Evita/ref|$refText$refPages}}";

	$refName = false;
	if ( isset( $entry['ref_n'] ) ) {
		$refName = preg_replace( '~ .*~', '', $entry['ref_n'] );
	}

	$seealso = false;
	if ( isset( $entry['seealso'] ) ) {
		$seealso = array_map( 'trim', (array)$entry['seealso'] );
		$seealso = array_unique( $seealso );
		$seealso = implode( ', ', $seealso );
	}

	$values = [
		'id' => $entry['id'],
		'ref' => $ref,
		'ref_name' => $refName,
		'meaning' => isset( $entry['meaning'] ) ? $entry['meaning'] : false,
		'note' => isset( $entry['note'] ) ? $entry['note'] : false,
		'seealso' => $seealso,
	];

	$values = array_filter( $values );

	return [ "Evita:$name" => $values ];
}

function formatEntry( array $entry ) {
	$fmt = "{{Evita\n";
	foreach ( $entry as $k => $v ) {
		$fmt .= "|$k=$v\n";
	}
	$fmt .= "}}\n";

	return $fmt;
}

function process( $IN, $OUT ) {
	is_dir( $OUT ) || mkdir( $OUT );
	$data = json_decode( file_get_contents( $IN ), true );

	$pages = [];
	foreach ( $data as $index => $rawEntry ) {
		foreach ( parseEntry( $rawEntry ) as $key => $value ) {
			$pages[$key][] = $value;
		}
	}

	foreach ( $pages as $key => $entries ) {
		$contents = '';
		foreach ( $entries as $entry ) {
			$contents .= formatEntry( $entry );
		}


		$key = strtr( $key, '_' , ' ' );
		$key = strtr( $key, '/' , '_' );
		file_put_contents( "$OUT/$key", $contents );
	}
}
