/*
    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor,
    Boston, MA  02110-1301, USA.
    ---
    Copyright (C) 2009, Ryan Peel ryan@2amlife.com
 */

Hash.prototype.without = function() {
    var values = $A(arguments);
	var retHash = $H();
    this.each(function(entry) {
		if(!values.include(entry.key))
			retHash.set(entry.key, entry.value);
    });
	return retHash;
}

Element.insertAfter = function(insert, element) {
	if (element.nextSibling) element.parentNode.insertBefore(insert, element.nextSibling);
	else element.parentNode.appendChild(insert);
}

// Fix exceptions thrown thrown when removing an element with no parent
Element._remove = Element.remove;
Element.remove = function(element) {
	element = $(element);
	if (element.parentNode)
		return Element._remove(element);
}

/*
 * Control.ColorPicker
 *
 * Transforms an ordinary input textbox into an interactive color chooser,
 * allowing the user to select a color from a swatch palette.
 *
 * Features:
 *  - Allows saving custom colors to the palette for later use
 *  - Customizable by CSS
 *
 * Written and maintained by Jeremy Jongsma (jeremy@jongsma.org)
 */
var Control = {};

Control.ColorPicker = Class.create();
Control.ColorPicker.prototype = {
	initialize: function (element, options) {
		this.element = $(element);
		this.options = Object.extend({
				className: 'colorpickerControl'
			}, options || {});
		this.colorpicker = new Control.ColorPickerPanel({
				onSelect: this.colorSelected.bind(this)
			});

		this.dialogOpen = false;
		this.element.maxLength = 7;

		this.dialog = new Element('div');
		this.dialog.style.position = 'absolute';
		var cpCont = new Element('div').addClassName(this.options.className);
		cpCont.insert(this.colorpicker.element);
		this.dialog.insert(cpCont);

		var cont = new Element('div', {'style': 'position: relative;'});
		this.element.parentNode.replaceChild(cont, this.element);
		cont.insert(this.element);

		var el_top = '4px';
		var size = (this.element.offsetHeight - 8);
		var el_left = (this.element.offsetLeft + this.element.offsetWidth - (size + 5)) + 'px';
		this.swatch = new Element('div', {'style':'border:1px solid gray; position:absolute; left:'+el_left+';top:'+el_top+'; font-size:1px; width:'+size+'px; height: '+ size + 'px; background-color:'+this.element.value});
		this.swatch.title = 'Open color palette';
		this.swatch.addClassName('inputExtension');
		cont.insert(this.swatch);

		this.element.onchange = this.textChanged.bindAsEventListener(this);
		this.element.onblur = this.hidePicker.bindAsEventListener(this);
		this.swatch.onclick = this.togglePicker.bindAsEventListener(this);
		this.documentClickListener = this.documentClickHandler.bindAsEventListener(this);
	},
	colorSelected: function(color) {
		this.element.value = color;
		this.swatch.style.backgroundColor = color;
		this.hidePicker();
	},
	textChanged: function(e) {
		this.swatch.style.backgroundColor = this.element.value;
	},
	togglePicker: function(e) {
		if (this.dialogOpen) this.hidePicker();
		else this.showPicker();
	},
	showPicker: function(e) {
		if (!this.dialogOpen) {
			var dim = Element.getDimensions(this.element);
			var position = Position.cumulativeOffset(this.element);
			var pickerTop = /MSIE/.test(navigator.userAgent) ? (position[1] + dim.height) + 'px' : (position[1] + dim.height - 1) + 'px';
			this.dialog.style.top = pickerTop;
			this.dialog.style.left = position[0] + 'px';
			document.body.appendChild(this.dialog);
			Event.observe(document, 'click', this.documentClickListener);
			this.dialogOpen = true;
		}
	},
	hidePicker: function(e) {
		if (this.dialogOpen) {
			Event.stopObserving(document, 'click', this.documentClickListener);
			Element.remove(this.dialog);
			this.dialogOpen = false;
		}
	},
	documentClickHandler: function(e) {
		var element = Event.element(e);
		var abort = false;
		do {
			if (element == this.swatch || element == this.dialog)
				abort = true;
		} while (element = element.parentNode);
		if (!abort)
			this.hidePicker();
	}
};

