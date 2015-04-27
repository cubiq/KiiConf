<?php
header('Content-Type: application/json');

$map = !empty( $_POST['map'] ) ? $_POST['map'] : '';

if ( !$map ) {
	die( json_encode( array( 'error' => 'Malformed request' ) ) );
}

$map = json_decode( $map );

$name = !empty( $map->header->Name ) ? preg_replace('/[^a-z0-9._]/i', '', str_replace(' ', '_', $map->header->Name)) : '';
$layout = !empty( $map->header->Layout ) ? preg_replace('/[^a-z0-9._]/i', '', str_replace(' ', '_', $map->header->Layout)) : '';

if ( !$name || !$layout ) {
	die( json_encode( array( 'error' => 'Malformed header' ) ) );
}

$filename = './layouts/' . $name . '-' . $layout . '.json';

file_put_contents($filename, json_encode( $map ));

echo json_encode( array( 'success' => true ) );
