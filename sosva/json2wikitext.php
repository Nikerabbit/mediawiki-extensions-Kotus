<?php
/**
 * @author Niklas Laxström
 */

$IN = $argv[1] ?? 'data.json';
$OUT = $argv[2] ?? 'entrypages';
process( $IN, $OUT );

function parseEntry( array $entry ): array {
	// LU = Lexical Unit
	[ $index, $id, $expression, $de, $fi, $page ] = $entry;

	if ( $id ) {
		$id = "$expression ($id)";
	} elseif ( str_contains( $expression, '.' ) ) {
		// SMW does not allow dots in the first five characters
		$id = str_replace( '.', '_', $expression );
	}

	$values = [
		'expression' => $expression,
		'de' => $de,
		'fi' => $fi,
		'page' => $page,
		'id' => $id,
	];

	$values = array_filter( $values );

	return [ "Sosva:$index" => $values ];
}

function formatEntry( array $entry ): string {
	$fmt = "{{Sosva\n";
	foreach ( $entry as $k => $v ) {
		$fmt .= "|$k=$v\n";
	}
	$fmt .= "}}\n";

	return $fmt;
}

function process( string $IN, string $OUT ): void {
	is_dir( $OUT ) || mkdir( $OUT );
	$data = json_decode( file_get_contents( $IN ), true );

	$pages = [];
	foreach ( $data as $rawEntry ) {
		foreach ( parseEntry( $rawEntry ) as $key => $value ) {
			$pages[$key][] = $value;
		}
	}

	foreach ( $pages as $key => $entries ) {
		$contents = "{{Sosva/header}}\n";
		foreach ( $entries as $entry ) {
			$contents .= formatEntry( $entry );
		}

		file_put_contents( "$OUT/$key", $contents );
	}
}
