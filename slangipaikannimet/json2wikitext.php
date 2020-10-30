<?php
/**
 * @author Niklas Laxström
 */
// phpcs:ignore
function parseEntry( array $entry ) {
	$index = mb_strtoupper( mb_substr( $entry[0], 0, 1 ) );

	if ( !preg_match( '/[A-ZÅÄÖ]/', $index ) ) {
		$index = '#';
	}

	$values = [
		'index' => $index,
		'expression' => $entry[0],
		'official name' => $entry[1],
		'location' => $entry[2],
		'example' => $entry[3],
		'comments' => $entry[4],
		'school' => $entry[5],
	];
	$values = array_filter( $values );

	return [ "Slangipaikannimet:$entry[5]" => $values ];
}

function formatEntry( array $entry ) {
	$fmt = "{{Slangipaikannimet\n";
	foreach ( $entry as $k => $v ) {
		$fmt .= "|$k=$v\n";
	}
	$fmt .= "}}\n";

	return $fmt;
}

is_dir( 'entrypages' ) || mkdir( 'entrypages' );
$data = json_decode( file_get_contents( 'koulukeruu.json' ) );

$pages = [];
foreach ( $data as $rawEntry ) {
	foreach ( parseEntry( $rawEntry ) as $key => $value ) {
		$pages[$key][] = $value;
	}
}

foreach ( $pages as $key => $entries ) {
	$contents = '';
	foreach ( $entries as $entry ) {
		$contents .= formatEntry( $entry );
	}

	file_put_contents( "entrypages/$key", $contents );
}
