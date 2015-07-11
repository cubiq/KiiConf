<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>KII Keyboard Configurator</title>

	<link rel="stylesheet" type="text/css" href="css/style.css">
	<script src="lib/jquery-2.1.3.min.js"></script>
	<script src="js/configurator.js"></script>
	<script src="js/key.js"></script>
	<script src="js/defaults.js"></script>
</head>
<body onload="APP(true)" class="configurator">

<div id="wrapper" class="wrapper">
	<nav class="cf">
		<select id="layout-list"><option value="">-</option><?php layoutList() ?></select>
		<button type="button" id="load-layout" class="button-read">load layout</button>
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

	$files = glob($directory);

	foreach ($files as $layout) {
		$layout = basename($layout, '.json');

		echo '<option value="' . $layout . '">' . str_replace('-', ' ', $layout) . '</option>';
	}
}
