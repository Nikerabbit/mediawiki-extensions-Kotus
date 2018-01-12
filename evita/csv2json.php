<?php
/**
 * @author Niklas Laxström
 */

ini_set( 'memory_limit', '1G' );

require '../vendor/autoload.php';

$IN = isset( $argv[1] ) ? $argv[1] : 'Evita.csv';
$OUT = isset( $argv[2] ) ? $argv[2] : 'data.json';
process( $IN, $OUT );

function process( $IN, $OUT ) {
	$data = parse( file_get_contents( $IN ) );

	$data = array_map( 'array_filter', $data );
	$json = json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

	file_put_contents( $OUT, $json );
}

function parse( $data ) {
	$data = str_replace( '’', '', $data );
	$data = str_replace( "\xC2\xA0", ' ', $data );

	$csv = new parseCSV();
	$csv->delimiter = ",";
	$csv->heading = true;
	$csv->enclosure = '"';
	$csv->parse( $data );

	$rows = itemize( $csv->data );
	$rows = array_map( 'mapAndFilterKeys', $rows );
	return $rows;
}

function itemize( array $data ) {
	$lastItemIndex = 0;

	foreach ( $data as $index => $row ) {
		$id = $row['hakusanaviittaus'];

		if ( $id !== '' ) {
			if ( $row['sanat_2::hakusana'] === '' ) {
				unset( $data[$index] );
			} else {
				$lastItemIndex = $index;
			}
			continue;
		}

		// Collapse this row to previous one
		foreach ( $row as $key => $value ) {
			if ( $value === '' ) continue;

			$data[$lastItemIndex][$key] = (array)$data[$lastItemIndex][$key];
			$data[$lastItemIndex][$key][] = $value;
		}
		unset( $data[$index] );
	}

	return array_values( $data );
}

function mapAndFilterKeys( array $row ) {
	$map = [
		'hakusanaviittaus' => 'id',
		'merkitys' => 'meaning',
		'huomautus' => 'note',
		'sanat_2::hakusana' => 'expression',
		'katso_5::katso' => 'seealso',
		'lahteet_3a::kirjoittaja' => 'ref_a',
		'lahteet_3a::vuosi1' => 'ref_y',
		'lahteet_3a::lähde' => 'ref_n',
		'sivut_3b::sivunumero' => 'ref_p',
	];

	$new = [];
	foreach ( $row as $key => $value ) {
		if ( isset( $map[$key] ) ) {
			$new[$map[$key]] = $value;
		}
	}

	return $new;
}
