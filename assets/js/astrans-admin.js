jQuery( document ).ready(function() {
	JSON.stringify = JSON.stringify || function (obj) { /** fucntion parse Json to string **/
	    var t = typeof (obj);
	    if (t != "object" || obj === null) {
	        /** simple data type **/
	        if (t == "string") obj = '"'+obj+'"';
	        return String(obj);
	    }
	    else {
	        /** recurse array or object **/
	        var n, v, json = [], arr = (obj && obj.constructor == Array);
	        for (n in obj) {
	            v = obj[n]; t = typeof(v);
	            if (t == "string") v = '"'+v+'"';
	            else if (t == "object" && v !== null) v = JSON.stringify(v);
	            json.push((arr ? "" : '"' + n + '":') + String(v));
	        }
	        return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
	    }
	}; /** end parse **/

	/* ajax load save */
	jQuery('.astrans-save button').click(function(event) {
		astrans_save();
	});
	active_control_astrans();
	/* check all sync slug */
	jQuery('.astrans_tr_all').live('click', function(event) {
		if(jQuery(this).is(':checked') === true){
			jQuery('#astrans_sync').find('input[type=checkbox].astrans_check_all').attr('checked', 'checked');
			jQuery('.astrans_tr_all').attr('checked', 'checked');
		}else{			
			jQuery('#astrans_sync').find('input[type=checkbox].astrans_check_all').removeAttr('checked');
			jQuery('.astrans_tr_all').removeAttr('checked');
		}		
	});
	/* display table sync */
	if(jQuery('select[name=astrans_sync_type]').val() == 0){
		astrans_hs();
	}
	jQuery('select[name=astrans_sync_type]').change(function(event) {
		if(jQuery(this).val() == 0){
			astrans_hs();
			active_control_astrans();
		}else{
			active_control_astrans(true);
			astrans_hs(true);
			astrans_sync(jQuery(this).val(), jQuery('input[name=astran_filter]:checked').val());
			jQuery('.astrans_search input').val('');
		}
	});
	/* astrans save sync */
	jQuery('#astrans_sync button').live('click', function(event) {		
		var papa = jQuery(this).parent().parent();
		var a = papa.attr('id').split("_");
		astrans_save_sync(a[1],a[2], papa.find('input[type=text]').val());
	});
	/* astrans save all sync */
	jQuery('.astrans_save_all').click(function(event) {
		jQuery('#astrans_sync tbody tr .astrans_check_all:checked').each(function(index, el) {
			var papa = jQuery(this).parent().parent();
			var a = papa.attr('id').split("_");
			astrans_save_sync(a[1],a[2], papa.find('input[type=text]').val());
		});
	});
	/* astrans filter sync */
	jQuery('input[name=astran_filter]').change(function(event) {
		if(jQuery('select[name=astrans_sync_type]').val() != 0){
			astrans_sync(jQuery('select[name=astrans_sync_type]').val(), jQuery('input[name=astran_filter]:checked').val() );
			jQuery('.astrans_search input').val('');
		}
	});
	/* astrans search sync */
	jQuery('.astrans_search button').click(function(event) {
		if(jQuery('select[name=astrans_sync_type]').val() != 0){
			if(jQuery('.astrans_search input').val() != ''){
				astrans_sync(jQuery('select[name=astrans_sync_type]').val(), jQuery('input[name=astran_filter]:checked').val(), jQuery('.astrans_search input').val() );
				jQuery('.astrans_search input').addClass('active_s');
			}else if( (jQuery('.astrans_search input').hasClass('active_s') === true) && (jQuery('.astrans_search input').val() == '') ){
				astrans_sync(jQuery('select[name=astrans_sync_type]').val(), jQuery('input[name=astran_filter]:checked').val(), jQuery('.astrans_search input').val() );
				jQuery('.astrans_search input').removeClass('active_s');
			}	
		}
	});
	/* page navi */
	jQuery('#astrans_navi a').live('click', function(event) {
		astrans_sync(jQuery('select[name=astrans_sync_type]').val(), jQuery('input[name=astran_filter]:checked').val(), jQuery('.astrans_search input').val(), jQuery(this).attr('data-page'));
	});
	/* set paged */
	jQuery('select[name=astrans_paged]').change(function(event) {
		var data = { action: 'set_paged' };
		data['paged'] = jQuery(this).val();
		jQuery.get(ajaxurl, data, function(response) {
			astrans_sync(jQuery('select[name=astrans_sync_type]').val(), jQuery('input[name=astran_filter]:checked').val());
	    });
	});

	jQuery(window).load(function() {
		if(jQuery('input[name=astrans_enable]').is(':checked') === false){
			jQuery('.astrans_for input').attr('disabled', 'disabled');
		}else{
			jQuery('.astrans_for input').removeAttr('disabled');
		}
	});
	jQuery('input[name=astrans_enable]').change(function(event) {
		if(jQuery('input[name=astrans_enable]').is(':checked') === false){
			jQuery('.astrans_for input').attr('disabled', 'disabled');
		}else{
			jQuery('.astrans_for input').removeAttr('disabled');
		}
	});

});

