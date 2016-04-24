/*
	KII Keyboard Editor
	Copyright (C) 2015 Matteo Spinelli

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

var APP = APP || {};

(function (window, document) {

// main application instance
var _instance;

// singleton
function APP (debug) {
	_instance = _instance || new APP.Class(debug);

	return _instance;
}

APP.VERSION = '0.2';
APP.GENERATOR = 'KIICONF';

APP.GRID_SIZE = 10;
APP.KEY_SIZE = APP.GRID_SIZE * 4;
APP.STAGE_WIDTH = 0;	// they will be populated later
APP.STAGE_HEIGHT = 0;

APP.Class = function (debug) {
	var that = this;

	this.header = {};
	this.matrix = [];

	this.$stage = $('#stage');

	APP.STAGE_WIDTH = Math.floor(this.$stage.width() / APP.GRID_SIZE);
	APP.STAGE_HEIGHT = Math.floor(this.$stage.height() / APP.GRID_SIZE);

	// load button
	$('#load-map').click(function () {
		new popup(
			'Load new keyboard map',
			'load map',
			'',
			$.proxy(that.loadKeyboardMap, that)
		);
	});

	// save button
	$('#save-map').click( $.proxy(this.saveKeyboardMap, this) );

	if ( debug ) {
		this.loadKeyboardMap();
	}

	//new APP.Key(this.$stage);
};

APP.Class.prototype = {
	loadKeyboardMap: function ( map ) {
		// a default key map is provided for ease of debug
		map = map || 'Name = MD1;\nVersion = 0.2;\nAuthor = "HaaTa (Jacob Alexander) 2014";\nKLL = 0.3;\n\n# Modified Date\nDate = 2014-09-14;\n\n\nS0x00 : U"Esc";\nS0x01 : U"1";\nS0x02 : U"2";\nS0x03 : U"3";\nS0x04 : U"4";\nS0x05 : U"5";\nS0x06 : U"6";\nS0x07 : U"7";\nS0x08 : U"8";\nS0x09 : U"9";\nS0x0A : U"0";\nS0x0B : U"Minus";\nS0x0C : U"Equal";\nS0x0D : U"Backslash";\nS0x0E : U"Backtick";\nS0x0F : U"Tab";\nS0x10 : U"Q";\nS0x11 : U"W";\nS0x12 : U"E";\nS0x13 : U"R";\nS0x14 : U"T";\nS0x15 : U"Y";\nS0x16 : U"U";\nS0x17 : U"I";\nS0x18 : U"O";\nS0x19 : U"P";\nS0x1A : U"LBrace";\nS0x1B : U"RBrace";\nS0x1C : U"Backspace";\nS0x1D : U"Ctrl";\nS0x1E : U"A";\nS0x1F : U"S";\nS0x20 : U"D";\nS0x21 : U"F";\nS0x22 : U"G";\nS0x23 : U"H";\nS0x24 : U"J";\nS0x25 : U"K";\nS0x26 : U"L";\nS0x27 : U"Semicolon";\nS0x28 : U"Quote";\nS0x29 : U"Enter";\nS0x2A : U"LShift";\nS0x2B : U"Z";\nS0x2C : U"X";\nS0x2D : U"C";\nS0x2E : U"V";\nS0x2F : U"B";\nS0x30 : U"N";\nS0x31 : U"M";\nS0x32 : U"Comma";\nS0x33 : U"Period";\nS0x34 : U"Slash";\nS0x35 : U"RShift";\nS0x36 : U"Function1"; # Fun key\nS0x37 : U"Function2"; # Left Blank Key\nS0x38 : U"LAlt";\nS0x39 : U"LGui";\nS0x3A : U"Space";\nS0x3B : U"RGui";\nS0x3C : U"RAlt";\nS0x3D : U"Function3"; # Right Blank Key 1\nS0x3E : U"Function4"; # Right Blank Key 2';

		var regEx = /S(0x[0-9A-F]{2}) : U"([^"]*)";/g;
		var match;
		var key;
		var defaults;

		this.clearKeyboardMap();

		this.header = {
			Name:      map.match(/Name ?= ?"?([^;"]*)"?;/i),
			Layout:    map.match(/Layout ?= ?"?([^;"]*)"?;/i) || 'Default',
			Base:      map.match(/Base ?= ?"?([^;"]*)"?;/i) || 'Blank',
			Version:   map.match(/Version ?= ?"?([^;"]*)"?;/i),
			Author:    map.match(/Author ?= ?"?([^;"]*)"?;/i),
			KLL:       map.match(/KLL ?= ?"?([^;"]*);"?/i),
			'Date':    map.match(/Date ?= ?"?([^;"]*)"?;/i),
			Generator: APP.GENERATOR + ' ' + APP.VERSION
		};

		// normalize header
		var that = this;
		$.each(this.header, function (key, value) {
			that.header[key] = !value ? '' : typeof value == 'string' ? value : value[1];
		});

		$('#kii-name').val(this.header.Name);
		$('#kii-layout').val(this.header.Layout);

		while ( match = regEx.exec(map) ) {
			key = match[2].toUpperCase();
			defaults = (key in APP.keyDefaults) ? APP.keyDefaults[key] : { x: 0, y: 0, w: 1, h: 1 };

			this.matrix.push( new APP.Key(this.$stage, {
				code: match[1],
				layers: {
				 	0: {
				 		key: key
				 	}},
				x: defaults.x,
				y: defaults.y,
				w: defaults.w,
				h: defaults.h
			}) );
		}
	},

	saveKeyboardMap: function (map) {
		var matrix = [];

		$.each(this.matrix, function (k, v) {
			matrix.push({
				code: v.code,
				x: v.x,
				y: v.y,
				w: v.width,
				h: v.height,
				layers: { '0': { key: v.layers[0].key } }
			});
		});

		this.header.Name = $('#kii-name').val();
		this.header.Layout = $('#kii-layout').val();

		$.ajax({
			type: 'post',
			url: 'save.php',
			data: {
				'map': JSON.stringify({ header: this.header, matrix: matrix }),
			},
			success: function (response) {
				alert( ( 'error' in response ) ? response.error : 'Saved!' );
			},
			error: function (response) {
				alert('Connection error!');
			}
		});
	},

	clearKeyboardMap: function () {
		this.header = {};
		this.matrix = [];

		this.$stage.find('.key').remove();
	}
};


function popup (title, action, value, cb) {
	var that = this;

	this.$cover = $('<div>')
		.addClass('cover')
		.appendTo('body');

	this.$popup = $('<div>')
		.addClass('popup')
		.appendTo('body');

	$('<h1>')
		.html(title)
		.appendTo(this.$popup);

	var $map = $('<textarea>')
		.html(value)
		.appendTo(this.$popup);

	var $buttons = $('<div>').appendTo(this.$popup);

	$('<button>')
		.attr('type', 'button')
		.html('cancel')
		.addClass('button-cancel')
		.click( $.proxy(this.destroy, this) )
		.appendTo($buttons);

	$('<button>')
		.attr('type', 'button')
		.html(action)
		.addClass('button-read')
		.click(function () {
			if ( !$map.val() ) {
				alert('c\'mon be creative!');
				return;
			}

			cb($map.val());
			that.destroy();
		})
		.appendTo($buttons);
}

popup.prototype = {
	destroy: function () {
		this.$cover.remove();
		this.$popup.remove();
	}
};

window.APP = APP;

})(window, document);
