<?php
/**
 * @author Niklas Laxström
 */

$IN = $argv[1] ?? 'ledlex';
$OUT = $argv[2] ?? 'data.json';
process( $IN, $OUT );

function process( string $IN, string $OUT ): void {
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

function parseIndex( string $filename ): string {
	$index = basename( $filename, '.php' );
	return match ( $index ) {
		'A1' => 'Ä',
		'O1' => 'Ö',
		'I' => 'IJ',
		'V' => 'VYZ',
		'A2' => 'Å',
		default => $index,
	};
}

function parseHtml( string $string ): array {
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
