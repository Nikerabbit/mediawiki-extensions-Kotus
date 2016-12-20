<?php
/**
 * @author Niklas Laxström
 */

$IN = isset( $argv[1] ) ? $argv[1] : 'data.json';
$OUT = isset( $argv[2] ) ? $argv[2] : 'entrypages';
process( $IN, $OUT );

function parseEntry( array $entry ) {
	// LU = Lexical Unit
	list( $index, $id, $expression, $de, $fi, $page ) = $entry;

	if ( $id ) {
		$id = "$expression ($id)";
	}

	$values = [
		'expression' => $expression,
		'id' => $id,
		'index' => $index,
		'de' => $de,
		'fi' => $fi,
		'page' => $page,
	];

	$values = array_filter( $values );

	return [ "Sosva:$index" => $values ];
}

function formatEntry( array $entry ) {
	$fmt = "{{Sosva\n";
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
		$contents = "<div style=\"float:right\">__TOC__</div>\n";
		foreach ( $entries as $entry ) {
			$contents .= formatEntry( $entry );
		}

		file_put_contents( "entrypages/$key", $contents );
	}
}
