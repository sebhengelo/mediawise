/*
 * Author: Digital Zoom Studio
 * Website: http://digitalzoomstudio.net/
 * Portfolio: http://bit.ly/nM4R6u
 * This is not free software.
 * ZoomBox v2.00
 */

var _zoombox_maincon;
window.zoombox_hardresize = false;
window.zoombox_forwardanimation = false;
window._zoombox_maincon = _zoombox_maincon;
//ZoomBox
(function ($) {
    var _conHolder
        , _holder = null
        , _theItem = null
        , _theDivItem = null
        ,_holderText = null
        , toload
        ,_holderBg
        ,_bigimageCon
        ,_bigimageThe
        ,_gallerymenuCon
        ,_conZoomboxArrows
        ;
    var maincon_orightml = '';
    //window params
    var _w;
    var ww
        , wh
        , cw
        , ch
        , iw //== item width
        , ih // == item height
        , width_border = 30
        ,bw //==big image width
        ,bh
        ,binw = 0 //bigimage natural width
        ,binh = 0 //bigimage natural height
        ,orig_bw
        ,orig_bh
        ,mouseX
        ,mouseY
        ,bi_viewIndexX = 0
        ,bi_viewIndexY = 0
        ,bigwidth = -1 // -- -1 = undefined
        ,bigheight = -1
        ,dims_scaling = 'proportional'
        ,the_type = 'thumb'
        ;
    var settings_paddingHorizontal = "default"
        ,settings_paddingVertical = "default"
        ;
    var gallerymenu_arr = []
        ,gallerymenu_startindex = 0
        ,gallerymenu_currindex = 0
        ;
    var busy = false;
    var int_calculateDims;

    //==main options var
    var o = {};
    var loaded_opts = false;
    var theurl = window.location.href;

    var translate_skin_nebula_closebtn = 'CLOSE <span class="strong-x">X</span>';


    var swipe_maintarget
        ,swipe_maintargettotalwidth = 0
        ,swipe_maintargettotalheight = 0
        ,swipe_maintargettotalclipwidth = 0
        ,swipe_maintargettotalclipheight = 0
        ,swipe_maintargetoriginalposx = 0
        ,swipe_maintargetoriginalposy = 0
        ,swipe_maintargettargetposx = 0
        ,swipe_maintargettargetposy = 0
        ,swipe_originalposx
        ,swipe_originalposy
        ,swipe_touchdownposx
        ,swipe_touchdownposy
        ,swipe_touchupposx
        ,swipe_touchupposy
        ,swipe_dragging = false
        ,dir_hor = false
        ,dir_ver = false
        ,has_zoom = true
        ,had_zoom = false// == works with o.settings_zoom_doNotGoBeyond1X / if the item is already at 1x there is no need to zoom, so we just remove the has-zoom class in that case
        ;



    var down_x = 0
        ,up_x = 0
        ,screen_mousex = 0
        ,dragging = false
        ,def_x = 0
        ,targetPositionX = 0
        ,_swiper = _conHolder
        ,_swiper_init_position = 0 // -- the swipper initial position before dragging
        ,currPageX = 0 // -- the current swiper position
        ;

    var inter_closezoombox = 0;

    $.fn.zoomBox = function (argo) {
        var defaults = {
            settings_paddingHorizontal: 'default',settings_paddingVertical: 'default'
            ,settings_resizemaincon: 'off'
            ,design_animation: 'fromcenter'
            ,design_skin: 'skin-gamma'
            ,design_borderwidth: 'default'
            ,settings_deeplinking: 'on'
            ,settings_disableSocial: 'off'
            ,settings_useImageTag: 'default' // -- use the img tag or div for displaying images
            ,zoombox_audioplayersettings : {}
            , settings_makeFunctional: false
            ,settings_usearrows: 'on'
            ,social_enableTwitterShare: 'on'
            ,social_enableGooglePlusShare: 'on'
            ,social_extraShareIcons: ''
            ,videoplayer_settings : {}
            ,audioplayer_settings : {}
            ,settings_extraClasses: ''
            ,settings_disablezoom: 'peritem'
            ,settings_zoom_doNotGoBeyond1X: 'off'
            ,settings_zoom_disableMouse: 'off'
            ,settings_enableSwipe: 'on'
            ,settings_enableSwipeOnDesktop: 'off'
            ,settings_galleryMenu: 'dock'
        };
//var o = {};
        //console.log(theurl);

        //lets singleton the main options
        if(!loaded_opts){
            o = $.extend(defaults, argo);
            loaded_opts = true;
        }else{
            o = $.extend(o, argo);
        }

        //console.log(maincon, busy);



        if(typeof window.zoombox_settings !='undefined'){
            if(typeof window.zoombox_settings.translate_skin_nebula_closebtn !='undefined'){
                translate_skin_nebula_closebtn = window.zoombox_settings.translate_skin_nebula_closebtn;
            }
        }





        $.fn.zoomBox.setOptions = function (argo) {
            if(argo.design_skin!=undefined){
                _zoombox_maincon.removeClass(o.design_skin);
                _zoombox_maincon.addClass(argo.design_skin);
            }
            if(argo.settings_extraClasses!=undefined){
                _zoombox_maincon.removeClass(o.settings_extraClasses);
                _zoombox_maincon.addClass(argo.settings_extraClasses);
            }
            o = $.extend(o, argo);
        };

        window.api_zoombox_setoptions = function (argo) {
            if(argo.design_skin!=undefined){
                _zoombox_maincon.removeClass(o.design_skin);
                _zoombox_maincon.addClass(argo.design_skin);
            }
            if(argo.settings_extraClasses!=undefined){
                _zoombox_maincon.removeClass(o.settings_extraClasses);
                _zoombox_maincon.addClass(argo.settings_extraClasses);
            }
            o = $.extend(o, argo);
        };




        setup_paddings();




        $(this).data('zoombox_options', o);



        if (typeof _zoombox_maincon == "undefined" && busy == false) {
            init();
        }

//        _zoombox_maincon = undefined;
//        console.info(_zoombox_maincon, typeof _zoombox_maincon);


        //===trying to singleton it
        function init() {
            //==singleton



            $('body').append('<div class="zoombox-maincon disabled"><div class="zoombox-bg" onclick=""></div><div class="gallery-menu-con"></div><div class="bigimage-con"></div></div>');
            _zoombox_maincon = $('body').children('.zoombox-maincon');
            _bigimageCon = _zoombox_maincon.children('.bigimage-con');
            _gallerymenuCon = _zoombox_maincon.children('.gallery-menu-con');
            if(String(_zoombox_maincon.attr('class')).indexOf('skin-')==-1){
                _zoombox_maincon.addClass(o.design_skin);
            }

            maincon_orightml = _zoombox_maincon.html();


            if(_zoombox_maincon.hasClass('skin-gamma')){
                o.design_skin = 'skin-gamma';

                if(isNaN(parseInt(o.design_borderwidth, 10))){
                    o.design_borderwidth = 30;
                }
            }


            if(_zoombox_maincon.hasClass('skin-nebula')){
                o.design_skin = 'skin-nebula';

                if(isNaN(parseInt(o.design_borderwidth, 10))){
                    o.design_borderwidth = 0;
                }


                if(o.settings_useImageTag=='default'){
                    o.settings_useImageTag = 'off';
                }
            }





            if(o.design_borderwidth=='default'){
                o.design_borderwidth = 30;
            }




            if(isNaN(parseInt(o.design_borderwidth, 10))){
                width_border = 30;
            }else{
                width_border = parseInt(o.design_borderwidth, 10);
            }


            if(o.settings_useImageTag=='default'){
                o.settings_useImageTag = 'on';
            }
            if(o.settings_useImageTag == 'on'){
            }

            _zoombox_maincon.addClass(o.settings_extraClasses);


            _w = $(window);
            busy = true;
            setTimeout(function () {
                busy = false;
            }, 500);

            ww = $(window).width();

//            console.log(theurl, theurl.indexOf('zoombox='))
            if(theurl.indexOf('zoombox=')>-1){
//                console.log('testtt', get_query_arg(theurl, 'zoombox'));
                if(get_query_arg(theurl, 'zoombox')){
                    var tempNr = parseInt(get_query_arg(theurl, 'zoombox'),10);
//                    console.info(String(tempNr), String(tempNr)=='NaN');
                    if(String(tempNr)!='NaN'){
                        if(tempNr>-1){
                            goto_item($('.zoombox').eq(tempNr));
                        }
                    }else{

                        var auxobj = $('#'+get_query_arg(theurl, 'zoombox'));
                        goto_item(auxobj);
                    }
                }
                //$('.zoombox').eq
            }

            _w.bind('resize', handleResize);
            $(document).undelegate(".zoombox-bg", "click");
            $(document).delegate(".zoombox-bg", "click", click_close);
            $(document).bind("keyup", handle_keyup);

            handleResize();


            window.dzszb_open = function (arg, argtype, otherargs) {
                if (_zoombox_maincon == undefined) {
                    alert('please init zoomBox first')
                    return;
                }
                gotoItem(arg, argtype, otherargs);
            };
            window.dzszb_open_item = function (arg) {
                if (_zoombox_maincon == undefined) {
                    alert('please init zoomBox first')
                    return;
                }
                goto_item(arg);
            };
            /*
             */

            //== for legacy, did not work :(
            $.fn.zoomBox.open = function (arg, argtype, otherargs) {
                if (_zoombox_maincon == undefined) {
                    alert('please init zoomBox first')
                    return;
                }
                gotoItem(arg, argtype, otherargs);
            };

            $.fn.zoomBox.close = function () {
                if (_zoombox_maincon == undefined) {
                    alert('please init zoomBox first')
                    return;
                }
                click_close();
            };
        }

        function handle_keyup(e){

            //console.info(e);

            if (e.which == 37) {
                _conZoomboxArrows.children('.zb-arrow-left').trigger('click');
                return false;
            }

            if (e.which == 39) {
                _conZoomboxArrows.children('.zb-arrow-right').trigger('click');
                return false;
            }

            if (e.which == 27) {
                close_zoombox();
                return false;
            }
        }

        function handleResize() {
            // === if the resize has the same values no need for the resize function
            if(window.zoombox_hardresize==false && (ww == _w.width() && wh == _w.height())){
                return;
            }



            ww = _w.width();
            wh = _w.height();
            if(o.settings_resizemaincon=='on'){
                _zoombox_maincon.css({ 'width': ww, 'height': wh });
            }
            clearTimeout(int_calculateDims);
            int_calculateDims = setTimeout(calculateDims, 600);

            window.zoombox_hardresize = false;
        }

        function click_close(e) {
            var _t = $(this);
            //console.log(_t);
            _zoombox_maincon.find('.zoombox-bg').eq(0).removeClass('pureblack');
            close_zoombox();
        }

        window.api_close_zoombox = function(){
            close_zoombox();
        }

        function close_zoombox(){
            if(_conHolder!=undefined){
                _conHolder.fadeOut('slow');
            }

            _zoombox_maincon.children('.zoombox-bg').fadeOut('slow');
            _zoombox_maincon.children('.gallery-menu-con').fadeOut('slow');
            _zoombox_maincon.children('.con-zoomboxArrows').fadeOut('slow');



            if(_holderBg!=undefined){
                _holderBg.css({
                    'width': 0
                    ,'height': 0
                    ,'margin-left': 0
                    ,'margin-top': 0
                })
            }


            //console.log(the_type);

            if(the_type=='image'){
                /*
                 _theItem.addClass('no-animation');
                 _theItem.css({
                 'transform-origin' : 'center center'
                 })
                 setTimeout(function(){

                 _theItem.removeClass('no-animation');
                 _theItem.css({
                 'transform' : 'scale(0)'
                 ,'opacity' : '0'
                 })
                 },30)
                 */
                //console.log(_holder);

            }
            //console.log(_holderCon);
            if(_conHolder!=undefined){
                _conHolder.css({
                    'opacity' : 0,
                    'transform' : 'scale(0.3)'
                })
            }
            if(_theItem!=undefined){
                _theItem.css({
                    'opacity' : '0'
                })
            }


            if(_gallerymenuCon.html()!=''){
                //_gallerymenuCon.children().remove();
                gallerymenu_arr = [];
            }

            if(_zoombox_maincon.hasClass('noanim')){
                _conHolder.hide();
                _zoombox_maincon.children('.zoombox-bg').hide();
                _zoombox_maincon.children('.gallery-menu-con').hide();
                _zoombox_maincon.children('.con-zoomboxArrows').hide();

                disable_zoombox();
            }else{


                setTimeout(function () {
                    disable_zoombox();
                }, 1000);
            }


            if(inter_closezoombox){
                clearTimeout(inter_closezoombox);
            }

            if(o.settings_deeplinking=='on' && has_history_api()==true){
                var newurl = add_query_arg(theurl, 'zoombox', "NaN");
                theurl = newurl;
                history.pushState({}, "", newurl);
            }
        }

        function disable_zoombox(){

            _zoombox_maincon.html(maincon_orightml);
            _zoombox_maincon.addClass('disabled');
            busy = false;
        }



        function click_imageitem(e){

            var _t = $(this);
//            console.info(_t);

            if(_bigimageThe!=undefined || _t.parent().hasClass('has-zoom')==false){
                return;
            }
            _bigimageCon.append('<div class="the-bg"></div>');
            _bigimageCon.append(_t.clone());
            _bigimageThe = _bigimageCon.children('img').eq(0);

            _bigimageThe.bind('touchstart', handle_touchStart);
            _bigimageThe.bind('touchmove', handle_touchMove);
            _bigimageThe.bind('touchend', handle_touchEnd);


            orig_bw = _bigimageThe.width();
            orig_bh = _bigimageThe.height();
            _bigimageThe.css('margin-left', orig_bw*(-0.5));
            _bigimageThe.css('margin-top', orig_bh*(-0.5));

            setTimeout(function(){
                //console.log(_bigimageThe.width());
                _bigimageThe.css('opacity',1);
                _bigimageThe.css('margin-left', 0);
                _bigimageThe.css('margin-top', 0);
                if(o.settings_zoom_disableMouse=='on'){

                    _bigimageThe.css('left', 0);
                    _bigimageThe.css('top', 0);
                }else{

                    _bigimageThe.css('left', 0);
                    _bigimageThe.css('top', 0);
                }



                _bigimageThe.unbind('mousemove');
                _bigimageThe.bind('mousemove',bigimage_handle_mouse);


                _bigimageThe.unbind('click');
                _bigimageThe.bind('click',bigimage_handle_mouse);
                _bigimageCon.addClass('active');

                if(ww<481){
                    $('html,body').animate({scrollTop: 0}, 500);
                    _zoombox_maincon.find('.zoombox-bg').eq(0).addClass('pureblack');
                }

                calculateDims();

            },500);
        }
        function handle_touchStart(e){
            //console.info('touchstart');
            swipe_maintarget = _bigimageThe;
            swipe_maintargettotalwidth = ww;
            swipe_maintargettotalclipwidth = bw;// should be bigger then totalwidth
            swipe_maintargettotalheight = wh;
            swipe_maintargettotalclipheight = bh;
            swipe_maintargetoriginalposx = parseInt(swipe_maintarget.css('left'), 10);
            swipe_maintargetoriginalposy = parseInt(swipe_maintarget.css('top'), 10);
            swipe_touchdownposx = e.originalEvent.touches[0].pageX;
            swipe_touchdownposy = e.originalEvent.touches[0].pageY;
            swipe_dragging = true;
        }
        function handle_touchMove(e){

            if(swipe_dragging==false){
                return;
            }else{
                if(dir_hor){
                    //console.log(swipe_maintargettotalwidth, swipe_maintargettotalclipwidth, swipe_maintargettotalheight, swipe_maintargettotalclipheight);
                    swipe_touchupposx = e.originalEvent.touches[0].pageX;
                    //console.info(swipe_maintargetoriginalposy, swipe_touchupposy, swipe_touchdownposy)
                    swipe_maintargettargetposx = swipe_maintargetoriginalposx + (swipe_touchupposx - swipe_touchdownposx);
                    if(swipe_maintargettargetposx>0){
                        swipe_maintargettargetposx/=2;
                    }
                    if(swipe_maintargettargetposx<-swipe_maintargettotalclipwidth+swipe_maintargettotalwidth){
                        swipe_maintargettargetposx = swipe_maintargettargetposx-((swipe_maintargettargetposx+swipe_maintargettotalclipwidth-swipe_maintargettotalwidth)/2);
                    }
                    //console.log(swipe_maintargettargetposx);

                    swipe_maintarget.css('left', swipe_maintargettargetposx);

                    if(swipe_maintargettargetposx>0){
                        swipe_maintargettargetposx = 0;
                    }
                    if(swipe_maintargettargetposx<-swipe_maintargettotalclipwidth+swipe_maintargettotalwidth){
                        swipe_maintargettargetposx = swipe_maintargettargetposx-(swipe_maintargettargetposx+swipe_maintargettotalclipwidth-swipe_maintargettotalwidth);
                    }
                }
                if(dir_ver){
                    swipe_touchupposy = e.originalEvent.touches[0].pageY;
                    //console.info(swipe_maintargetoriginalposy, swipe_touchupposy, swipe_touchdownposy)
                    swipe_maintargettargetposy = swipe_maintargetoriginalposy + (swipe_touchupposy - swipe_touchdownposy);
                    if(swipe_maintargettargetposy>0){
                        swipe_maintargettargetposy/=2;
                    }
                    if(swipe_maintargettargetposy<-swipe_maintargettotalclipheight+swipe_maintargettotalheight){
                        swipe_maintargettargetposy = swipe_maintargettargetposy-((swipe_maintargettargetposy+swipe_maintargettotalclipheight-swipe_maintargettotalheight)/2);
                    }
                    //console.log(swipe_maintargettargetposy);

                    swipe_maintarget.css('top', swipe_maintargettargetposy);

                    if(swipe_maintargettargetposy>0){
                        swipe_maintargettargetposy = 0;
                    }
                    if(swipe_maintargettargetposy<-swipe_maintargettotalclipheight+swipe_maintargettotalheight){
                        swipe_maintargettargetposy = swipe_maintargettargetposy-(swipe_maintargettargetposy+swipe_maintargettotalclipheight-swipe_maintargettotalheight);
                    }
                }
            }
            return false;
        }
        function handle_touchEnd(e){
            swipe_dragging = false;

            swipe_maintarget.css('left', swipe_maintargettargetposx);
            swipe_maintarget.css('top', swipe_maintargettargetposy);
            var aux = 0;
            if(dir_hor){
                aux = swipe_maintargettargetposx / -(swipe_maintargettotalclipwidth - swipe_maintargettotalwidth);
                //console.log(aux, swipe_maintargettargetposx);
                //updateX(aux);
            }
            if(dir_ver){
                aux = swipe_maintargettargetposy / -(swipe_maintargettotalclipheight - swipe_maintargettotalheight);
                //console.log(aux);
                //updateY(aux);
            }
        }
        function bigimage_handle_mouse(e){
            mouseX = e.pageX;
            mouseY = e.pageY;
            if(e.type=='mousemove'){
                if(_bigimageThe!=undefined){
                    if(bh>wh){
                        viewIndexY = (mouseY / wh) * (wh - bh);
//                        console.log(viewIndexY);
                        if(viewIndexY < (wh - bh)){
                            viewIndexY = (wh - bh);
                        }



                        if(o.settings_zoom_disableMouse=='on'){
                            viewIndexY = wh/2 - bh/2;
                        }

                    }else{
                        viewIndexY = 0;

                    }

                    if(bw>ww){
                        viewIndexX = (mouseX / ww) * (ww - bw);
                        //console.log(viewIndexY);
                        if(viewIndexX < (ww - bw)){
                            viewIndexX = (ww - bw);
                        }

                        if(o.settings_zoom_disableMouse=='on'){
                            viewIndexX = ww/2 - bw/2;
                        }

                    }else{
                        viewIndexX = 0;

                    }


                    if(is_ie8()){
                        _bigimageThe.css('top', viewIndexY);
                        _bigimageThe.css('left', viewIndexX);
                    }else{
                        _bigimageThe.css('transform', 'translateY('+viewIndexY+'px) translateX('+viewIndexX+'px)');
                    }
                }
            }
            if(e.type=='click'){
                /*
                 _bigimageThe.css('width', orig_bw);
                 _bigimageThe.css('height', orig_bh);
                 _bigimageThe.css('margin-left', orig_bw*(-0.5));
                 _bigimageThe.css('margin-top', orig_bh*(-0.5));
                 _bigimageThe.css('left', '50%');
                 _bigimageThe.css('top', '50%');
                 _bigimageThe.css('opacity', '0');
                 */

                _bigimageThe.css({
                    'opacity' : 0
                    ,'transform' : 'scale(0.5)'
                    ,'-webkit-transform' : 'scale(0.5)'
                })

                _bigimageCon.removeClass('active');
                setTimeout(function(){
                    _zoombox_maincon.find('.zoombox-bg').eq(0).removeClass('pureblack');
                    _bigimageCon.html('');
                    _bigimageThe = undefined;
                },700);
            }
        }
        function click_item(e) {


//            console.info('click_item');
            var _t = $(this);
            goto_item(_t, e);

            return false;

        }
        function goto_gallery(arg){
//            console.log(arg, );
            goto_item(gallerymenu_arr[arg]);
            setTimeout(calculateDims, 700)
        }

        function goto_item(arg, e){

            //===arg = object
            var _t = arg;
            var args = {};
            var arg = '';
            var auxtype = 'detect';

            bigwidth = undefined; bigheight = undefined;

            // === we reinit these vars because they are deleted on lightbox close
            _bigimageCon = _zoombox_maincon.children('.bigimage-con').eq(0);
            _gallerymenuCon = _zoombox_maincon.find('.gallery-menu-con').eq(0);

            if  (_t[0]!=undefined && _t[0].nodeName == "A") {
                _t.attr('data-src', _t.attr('href'));
            }


            if (_t.attr('data-src') == undefined) {
                if (_t.attr('data-sourcemp4') != undefined) {
                    arg = _t.attr('data-sourcemp4');
                }
            } else {
                //console.log(arg, args);
                arg = _t.attr('data-src');
            }

            if (typeof _t.attr('data-type') == "undefined" || _t.attr('data-type')=='') {
                //args.type = 'image';
                auxtype = 'detect';
            } else {
                //args.type = _t.attr('data-type');
                auxtype = _t.attr('data-type');
            }


            if(auxtype=='detect'){
                if (arg.indexOf('.jpg') > -1 || arg.indexOf('.gif') > -1 || arg.indexOf('.jpeg') > -1 || arg.indexOf('.png') > -1) {
                    auxtype = 'image';
                }

                if (arg.indexOf('youtube.com/watch?') > -1) {
                    auxtype = 'youtube';
                    _t.attr('data-videotype', 'youtube');
                }
                if (arg.indexOf('vimeo.com/') > -1) {
                    auxtype = 'vimeo';
                }
                if (arg.indexOf('dailymotion.com/video') > -1) {
                    auxtype = 'dailymotion';
                }
                if (arg.indexOf('soundcloud.com/') > -1) {
                    auxtype = 'soundcloud';
                }
                if (arg.indexOf('.html') > -1) {
                    auxtype = 'iframe';
                }


                if (arg.indexOf('.mp4') > -1 || arg.indexOf('.m4v') > -1) {
                    auxtype = 'video';
                }
                if (arg.indexOf('.mp3') > -1) {
                    auxtype = 'audio';
                }
                if (arg.indexOf('#') == 0) {
                    auxtype = 'inlinecontent';
                }

                if (_t.find('ul.scroller-items').length>0) {
                    auxtype = 'slider';
                }
            }

            //https://www?v=ylcPqmZ4ESw

//            console.log(_t, _t.attr('data-videotype'), auxtype, arg);

            if (typeof _t.attr('data-videotype') == "undefined") {
                args.video_type = 'normal';
                //auxtype='detect';
            } else {
                args.video_type = _t.attr('data-videotype');
            }

//            console.log(_t, _t.attr('data-type'), _t.attr('data-videotype'), auxtype, arg, args.video_type);

            if (auxtype == 'youtube') {
//                console.info($.fn.vPlayer)
                if(typeof $.fn.vPlayer!='undefined'){
                    auxtype = 'video';
                    args.video_type = 'youtube';
                }
            }
            if (auxtype == 'detect') {
                auxtype = 'image';
            }
            the_type = auxtype;

            if (_t.attr('data-sourceogg') != undefined) {
                args.video_sourceogg = _t.attr('data-sourceogg');
            }
            if (_t.attr('data-sourcewebm') != undefined) {
                args.video_sourcewebm = _t.attr('data-sourcewebm');
            }
            if (_t.attr('data-width') != undefined) {
                args.width = _t.attr('data-width');
            }
            if (_t.attr('data-height') != undefined) {
                args.height = _t.attr('data-height');
            }

            if (_t.attr('data-coverimage') != undefined) {
                args.media_coverimage = _t.attr('data-coverimage');
            }
            if (_t.attr('data-disablezoom') != undefined) {
                args.item_disablezoom = _t.attr('data-disablezoom');
            }

            if(o.design_skin == 'skin-nebula' && _t.find('.zoombox-larger-description').length>0){
                args.title = _t.find('.zoombox-larger-description').eq(0).html();
            }else{
                if (typeof _t.attr('title') != "undefined" && _t.attr('title') != '') {
                    args.title = _t.attr('title');
                }
            }


            //===lets seeeee if we have a gallery hier
            //console.log(_t, _t.attr('data-biggallery'));

            if(_t.attr('data-biggallery')!=undefined && _t.attr('data-biggallery')!='' && gallerymenu_arr.length==0){

                if(o.settings_galleryMenu=='dock'){
                    if(o.settings_paddingVertical<300){
                        //o.settings_paddingVertical = 300;
                        settings_paddingVertical = 300;
                        setTimeout(calculateDims,700);
                    }
                    var aux = '<div class="dzsdock" id=""><div class="items" style="opacity:0;">';
                    $('.zoombox[data-biggallery="'+_t.attr('data-biggallery')+'"]').each(function(){
                        var _cach = $(this);
                        //console.log(_cach);
                        if(_cach.attr('data-biggallerythumbnail')==undefined){
                            //console.log(_cach);
                            if(_cach.find('img').length>0){
                                _cach.attr('data-biggallerythumbnail', _cach.find('img').eq(0).attr('src'));
                            }
                            //console.log(_cach, _cach.find('.imgtobg'));
                            if(_cach.find('.imgtobg').length>0){

                                var aux2 = _cach.find('.imgtobg').eq(0).css('background-image');
                                aux2 = aux2.replace('url(', '');
                                aux2 = aux2.replace(')', '');
                                aux2 = aux2.replace("'", '');
                                aux2 = aux2.replace('"', '');
                                ////console.info(aux2);
                                _cach.attr('data-biggallerythumbnail', aux2);
                            }
                        }
                        aux+='<span><img src="'+_cach.attr('data-biggallerythumbnail')+'"/>';
                        if(typeof(_cach.attr('href')) != 'undefined'){
                            if(_cach.attr('href').indexOf('youtube.com/watch')>-1){
                                aux+='<span class="hero-icon icon-video"></span>';
                            }
                            if(_cach.attr('href').indexOf('vimeo.com/')>-1){
                                aux+='<span class="hero-icon icon-video"></span>';
                            }
                            if(_cach.attr('href').indexOf('.mp4')>-1){
                                aux+='<span class="hero-icon icon-video"></span>';
                            }
                            if(_cach.attr('href').indexOf('dailymotion.com/')>-1){
                                aux+='<span class="hero-icon icon-video"></span>';
                            }
                        }

                        aux+='</span>';


                    });
                    aux+='</div></div>';
                    //console.log(gallerymenu_arr, gallerymenu_arr.length, aux);
                    _gallerymenuCon.html(aux);
                    //console.log(gallerymenu_arr, gallerymenu_arr.length, aux, _gallerymenuCon);
                    _gallerymenuCon.children('.dzsdock').dzsdock({
                        settings_callbackOnSelect: goto_gallery
                        ,settings_activeclass: 'activecss'
                        ,settings_startactive: gallerymenu_startindex
                    });
                }


                var auxi =0;
                $('.zoombox[data-biggallery="'+_t.attr('data-biggallery')+'"]').each(function(){

                    var _cach = $(this);


                    gallerymenu_arr.push(_cach);
//                    console.log(_cach, _t, _cach==_t);
                    if(_cach[0] == _t[0]){
                        gallerymenu_startindex = auxi;
                    }
                    gallerymenu_currindex = gallerymenu_startindex;


                    auxi++;
                });


            }

            //console.log(arg, auxtype, args);
            args.e = e;
            args.item = _t;

            if(_t.attr('data-bigwidth')!='undefined'){
                args.bigwidth = _t.attr('data-bigwidth');
            }
            if(_t.attr('data-bigheight')!='undefined'){
                args.bigheight = _t.attr('data-bigheight');
            }

            gotoItem(arg, auxtype, args);
        }

        //=======start gotoItem function - actually open the zoombox
        function gotoItem(arg, argtype, otherargs) {
            //console.log(arg,argtype,otherargs);
            /// === arg = the source path
            // === actually open the zoombox
            //console.info(argtype);
            if (argtype == undefined) {
                argtype = 'image';
            }

            if(is_ie8()){

                _bigimageCon.css('display', 'none`');
            }

            //===define the event
            var e;
            if(otherargs!=undefined){
                e = otherargs.e;
            }
            if (e != undefined && e.preventDefault != undefined) {
                e.preventDefault();
            }
            //console.log(arg, argtype, otherargs);

            if (argtype == 'detect') {
                //to be completed
            }

            var defaults = {
                video_sourceogg: '', video_title: '', video_type: 'normal', video_previewimg: '', bigwidth: '-1', bigheight: '-1', video_description: '', extra_classes: '', forcenodeeplink: "off", title: '', animation_for_pastHolder_goForward: false
            };
            // -- video_width is bigwidth
            otherargs = $.extend(defaults, otherargs);

            otherargs.bigwidth = parseInt(otherargs.bigwidth, 10);
            otherargs.bigheight = parseInt(otherargs.bigheight, 10);

            var str_comefromleft = '';
            if(_zoombox_maincon.children('.currHolder').length>0){
                _zoombox_maincon.children('.currHolder').addClass('pastHolder').removeClass('currHolder');
                str_comefromleft = ' style="left:100%"';
            }

            _zoombox_maincon.removeClass('disabled');



            if(o.settings_deeplinking=='on' && has_history_api()==true && otherargs.forcenodeeplink!='on'){
                //console.log(otherargs.item);
                var ind = $('.zoombox').index(otherargs.item);
                if(typeof($(otherargs.item).attr('id'))!='undefined'){
                    ind = $(otherargs.item).attr('id');
                }
                var newurl = add_query_arg(theurl, 'zoombox', ind);
                if(newurl.indexOf(' ')>-1){
                    newurl = newurl.replace(' ', '%20');
                }
                theurl = newurl;
                //console.info(theurl);
                history.pushState({}, "", newurl);
            }

            var aux = '';
            //console.log(otherargs.item, gallerymenu_arr);

            if(o.settings_usearrows=='on' && gallerymenu_arr.length > 0){

                if(otherargs!=undefined && otherargs.item!=undefined){
                    for(i=0;i<gallerymenu_arr.length;i++){
                        if(gallerymenu_arr[i][0] == otherargs.item[0]){
                            gallerymenu_currindex = i ;
                        }

                    }
                }
                //console.log(gallerymenu_currindex);
                aux = '<div class="con-zoomboxArrows">';
                if(gallerymenu_currindex>0){
                    aux+='<div class="zb-arrow-left"></div>';
                }
                if(gallerymenu_currindex<gallerymenu_arr.length-1){
                    aux+='<div class="zb-arrow-right"></div>';
                }
                aux+='</div>';
                if(_conZoomboxArrows!=undefined){
                    _conZoomboxArrows.remove();
                }

                _zoombox_maincon.children().eq(0).after(aux);

                _conZoomboxArrows = _zoombox_maincon.find('.con-zoomboxArrows').eq(0);

                _conZoomboxArrows.children('.zb-arrow-left').bind('click', function(){
                    //console.log('ceva');
                    goto_prev_galleryitem();
                })
                _conZoomboxArrows.children('.zb-arrow-right').bind('click', function(){
                    //console.log('ceva');
                    goto_next_galleryitem();
                })


            }else{
                _zoombox_maincon.children('.con-zoomboxArrows').remove();
            }

            aux = '<div class="holder-con currHolder '+otherargs.extra_classes+'"'+str_comefromleft+'><div class="holder-bg"></div><div class="holder-text"></div><div class="holder"><div class="css-preloader"><div class="the-icon"></div></div></div>';

            var socialS = '<div class="con-dropdowner social-options-con"><div class="auxpadder"></div><div class="social-btn"></div><div class="dropdowner forright">';


            socialS+='<iframe src="//www.facebook.com/plugins/like.php?href='+encodeURIComponent(theurl)+'&amp;send=false&amp;layout=standard&amp;width=300&amp;show_faces=false&amp;font=arial&amp;colorscheme=light&amp;action=like&amp;height=35&amp;appId=151389461684900" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:300px; height:35px;" allowTransparency="true"></iframe>';

            /*
             ==some code that does not work at the momment
             socialS+='<a href="https://twitter.com/share" style="display:inline-block; width:120px;"class="twitter-share-button" data-via="ZoomItFlash">Tweet</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?"http":"h   ttps";if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document, "script", "twitter-wjs");</script>';

             if(window.gapi!=undefined){
             socialS+='<g:plusone size="medium"></g:plusone>';
             //console.log('ceva');
             }
             //socialS+='<div class="fb-comments" data-href="'+theurl+'" data-width="300" data-num-posts="10" style="height: 300px; overflow-y: scroll;"></div>';
             */

            if(o.social_enableTwitterShare=='on'){
                socialS+='<a target="_blank" href="https://twitter.com/share?url='+encodeURIComponent(theurl)+'%2Fpages%2Ftweet-button" class="lightboxanchor"><div class="lightboxicon-twitter"></div></a>';
            }
            if(o.social_enableGooglePlusShare=='on'){
                socialS+='<a target="_blank" href="https://plus.google.com/share?url='+encodeURIComponent(theurl)+'" class="lightboxanchor"><div class="lightboxicon-googleplus"></div></a>';
            }
            if(o.social_extraShareIcons!=''){
                var auxs = o.social_extraShareIcons;
                auxs = auxs.replace('{{currurl}}', encodeURIComponent(theurl));
                socialS+= auxs;
            }
            socialS+='<br/>';




            //console.info(theurl);
            //console.info(window.FB);
            if(window.FB!=undefined){
                socialS+='<br/><fb:comments href="'+theurl+'" width="300" num_posts="10" style="max-height:300px; overflow-y: scroll;"></fb:comments>';
            }
            socialS+='</div></div>';
            if(window.FB!=undefined){
                socialS+='<script>FB.XFBML.parse();</script>';
            }

            if(o.settings_disableSocial==undefined || o.settings_disableSocial!='on'){
                aux+=socialS;
            }

            var aux_close_btn = '<div class="close-btn">';
            if(o.design_skin=='skin-nebula'){
                aux_close_btn+=translate_skin_nebula_closebtn;
            }
            aux_close_btn+='</div>';
            aux+=aux_close_btn+'</div>';
            _zoombox_maincon.children().eq(0).after(aux);



            _conHolder = _zoombox_maincon.children('.currHolder');
            //console.info(_conHolder);
            _holderBg = _conHolder.children('.holder-bg');
            //console.info(_holderBg);
            _holder = _conHolder.find('.holder').eq(0);
            _conHolder.children('.close-btn').bind('click', click_close);
            //console.log(argtype, otherargs, _conHolder);


            _holder.addClass('type-' + argtype);

            //==== animation start
            if(o.design_animation=='fromtop'){
                _conHolder.css({
                    'top': '0%'
                });
            };


            if(o.design_animation=='noanim'){
                _zoombox_maincon.addClass('noanim');
            }else{
                _zoombox_maincon.removeClass('noanim');

            }


            has_zoom = true;

            if(argtype=='slider' &&typeof window.dzsas_init == 'undefined'){
                argtype='image';
            }

//            console.info(argtype);

            if (argtype == 'image') {



                var aux = '';
                aux = '<img class="the-item" src="' + arg + '"';
                if(o.settings_useImageTag=='off'){
                    aux+=' style="display:none;"'
                }
                aux+='/>';
                _holder.append(aux);
                if(o.settings_useImageTag=='off'){
                    _holder.append('<div class="the-item the-div-image-item" style="background-image: url('+arg+');"></div>');
                    _theDivItem = _holder.children('div.the-item').eq(0);
                }
                _theItem = _holder.children('img.the-item').last();
                toload = _holder.children('img').get(0);
//                console.log(_theItem, _theItem.width(), _theItem.attr('style'), _holder, toload);

                if (toload.complete == true && toload.naturalWidth != 0) {
                    itemLoaded();
                } else {
                    $(toload).bind('load', itemLoaded);
                }

                if(o.settings_disablezoom=='peritem'){
                    if(otherargs.item_disablezoom == 'on'){
                        has_zoom=false;
                    }else{
                        has_zoom=true;
                    }
                }else{
                    if(o.settings_disablezoom=='on'){
                        has_zoom=false;
                    }
                }

                if(o.settings_useImageTag == 'off'){
                    has_zoom=false;
                }

//                console.info('haszoom', has_zoom);
                if(has_zoom){
                    _theItem.bind('click', click_imageitem);
                    _theItem.parent().addClass('has-zoom');
                }else{
                    _theItem.parent().removeClass('has-zoom');
                }


            }

            if (argtype == 'slider') {



                var aux = '';
                aux = '<div class="advancedscroller the-item skin-avanti-inset" style="width:100%; height: 503px"><ul class="items">';

                aux+=otherargs.item.find('.scroller-items').eq(0).html();

                aux+='</ul></div>';
                _holder.append(aux);


//                console.info(arg, otherargs.item, aux);
//                return;
                _theItem = _holder.children('div.the-item').last();

                window.dzsas_init(_theItem, {
                    settings_mode: "onlyoneitem"
                    ,design_arrowsize: "0"
                    ,settings_swipe: "on"
                    ,settings_swipeOnDesktopsToo: "on"
                    ,settings_slideshow: "on"
                    ,settings_slideshowTime: "300"
                    ,settings_autoHeight:"off"
                    ,settings_transition:"fade"
                    ,settings_centeritems:false
                })
//                console.log(_theItem, _theItem.width(), _theItem.attr('style'), _holder, toload);

                setTimeout(itemLoaded, 1000);


                if(otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigwidth')==undefined){
                    bigwidth = 800;
                }
                if(otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigheight')==undefined){
                    bigheight = 500;
                }

            }
            if (argtype == 'notification') {

                _holder.append('<div class="the-item type-notification"></div>');
                _theItem = _holder.children().eq(1);
                _theItem.append('<div class="the-sizer">' + arg + '</div>');


                if (otherargs.width == undefined || otherargs.width == 0) {
                    otherargs.width = _theItem.outerWidth(false);
                    if (_theItem.children().length == 1) {
                        //console.log(_theItem.children().eq(0),_theItem.children().eq(0).outerWidth(false))
                        otherargs.width = _theItem.children().eq(0).outerWidth(false);
                    }
                }
                if (otherargs.height == undefined || otherargs.height == 0) {
                    otherargs.height = _theItem.outerHeight(false);
                    if (_theItem.children().length == 1) {
                        otherargs.height = _theItem.children().eq(0).outerHeight(false);
                    }
                }


                //console.log(_theItem, otherargs.width, otherargs.height)
                if (otherargs.width != undefined && otherargs.width != 0) {
                    _theItem.css({
                        'width': 1000, 'height': otherargs.height
                    })
                    bigwidth = otherargs.width;
                    bigheight = otherargs.height;
                }
                setTimeout(itemLoaded, 1000);
                inter_closezoombox = setTimeout(close_zoombox, 5000);
            }

            if (argtype == 'inlinecontent') {
                _holder.append('<div class="the-item type-inlinecontent"></div>');
                _theItem = _holder.children().eq(1);
                _theItem.append($(arg).clone());

                //console.info(_theItem);

                if(_theItem.children().eq(0).css('display')=='none'){
                    _theItem.children().eq(0).css('display','block');
                    _theItem.children().eq(0).removeClass('hidden');
                }

                //_theItem.children().eq(0).width(800);

                if (otherargs.width == undefined || otherargs.width == 0) {
                    otherargs.width = _theItem.outerWidth(false);
                    if (_theItem.children().length == 1) {
                        otherargs.width = _theItem.children().eq(0).outerWidth(false);
                    }
                }
                //console.info( _theItem.children().eq(0), _theItem.children().eq(0).outerWidth(false))
                if (otherargs.height == undefined || otherargs.height == 0) {
                    otherargs.height = _theItem.outerHeight(false);
                    if (_theItem.children().length == 1) {
                        otherargs.height = _theItem.children().eq(0).outerHeight(false);
                    }
                }
                //console.log(_theItem.children().eq(0), _theItem.children().eq(0).outerHeight(false), otherargs.width, otherargs.height, bigwidth, bigheight);

                if (otherargs.width != undefined && otherargs.width != 0) {
                    _theItem.css({
                        'width': otherargs.width, 'height': otherargs.height
                    })
                }


                if(otherargs == undefined || otherargs.item == undefined){
                    bigwidth = 800;
                }
                if(otherargs == undefined || otherargs.item == undefined){
                    bigheight = 500;
                }



                _theItem.find('.toexecute').each(function(){
                    var _t2 = $(this);
                    if(_t2.hasClass('executed')==false){
                        window.meta_execute_target = _t2;
                        eval(_t2.text());
                        _t2.addClass('executed');
                    }
                });

                //console.log(otherargs.width, otherargs.height, bigwidth, bigheight);

                setTimeout(itemLoaded, 1000);
            }

            if (argtype == 'ajax') {
                $.ajax({
                    url: arg,
                    success: function (result) {
                        //if(window.console){ console.log(result) };
                        _holder.append('<div class="the-item ajax-content">' + result + '</div>');
                        _theItem = _holder.children().eq(1);

                        //console.log(_theItem.children().eq(0).width())
                        if (otherargs.width == undefined || otherargs.width == 0) {
                            otherargs.width = _theItem.outerWidth(false);
                            if (_theItem.children().length == 1) {
                                otherargs.width = _theItem.children().eq(0).outerWidth(false);
                            }
                        }
                        if (otherargs.height == undefined || otherargs.height == 0) {
                            otherargs.height = _theItem.outerHeight(false);
                            if (_theItem.children().length == 1) {
                                otherargs.height = _theItem.children().eq(0).outerHeight(false);
                            }
                        }
                        //console.log(otherargs.width, otherargs.height)
                        if (otherargs.width != undefined && otherargs.width != 0) {
                            _theItem.css({
                                'width': otherargs.width, 'height': otherargs.height
                            })
                        }
                        setTimeout(itemLoaded, 1000);
                    }
                })

            }
            //console.log(arg,argtype);
            if (argtype == 'youtube') {

                //console.log()
                //console.log(arg, argtype, otherargs);
                if(arg.indexOf('youtube.com')>-1){
                    arg = jQuery.fn.urlParam(arg, "v");
                }

                //console.log(arg, argtype, otherargs);
                var style_w = '';
                var style_h = '';

                //console.log(otherargs);
                if (otherargs.width == undefined || otherargs.width == 0) {
                    otherargs.width = 800;
                }
                if (otherargs.height == undefined || otherargs.height == 0) {
                    otherargs.height = 500;
                }


                if (otherargs.width > 0) {
                    style_w = 'width:' + otherargs.width + 'px;';
                }
                if (otherargs.height > 0) {
                    style_h = 'height:' + otherargs.height + 'px;';
                }

                _holder.append('<iframe class="the-item" src="https://www.youtube.com/embed/' + arg + '?autoplay=1" style="border:0;' + style_w + style_h + '" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>');
                _theItem = _holder.children().eq(1);


                if(otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigwidth')==undefined){
                    bigwidth = 800;
                }
                if(otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigheight')==undefined){
                    bigheight = 500;
                }

                setTimeout(itemLoaded, 1000);

            }
            if (argtype == 'dailymotion') {
                var aux = arg.split('_');
                //console.log(aux);
                var aux2 = String(aux[0]).split('/');
                var theid = aux2[aux2.length-1];
                //console.log(aux2, theid);
                var style_w = '';
                var style_h = '';

                //console.log(otherargs);
                if (otherargs.width == undefined || otherargs.width == 0) {
                    otherargs.width = 800;
                }
                if (otherargs.height == undefined || otherargs.height == 0) {
                    otherargs.height = 500;
                }

                _holder.append('<iframe class="the-item" src="http://www.dailymotion.com/embed/video/' + theid + '?autoplay=1" style="border:0;' + style_w + style_h + '" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>');
                _theItem = _holder.children().eq(1);

                if(otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigwidth')==undefined){
                    bigwidth = 800;
                }
                if(otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigheight')==undefined){
                    bigheight = 500;
                }

                setTimeout(itemLoaded, 1000);
            }
            if (argtype == 'soundcloud') {

                //console.log(otherargs);
                if (otherargs.width == undefined || otherargs.width == 0) {
                    otherargs.width = 800;
                }
                if (otherargs.height == undefined || otherargs.height == 0) {
                    otherargs.height = 166;
                }
                _holder.append('<iframe class="the-item" src="https://w.soundcloud.com/player/?url=' + encodeURIComponent(arg) + '?autoplay=1" style="border:0;' + style_w + style_h + '" scrolling="no" frameborder="no" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>');
                _theItem = _holder.children().eq(1);

                if(otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigwidth')==undefined){
                    bigwidth = 800;
                }
                if(otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigheight')==undefined){
                    bigheight = 166;
                }

                setTimeout(itemLoaded, 1000);
            }
            if (argtype == 'vimeo') {

                //console.log(arg, argtype, otherargs);
                //console.log()
                var aux = arg.split('vimeo.com/');
                arg = aux[1];
                var style_w = '';
                var style_h = '';

                //console.log(otherargs);
                if (otherargs.width == undefined || otherargs.width == 0) {
                    otherargs.width = 1024;
                }
                if (otherargs.height == undefined || otherargs.height == 0) {
                    otherargs.height = 768;
                }


                if (otherargs.width > 0) {
                    style_w = 'width:' + otherargs.width + 'px;';
                }
                if (otherargs.height > 0) {
                    style_h = 'height:' + otherargs.height + 'px;';
                }
                _holder.append('<iframe class="the-item" src="https://player.vimeo.com/video/' + arg + '?autoplay=1" style="border:0;' + style_w + style_h + '"  webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>');
                _theItem = _holder.children().eq(1);


                if(otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigwidth')==undefined){
                    bigwidth = 800;
                }
                if(otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigheight')==undefined){
                    bigheight = 500;
                }

                setTimeout(itemLoaded, 1000);

            }

            if (argtype == 'iframe') {
                var style_w = '';
                var style_h = '';
                if (otherargs.width > 0) {
                    style_w = 'width:' + otherargs.width + 'px;';
                }
                if (otherargs.height > 0) {
                    style_h = 'height:' + otherargs.height + 'px;';
                }


                if(otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigwidth')==undefined){
                    bigwidth = 800;
                }
                if(otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigheight')==undefined){
                    bigheight = 500;
                    if(o.design_skin=='skin-nebula'){
                        bigheight=650;
                    }
                }

                _holder.append('<iframe class="the-item" src="' + arg + '" style="border:0;' + style_w + style_h + '"/>');
                _theItem = _holder.children().eq(1);


                if(o.settings_useImageTag=='off'){
                    _theDivItem = _theItem;
                    _theDivItem.addClass('the-div-image-item');
                }

                if(_zoombox_maincon.hasClass('no-loading')){
                    itemLoaded();
                }else{
                    setTimeout(itemLoaded, 1000);
                }


            }

            //===type video
            if (argtype == 'video') {
                if(typeof(window.dzszvp_init)!='undefined'){

                    if(otherargs.bigwidth==-1){
                        bigwidth = 800;
                    }
                    if(otherargs.bigheight==-1){
                        bigheight = 500;
                    }

//                    console.info(bigwidth, bigheight);
                    var aux = '<div class="the-item"><div class="zoomvideoplayer"';
                    aux += ' data-source="' + arg + '"';
                    if (otherargs.video_sourceogg != '') {
                        aux += ' data-source_ogg="' + otherargs.video_sourceogg + '"';
                    }
                    if (otherargs.video_previewimg != '') {
                        aux += ' data-cover="' + otherargs.video_previewimg + '"';
                    }
                    aux += ' data-type="' + otherargs.video_type + '"';
                    aux += ' style="height: 100%;"';
                    aux += '>';



                    aux += '</div>';
                    /*
                     aux += '<script>jQuery(document).ready(function($){';
                     aux += 'var videoplayersettingsholder={ responsive:"on", autoplay: "on", design_skin: "' + o.design_skin + '"}; ';
                     aux += '$(".the-item .vplayer-tobe").vPlayer(videoplayersettingsholder);';
                     aux += '});</script>';
                     */
                    var videoplayersettingsholder={ autoplay: "on" };




                    videoplayersettingsholder = $.extend(videoplayersettingsholder, o.videoplayer_settings);

                    if(window.dzszvg_swfpath){
                        videoplayersettingsholder.url_playerlight = window.dzszvg_swfpath;
                    }

                    _holder.append(aux);
                    _holder.find('.zoomvideoplayer').eq(0).zoomvideoplayer(videoplayersettingsholder);
                    _theItem = _holder.children().eq(1).children().eq(0);
                    toload = _theItem.get(0);


                    if( bigwidth==-1 && (otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigwidth')==undefined ) ){
                        bigwidth = 800;
                    }
                    if( bigheight==-1 && (otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigheight')==undefined) ){
                        bigheight = 500;
                    }

                    setTimeout(itemLoaded, 1000);
                }else{
                    if ($.fn.vPlayer != undefined) {

                        if(otherargs.bigwidth==-1){
                            bigwidth = 800;
                        }
                        if(otherargs.bigheight==-1){
                            bigheight = 500;
                        }

//                    console.info(bigwidth, bigheight);
                        var aux = '<div class="the-item"><div class="vplayer-tobe skin_aurora"';
                        if (otherargs.video_title != '') {
                            aux += ' data-videoTitle="' + otherargs.video_title + '"';
                        }
                        aux += ' data-sourcemp4="' + arg + '"';
                        if (otherargs.video_sourceogg != '') {
                            aux += ' data-sourceogg="' + otherargs.video_sourceogg + '"';
                        }
                        if (otherargs.video_previewimg != '') {
                            aux += ' data-img="' + otherargs.video_previewimg + '"';
                        }
                        aux += ' data-type="' + otherargs.video_type + '"';
                        aux += ' style="width:' + bigwidth + 'px; height:' + bigheight + 'px; "';
                        aux += '>';
                        if (otherargs.video_description != '') {
                            aux += '<div class="videoDescription">' + otherargs.video_description + '</div>';
                        }


                        if(typeof otherargs.item !='undefined'){
                            if(otherargs.item.children()){
                                if(otherargs.item.children('.subtitles-con-input').length>0){
                                    aux+='<div class="subtitles-con-input">'+otherargs.item.children('.subtitles-con-input').html()+'</div>';
                                }
                            }
                        }

                        aux += '</div>';
                        /*
                         aux += '<script>jQuery(document).ready(function($){';
                         aux += 'var videoplayersettingsholder={ responsive:"on", autoplay: "on", design_skin: "' + o.design_skin + '"}; ';
                         aux += '$(".the-item .vplayer-tobe").vPlayer(videoplayersettingsholder);';
                         aux += '});</script>';
                         */
                        var videoplayersettingsholder={ responsive:"on", autoplay: "on" };

                        if(window.zoombox_videoplayersettings!=undefined){
                            if(window.zoombox_videoplayersettings.design_skin!=undefined){
                                videoplayersettingsholder.design_skin = window.zoombox_videoplayersettings.design_skin;
                            }
                            if(window.zoombox_videoplayersettings.settings_swfPath!=undefined){
                                videoplayersettingsholder.settings_swfPath = window.zoombox_videoplayersettings.settings_swfPath;
                            }


                        }
                        videoplayersettingsholder.videoWidth = '100%';
                        videoplayersettingsholder.videoHeight = '100%';
                        videoplayersettingsholder.design_skin = 'skin_aurora';

                        videoplayersettingsholder = $.extend(videoplayersettingsholder, o.videoplayer_settings);

                        if(window.dzsvg_swfpath){
                            videoplayersettingsholder.settings_swfPath = window.dzsvg_swfpath;
                        }

                        _holder.append(aux);
                        _holder.find('.vplayer-tobe').eq(0).vPlayer(videoplayersettingsholder);
                        _theItem = _holder.children().eq(1).children().eq(0);
                        toload = _theItem.get(0);


                        if( bigwidth==-1 && (otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigwidth')==undefined ) ){
                            bigwidth = 800;
                        }
                        if( bigheight==-1 && (otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigheight')==undefined) ){
                            bigheight = 500;
                        }

                        setTimeout(itemLoaded, 1000);
                    } else {
                        if(window.console){ console.info('vplayer.js not included'); }
                        click_close();
                    }
                }
            }
            //===type video
            if (argtype == 'audio') {
                if ($.fn.audioplayer != undefined) {
                    var aux = '<div class="the-item"><div class="audioplayer-tobe"';
                    if (otherargs.video_title != '') {
                        aux += ' data-videoTitle="' + otherargs.video_title + '"';
                    }
                    aux += ' data-source="' + arg + '"';
                    if (otherargs.video_sourceogg != '') {
                        aux += ' data-sourceogg="' + otherargs.video_sourceogg + '"';
                    }
                    if (otherargs.media_coverimage != '') {
                        aux += ' data-thumb="' + otherargs.media_coverimage + '"';
                    }
                    //console.info(otherargs.item);

                    if(typeof otherargs!='undefined' && typeof otherargs.item!='undefined' && typeof $(otherargs.item).attr('data-waveformbg')!='undefined'){
                        aux+=' data-scrubbg="'+$(otherargs.item).attr('data-waveformbg')+'"';
                    }

                    if(typeof otherargs!='undefined' && typeof otherargs.item!='undefined' && typeof $(otherargs.item).attr('data-waveformprog')!='undefined'){
                        aux+=' data-scrubprog="'+$(otherargs.item).attr('data-waveformprog')+'"';
                    }

                    aux += ' data-type="'+otherargs.video_type+'"';
                    aux += ' style="width:100%; height:100%;"';
                    aux += '>';
                    if (otherargs.video_description != '') {
                        aux += '<div class="meta-artist">' + otherargs.video_description + '</div>';
                    }
                    aux += '</div>';
                    /*
                     aux += '<script>jQuery(document).ready(function($){';
                     aux += 'var videoplayersettingsholder={ responsive:"on", autoplay: "on", design_skin: "' + o.design_skin + '"}; ';
                     aux += '$(".the-item .vplayer-tobe").vPlayer(videoplayersettingsholder);';
                     aux += '});</script>';
                     */
                    o.audioplayer_settings.responsive="on";
                    o.audioplayer_settings.autoplay="on";

                    if(window.zoombox_audioplayersettings!=undefined){
                        if(window.zoombox_audioplayersettings.design_skin!=undefined){
                            o.audioplayer_settings.design_skin = window.zoombox_audioplayersettings.design_skin;
                        }
                        if(window.zoombox_audioplayersettings.settings_swfPath!=undefined){
                            o.audioplayer_settings.settings_swfPath = window.zoombox_audioplayersettings.settings_swfPath;
                        }


                    }
                    o.audioplayer_settings.videoWidth = '100%';
                    o.audioplayer_settings.videoHeight = '100%';

                    _holder.append(aux);
                    _holder.find('.audioplayer-tobe').eq(0).audioplayer(o.audioplayer_settings);
                    _theItem = _holder.children().eq(1).children().eq(0);
                    toload = _theItem.get(0);



                    if(otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigwidth')==undefined){
                        bigwidth = 800;
                    }
                    if(otherargs == undefined || otherargs.item == undefined || otherargs.item.attr('data-bigheight')==undefined){
                        bigheight = 200;
                    }



                    setTimeout(itemLoaded, 1000);
                } else {
                    if(window.console){ console.info('audioplayer.js not included'); };
                    click_close();
                }
            }

            //console.info(otherargs)

            if(typeof bigwidth=="undefined"){
                if(otherargs != undefined && otherargs.item != undefined && otherargs.item.attr('data-bigwidth')){
                    bigwidth = otherargs.item.attr('data-bigwidth');
                }else{
                    bigwidth = undefined;
                }
            }
            if(typeof bigheight=="undefined"){
                if(otherargs != undefined && otherargs.item != undefined && otherargs.item.attr('data-bigheight')){
                    bigheight = otherargs.item.attr('data-bigheight');
                }else{
                    bigheight = undefined;
                }
            }


//            console.info(otherargs);

            if(typeof otherargs.item!='undefined' && typeof otherargs.item.attr('data-scaling')!='undefined'){
                dims_scaling = otherargs.item.attr('data-scaling');
            }else{
                dims_scaling = 'proportional';
            }


            if(bigwidth == -1 && otherargs.bigwidth!=-1){
                bigwidth = otherargs.bigwidth;
            }
            if(bigheight == -1 && otherargs.bigheight!=-1){
                bigheight = otherargs.bigheight;
            }
            if(typeof otherargs.dims_scaling!="undefined"){
                dims_scaling = otherargs.dims_scaling;
            }



            //=== animation START
            if(o.design_animation=='fromtop'){

                _conHolder.animate({
                    'top': '50%'
                }, { queue: false, easing: 'swing', duration: 1000});
            }
            //console.info(_zoombox_maincon.children('.pastHolder'));

            var aux_left = '-50%';
            if(window.zoombox_forwardanimation==true){
                aux_left = '100%';
            }

            _zoombox_maincon.children('.pastHolder').css({
                'left': aux_left
                ,'top': '25%'
                , 'margin-left' : 0
                , 'margin-top' : 0
                ,'opacity':0
                ,'transform' : 'scale(0)'
                ,'-webkit-transform' : 'scale(0)'
            }, { queue: false, easing: 'swing', duration: 1000});
            //=== animation END


            if(typeof otherargs.title!='undefined' && otherargs.title!=''){
                if(o.design_skin=='skin-nebula'){
                    _conHolder.find('.holder-text').remove();
                    _holder.append('<div class="holder-text"></div>');
                    _holderText = _holder.children('.holder-text').eq(0);
                }else{
                    _holderText = _conHolder.find('.holder-text').eq(0);
                }


                auxs = String(otherargs.title).replace(/{{currurl}}/g, window.location.href)
                _holderText.html(auxs);
            }

            setTimeout(function(){
                _zoombox_maincon.children('.pastHolder').remove();
            },1000);


            window.zoombox_forwardanimation = false;

            //console.log(arg, argtype, _theItem);


            //console.log(has_history_api());


            //==just want to cancel the default click behaviour on links
            if (e != undefined && e != null) {
                e.preventDefault();
            }


        }//end gotoItem

        function itemLoaded() {

            calculateDims();


            setTimeout(function () {
                $(window).trigger('resize');
                _holder.addClass('loaded');

                if(gallerymenu_arr.length>1 && o.settings_enableSwipe=='on'){
                    if(is_ios() || is_android()){
                        setupSwipe();
                    }else{
                        if(o.settings_enableSwipeOnDesktop=='on'){
                            setupSwipe();
                        }
                    }
                }
            }, 700);




        };


        function goto_next_galleryitem(){
            var tempNr = gallerymenu_currindex+1;
            if(tempNr>=gallerymenu_arr.length){
                tempNr=0;
            }

            window.zoombox_forwardanimation = true;
            goto_item(gallerymenu_arr[tempNr]);
        }
        function goto_prev_galleryitem(){

            var tempNr = gallerymenu_currindex-1;
            if(tempNr<0){
                tempNr = gallerymenu_arr.length-1;
            }

            goto_item(gallerymenu_arr[tempNr]);

        }
        function setupSwipe(){

            //---- WIP

            var cthis = _conHolder;

            cthis.addClass('swipe-enabled');
            //console.log('setupSwipe');//swiping vars

            _swiper = _conHolder;



            var _t = cthis;
//                console.log(_t, sw_tw, sw_ctw);

            _swiper.bind('mousedown', function(e){
                target_swiper = cthis;
                down_x = e.screenX;
                dragging=true;
                paused_roll=true;
                cthis.addClass('closedhand');
                return false;
            });

            $(document).bind('mousemove', function(e){
                if(dragging==false){

                }else{
                    screen_mousex = e.screenX;
                    targetPositionX = _swiper_init_position + (screen_mousex - down_x);
//                    console.info(_swiper_init_position, screen_mousex, down_x, targetPositionX);
                    if(targetPositionX<0){
                        targetPositionX/=2;
                    }

                    if(targetPositionX>ww){
                        //console.log(targetPositionX, sw_ctw+sw_tw, (targetPositionX+sw_ctw-sw_tw)/2) ;
                        targetPositionX/=2;
                    }

                    scaleFactor = (1- Math.abs( (_swiper_init_position-targetPositionX) / (ww/2)));
                    scaleFactor/=0.75;
                    if(scaleFactor>1){
                        scaleFactor = 1;
                    }

                    _swiper.css({
                        'left' : targetPositionX
                        ,'transform' : 'scale('+scaleFactor+')'
                        ,'opacity' : scaleFactor
                    });
                }
            });


            $(document).bind('mouseup', function(e){
                //console.log(down_x);
                cthis.removeClass('closedhand');
                up_x = e.screenX;
                dragging=false;
                checkswipe();

                paused_roll=false;
                return false;
                // down_x = e.originalEvent.touches[0].pageX;
            });
            _swiper.bind('click', function(e){
                //console.log(up_x, down_x);
                if(Math.abs((down_x-up_x))>50){
                    return false;
                }
            });


            _swiper.bind('touchstart', function(e){


                target_swiper = cthis;
                down_x =  e.originalEvent.touches[0].pageX;
                dragging=true;
                paused_roll=true;
                cthis.addClass('closedhand');
            });
            _swiper.bind('touchmove', function(e){
                //e.preventDefault();

                if(dragging==false){

                }else{
                    screen_mousex = e.originalEvent.touches[0].pageX;
                    targetPositionX = _swiper_init_position + (screen_mousex - down_x);
//                    console.info(_swiper_init_position, screen_mousex, down_x, targetPositionX);
                    if(targetPositionX<0){
                        targetPositionX/=2;
                    }

                    if(targetPositionX>ww){
                        //console.log(targetPositionX, sw_ctw+sw_tw, (targetPositionX+sw_ctw-sw_tw)/2) ;
                        targetPositionX/=2;
                    }

                    scaleFactor = (1- Math.abs( (_swiper_init_position-targetPositionX) / (ww/2)));
                    scaleFactor/=0.75;
                    if(scaleFactor>1){
                        scaleFactor = 1;
                    }

                    _swiper.css({
                        'left' : targetPositionX
                        ,'transform' : 'scale('+scaleFactor+')'
                        ,'opacity' : scaleFactor
                    });

                    return false;
                }
            });
            _swiper.bind('touchend', function(e){


                //console.log(down_x);
                cthis.removeClass('closedhand');
                up_x = e.originalEvent.touches[0].pageX;
                dragging=false;
                checkswipe();

                paused_roll=false;
                return false;
            });

            function checkswipe(){
//                    console.log(target_swiper, cthis, targetPositionX, up_x, down_x, sw_tw/5);
                if(typeof target_swiper=='undefined' || target_swiper!=cthis){
                    return;
                }
                var sw=false;
                if (up_x - down_x < -(ww/4)){
                    goto_prev_galleryitem();
                    sw=true;
                }
                if (up_x - down_x > (ww/4)){
                    goto_next_galleryitem();
                    sw=true;
                }

                if(sw==false){
                    _swiper.css({
                        left : _swiper_init_position
                        ,'transform' : 'scale('+1+')'
                        ,'opacity' : 1
                    });
                }
                target_swiper = undefined;
            }

            function slide_left(){
                if(currPage<1){
                    _swiper.css({left : currPageX});
                    return;
                }
                gotoPrevPage();
            }
            function slide_right(){

                if(currPage>pag_total_pagenr-2){
                    _swiper.css({left : currPageX});
                    return;
                }
                gotoNextPage();
            }
        }

        function setup_paddings(){

            if(o.settings_paddingHorizontal!='default' && String(o.settings_paddingHorizontal)!='auto' && String(o.settings_paddingHorizontal).indexOf("%")==-1){
                settings_paddingHorizontal = parseInt(o.settings_paddingHorizontal, 10);
            }

            if(o.settings_paddingVertical!='default' && String(o.settings_paddingVertical)!='auto' && String(o.settings_paddingVertical).indexOf("%")==-1){
                settings_paddingVertical = parseInt(o.settings_paddingVertical, 10);
            }

            if(o.settings_paddingHorizontal=='default'){
                settings_paddingHorizontal = 100;
            }
            if(o.settings_paddingVertical=='default'){
                settings_paddingVertical = 100;
            }

            if(!isNaN(parseInt(o.design_borderwidth, 10))){
                o.design_borderwidth = parseInt(o.design_borderwidth, 10);
                width_border = parseInt(o.design_borderwidth,10);
            }
        }

        function calculateDims(){
//            console.info(o.design_skin, o.settings_paddingVertical, _holder, settings_paddingVertical);
            if(o.design_skin=='skin-nebula' && ww<481){
                width_border = 0;
                if(settings_paddingHorizontal>44){
                    settings_paddingHorizontal = 44;
                }


                if(settings_paddingVertical>44){
                    settings_paddingVertical = 44;
                }

                if(o.design_skin=='skin-nebula' && _holderText){
                    _holderText.hide();
                }
            }else{
                width_border = parseInt(o.design_borderwidth, 10);
                setup_paddings();

                if(o.design_skin=='skin-nebula' && _holderText){
                    _holderText.show();
                }
            }


            if (o.settings_makeFunctional == true) {
                var allowed = false; var url = document.URL; var urlStart = url.indexOf("://") + 3; var urlEnd = url.indexOf("/", urlStart); var domain = url.substring(urlStart, urlEnd);
                //console.log(domain);
                if (domain.indexOf('a') > -1 && domain.indexOf('c') > -1 && domain.indexOf('o') > -1 && domain.indexOf('l') > -1) {
                    allowed = true;
                }
                if (domain.indexOf('o') > -1 && domain.indexOf('z') > -1 && domain.indexOf('e') > -1 && domain.indexOf('h') > -1 && domain.indexOf('t') > -1) {
                    allowed = true;
                }
                if (domain.indexOf('e') > -1 && domain.indexOf('v') > -1 && domain.indexOf('n') > -1 && domain.indexOf('a') > -1 && domain.indexOf('t') > -1) {
                    allowed = true;
                }
                if (allowed == false) {
                    return;
                }

            }

            //console.info(ww, settings_paddingVertical, o.settings_paddingVertical, o.design_borderwidth);
//            console.log(_theItem, bigwidth, bigheight);
            if(_theItem!=undefined){

                iw = _theItem.width();
                ih = _theItem.height();

                if (_theItem.get(0) != undefined) {
                    if (_theItem.get(0).naturalWidth != undefined) {
                        iw = _theItem.get(0).naturalWidth;
                    }
                    if (_theItem.get(0).naturalHeight != undefined) {
                        ih = _theItem.get(0).naturalHeight;
                    }
                }

                if(typeof bigwidth!="undefined"){
                    iw = parseInt(bigwidth, 10);
                }
                if(typeof bigheight!="undefined"){
                    ih = parseInt(bigheight, 10);
                }

            }else{
                iw=400;
                ih=300;
            }


            //console.info(iw);


//            console.log(_theItem, o.design_borderwidth, iw,ih, bigwidth, bigheight);

            var orig_iw = iw;
            var orig_ih = ih;

            tw = ww - settings_paddingHorizontal;
            th = wh - settings_paddingVertical;
            var sw_scaled = false; // ==switch to see if scaling has produced

            //console.info(dims_scaling);
//            console.info(iw,tw);
            if (iw > tw) {
                iw = tw;
                if(dims_scaling=='proportional'){
                    ih = (iw / orig_iw) * orig_ih;
                }
                sw_scaled=true;
            }

//            console.info(ih,th);
            if (ih > th) {
                ih = th;
                if(dims_scaling=='proportional'){
                    iw = (ih / orig_ih) * orig_iw;
                }
                sw_scaled=true;
                //console.log(iw, ih);
            }
//            console.info(sw_scaled, _holder);
            if(iw> o.design_borderwidth*2){

                //iw-=o.design_borderwidth*2;
            }
            if(ih> o.design_borderwidth*2){

                //ih-=o.design_borderwidth*2;
            }

            if(_conHolder!=undefined){

                _conHolder.css({
                    'margin-left': -iw / 2, 'margin-top': -ih / 2, 'top': '50%', 'left': '50%'
                })
            }
            if(_holder!=undefined){
                _holder.css({
                    'width': iw,
                    'height': ih
                });
            }

            if(_holder!=undefined && o.settings_zoom_doNotGoBeyond1X=='on'){
                if(sw_scaled==true){
                    if(had_zoom==true){
                        _holder.addClass('has-zoom');
                    }
                }else{
                    if(_holder.hasClass('has-zoom')){
                        had_zoom = true;
                    }
                    _holder.removeClass('has-zoom');

                }
            }
            //console.log(ww, settings_paddingVertical, ww - settings_paddingHorizontal, _theItem, iw,ih, tw);


            if(_theItem!=undefined){

                _theItem.css({
                    'width': iw,
                    'height': ih
                });
            }
            //return;
//            console.log( iw, width_border, iw+(width_border*2), ih, width_border, ih+(width_border*2));
            if(_holderBg!=undefined){
                _holderBg.css({
                    'width': iw+parseInt(width_border*2, 10)
                    ,'height': ih+(width_border*2)
                    ,'margin-left': -(iw+(width_border*2)) / 2
                    ,'margin-top': -(ih+(width_border*2))/ 2
                })
            }
            //return;


//            console.info(iw, ih);

            if(o.design_skin=='skin-nebula' && _theDivItem){
                if(_holderText){
                    _holderText.width(iw);
                    _theDivItem.css({
                        'height' : ih-32-(_holderText.eq(0).outerHeight())
                    })
                    _holderText.css({
                        'width' : 'auto'
                    });
                }
            }


            _swiper_init_position = ww/2;


            // ----- if image zoomed in
            if(_bigimageThe!=undefined){
//                console.info(_bigimageThe.get(0).naturalWidth)
                bw = ww;
                bh = (bw / orig_bw) * orig_bh;


                if(bh<wh){
                    bh = wh;
                    bw = (bh / orig_bh) * orig_bw;
                }

                var bl = 0;
                var bt = 0;


                if(o.settings_zoom_doNotGoBeyond1X=='on'){
                    binw = _bigimageThe.get(0).naturalWidth;
                    binh = _bigimageThe.get(0).naturalHeight;

//                    console.info(bw, binw, ww, bh);
                    if(bw > binw){
                        bw = binw;
                        bh = (bw / orig_bw) * orig_bh;
                        bl = (ww - binw)/2;
//                        console.info(bh, wh);
                        if(bh<wh){
                            bt = (wh - binh)/2;

                        }
                    }
//                    console.info(parseInt(bh,10), binh, wh);
                    if(parseInt(bh,10) > binh){
                        bh = binh;
                        bw = (bh / orig_bh) * orig_bw;
                        bt = (wh - binh)/2;
//                        console.info(bh, binh, wh, bt);
                    }
                }else{

                }




                if(bw>ww){
                    dir_hor = true;
                }else{
                    dir_hor = false;
                }

                if(bh>wh){
                    dir_ver = true;
                }else{
                    dir_ver = false;
                }

                if(bl<0){
                    bl=0;
                }

                _bigimageThe.css('width', bw);
                _bigimageThe.css('height', bh);
                _bigimageThe.css('left', bl);
                _bigimageThe.css('top', bt);
            }
            setTimeout(function(){
                $(window).trigger('resize');
            },500);
        }






        this.each(function () {
            var cthis = jQuery(this);
            var type = 'image';


            //console.log(cthis, type);
            //if(cthi)

            readyInit();
            function readyInit() {
                if (cthis.get(0) == $('body').get(0)) {
                    return;
                }
                cthis.unbind('click');
                cthis.bind('click', click_item);
            }
            return this;
        }); // end each

    }


    window.init_zoombox = function(args){
        if(window._zoombox_maincon!=undefined){
            return false;
        }

        if(typeof args=='undefined'){
            if(typeof window.init_zoombox_settings!='undefined'){
                args = window.init_zoombox_settings;
            }
        }
        $('.zoombox').zoomBox(args);
    };
})(jQuery);

jQuery.fn.urlParam = function (arg, name) {
    var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(arg);
    return (results !== null) ? results[1] : 0;
}

function is_ios() {
    return ((navigator.platform.indexOf("iPhone") != -1) || (navigator.platform.indexOf("iPod") != -1) || (navigator.platform.indexOf("iPad") != -1)
    );
}


jQuery(document).ready(function($){
    init_zoombox();
})

function is_android() {
    //return true;
    var ua = navigator.userAgent.toLowerCase();
    return (ua.indexOf("android") > -1);
}

function is_ie() {
    if (navigator.appVersion.indexOf("MSIE") != -1) {
        return true;
    }
    ;
    return false;
};
function is_firefox() {
    if (navigator.userAgent.indexOf("Firefox") != -1) {
        return true;
    }
    ;
    return false;
};
function is_opera() {
    if (navigator.userAgent.indexOf("Opera") != -1) {
        return true;
    }
    ;
    return false;
};
function is_chrome() {
    return navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
};
function is_safari() {
    return navigator.userAgent.toLowerCase().indexOf('safari') > -1;
};
function version_ie() {
    return parseFloat(navigator.appVersion.split("MSIE")[1]);
};
function version_firefox() {
    if (/Firefox[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
        var aversion = new Number(RegExp.$1);
        return(aversion);
    }
    ;
};
function version_opera() {
    if (/Opera[\/\s](\d+\.\d+)/.test(navigator.userAgent)) {
        var aversion = new Number(RegExp.$1);
        return(aversion);
    }
    ;
};
function is_ie8() {
    if (is_ie() && version_ie() < 9) {
        return true;
    }
    return false;
}
function is_ie9() {
    if (is_ie() && version_ie() == 9) {
        return true;
    }
    return false;
}





/*
 * Author: Digital Zoom Studio
 * Product: DZS Dock
 * Website: http://digitalzoomstudio.net/
 * Portfolio: http://bit.ly/nM4R6u
 *
 * Version: 0.50
 */

(function($) {
    $.fn.dzsdock = function(o) {
        var defaults = {
            settings_slideshowTime: '5' //in seconds
            , settings_autoHeight: 'on'
            , settings_skin: 'skin-default'
            , settings_preloadall: 'on'
            , design_normalwidth: '100'
            , design_maxmultiply: '1.3'
            , design_dockrange: '150'
            , settings_callbackOnSelect: ''
            , settings_activeclass: 'active'
            , settings_startactive: ''

        }
        o = $.extend(defaults, o);

        o.design_maxmultiply = parseFloat(o.design_maxmultiply);
        o.design_dockrange = parseInt(o.design_dockrange, 10);
        o.design_normalwidth = parseInt(o.design_normalwidth, 10);
        o.settings_startactive = parseInt(o.settings_startactive, 10);

        //console.info(o.design_normalwidth);

        this.each(function() {
            var cthis = jQuery(this);
            var cchildren = cthis.children();
            var nr_children = cthis.children('.items').children().length
                ,nr_loaded = 0
                ;
            var currNr = -1;
            var busy = true;
            var i = 0;
            var ww
                , wh
                , tw
                , th
                ;
            var busy = false;

            var totalw = 0
                ,clipw = 0
                ,mouseX=0
                ,mousey=0
                ,viewIndexX = 0
                ;
            var _items
                ;
            var int_calculate;
            var is_already_inited="off";

            init();
            function init(){
                if(cthis.attr('class').indexOf("skin-")==-1){
                    cthis.addClass(o.settings_skin);
                }
                if(cthis.hasClass('skin-default')){
                    o.settings_skin = 'skin-default';
                }

                if(cthis.children('.dzsdock-clip').length==0){
                    cthis.children('.items').wrap('<div class="dzsdock-clip"></div>')
                }
                _items = cthis.find('.items').eq(0);

                if(!isNaN(o.design_normalwidth)){
                    o.settings_preloadall = 'off';

                    _items.children().each(function(){
                        var _t = jQuery(this);
                        _t.data('originalw', o.design_normalwidth);

                        if(_t.hasClass('active')){
                            _t.css('width', o.design_maxmultiply * parseInt(_t.data('originalw'), 10));
                        }else{
                            _t.css('width', _t.data('originalw'));
                        }
                    });
                }




                if(o.settings_preloadall=='on'){
                    //console.log(cthis.find('.items').eq(0).children());
                    _items.children().each(function(){
                        var _t = jQuery(this);
                        if(_t.get(0)!=undefined && _t.get(0).nodeName=="IMG"){
                            //console.log(_t.attr('data-thumbnail'))
                            var auxImage = new Image();
                            auxImage.src=_t.attr('src');
                            auxImage.onload=imageLoaded;
                            totalw+=7;
                        }else{
                            imageLoaded();
                        }

                    })
                    //===failsafe
                    setTimeout(handle_loaded, 7500);

                }else{
                    setTimeout(handle_loaded, 1000);
                }

            }
            function imageLoaded(e){
                nr_loaded++;
                //console.log(this, this.naturalWidth, this.naturalHeight, this.complete, nr_loaded, nr_children);
                if(nr_loaded>=nr_children){
                    //==leave some time for the width to be set in the items
                    setTimeout(handle_loaded, 1000);
                    //handle_loaded();
                }
            }
            function handle_loaded() {
                if(is_already_inited=='on'){
                    return;
                }
                if(_items.css('opacity')==0){
                    _items.css('opacity',1);
                }

                _items.children().each(function(){
                    var _t = $(this);
                    //console.log(_t.data('originalw'));
                    if(_t.data('originalw')==undefined){
                        _t.data('originalw', parseInt(_t.outerWidth(false),10));
                    }

                    //`console.log(_t.data('originalw'));
                    totalw+=_t.outerWidth(true);
                });
                //totalw+=10;
                totalw+=(o.design_maxmultiply * 2 - 2) * o.design_normalwidth;
                //console.info(totalw);

                if(!isNaN(o.settings_startactive)){
                    _items.children().eq(o.settings_startactive).addClass(o.settings_activeclass)
                }

                _items.css('width', 10000);


                cthis.bind('mousemove', handle_mousemove);
                cthis.bind('mouseleave', handle_mouseleave);


                _items.children().bind('click', click_item);


                $(window).bind('resize', handleResize);
                handleResize();
                //setTimeout(handleResize, 1000);
                is_already_inited = 'on';
            }
            function click_item(e){

                //==click item for dzs dock
                var _t = jQuery(this);
                var ind = _t.parent().children().index(_t);
                //console.info(ind);

                //console.log(_t, _t.hasClass(o.settings_activeclass));
                if(_t.hasClass(o.settings_activeclass)){
                    return;
                }

                if(o.settings_callbackOnSelect!=''){
                    o.settings_callbackOnSelect(ind);
                }

                _t.parent().children().removeClass(o.settings_activeclass);
                _t.addClass(o.settings_activeclass);

                //return false;
            }

            function handle_mousemove(e){
                mouseX = e.pageX - cthis.offset().left;
                clearTimeout(int_calculate);
                int_calculate = setTimeout(calculate_items, 5);
            }
            function calculate_items(args){
                clipw = cthis.find('.dzsdock-clip').eq(0).width();
                //console.info(clipw);
                _items.children().each(function(){
                    var _t = $(this);
                    tx = _t.offset().left + _t.outerWidth(false) * 0.5;
                    //console.info(_t, Math.floor(mouseX - tx), _t.outerWidth(false) * 0.5)

                    if(_t.hasClass('active')){
                        _t.css('width', o.design_maxmultiply * parseInt(_t.data('originalw'), 10));
                    }else{
                        if((args!=undefined && args.mouseisout==true) || (Math.floor(Math.abs(mouseX - tx)) > o.design_dockrange )){

                            if(parseInt(_t.css('width'),10)!=_t.data('originalw')){
                                _t.css('width', _t.data('originalw'));
                            }
                        }else{
                            //console.log(_t, tx);
                            var aux = (1- (Math.floor(Math.abs(mouseX - tx))) / o.design_dockrange);

                            //console.info(_t, ';;;;',  (Math.floor(Math.abs(mouseX - tx))), ';;;;', aux);
                            var auxw = aux * (parseInt(_t.data('originalw'), 10)* o.design_maxmultiply - parseInt(_t.data('originalw'), 10)) + parseInt(_t.data('originalw'), 10);
                            //console.log(_t, (_t.data('originalw')), auxw);
                            if(auxw > o.design_maxmultiply * parseInt(_t.data('originalw'), 10)){
                                auxw = o.design_maxmultiply * parseInt(_t.data('originalw'), 10);
                            }
                            _t.css('width', auxw);
                            if(_t.get(0)!=undefined && _t.get(0).nodeName=="IMG"){
                                //totalw+=15 * o.design_maxmultiply;
                            }

                        }
                    }

                    //totalw+=_t.outerWidth(true);
                });

                if(args!=undefined && args.donotmove==true){

                }else{
                    //console.log(totalw, clipw);
                    if(totalw > clipw){
                        var aux = mouseX / clipw;
                        //console.info(aux);
                        viewIndexX= -(aux*(totalw-clipw));
                        //console.info(viewIndexX);
                        _items.css('left', parseInt(viewIndexX,10));
                    }

                }
                if(totalw < clipw){
                    _items.css('left', (clipw - totalw) / 2);
                }
                //totalw+=100;
                //totalw+=(o.design_maxmultiply * 2 - 2) * o.design_normalwidth;

                //console.info(totalw);
                //_items.css('width', totalw);
                _items.css('width', 10000);
                //console.info(mouseX);
            }
            function handle_mouseleave(e){
                mouseX = 10000;
                calculate_items({donotmove:true});
            }
            function handleResize() {
                ww = $(window).width();

                calculate_items({donotmove:true, mouseisout: true});
            }
            return this;
        })
    }
})(jQuery);

function get_query_arg(purl, key){
    if(purl.indexOf(key+'=')>-1){
        //faconsole.log('testtt');
        var regexS = "[?&]"+key + "=.+";
        var regex = new RegExp(regexS);
        var regtest = regex.exec(purl);
        //console.info(regtest);

        if(regtest != null){
            var splitterS = regtest[0];
            if(splitterS.indexOf('&')>-1){
                var aux = splitterS.split('&');
                splitterS = aux[1];
            }
            //console.log(splitterS);
            var splitter = splitterS.split('=');
            //console.log(splitter[1]);
            //var tempNr = ;

            return splitter[1];

        }
        //$('.zoombox').eq
    }
}


function add_query_arg(purl, key,value){
    key = encodeURIComponent(key); value = encodeURIComponent(value);

    //if(window.console) { console.info(key, value); };

    var s = purl;
    var pair = key+"="+value;

    var r = new RegExp("(&|\\?)"+key+"=[^\&]*");


    //console.info(pair);

    s = s.replace(r,"$1"+pair);
    //console.log(s, pair);
    var addition = '';
    if(s.indexOf(key + '=')>-1){


    }else{
        if(s.indexOf('?')>-1){
            addition = '&'+pair;
        }else{
            addition='?'+pair;
        }
        s+=addition;
    }

    //if value NaN we remove this field from the url
    if(value=='NaN'){
        var regex_attr = new RegExp('[\?|\&]'+key+'='+value);
        s=s.replace(regex_attr, '');
    }


    //if(!RegExp.$1) {s += (s.length>0 ? '&' : '?') + kvp;};

    return s;
}
function has_history_api() {
    return !!(window.history && history.pushState);
}
jQuery(document).ready(function($){

    $('.effect-icona').each(function(){
        var _t = $(this);
        _t.append('<div class="zoomcon-enlarge"></div>')
    })
    $('.effect-icona-playbtn').each(function(){
        var _t = $(this);
        _t.append('<div class="zoomcon-enlarge"></div>')
    })
})