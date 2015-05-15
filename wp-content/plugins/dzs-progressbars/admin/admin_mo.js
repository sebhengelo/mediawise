

function page_mainoptions_ready(){
    jQuery('.save-mainoptions').bind('click', mo_saveall);


    jQuery('.saveconfirmer').fadeOut('fast');
};




function mo_saveall(){
    jQuery('#save-ajax-loading').css('visibility', 'visible');
    var mainarray = jQuery('.mainsettings').serialize();
    var data = {
        action: 'dzsprg_ajax_save_mo',
        postdata: mainarray
    };
    jQuery('.saveconfirmer').html('Options saved.');
    jQuery('.saveconfirmer').fadeIn('fast').delay(2000).fadeOut('fast');
    jQuery.post(ajaxurl, data, function(response) {
        if(window.console !=undefined ){
            console.log('Got this from the server: ' + response);
        }
        jQuery('#save-ajax-loading').css('visibility', 'hidden');
    });

    return false;
}