/* function call ajax save */
function astrans_save(){
	var a = jQuery('input[name=astrans_enable]');
	var data = { action: 'save_astrans_astrans' };

	/* astrans language */
	data['astrans_language'] = jQuery('select[name=astrans_language]').val();

	a.is(':checked') === true ? data['astrans_status'] = '1' : data['astrans_status'] = '0';
	if(data['astrans_status'] == '1'){		
		/* astrans for */
		var arr_show = {};
		jQuery('.astrans_for input:checkbox').each(function(index, el) {
			if(jQuery(this).is(':checked') === true){
				arr_show[jQuery(this).attr('id')] = '1';
			}else{
				arr_show[jQuery(this).attr('id')] = '0';
			}
		});
		data['astrans_trans_for'] = JSON.stringify(arr_show);
	}

	jQuery('#astrans_loading').addClass('active');
	jQuery.get(ajaxurl, data, function(response) {		
        if(response.success === true) {
        	setTimeout(function(){
        		show_mes( response.data['status'] , response.data['messenger']);
        		jQuery('#astrans_loading').removeClass('active');	
        	}, 500);
        }
    });
}
/* Function call ajax load data sync
* 
*  post_type : page, post or cate
*
*/
function astrans_sync(post_type, filter = null, keyworks = '', page = 1){
	active_control_astrans();
	var data = { action: 'table_sync_astrans'};
	data['post_type'] = post_type;
	data['filter'] = filter;
	data['keyworks'] = keyworks;
	data['paged']	= page;
	data['pre_page'] = jQuery('select[name=astrans_paged]').val();
	var data_navi = data;
	
	jQuery('#astrans_loading2').addClass('active');
	jQuery.get(ajaxurl, data, function(response) {
		setTimeout(function(){
			jQuery('#astrans_sync tbody').find('tr').not('.astrans_loading').remove();
			jQuery('#astrans_sync tbody').append(response.slice(0, -1));
			jQuery('#astrans_loading2').removeClass('active');
			active_control_astrans(true);
			if(response.slice(0, -1) == ''){
				jQuery('#astrans_sync tbody').append('<tr><td colspan=20 class=text-center>All translated slug or search keywords do not match</td></tr>');
			}
    	}, 500);        	
    });
	
	data_navi['action'] = 'astrans_navi';
    jQuery.get(ajaxurl, data_navi, function(response) {
    	jQuery('#astrans_navi').empty().append(response.slice(0, -1));
    });
}
/* Function call ajax save sync
* 
*  post_type : page, post or cate
*
*/
function astrans_save_sync(type, id, slug){
	active_control_astrans();
	var data = { action: 'save_sync_astrans'};
	data['type'] = type;
	data['id'] = id;
	data['slug'] = slug;
	jQuery('#astrans_loading2').addClass('active');
	jQuery.get(ajaxurl, data, function(response) {
		setTimeout(function(){
			jQuery('#astrans_loading2').removeClass('active');
			astrans_sync(type, jQuery('input[name=astran_filter]:checked').val());
			show_mes(true, 'Successfully Saved');
		}, 500);
    });
}
/* Enabled/Disabled controll */
function active_control_astrans(active = false){
	if(active === false){
		jQuery('input[name=astran_filter]').attr('disabled', '');
		jQuery('.astrans_search button').addClass('save');
		jQuery('.astrans_search').children().attr('disabled', '');
		jQuery('.astrans_save_all').addClass('save');		
	}else{
		jQuery('input[name=astran_filter').removeAttr('disabled');
		jQuery('.astrans_search').children().removeAttr('disabled');
		jQuery('.astrans_search button').removeClass('save');
		jQuery('.astrans_save_all').removeClass('save');
	}
}
function astrans_hs(active = false){
	if(active === false){
		jQuery('#astrans_sync').addClass('astrans_hide');
		jQuery('.astrans_save_all').addClass('astrans_hide');
		jQuery('.astrans_search').addClass('astrans_hide');		
	}else{
		jQuery('#astrans_sync').removeClass('astrans_hide');
		jQuery('.astrans_save_all').removeClass('astrans_hide');
		jQuery('.astrans_search').removeClass('astrans_hide');
	}
}
function show_mes(status = true, mes = ''){
	if(status == true ){
		var html = '<div class="mes"><i class="dashicons dashicons-yes"></i><span>'+mes+'</span></div>';
	}else{
		var html = '<div class="mes mes-no"><i class="dashicons dashicons-no"></i><span>'+mes+'</span></div>';
	}
	jQuery(html).prependTo('#messenger_ast');
	jQuery('#messenger_ast .mes:first-child').fadeIn('slow');
	setTimeout(function(){ 
		jQuery('#messenger_ast .mes:last-child').fadeOut('slow');
		setTimeout(function(){
			jQuery('#messenger_ast .mes:last-child').remove();
		}, 1000);		
	}, 6000);
}