Control.ColorPickerPanel = Class.create();
Control.ColorPickerPanel.prototype = {

	initialize: function(options) {
		this.options = Object.extend({
				addLabel: 'Add',
				colors: Array(
					'#000000', '#993300', '#333300', '#003300', '#003366', '#000080', '#333399', '#333333',
					'#800000', '#FF6600', '#808000', '#008000', '#008080', '#0000FF', '#666699', '#808080',
					'#FF0000', '#FF9900', '#99CC00', '#339966', '#33CCCC', '#3366FF', '#800080', '#969696',
					'#FF00FF', '#FFCC00', '#FFFF00', '#00FF00', '#00FFFF', '#00CCFF', '#993366', '#C0C0C0',
					'#FF99CC', '#FFCC99', '#FFFF99', '#CCFFCC', '#CCFFFF', '#99CCFF', '#CC99FF', '#FFFFFF'),
				onSelect: Prototype.emptyFunction
			}, options || {});
		this.activeCustomSwatch =  null,
		this.customSwatches = [];

		this.element = this.create();
	},

	create: function() {
		var cont = document.createElement('div');
		var colors = this.options.colors;

		// Create swatch table
		var swatchTable = document.createElement('table');
		swatchTable.cellPadding = 0;
		swatchTable.cellSpacing = 0;
		swatchTable.border = 0;
		for (var i = 0; i < 5; ++i) {
			var row = swatchTable.insertRow(i);
			for (var j = 0; j < 8; ++j) {
				var cell = row.insertCell(j);
				var color = colors[(8 * i) + j];
				var swatch = document.createElement('div');
				Element.setStyle(swatch, {'width': '15px', 'height': '15px', 'fontSize': '1px', 'border': '1px solid #EEEEEE', 'backgroundColor': color, 'padding': '0'});
				swatch.onclick = this.swatchClickListener(color);
				swatch.onmouseover = this.swatchHoverListener(color);
				cell.appendChild(swatch);
			}
		}

		// Add spacer row
		var spacerRow = swatchTable.insertRow(5);
		var spacerCell = spacerRow.insertCell(0);
		//spacerCell.colSpan = 8;
		spacerCell.colSpan = 8;
		var hr = document.createElement('hr');
		Element.setStyle(hr, {'color': 'gray', 'backgroundColor': 'gray', 'height': '1px', 'border': '0', 'marginTop': '3px', 'marginBottom': '3px', 'padding': '0'});
		spacerCell.appendChild(hr);

		// Add custom color row
		var customRow = swatchTable.insertRow(6);
		var customColors = this.loadSetting('customColors')
			?  this.loadSetting('customColors').split(',')
			: new Array();
		this.customSwatches = [];
		for (var i = 0; i < 8; ++i) {
			var cell = customRow.insertCell(i);
			var color = customColors[i] ? customColors[i] : '#000000';
			var swatch = document.createElement('div');
			Element.setStyle(swatch, {'width': '15px', 'height': '15px', 'fontSize': '15px', 'border': '1px solid #EEEEEE', 'backgroundColor': color, 'padding': '0'});
			cell.appendChild(swatch);
			swatch.onclick = this.swatchCustomClickListener(color, swatch);
			swatch.onmouseover = this.swatchHoverListener(color);
			this.customSwatches.push(swatch);
		}

		// Add spacer row
		spacerRow = swatchTable.insertRow(7);
		spacerCell = spacerRow.insertCell(0);
		spacerCell.colSpan = 8;
		hr = document.createElement('hr');
		Element.setStyle(hr, {'color': 'gray', 'backgroundColor': 'gray', 'height': '1px', 'border': '0', 'marginTop': '3px', 'marginBottom': '3px', 'padding': '0'});
		spacerCell.appendChild(hr);

		// Add custom color entry interface
		var entryRow = swatchTable.insertRow(8);
		var entryCell = entryRow.insertCell(0);
		entryCell.colSpan = 8;
		var entryTable = document.createElement('table');
		entryTable.cellPadding = 0;
		entryTable.cellSpacing = 0;
		entryTable.border = 0;
		entryTable.style.width = '136px';
		entryCell.appendChild(entryTable);

		entryRow = entryTable.insertRow(0);
		var previewCell = entryRow.insertCell(0);
		previewCell.valign = 'bottom';
		var preview = document.createElement('div');
		Element.setStyle(preview, {'width': '15px', 'height': '15px', 'fontSize': '15px', 'border': '1px solid #EEEEEE', 'backgroundColor': '#000000'});
		previewCell.appendChild(preview);
		this.previewSwatch = preview;

		var textboxCell = entryRow.insertCell(1);
		textboxCell.valign = 'bottom';
		textboxCell.align = 'center';
		var textbox = document.createElement('input');
		textbox.type = 'text';
		textbox.value = '#000000';
		Element.setStyle(textbox, {'width': '70px', 'border': '1px solid gray' });
		textbox.onkeyup = function(e) {
				this.previewSwatch.style.backgroundColor = textbox.value;
			}.bindAsEventListener(this);
		textboxCell.appendChild(textbox);
		this.customInput = textbox;

		var submitCell = entryRow.insertCell(2);
		submitCell.valign = 'bottom';
		submitCell.align = 'right';
		var submit = document.createElement('input');
		submit.type = 'button';
		Element.setStyle(submit, {'width': '40px', 'border': '1px solid gray'});
		submit.value = this.options.addLabel;
		submit.onclick = function(e) {
				var idx = 0;
				if (this.activeCustomSwatch) {
					for (var i = 0; i < this.customSwatches.length; ++i)
						if (this.customSwatches[i] == this.activeCustomSwatch) {
							idx = i;
							break;
						}
					this.activeCustomSwatch.style.border = '1px solid #EEEEEE';
					this.activeCustomSwatch = null;
				} else {
					var lastIndex = this.loadSetting('customColorIndex');
					if (lastIndex) idx = (parseInt(lastIndex) + 1) % 8;
				}
				this.saveSetting('customColorIndex', idx);
				customColors[idx] = this.customSwatches[idx].style.backgroundColor = this.customInput.value;
				this.customSwatches[idx].onclick = this.swatchCustomClickListener(customColors[idx], this.customSwatches[idx]);
				this.customSwatches[idx].onmouseover = this.swatchHoverListener(customColors[idx]);
				this.saveSetting('customColors', customColors.join(','));
			}.bindAsEventListener(this);
		submitCell.appendChild(submit);

		// Create form
		var swatchForm = document.createElement('form');
		Element.setStyle(swatchForm, {'margin': '0', 'padding': '0'});
		swatchForm.onsubmit = function() {
			if (this.activeCustomSwatch) this.activeCustomSwatch.style.border = '1px solid #EEEEEE';
			this.activeCustomSwatch = null;
			this.editor.setDialogColor(this.customInput.value);
			return false;
		}.bindAsEventListener(this);
		swatchForm.appendChild(swatchTable);

		// Add to dialog window
		cont.appendChild(swatchForm);
		return cont;
	},

	swatchClickListener: function(color) {
		return function(e) {
				if (this.activeCustomSwatch) this.activeCustomSwatch.style.border = '1px solid #EEEEEE';
				this.activeCustomSwatch = null;
				this.options.onSelect(color);
			}.bindAsEventListener(this);
	},

	swatchCustomClickListener: function(color, element) {
		return function(e) {
				if (e.ctrlKey) {
					if (this.activeCustomSwatch) this.activeCustomSwatch.style.border = '1px solid #EEEEEE';
					this.activeCustomSwatch = element;
					this.activeCustomSwatch.style.border = '1px solid #FF0000';
				} else {
					this.activeCustomSwatch = null;
					this.options.onSelect(color);
				}
			}.bindAsEventListener(this);
	},

	swatchHoverListener: function(color) {
		return function(e) {
				this.previewSwatch.style.backgroundColor = color;
				this.customInput.value = color;
			}.bindAsEventListener(this);
	},

	loadSetting: function(name) {
		name = 'colorpicker_' + name;
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	},

	saveSetting: function(name, value, days) {
		name = 'colorpicker_' + name;
		if (!days) days = 180;
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
		document.cookie = name+"="+value+expires+"; path=/";
	},

	clearSetting: function(name) {
		this.saveSetting(name,"",-1);
	}

};




