<?php
/**
 * @author Niklas Laxström
 */

$IN = isset( $argv[1] ) ? $argv[1] : 'vepsa.sql';
$OUT = isset( $argv[2] ) ? $argv[2] : 'vepsa.json';

$data = file_get_contents( $IN );

$separator = "\r\n";
$line = strtok( $data, $separator );

$all = [];

while ( $line !== false ) {
	if ( substr( $line, 0, 11 ) !== 'INSERT INTO' ) {
		$line = strtok( $separator );
		continue;
	}

	$values = substr( $line, 27 );
	$all = array_merge( $all, tokenize( $values ) );

	$line = strtok( $separator );
}

file_put_contents( $OUT, json_encode( $all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );

function tokenize( $string ) {
	$entries = [];
	$itemStack = [];
	$charStack = '';
	$state = 'BEGIN';

	$len = strlen( $string );

	for ( $i = 0; $i < $len; $i++ ) {
		$c = $string[$i];
		switch ( $state ) {
		case 'BEGIN':
		if ( $c === '(' ) {
			$state = 'BEGIN-ENTRY';
		} else {
			exit( 'Parse error: ' . $state );
		}
		continue;

		case 'BEGIN-ENTRY':
		if ( $c === '\'' ) {
			$state = 'BEGIN-ITEM';
		} elseif ( ctype_digit( $c ) ) {
			$charStack .= $c;
			$state = 'ITEM-NUMBER';
		} else {
			exit( 'Parse error: ' . $state );
		}
		continue;

		case 'BEGIN-ITEM':
		if ( $c === '\'' && $string[ $i - 1 ] !== '\\' ) {
			$itemStack[] = $charStack;
			$charStack = '';
			$state = 'END-ITEM';
		} elseif ( $c !== '\\' ) {
			$charStack .= $c;
		}
		continue;

		case 'ITEM-NUMBER':
		if ( $c === ',' ) {
			$itemStack[] = $charStack;
			$charStack = '';
			$state = 'END-ITEM';
			// Fall through
		} else {
			$charStack .= $c;
			continue;
		}

		case 'END-ITEM':
		if ( $c === ',' ) {
			$state = 'BEGIN-ENTRY';
		} elseif ( $c === ')' ) {
			$entries[] = $itemStack;
			$itemStack = [];
			$state = 'END-ENTRY';
		} else {
			exit( 'Parse error: ' . $state );
		}
		continue;

		case 'END-ENTRY':
		if ( $c === ',' ) {
			$state = 'BEGIN';
		} elseif ( $c === ';' ) {
			return $entries;
		} else {
			exit( 'Parse error: ' . $state );
		}
		continue;

		default:
		exit( 'Parse error: unknown state: ' . $state );
		}
	}

	exit( 'Parse error: truncated' );
}
