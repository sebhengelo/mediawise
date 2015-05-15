// DZS Progress Bars
// @version 1.22
// @this is not free software
// @copyright - http://digitalzoomstudio.net

(function($){

    $.fn.progressbars = function(o) {

        var defaults = {
            animation_time: "2000"
            ,maxperc: "100"
            ,maxnr: "100"
            ,initon: "scroll"
            ,start_delay: "0"
            ,settings_makeFunctional: false
        };

        if(typeof o =='undefined'){
            if(typeof $(this).attr('data-options')!='undefined'  && $(this).attr('data-options')!=''){
                var aux = $(this).attr('data-options');
                aux = 'var aux_opts = ' + aux;
                eval(aux);
                o = aux_opts;
            }
        }


        o = $.extend(defaults, o);
        this.each(function(){

            var cthis = $(this);
            var cthis_animprops = {};

            var started = false
                ,finished = false
                ;

            var initon='init';

            var startTime = 0
                ,totalAnimTime = 2000
                ,maxperc = 100
                ,maxnr = 700
                ;

            var circle_vars = ['circle_outer_width', 'circle_outside_fill', 'circle_line_width', 'circle_inside_fill'];

            //--temp vars
            var _cnv = null;
            var _cnv_ctx = null;

            var _w = $(window);

//            console.info(cthis);


            if(cthis.attr('data-animprops')!='' && typeof cthis.attr('data-animprops')!='undefined'){
                cthis_animprops = JSON.parse(cthis.attr('data-animprops'));
            }
            cthis_animprops = $.extend(o, cthis_animprops);

//            console.info(cthis_animprops);
            if(typeof cthis_animprops.maxperc!='undefined'){
                if(String(cthis_animprops.maxperc).indexOf('{{arg')>-1){
                    var regex_getdef = new RegExp("{{arg(.)-default(.*?)}}");
                    var auxa = regex_getdef.exec(String(cthis_animprops.maxperc));
                    if(auxa){
                        if(typeof auxa[2]!='undefined'){
                            maxperc = Number(auxa[2]);
                        }
                    }
//                    console.info(regex_getdef, auxa, cthis_animprops,auxa[3])
                }else{
                    maxperc=cthis_animprops.maxperc;
                }

                if(isNaN(maxperc)){
                    maxperc=100;
                }


            }
            if(typeof cthis_animprops.maxnr!='undefined'){
                if(String(cthis_animprops.maxnr).indexOf('{{arg')>-1){
                    var regex_getdef = new RegExp("{{arg(.)-default(.*?)}}");
                    var auxa = regex_getdef.exec(String(cthis_animprops.maxnr));
                    if(auxa){
                        if(typeof auxa[2]!='undefined'){
                            maxnr = Number(auxa[2]);
                        }
                    }
//                    console.info(regex_getdef, auxa, cthis_animprops,auxa[2])
                }else{
                    maxnr=cthis_animprops.maxnr;
                }

                if(isNaN(maxnr)){
                    maxnr=100;
                }


            }
//            console.info(maxnr);
            if(typeof cthis_animprops.initon!='undefined'){
                initon=cthis_animprops.initon;
            }
            if(typeof cthis_animprops.animation_time!='undefined'){
                totalAnimTime=cthis_animprops.animation_time;
            }
            cthis_animprops.start_delay = Number(cthis_animprops.start_delay);

            var debug_var = true;
//            console.info(cthis, cthis.attr('data-animprops'));
//            console.log(JSON.parse(cthis.attr('animprops')));

            if(cthis.hasClass('inited')){
                return;
            }

            cthis.addClass('inited');

            handle_resize();
            reinit();


            cthis.get(0).api_restart_and_reinit = function(){
                started = false;
                finished = false;
                reinit();
            };




            function start_it(){
//                console.info('startit');
                if(started){
                    return;
                }


                startTime = new Date().getTime();
                handle_frame();
                setInterval(debug_func, 250);


                _w.bind('resize', handle_resize);
                handle_resize();

                cthis.addClass('started');
                started=true;
            }
            function handle_scroll(e){
                var windowh = _w.scrollTop() + _w.height();

                if(windowh>=cthis.offset().top+30){
                    _w.unbind('scroll', handle_scroll);

                    if(cthis_animprops.start_delay==0){
                        start_it();
                    }else{
                        setTimeout(start_it, cthis_animprops.start_delay);
                    }

                }
            }

            function reinit(){

                cthis.children().each(function(){
                    var _t = $(this);
                    var drawcircle = false;
                    var textisvar = false;
                    var animprops = {};
//                console.info(_t, _t.attr('data-animprops'));


                    if(_t.hasClass('progress-bars-item--text')){
                        if(String(_t.html()).indexOf('{{') > -1 && String(_t.html()).indexOf('}}')>-1){
                            textisvar = true;
                        }
                    }

                    //console.log(_t,textisvar);
                    if(_t.attr('data-animprops')!='' && typeof _t.attr('data-animprops')!='undefined'){
//                    console.info(cthis, _t);
                        animprops = JSON.parse(_t.attr('data-animprops'));
//                    _t.data('animprops', JSON.parse(_t.attr('data-animprops')));
                        _t.data('animprops', animprops);
//                    jQuery.data(_t, 'animprops', 'ceva');

//                    console.info(_t.data('animprops'))


                        var circle_args = {};

                        if(_t.data('circle_args')!=undefined && _t.data('circle_args')!=''){
                            circle_args = _t.data('circle_args');
                        }


                        var animprops = _t.data('animprops');
                        for(animprop in _t.data('animprops')) {
//                            console.info(animprop, animprops[animprop]);

                            var val = animprops[animprop];
                            for(i=0;i<circle_args.length;i++){

                                circle_args[animprop] = val;
                                _t.data('circle_args', circle_args);
                            }
                        }
                        if(textisvar){
                            animprops['text'] = _t.html();
                            _t.data('animprops', animprops);
                        }
                    }else{
                        if(textisvar){
                            animprops['text'] = _t.html();
                            _t.data('animprops', animprops);
                        }
                    }
                    if(drawcircle){

                        draw_circle(_t, _cnv, _cnv_ctx);
                    }
                })

//                console.info(initon, started);

                if(initon=='init'){

                    start_it();
                }

                if(initon=='scroll') {
                    _w.bind('scroll', handle_scroll);
                    handle_scroll();
                }
            }

            // time, begin, change ( finish - b ), duration
            var easeIn = function(t, b, c, d) {

                return -c *(t/=d)*(t-2) + b;

            };

            // percent, elapsed time, start value, end value, total duration
            function easeOutQuad (t, b, c, d) {
                return -c *(t/=d)*(t-2) + b;
            };

            function animationFunction(t, b, c, d) {
                return easeOutQuad (t, b, c, d)
            };

            function debug_func(){
                debug_var = true;
            }

            function handle_resize(){
                calculate_dims();
            }

            function calculate_dims(){


                if(o.settings_makeFunctional==true){
                    var allowed=false;

                    var url = document.URL;
                    var urlStart = url.indexOf("://")+3;
                    var urlEnd = url.indexOf("/", urlStart);
                    var domain = url.substring(urlStart, urlEnd);
                    //console.log(domain);
                    if(domain.indexOf('a')>-1 && domain.indexOf('c')>-1 && domain.indexOf('o')>-1 && domain.indexOf('l')>-1){
                        allowed=true;
                    }
                    if(domain.indexOf('o')>-1 && domain.indexOf('z')>-1 && domain.indexOf('e')>-1 && domain.indexOf('h')>-1 && domain.indexOf('t')>-1){
                        allowed=true;
                    }
                    if(domain.indexOf('e')>-1 && domain.indexOf('v')>-1 && domain.indexOf('n')>-1 && domain.indexOf('a')>-1 && domain.indexOf('t')>-1){
                        allowed=true;
                    }
                    if(allowed==false){
                        return false;
                    }

                }


//                console.info('calculate_dims', cthis.children());
                cthis.children().each(function() {
                    var _t = $(this);
                    _cnv = null;
                    _cnv_ctx = null;

                    if(_t.get(0).nodeName=="CANVAS"){
                        _cnv = _t.get(0);
                        _cnv_ctx = _cnv.getContext("2d");

                    }

                    if (_t.data('animprops') != 'undefined') {
                        var animprops = _t.data('animprops');
                        for (animprop in _t.data('animprops')) {
                            var val = animprops[animprop];
                            if(val=='{{width}}'){
                                val = val.replace('{{width}}', _t.outerWidth());
                                _t.css(animprop, val);
                            }
                            if(val=='{{center}}'){
                                if(animprop=='top'){
                                    val = val.replace('{{center}}', Number(cthis.outerHeight()/2 - _t.outerHeight()/2) + 'px' );
//                                    console.info(_t, animprop, val);
                                    _t.css(animprop, val);
                                }
                                if(animprop=='left'){
                                    val = val.replace('{{center}}', Number(cthis.outerWidth()/2 - _t.outerWidth()/2) + 'px' );
//                                    console.info(_t, animprop, val);
                                    _t.css(animprop, val);
                                }

                            }

                            if(_cnv){
                                _cnv.width = _t.outerWidth();
                                _cnv.height = _t.outerHeight();
                            }

                            if(_t.hasClass('progress-bars-item--circ') && _cnv){

                                draw_circle(_t, _cnv, _cnv_ctx);
                            }
                        }
                    }
                });
            }

            function handle_frame(){

//                console.info('ceva');


                var currTime = new Date().getTime();
                var animTime = currTime - startTime;
                var animPerc = animTime / totalAnimTime;



                cthis.children().each(function() {
                    var _t = $(this);
                    var drawcircle = false;
                    _cnv = null;
                    _cnv_ctx = null;

                    if(_t.get(0).nodeName=="CANVAS"){
                        _cnv = _t.get(0);
                        _cnv_ctx = _cnv.getContext("2d");
                    }
//                    console.info(_cnv, _cnv_ctx);


                    var animpropsaux = _t.data('animprops');
                    if(_t.html()!='' && String(_t.html()).indexOf('{{') > -1 && typeof animpropsaux=='object' && typeof animpropsaux.text=='undefined'){
                        animpropsaux.text = (_t.html());

                    }

                    if(_t.data('animprops') && _t.data('animprops')!='undefined'){
                        var animprops = _t.data('animprops');

                        //console.info(_t, animprops);
                        for(animprop in _t.data('animprops')){
//                            console.info(animprop, animprops[animprop]);

                            var val = animprops[animprop];
                            var perc = 0;
                            var perc100 = 0;


                            // -- this belongs in handle resize function
                            if(val=='{{width}}'){
                                continue;
                            }

                            if(currTime - startTime > totalAnimTime){
                                finished=true;
                                perc = maxperc;
                                perc100 = 100;
                            }else{
                                perc = animationFunction(animPerc*totalAnimTime, 0, maxperc, totalAnimTime);
                                perc100 = animationFunction(animPerc*totalAnimTime, 0, 100, totalAnimTime);
                            }
                            if(perc>maxperc){
                                perc = maxperc;
                            }
                            if(perc100>100){
                                perc100 = 100;
                            }

                            if(animprop=='text'){
                                perc = parseInt(perc,10);
                            }
//                            console.info('ceva');
                            val = val.replace('{{perc}}', perc+'%');
                            val = val.replace('{{perc100}}', perc100+'%');
                            val = val.replace('{{perc-decimal}}', perc/100);
                            val = val.replace('{{perc100-decimal}}', perc100/100);
                            val = val.replace('{{percmaxnr}}', parseInt(perc /100 * maxnr, 10));

                            if(animprop=='width' || animprop=='height' || animprop=='opacity' || animprop=='left' || animprop=='right'){

                                _t.css(animprop, val);
                            }

                            //console.info(_t, animprop);

                            if(animprop=='text'){
                                _t.html(val);


                                var regex_getdef = new RegExp("{{arg(.)-default(.*?)}}");
                                var auxa = regex_getdef.exec(String(val));
                                if(auxa){
                                    if(auxa[2]){
                                        _t.html( String(_t.html()).replace(regex_getdef, auxa[2]))
                                    }
                                }
                            }

                            for(i=0;i<circle_vars.length;i++){
                                if(animprop==circle_vars[i]){
//                                console.info(_t.data('circle_args'));
//                                console.info(perc/100, Number(val));
                                    var circle_args = {};

                                    if(_t.data('circle_args')!=undefined && _t.data('circle_args')!=''){
                                        circle_args = _t.data('circle_args');
                                    }
                                    circle_args[animprop] = val;
                                    drawcircle = true;
                                    _t.data('circle_args', circle_args);
//                                console.info(perc, animprops);
                                }
                            }





                        }
                    }



                    if(drawcircle){

                        draw_circle(_t, _cnv, _cnv_ctx);
                    }
                });



                if(debug_var){
//                    console.info(currTime - startTime);
//                    console.info(easeOutQuad(animPerc, animPerc*totalAnimTime, 0, 100, totalAnimTime));
                    debug_var = false;
                }

                if(finished==false){
                    requestAnimFrame(handle_frame);
                }

            }

            function draw_circle(_t, _cnv, _cnv_ctx){

                var circle_args = {
                    'circle_inside_fill' : 'transparent'
                    ,'circle_outside_fill' : '#fb5757'
                    ,'circle_line_width' : '50'
                    ,'circle_outer_width' : '0.5'// -- a value from 0 to 1 where 1 is the full circle
                };



//                console.info(_t.data('circle_args'));
                if(_t.data('circle_args')!=undefined && _t.data('circle_args')!=''){
                    circle_args = $.extend(circle_args, _t.data('circle_args'));
                }

//                console.info(circle_args.circle_outside_fill)
                if(String(circle_args.circle_outer_width).indexOf('{{') > -1 || _cnv==null){
                    return;
                }


                var centerX = _cnv.width / 2;
                var centerY = _cnv.height / 2;
                var radius = _cnv.width/2-circle_args.circle_line_width;
                var startAngle = -0.5*Math.PI;

                var circle_outer_width = Number(circle_args.circle_outer_width)*2 - 0.5;

                var endAngle = circle_outer_width * Math.PI;



//                                console.info(circle_args);

                _cnv_ctx.clearRect(0,0,_cnv.width, _cnv.height);


                _cnv_ctx.beginPath();
//                _cnv_ctx.lineCap = 'round';
//                console.info(_cnv.width, circle_args.circle_line_width, centerX,centerY, radius, startAngle, endAngle)
                if(radius>0){
                    _cnv_ctx.arc(centerX, centerY, radius, startAngle,  endAngle, false);
                }

                if(circle_args.circle_inside_fill!='' && circle_args.circle_inside_fill!='transparent') {
                    _cnv_ctx.fillStyle = circle_args.circle_inside_fill;
                    _cnv_ctx.fill();
                }
                if(circle_args.circle_outside_fill!='' && circle_args.circle_outside_fill!='transparent'){

                    _cnv_ctx.lineWidth = circle_args.circle_line_width;
                    _cnv_ctx.strokeStyle = circle_args.circle_outside_fill;
                    _cnv_ctx.stroke();
                }
            }


            return this;
        });
    }
    window.dzsprb_init = function(selector, settings){
        if(typeof(settings)!="undefined" && typeof(settings.init_each)!="undefined" && settings.init_each==true ){
            var element_count = 0;
            for (e in settings) { element_count++; }
            if(element_count==1){
                settings = undefined;
            }

            $(selector).each(function(){
                var _t = $(this);
                _t.progressbars(settings)
            });
        }else{
//            console.info(selector, $(selector));
            $(selector).progressbars(settings);
        }
    }
})(jQuery);


window.requestAnimFrame = (function(){
    return  window.requestAnimationFrame       ||
    window.webkitRequestAnimationFrame ||
    window.mozRequestAnimationFrame    ||
    function( callback ){
        window.setTimeout(callback, 1000 / 60);
    };
})();

if(typeof jQuery!='undefined'){
    jQuery(document).ready(function($){
        dzsprb_init('.dzs-progress-bar.auto-init',{init_each : true});
    })
}else{
    alert('progressbar.js - this plugin is based on jQuery -> please include jQuery')
}