Effect.Accordian = function(el){
	typeof el != 'object' ? el = $(el) : 0;
    var cls = 'curOpt';
    var pel = $((el.up('ul')));
    var mel = $((pel.select('.'+cls).pluck('id').first()));
	if(typeof mel != 'object'){
		new Effect.BlindDown(el, {scaleFromCenter:true, duration:0.3});
		el.addClassName(cls)
	}else if (mel != el){
		new Effect.Parallel([
			new Effect.BlindUp(mel, {scaleFromCenter:true}),
			new Effect.BlindDown(el, {scaleFromCenter:true})
		], {
			  duration: 0.3
		});
		mel.removeClassName(cls);
		el.addClassName(cls)
    }else{
        new Effect.BlindUp(el, {scaleFromCenter:true, duration:0.3});
		el.removeClassName(cls);
		pel.select('.curOpen').each(function(oel){
			oel.removeClassName('curOpen')
		})
    }
}

Element.addMethods({
    accordian: function(element){
        new Effect.Accordian(element);
    }
});

var AnyFont = {

	ajaxUrl: false,
	otn: false,

	showOptions: function(el){
		$(el).style.display = $(el).style.display == 'none' ? '' : 'none';
	},

	toggleNew: function(elID){
		$(elID).getStyle('display') == 'none' ? new Effect.BlindDown(elID, {scaleFromCenter:true, duration:0.5}) : new Effect.BlindUp(elID, {scaleFromCenter:true, duration:0.5});
	},

	selectAll: function(el, val){
		this.val = val
		$(el).select(".clist").each(function(el){
			el.checked = !this.val.checked ? false : true;
		}.bind(this));
	},

	updateStyle: function(fel){
		AnyFont.showMessage(af_i18n.msg_saving_style, false);
		new Ajax.Request(AnyFont.ajaxUrl, {
			parameters: Form.serialize(fel)+'&action=anyfont_edit_styles',
			onSuccess: function(transport){
				this.resp = transport.responseJSON;
				if(this.resp.savestatus == "saved"){
					AnyFont.showMessage(af_i18n.msg_saved_style, 5);
					this.img = new Image();
					this.img.src = 'data:image/png;base64,'+this.resp.img;
					this.imgID = 'preview_image_'+this.resp.stylename.gsub(" ", "_");
					$(this.imgID).replace(this.img);
					this.img.id = this.imgID;
					this.img.addClassName('anyfont-style-preview');
				} else if(this.resp.savestatus == "savedNew"){
					AnyFont.showMessage(this.resp.msg, 5);
					this.upel = $('anyfont-list').down('ul.style-list');
					AnyFont.toggleNew('anyfont-style-new');
					this.upel.insert(this.resp.styleblock);
					AnyFont.initColorPicker();
					AnyFont.styleOptionsHide();
					AnyFont.stylesAccordian();
					new CheckboxStyle($('anyfont-options-'+this.resp.stylename));
				} else {
					AnyFont.showMessage(af_i18n.err_saving_style, 5);
				}
			}.bind(this)
		});
	},

	clearCache: function(){
		var response = confirm(af_i18n.chk_clear_cache);
		if(response){
			AnyFont.showMessage(af_i18n.msg_clear_cache, false);
			new Ajax.Request(AnyFont.ajaxUrl, {
				parameters: 'action=anyfont_clear_cache',
				onSuccess: function(transport){
					$('image_count').update(af_i18n.msg_no_images);
					$('image_size').update("");
					AnyFont.showMessage(transport.responseText, 5);
				}
			});
		}
	},

	fontUploaded: function(){
		var data = (frames['upload_target'].document.getElementsByTagName("body")[0].innerHTML).evalJSON();
		if(data.success) {
			AnyFont.showMessage(data.file_name+" "+af_i18n.msg_upload_success, 5);
			var font_checkbox = new Element("li").addClassName("checkbox").insert(new Element("input", {'type':'checkbox', 'name':data.file_name+"_checkbox"}).addClassName('clist'));
			var font_image = new Element("li").addClassName("font-name").insert(new Element("img", {'src':data.img_url, 'alt':data.file_name}));
			var font_del = new Element("li").addClassName("actions").insert(new Element("img", {'src':data.img_del, 'alt':'delete', 'onclick':"AnyFont.deleteFont('"+data.file_name+"');"}));
			var font =  new Element("li").addClassName("anyfont-font-block").insert(new Element("ul", {'id':data.file_name+"_item", 'class':"style-list-item"}).insert(font_checkbox).insert(font_image).insert(font_del));
			$('anyfont-fontlist').insert({top:font});
			$('font').setValue('');
		} else if(data.failure) {
			AnyFont.showMessage(af_i18n.err_upload_failed+" "+data.failure, 5);
		}
	},

	deleteFont: function(font){
		if(!font){
			this.fontlist = [];
			$('anyfont-fontlist').select('.clist').each(function(el){
				this.na = el.name.split("_checkbox");
				el.getValue() == "on" ? this.fontlist.push(this.na[0]):0;
			}.bind(this));
			if(this.fontlist.length > 0){
				this.param = 'action=anyfont_delete_font&fonts='+this.fontlist;
				this.fontlist.each(function(font){
					$(font+'_item').up("li.anyfont-font-block").remove();
				});
				AnyFont.showMessage(af_i18n.msg_del_fonts, 5);
			} else {
				AnyFont.showMessage(af_i18n.err_select_font, 5);
				return false;
			}
		}else{
			this.param = 'action=anyfont_delete_font&font-name='+font;
			AnyFont.showMessage(af_i18n.msg_del+" "+font+"...", 5);
			$(font+'_item').up("li.anyfont-font-block").remove();
		}
		new Ajax.Request(AnyFont.ajaxUrl, {
			parameters: this.param,
			onSuccess: function(transport){
				AnyFont.showMessage(transport.responseText, 5);
			}
		});
	},

	deleteStyle: function(style){
		if(!style){
			this.stylelist = [];
			$('anyfont-list').select('.clist').each(function(el){
				this.na = el.name.split("_checkbox");
				el.getValue() == "on" ? this.stylelist.push(this.na[0]):0;
			}.bind(this));
			if(this.stylelist.length > 0){
				this.confirmed = confirm(af_i18n.chk_del_styles+"\n\n"+af_i18n.del_style_note);
				if(this.confirmed){
					this.param = 'action=anyfont_delete_style&styles='+this.stylelist;
					this.stylelist.each(function(style){
						$(style+'_item').up("li.anyfont-style-block").remove();
					});
					AnyFont.showMessage(af_i18n.msg_del_styles, 5);
				}else{
					return false;
				}
			} else {
				AnyFont.showMessage(af_i18n.err_select_style, 5);
				return false;
			}
		}else{
			this.confirmed = confirm(af_i18n.chk_del_style+"\n\n"+af_i18n.del_style_note);
			if(this.confirmed){
				this.param = 'action=anyfont_delete_style&style-name='+style;
				AnyFont.showMessage(af_i18n.msg_del+" "+style+"...", 5);
				$(style+'_item').up("li.anyfont-style-block").remove();
			}else{
				return false;
			}
		}
		new Ajax.Request(AnyFont.ajaxUrl, {
			parameters: this.param,
			onSuccess: function(transport){
				AnyFont.showMessage(transport.responseText, 5);
			}
		});
	},

	startUpload: function(){
		AnyFont.showMessage(af_i18n.msg_upload_start, false);
	},

	showMessage: function(msg, timeout){
		this.offset = document.viewport.getScrollOffsets();
		this.msgbox = $("anyfont-upload-messages");
		if(this.offset[1] > 0){
			this.msgbox.style.position = "absolute";
			this.msgbox.style.top = (this.offset[1] + 50)+"px";
			this.msgbox.style.left = "250px";
			this.msgbox.style.padding = "15px 50px"
			this.msgbox.style.background = "rgb(255, 251, 204) url(../wp-content/plugins/anyfont/img/info.png) no-repeat 0 0";
		} else {
			this.msgbox.style.position = "relative";
			this.msgbox.style.top = "";
			this.msgbox.style.left = "";
			this.msgbox.style.padding = "5px";
			this.msgbox.style.background = this.msgbox.style.backgroundColor
		}
		this.msgbox.update(msg);
		this.msgbox.getStyle('display') == 'none' ? new Effect.Appear("anyfont-upload-messages") : 0;
		if(timeout != false){
			setTimeout("AnyFont.hideMessage()", (timeout*1000));
		}
	},

	hideMessage:  function(){
		$("anyfont-upload-messages").getStyle('display') != 'none' ? new Effect.Fade("anyfont-upload-messages") : 0;
	},

	initColorPicker: function(){
		if( (typeof $('anyfont-style-new')) === 'object' ){
			$('anyfont-style-new').select('.colorinput').each(function(el){
				if(!el.hasClassName('color-on')){
					new Control.ColorPicker(el);
					el.addClassName('color-on');
				}
			});
		}
		if( ( typeof $('anyfont-list') ) === 'object' ){
			$('anyfont-list').select('.colorinput').each(function(el){
				if(!el.hasClassName('color-on')){
					new Control.ColorPicker(el);
					el.addClassName('color-on');
				}
			})
		}
	},

	stylesAccordian: function(){
		if( (typeof $('anyfont-list') ) === "object" ){
			$('anyfont-list').select('.anyfont-style-edit').each(function(action_el){
				if(!action_el.hasClassName('accord')){
					action_el.observe('click', function(e){
						this.el = e.element();
						this.el.up('ul.style-list').select('.curOpen').each(function(oel){
							oel.removeClassName('curOpen');
						});
						this.el = this.el.up('li.anyfont-style-block').addClassName('curOpen').down('div');
						this.el.accordian();
					});
					action_el.addClassName('accord');
				}
			});
		}
	},

	styleOptionsHide: function(){
		if( typeof( $('anyfont-list') ) === 'object' ){
			$('anyfont-list').select('.anyfont-options-block').each(function(el){
				el.hide();
			});
		}
	},

	toggleDisabled: function(el){
		dropdown = $(el).up('div').next('select');
		el.getValue() == 'on' ? dropdown.enable() : dropdown.disable();
	},

	toggleHidden: function(el){
		hidden_el = $(el).up('div').next('div.hidden_option');
		el.getValue() == 'on' ? new Effect.BlindUp(hidden_el, {scaleFromCenter:true, duration:0.3}) : new Effect.BlindDown(hidden_el, {scaleFromCenter:true, duration:0.3});
	},

	updateOptions: function(frm){
		AnyFont.showMessage(af_i18n.msg_saving_settings, 5);
		this.params = $(frm).serialize();
		new Ajax.Request(AnyFont.ajaxUrl, {
			parameters: 'action=anyfont_update_option&'+this.params,
			onSuccess: function(transport){
				AnyFont.showMessage(transport.responseText, 5);
			}
		});
	}
}

