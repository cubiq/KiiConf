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

$default = './layouts/' . $name . '-' . $layout . '.json';
$default = json_decode( file_get_contents($default) );
$default = $default->matrix;

$layers = array();

// Find the differences between the default map and the user's map
foreach ( $map->matrix as $i => $key ) {
	foreach ($key->layers as $l => $layer) {
		if ( $default[$i]->layers->{0}->key != $layer->key ) {
			$layers[$l][$key->code] = $layer->key;
		}
	}
}

$header = implode("\n", array_map(function ($v, $k) { return $k . '=' . $v; }, (array)$map->header, array_keys((array)$map->header)));
$files = array();
$hashbaby = '';

if ( empty( $layers) ) {
	die( json_encode( array( 'error' => 'Nothing to do...' ) ) );
}

foreach ($layers as $n => $layer) {
	$out = implode("\n", array_map(function ($v, $k) { return $k . ' : U"' . $v . '"'; }, $layer, array_keys($layer)));
	$out = $header . "\n\n" . $out . "\n";
	$hashbaby .= $out;

	$files[] = array('content' => $out, 'name' => $name . '-' . $layout . '-' . $n . '.KII');
}

$zipfile = './tmp/' . md5($hashbaby) . '.zip';

if ( !file_exists ( $zipfile ) ) {
	$zip = new ZipArchive;
	$zip->open($zipfile, ZipArchive::CREATE);
	foreach ($files as $file) {
		$zip->addFromString($file['name'], $file['content']);
	}
	$zip->close();
}

echo json_encode( array( 'success' => true, 'filename' => './tmp/' . basename( $zipfile ) ) );
exit;