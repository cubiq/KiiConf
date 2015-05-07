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
			$layers[$l][$default[$i]->layers->{0}->key] = $layer->key;
		}
	}
}

$header = implode("\n", array_map(function ($v, $k) { return $k . '=' . $v; }, (array)$map->header, array_keys((array)$map->header)));
$files = array();
$file_args = array();
$hashbaby = $name . $layout; //Set name of base and layout here as an md5 seed

// Always run compiler
/*
if ( empty( $layers) ) {
	die( json_encode( array( 'error' => 'Nothing to do...' ) ) );
}
*/
$max_layer = 0;
foreach ($layers as $n => $layer) {
	$out = implode("\n", array_map(function ($v, $k) { return 'U"' . $k . '" : U"' . $v . '"'; }, $layer, array_keys($layer)));
	$out = $header . "\n\n" . $out . "\n";
	$hashbaby .= $out;

	$files[$n] = $file = array('content' => $out, 'name' => $name . '-' . $layout . '-' . $n . '.kll' );

	if ( $n > $max_layer ) {
		$max_layer = $n;
	}
}

// Now that the layout files are ready, create directory for compilation object files
$md5sum = md5($hashbaby);
$objpath = './tmp/' . $md5sum;
mkdir($objpath, 0700);

// Run compilation, very simple, 1 layer per entry (script supports complicated entries)
$cmd = './build_layout.bash ' . $md5sum . ' ';
for ( $c = 0; $c < $max_layer; $c++ ) {
	$path = $objpath . '/' . $files[$c]['name'];
	file_put_contents( $path, $files[$c]['content'] . ';' ); // Write kll file

	$cmd .= '"' . $files[$c]['name'] . ' ' . $c . '" ';
}
$retval = 0;
exec( escapeshellcmd( $cmd ), $output, $retval );

// If failed display log
if ( $retval != 0 ) {
	$log_out = '';
	foreach ($output as $line) {
		$log_out .= $line . "\n";
	}
	die( json_encode( array( 'error' => $log_out ) ) ); // TODO Show a better way...
}

// Always create the zip file (the date is always updated, which changes the binary)
$zipfile = './tmp/' . $md5sum . '.zip';
$zip = new ZipArchive;
$zip->open($zipfile, ZipArchive::CREATE);
$kll_files = glob( $objpath . "/*.kll", GLOB_NOCHECK );
$bin_files = glob( $objpath . "/*.dfu.bin", GLOB_NOCHECK );

foreach ( array_merge( $kll_files, $bin_files ) as $file ) {
	$zip->addFile( $file );
}

$zip->close();

echo json_encode( array( 'success' => true, 'filename' => './tmp/' . basename( $zipfile ) ) );
exit;
