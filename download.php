<?php
header('Content-Type: application/json');

$map_orig = !empty( $_POST['map'] ) ? $_POST['map'] : '';

if ( !$map_orig ) {
	die( json_encode( array( 'error' => 'Malformed request' ) ) );
}

$map = json_decode( $map_orig );

$name = !empty( $map->header->Name ) ? preg_replace('/[^a-z0-9._]/i', '', str_replace(' ', '_', $map->header->Name)) : '';
$layout = !empty( $map->header->Layout ) ? preg_replace('/[^a-z0-9._]/i', '', str_replace(' ', '_', $map->header->Layout)) : '';
$base_layout = !empty( $map->header->Base ) ? preg_replace('/[^a-z0-9._]/i', '', str_replace(' ', '_', $map->header->Base)) : '';

if ( !$name || !$layout ) {
	die( json_encode( array( 'error' => 'Malformed header' ) ) );
}

$default = './layouts/' . $name . '-' . $base_layout . '.json';
$default = json_decode( file_get_contents($default) );
$default = $default->matrix;

$layers = array();

// Find the differences between the default map and the user's map
foreach ( $map->matrix as $i => $key ) {
	foreach ( $key->layers as $l => $layer ) {
		if ( $default[$i]->layers->{0}->key != $layer->key ) {
			$layers[$l][$default[$i]->layers->{0}->key] = $layer->key;
		}
	}
}

$header = implode("\n", array_map(function ($v, $k) { return $k . ' = "' . $v . '";'; }, (array)$map->header, array_keys((array)$map->header)));
$files = array();
$file_args = array();
$hashbaby = $name . $layout; // Set name of base and layout here as an md5 seed
$layout_name = $name . '-' . $layout;


// Generate .kll files
$max_layer = 0;
foreach ( $layers as $n => $layer ) {
	$out = implode("\n", array_map(function ($v, $k) {
		if ( preg_match("/^((CONS|SYS|#):)?(.+)/i", $v, $match) ) {
			if ( $match[2] == '#' ) {
				$v = $match[3];
			} else if ( $match[2] == 'CONS' or $match[2] == 'SYS' ) {
				$v = $match[2] . '"' . $match[3] . '"';
			} else {
				$v = 'U"' . $v . '"';
			}
		} else {
			$v = 'U"' . $v . '"';
		}

		return 'U"' . $k . '" : ' . $v . ';';
	}, $layer, array_keys($layer)));
	$out = $header . "\n\n" . $out . "\n\n";
	$hashbaby .= $out;

	$files[$n] = $file = array('content' => $out, 'name' => $layout_name . '-' . $n . '.kll' );

	if ( $n > $max_layer ) {
		$max_layer = $n;
	}
}

// Now that the layout files are ready, create directory for compilation object files
$md5sum = md5( $hashbaby );
$objpath = './tmp/' . $md5sum;
mkdir( $objpath, 0700 );


// Save the configuration json to the folder in order to import later
$path = $objpath . '/' .$name . '-' . $layout . '.json';
file_put_contents( $path, $map_orig );


// Run compilation, very simple, 1 layer per entry (script supports complicated entries)
$log_file = $objpath . '/build.log';
$cmd = './build_layout.bash ' . $md5sum . ' ' . $name . ' ';
for ( $c = 0; $c <= $max_layer; $c++ ) {
	$path = $objpath . '/' . $files[$c]['name'];
	file_put_contents( $path, $files[$c]['content'] ); // Write kll file

	$cmd .= '"' . $files[$c]['name'] . '" ';
}
$cmd .= ' 2>&1';
file_put_contents( $log_file , $cmd . "\n" ); // Reset the log file, with the specified command
$handle = popen( $cmd, 'r' );
while ( !feof( $handle ) ) {
	file_put_contents( $log_file, fgets( $handle ), FILE_APPEND );
}
$retval = pclose( $handle );


// If failed mark the zip file with an _error
$error_str = '';
if ( $retval != 0 ) {
	$error_str = '_error';
}


// Always create the zip file (the date is always updated, which changes the binary)
$zip_path = './tmp';
$zipfile = $zip_path . '/' . $layout_name . '-' . $md5sum . $error_str . '.zip';
$zip = new ZipArchive;
$zip->open( $zipfile, ZipArchive::CREATE );
$kll_files  = glob( $objpath . "/*.kll", GLOB_NOCHECK );
$bin_files  = glob( $objpath . "/*.dfu.bin", GLOB_NOCHECK );
$log_files  = glob( $objpath . "/*.log", GLOB_NOCHECK );
$hdr_files  = glob( $objpath . "/*.h", GLOB_NOCHECK );
$json_files = glob( $objpath . "/*.json", GLOB_NOCHECK );

// Add each of the files, flattening the dir hierarchy
foreach ( array_merge( $kll_files, $bin_files, $json_files ) as $file ) {
	$zip->addFile( $file, basename( $file ) );
}

// Add each of the kll files to the kll directory
foreach ( array_merge( $kll_files ) as $file ) {
	$zip->addFile( $file, "kll/" . basename( $file ) );
}

// Add the log/debug files to the log directory
foreach ( array_merge( $hdr_files, $log_files ) as $file ) {
	$zip->addFile( $file, "log/" . basename( $file ) );
}

$zip->close();

// Output zip file path
echo json_encode( array( 'success' => true, 'filename' => $zip_path . '/' . basename( $zipfile ) ) );
exit;
