<?php
/**
 * @author Niklas Laxström
 */

$IN = $argv[1] ?? 'data.json';
$OUT = $argv[2] ?? 'entrypages';
process( $IN, $OUT );

function parseEntry( array $entry ): array {
	$index = $entry['index'];
	return [ "Suomalais-venäläinen kirja-alan sanasto:$index" => $entry ];
}

function formatEntry( array $entry ): string {
	$fmt = "=== {$entry['expression']} ===\n";

	if ( isset( $entry['see'] ) ) {
		$see = $entry['see'];
		$fmt .= ":''{{INT:kotus-kirjasanasto-see|$see}}''\n\n";
	}

	if ( isset( $entry['note'] ) ) {
		$type = $entry['note'];
		$fmt .= ":''{{INT:kotus-kirjasanasto-note-$type}}''\n\n";
	}

	if ( isset( $entry['translations'] ) ) {
		$fmt .= $entry['translations'] . "\n";
	}

	if ( isset( $entry['subs'] ) ) {
		foreach ( $entry['subs'] as $sub ) {
			$fmt .= "; {$sub['expression']} : {$sub['translations']}\n";
		}
	}

	return trim( $fmt ) . "\n";
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
		$contents = '{{Suomalais-venäläinen kirja-alan sanasto/index}}';
		$contents .= "<div style=\"float:right\">__TOC__</div>\n";
		foreach ( $entries as $entry ) {
			$contents .= formatEntry( $entry );
		}

		file_put_contents( "$OUT/$key", $contents );
	}
}
