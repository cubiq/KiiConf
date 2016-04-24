<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>KII Keyboard Configurator</title>

	<link rel="stylesheet" type="text/css" href="css/style.css?v=<?php echo filemtime('css/style.css') ?>">
	<script src="lib/jquery-2.2.1.min.js"></script>
	<script src="js/configurator.js"></script>
	<script src="js/key.js"></script>
	<script src="js/defaults.js"></script>
</head>
<body onload="APP(false)" class="configurator">

<div id="wrapper" class="wrapper">
	<nav class="cf">
		<div id="layout-list" class="pseudo-select"><?php layoutList() ?></div>
		<button type="button" id="import-map" class="button-read">import map</button>
		<input type="button" onclick="window.top.location.href='https://github.com/kiibohd/KiiConf';" value="Wiki" />
		<input type="button" onclick="location.href='stats.json';" value="Version" />
		<button type="button" id="download-map" class="button-write floatright">download firmware</button>
	</nav>

	<ul id="layers" class="tabs">
		<li class="tab-layer-0 selected"><input id="layer-check-0" type="checkbox" value="0" checked title="toggle visibility">Main</li>
		<li class="tab-layer-1"><input id="layer-check-1" type="checkbox" value="1" checked title="toggle visibility">Layer 1</li>
		<li class="tab-layer-2"><input id="layer-check-2" type="checkbox" value="2" checked title="toggle visibility">Layer 2</li>
		<li class="tab-layer-3"><input id="layer-check-3" type="checkbox" value="3" title="toggle visibility">Layer 3</li>
		<li class="tab-layer-4"><input id="layer-check-4" type="checkbox" value="4" title="toggle visibility">Layer 4</li>
		<li class="tab-layer-5"><input id="layer-check-5" type="checkbox" value="5" title="toggle visibility">Layer 5</li>
		<li class="tab-layer-6"><input id="layer-check-6" type="checkbox" value="6" title="toggle visibility">Layer 6</li>
		<li class="tab-layer-7"><input id="layer-check-7" type="checkbox" value="7" title="toggle visibility">Layer 7</li>
	</ul>

	<div class="container" id="container">
		<div id="stage" class="keyboard"></div>
	</div>
</div>

<div id="shortcuts" class="shortcuts cf" style="display:none">
	<ul id="group-special" class="group">
		<li class="title">special</li>
		<li><span id="clear-key" class="shortcut-button" data-key="*CLEAR">CLR-KEY</span></li>
	</ul>
</div>

</body>
</html><?php

function layoutList () {
	$directory = './layouts/*.json';

	$specified_layout = !empty($_GET['layout']) ? $_GET['layout'] : 'KType-Blank'; // default to KType-Blank

	$files = glob($directory);
	$old_keyboard = '';
	$out = '<span id="active-layout-title">' . str_replace('-', ' ', $specified_layout) . '</span><ul>' . "\n";

	foreach ($files as $layout) {
		$layout = basename($layout, '.json');

		list($keyboard, $variant) = explode('-', $layout, 2);

		if ( $keyboard !== $old_keyboard ) {
			if ( $old_keyboard !== '' ) {
				$out .= '</ul></li>' . "\n";
			}

			$out .= '<li>' . "\n";
			$out .= '<a href="#" onclick="return false">' . htmlspecialchars($keyboard) . '</a>' . "\n";
			$out .= '<ul>' . "\n";

			$old_keyboard = $keyboard;
		}

		$selected = strcasecmp( $specified_layout, $layout ) == 0 ? ' class="selected" ' : '';
		$out .= '<li' . $selected . ' data-layout="' . htmlspecialchars($layout) . '"><a href="?layout=' . urlencode($layout) . '">' . htmlspecialchars($variant) . '</a></li>' . "\n";
	}

	$out .= '</ul></li></ul>';
	echo $out;
}
