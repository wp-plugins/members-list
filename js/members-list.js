/**************************************************************************************************/
/***
/***	WORDPRESS MEMBERS LIST PLUGIN JAVASCRIPT
/***	-----------------------------------------------------------------------
/***	Written by Matthew Praetzel. Copyright (c) 2009 Matthew Praetzel.
/***	-----------------------------------------------------------------------
/***	All Rights Reserved. Any use of these functions & scripts without written consent is prohibited.
/***
/**************************************************************************************************/

/*-----------------------
	Variables
-----------------------*/

/*-----------------------
	Initialize
-----------------------*/
jQuery(document).ready(function() {
	jQuery('#members_list_fields').tableDnD({
		onDrop : function () {
			jQuery('#fields tr:even').addClass('alternate');
			jQuery('#fields tr:odd').removeClass('alternate');
			tern_members_submitForm();
		}
	});
});
/*-----------------------
	Forms
-----------------------*/
function tern_members_submitForm() {
	var p = tern_members_getFormPost('tern_wp_members_list_fm');
	jQuery.ajax({
		async : false,
		type : 'POST',
		url : tern_wp_root+'/wp-admin/admin.php',
		dataType : 'text',
		data : p,
		success : function (m) {
			jQuery('#tern_wp_message').html(m);
		},
		error : function () {
			jQuery('#tern_wp_message').html('There was an error while processing your request. Please try again.');
		}
	});
	jQuery('#tern_members_sample_markup').load(tern_wp_root+'/wp-admin/admin.php','page=Configure Mark-Up&action=getmarkup',function () {});
}
function tern_members_editField(i) {
	var p = document.getElementById(i);
	var n = jQuery('#'+i+' .tern_members_fields').toggleClass('hidden');
	var o = jQuery('#'+i+' .tern_memebrs_edit');
	o.html = o.html() == 'Edit' ? 'Quit Editing' : 'Edit';
}
function tern_members_renderField(i) {
	var a = ['field_titles','field_markups'];
	jQuery('#'+i+' .tern_members_fields').each(function() {
		var n = this.name ? this.name.replace('%5B%5D','') : '';
		for(k in a) {
			if(this.name && n == a[k]) {
				jQuery('#'+i+' .'+n).text(this.value);
				break;
			}
		}
	});
	tern_members_submitForm();
	tern_members_editField(i);
}
function tern_members_getFormPost(f) {
	var f = document.getElementById(f),e = f.elements,p = '',v;
	for(var i=0;i<e.length;i++) {
		if(e[i].name) {
			if((this.tern_members_inputType(e[i]) == 'radio' || this.tern_members_inputType(e[i]) == 'checkbox') && !e[i].checked) {
				continue;
			}
			v = e[i].name + '=' + escape(e[i].value);
			p += p.length > 0 ? '&' + v : v;
		}
	}
	return p;
}
function tern_members_inputType(i) {
	if(i && i.type) { return i.type; }
	return '';
}