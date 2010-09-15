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
	Initialize
-----------------------*/
(function($) {
		  
	$(document).ready(function () {
		$('#query').bind('focus',function (){
			if(this.value=='search...') { this.value='';$('#query').toggleClass('focus'); }
		});
		$('#query').bind('blur',function (){
			if(this.value=='') { this.value='search...';$('#query').toggleClass('focus'); }
		});
	});

})(jQuery);