<?php
/**
 * @author Niklas Laxström
 */

$IN = $argv[1] ?? 'data.json';
$OUT = $argv[2] ?? 'entrypages';
process( $IN, $OUT );

function parseEntry( string $index, array $entry ): array {
	return [ "Namnledslexikon:$index" => $entry ];
}

function formatEntry( array $entry ): string {
	[ $name, $description ] = $entry;

	return <<<WIKITEXT
	== $name ==
	$description

	WIKITEXT;
}

function process( string $IN, string $OUT ): void {
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
