<?php
/**
 * @author Niklas Laxström
 */

$IN = isset( $argv[1] ) ? $argv[1] : 'data.json';
$OUT = isset( $argv[2] ) ? $argv[2] : 'entrypages';
process( $IN, $OUT );

function parseEntry( $index, array $entry ) {
	return [ "Namnledslexikon:$index" => $entry ];
}

function formatEntry( array $entry ) {
	list( $name, $description ) = $entry;

	return <<<WIKITEXT
== $name ==
$description

WIKITEXT;
}

function process( $IN, $OUT ) {
	is_dir( $OUT ) || mkdir( $OUT );
	$data = json_decode( file_get_contents( $IN ), true );

	$pages = [];
	foreach ( $data as $index => $entries ) {
		foreach ( $entries as $entry ) {
			foreach ( parseEntry( $index, $entry ) as $key => $value ) {
				$pages[$key][] = $value;
			}
		}
	}

	foreach ( $pages as $key => $entries ) {
		$contents = "{{Namnledslexikon/index}}<div style=\"float:right\">__TOC__</div>\n";
		foreach ( $entries as $entry ) {
			$contents .= formatEntry( $entry );
		}

		file_put_contents( "$OUT/$key", $contents );
	}
}
