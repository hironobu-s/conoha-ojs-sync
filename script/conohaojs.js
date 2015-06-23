var conohaojs_connect_test = function() {
    var data = {
	action: "conohaojs_connect_test",
	username: jQuery("#conohaojs-username").val(),
	password: jQuery("#conohaojs-password").val(),
	tenantId: jQuery("#conohaojs-tenant-id").val(),
	authUrl: jQuery("#conohaojs-auth-url").val(),
	region: jQuery("#conohaojs-region").val()
    };

    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: data,
        success: function (response) {
	    var res = jQuery.parseJSON(response);

            jQuery("html,body").animate({scrollTop: 0}, 1000);
            jQuery("#conohaojs-flash P").empty().append(res["message"]);
	    if(res["is_error"]) {
		jQuery("#conohaojs-flash").addClass("error");
	    } else {
		jQuery("#conohaojs-flash").removeClass("error");
	    }

	    jQuery('#conohaojs-flash').show();
        }
        //dataType: 'html'
    });
    jQuery("#selupload_spinner").unbind("ajaxSend");
};

var conohaojs_resync = function() {
    if( ! confirm("It will may take a long time. Are you sure? ")) {
	return;
    }
};

jQuery(function() {
    jQuery("#conohaojs-flash").hide();
});
