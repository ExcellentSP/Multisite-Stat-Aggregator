(function($){

    var ajaxRequest = function(){
        var siteID = $( '#mssa_select_site' ).val();
        $.ajax({
            url: "/wp-json/vendor/mssa/v1/site/" + siteID,
            context: document.body,
            accepts: 'application/json'
        }).done(function( response ) {
            response = JSON.parse(response);
            var mss = $( "#mssa_multisite_stats" );
            mss.empty();
            mss.append( response.body );
        });
    };

    var siteSwitch = $( '#mssa_select_site' );
    siteSwitch.on( 'change', ajaxRequest );

    setInterval(ajaxRequest, 60000);

})(jQuery);