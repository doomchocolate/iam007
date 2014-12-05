jQuery(function(){
    var headerH = jQuery('#tz-header-wrapper').height();
    jQuery(document).ready(function(){
       headerH  = jQuery('#tz-header-wrapper').height();
    });
    jQuery(window).scroll(function(){
//        if(jQuery(window).width() > 979){
            if(jQuery('#tz-header-wrapper').hasClass('tz-header-slider')){
                if( jQuery(this).scrollTop() >(jQuery('#tz-header-wrapper').height()/2) && jQuery(this).scrollTop() <= jQuery('#tz-header-wrapper').height()){
                    jQuery('#tz-header-wrapper').css({
        //                'top':'-100%'
                        height: jQuery('#tz-header-wrapper').height() * 2
                    });
                }else{
                    jQuery('#tz-header-wrapper').css({
                        //                'top':'-100%'
                        height: 'none'
                    });
                }
    //            else{
    //                jQuery('#tz-header-wrapper').css({
    //                    'top':'0',
    //                    height: headerH * 2
    //                });
    //            }
    //            if( (jQuery(this).scrollTop() + jQuery('#tz-header-wrapper').outerHeight()) >= (jQuery('#tz-slide-show-wrapper').height() -headerH * 2)){
                if( (jQuery(this).scrollTop() ) >= headerH){
    //                jQuery('#tz-header-wrapper').addClass('tz-header-fixed').css({'top':0});
                    jQuery('#tz-header-wrapper').addClass('tz-header-fixed').css({height: jQuery('#tz-header-wrapper').height()}).css({height: 'none'});
                }else{
                    if(jQuery('#tz-header-wrapper').hasClass('tz-header-fixed')){
                        jQuery('#tz-header-wrapper').removeClass('tz-header-fixed');
                    }
                }
            }
//        }
    });
});