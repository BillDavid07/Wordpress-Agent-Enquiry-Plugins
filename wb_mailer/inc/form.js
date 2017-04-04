//var $ = jQuery;

jQuery(document).on('submit', '#enquiyform', function(e){
	e.preventDefault();

    var ajaxurl = base_url;

    jQuery.ajax({
           type : "post",
           dataType : "json",
           url : ajaxurl,
			   data : jQuery('#enquiyform').serialize(),
           beforeSend: function(){
            jQuery('#enquiyform button[type="submit"]').html('Sending...');
           },
           complete: function(){
            jQuery('#enquiyform button[type="submit"]').html('Send');
           },
           success: function(response) {

             if(response.status){
                jQuery('#ajax-response').show();

                var ht = '<div class="alert alert-success">\
                  <strong>Success!</strong> ' + response.message + '\
                </div>';

                jQuery('.msg').html(ht);
                jQuery('#enquiyform')[0].reset();
             }else{

                var ht = '<div class="alert alert-danger">\
                  <strong>Error!</strong> ' + response.message + '\
                </div>';

                jQuery('#ajax-response').show();
                jQuery('.msg').html(ht);
             }

            setTimeout(function(){
          		jQuery('#ajax-response').fadeOut('slow');
        	}, 3000);
           }
    });  
});