<?php
/**
 * @author Niklas Laxström
 */

$IN = $argv[1] ?? 'julk9';
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
		$all = array_merge( $all, parseHtml( $input ) );
	}

	file_put_contents( $OUT, json_encode( $all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
}

function parseHtml( string $string ): array {
	$output = [];

	$string = html_entity_decode( $string );
	$string = str_replace( '<b>', '', $string );
	$string = str_replace( '</b>', '', $string );
	$string = preg_replace( '/ +/', ' ', $string );

	$matches = [];
	if ( !preg_match( '~<h1>(.+)</h1>~', $string, $matches ) ) {
		return [];
	}

	$index = $matches[1];

	preg_match_all( '~<p class="(.+)">(.+)</p>~sU', $string, $matches, PREG_SET_ORDER );

	$item = null;
	foreach ( $matches as $match ) {
		[ , $class, $content ] = $match;

		if ( $class === 'sisennys' ) {
			[ $expression, $translation ] = explode( '* ', $content );
			$note = extractNote( $expression );

			$item['subs'][] = [
				'expression' => ltrim( $expression, ' -' ),
				'translations' => cleanUpTranslation( $translation ),
			];

			if ( $note ) {
				$item['note'] = $note;
			}
			continue;
		}

		if ( $item ) {
			$output[] = $item;
		}

		$content = trim( $content );

		// ääni:
		if ( str_ends_with( $content, ':' ) ) {
			$item = [
				'index' => $index,
				'type' => 'aggregate',
				'expression' => rtrim( $content, ':' ),
			];
			continue;
		}

		// lainakirjasto = lainauskirjasto
		if ( preg_match( '/(.+) = (.+)/', $content ) ) {
			$item = [
				'index' => $index,
				'type' => 'aggregate',
				'expression' => $content,
			];
			continue;
		}

		// myyminen, ks. myynti
		if ( preg_match( '/(.+), (ks\.) (.+)/', $content, $matches ) ) {
			$item = [
				'index' => $index,
				'type' => 'aggregate',
				'expression' => trim( $matches[1] ),
				'see' => $matches[3],
			];
			continue;
		}

		[ $expression, $translation ] = explode( ' * ', $content );
		$note = extractNote( $expression );

		$item = [
			'index' => $index,
			'expression' => trim( $expression ),
			'translations' => cleanUpTranslation( $translation ),
		];

		if ( $note ) {
			$item['note'] = $note;
		}
	}

	if ( $item ) {
		$output[] = $item;
	}

	return $output;
}

function cleanUpTranslation( string $string ): string {
	return trim( mb_strtolower( $string ) );
}

function extractNote( string &$string ): string {
	$string = trim( $string );
	$ok = preg_match( '/^(.+) \((ks|luett|luok|sid\.mat)\.\)$/', $string, $matches );
	if ( !$ok ) {
		return '';
	}

	$string = $matches[1];
	return $matches[2];
}