var CheckboxStyle = Class.create({

	initialize: function(parentEl){
		this.parentEl = parentEl;
		this.parentEl.select("div.anyfont_checkbox").each(function(e){
			e.style.background = "transparent url(../wp-content/plugins/anyfont/img/checkbox.gif) no-repeat scroll 150px 2px";
			if(e.hasClassName('anyfont_checkbox_on')){
				e.style.backgroundPosition = "150px -48px";
			} else {
				e.style.backgroundPosition = "150px 2px";
			}
			e.down('input').hide();
			e.observe("mousedown", function(event){
				this.del = event.element();
				!this.del.hasClassName('anyfont_checkbox') ? this.del = this.del.up("div.anyfont_checkbox") : 0;
				if(this.del.className == "anyfont_checkbox"){
					this.del.style.backgroundPosition = "150px -23px";
				} else {
					this.del.style.backgroundPosition = "150px -75px";
				}
			}.bind(this));
			e.observe("mouseup", function(event){
				this.uel = event.element();
				!this.uel.hasClassName('anyfont_checkbox') ? this.uel = this.uel.up("div.anyfont_checkbox") : 0;
				this.selector = this.uel.down('input');
				if(this.uel.className == "anyfont_checkbox") {
					this.selector.checked = true;
					this.uel.addClassName("anyfont_checkbox_on");
					this.uel.style.backgroundPosition = "150px -48px";
				} else  if(this.uel.className == "anyfont_checkbox anyfont_checkbox_on"){
					this.selector.checked = false;
					this.uel.removeClassName("anyfont_checkbox_on");
					this.uel.style.backgroundPosition = "150px 2px";
				}
				if(!this.selector.hasClassName('anyfont_chk_only')){
					if(!this.selector.hasClassName("settings")){
						this.hidden_el = this.uel.next('div.hidden_option');
						!this.selector.checked ? new Effect.BlindUp(this.hidden_el, {scaleFromCenter:true, duration:0.3}) : new Effect.BlindDown(this.hidden_el, {scaleFromCenter:true, duration:0.3});
					} else {
						this.dropdown = this.uel.next('select');
						!this.selector.checked ? this.dropdown.disable() : this.dropdown.enable();
					}
				}
			}.bind(this));
		}.bind(this));
		document.observe("mouseup", function(){
			this.parentEl.select("div.anyfont_checkbox").each(function(e){
				if(e.down('input').getValue() == 'on'){
					e.style.backgroundPosition = "150px -48px";
				} else {
					e.style.backgroundPosition = "150px 2px";
				}
			})
		}.bind(this))
	}
});


document.observe("dom:loaded", function() {
	try{AnyFont.ajaxUrl = userSettings.url+'wp-admin/admin-ajax.php'}catch(e){}
	var loc = document.location.toString();
	page = loc.split("=");
	if(page[1] == "anyfont-styles"){
		AnyFont.initColorPicker();
		$('anyfont-style-new').hide();
		AnyFont.stylesAccordian();
		AnyFont.styleOptionsHide();
		$('anyfont_page').select(".anyfont_style_settings").each(function(el){
			new CheckboxStyle(el);
		});
	} else if(page[1] == 'anyfont-fonts'){
		$('file_upload_form').onsubmit=function() {
			$('file_upload_form').target = 'upload_target';
			$("upload_target").onload = AnyFont.fontUploaded
		}
	} else if(page[1] == 'anyfont-settings'){
		new CheckboxStyle($('autoreplace_form'));
		new CheckboxStyle($('advanced_form'));
	}
});