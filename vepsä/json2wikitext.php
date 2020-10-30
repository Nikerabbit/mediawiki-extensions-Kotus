<?php
/**
 * @author Niklas Laxström
 */
// phpcs:ignore
function cleanupLocation( $original ) {
	if ( trim( $original ) === '' ) {
		return '';
	}

	# Sometimes they use comma or colon instead of semicolon
	$string = strtr( $original, ':', ';' );
	$string = strtr( $string, ',', ';' );
	$locations = explode( ';', $string );

	// Using separate loop because PHP does not like unsetting stuff
	foreach ( $locations as $index => $value ) {
		$value = trim( $value );
		if ( $value === '[muu]' ) {
			$locations[$index] = '(muu)';
			unset( $locations[$index + 1] );
		}
	}

	foreach ( $locations as $index => $value ) {
		$value = trim( $value );

		if ( $value === 'Tšidoi - Noidal' ) {
			$value = 'Noidal';
		}

		# Strip additional stuff
		if ( !preg_match( '/^([^ ?§]+)/u', $value, $cleanValue ) ) {
			unset( $locations[$index] );
			continue;
		}

		$value = trim( $cleanValue[1] );

		if ( $value === 'äänis-' ) {
			$value = 'äänis- eli pohjoisvepsää';
		} elseif ( $value === 'Nemža' ) {
			$value = 'Nemž';
		}

		$locations[$index] = $value;
	}

	$normalized = implode( '; ', $locations );
	if ( $normalized === $original ) {
		return '';
	} else {
		return $normalized;
	}
}

function parseEntry( array $entry ) {
	if ( $entry[0] === '10998' ) {
		$entry = [ '10998', 'a', 'aлe₍ta', '-nen, -nou̯, -tau̯, ei̯ -ne Vil.; Kl.', '', '',
			'aLeneškas (pogod)=alkoi tyyntyä, "aleta", mennä alas', '', 'Korjattu sanat-tuonnissa' ];
	}

	$index = mb_strtoupper( mb_substr( $entry[2], 0, 1 ) );
	if ( $index === 'Χ' ) {
		$index = 'X';
	} elseif ( $index === 'Л' ) {
		$index = 'L';
	}

	$values = [
		'index' => $index,
		'subid' => $entry[1],
		'expression' => $entry[2],
		'inflection' => $entry[3],
		'meaning' => $entry[4],
		'locations' => $entry[5],
		'locations/clean' => cleanupLocation( $entry[5] ),
		'example' => $entry[6],
		'info' => $entry[7],
		'notes' => $entry[8]
	];
	$values = array_filter( $values );

	return [ "Vepsä:$entry[0]" => $values ];
}

function formatEntry( array $entry ) {
	$fmt = "{{Vepsä\n";
	foreach ( $entry as $k => $v ) {
		$fmt .= "|$k=$v\n";
	}
	$fmt .= "}}\n";

	return $fmt;
}

mkdir( 'entrypages' );
$data = json_decode( file_get_contents( 'vepsa.json' ) );

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
