



jQuery(document).ready(function($){



    var includemediasupport = '';
    var tinymce_external_plugins = {};

    if(includemediasupport==',filemanager'){
        tinymce_external_plugins = { "filemanager" : dzsprg_builder_settings.theurl + 'filemanager/plugin.min.js'};
    }


    var tinymce_settings = {
        script_url : window.dzsprg_builder_settings.theurl + 'tinymce/tinymce/tinymce.min.js'
        ,mode : "textareas"
        ,theme : "modern"
        ,plugins : "image,code,media,hr,fullscreen,advlist,fontawesome"+includemediasupport
        ,relative_urls : false
        ,remove_script_host : false
        ,image_advtab: true
        ,convert_urls : true
        ,forced_root_block : ""
        ,extended_valid_elements: 'span[class],a[*]'
        ,content_css: '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css'
        ,theme_advanced_toolbar_location : "top"
        //,theme_advanced_toolbar_align : "left"
        //,theme_advanced_statusbar_location : "bottom"
        ,toolbar: "bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | removeformat code  | fontawesome | responsivefilemanager"

        ,external_filemanager_path:dzsprg_builder_settings.theurl + 'builder/tinymce/filemanager/'
        ,filemanager_title:"Responsive Filemanager"
        ,external_plugins: tinymce_external_plugins
        ,setup : function(ed) {
        }
    };



    var _areaCanvas = $('.dzsprg-builder-con--canvas-area').eq(0);
    var _areaLayers = $('.dzsprg-builder-con--layers-area').eq(0);


    dzsscr_init('.scroller-con');

    _areaLayers.sortable({
        placeholder: "ui-state-highlight"
        ,handle: '.sortable-handle-con'
        ,start: function(arg1,arg2){
            var _t = arg2.item;
//            console.info(_t.find('.with-tinymce.activated'))
            _t.find('.with-tinymce.activated').removeClass('activated').tinymce().remove();

        }
        ,stop: function(){
            arrange_layers();
            update_fields();
        }
    });
    setTimeout(function(){
        $('.saveconfirmer').removeClass('active');
    }, 1000)

//    _areaLayers.disableSelection();



//    console.info($('input[name="bars[mainsettings][maxperc]"]').eq(0).val());
    $( ".jqueryui-slider.for-perc" ).slider({
        range: "max",
        min: 1,
        max: 100,
        value: $('input[name="bars[mainsettings][maxperc]"]').eq(0).val(),
        slide: function( e, ui ) {
//            $( "#amount" ).val( ui.value );
            var _t = $(this);
            var _par = _t.parent();
            _par.find('input').eq(0).val(ui.value);
            _par.find('input').eq(0).trigger('change');
        }
    });

    $('.builder-add-rect').bind('click', click_add_btn);
    $('.builder-add-text').bind('click', click_add_btn);
    $('.builder-add-circ').bind('click', click_add_btn);
    $('input[name="builder_skin_name"]').bind('change', change_skin_name);
    $('.btn-show-select').bind('click', click_show_select);
    $('.btn-save-skin').bind('click', click_save_skin);
    $('.btn-preview').bind('click' ,click_preview);

    $(document).delegate('.builder-field','change', change_builder_field);
    $(document).delegate('.builder-layer--head','click', click_layer_head);
    $(document).delegate('.builder-layer--btn-delete','click', click_layer_delete);
    $(document).delegate('.dzsprg-builder-con--canvas-area > *', 'click', click_layer_in_canvas);


    tinymce_settings.setup  = function(ed) {
        ed.on('change', function(e) {
            update_fields();
        });
    }


    var _c = _areaLayers.children().find('textarea.with-tinymce');
    _c.each(function(){
        var _t = $(this);
        if(_t.hasClass('activated')==false){
            _t.addClass('activated').tinymce(tinymce_settings);
        }
    })


    $('#tabs-mainsettings').dzstabsandaccordions({
        'design_tabsposition' : 'top'
        ,design_transition: 'fade'
        ,design_tabswidth: 'fullwidth'
        ,toggle_breakpoint : '4000'
        ,toggle_type: 'toggle'
        ,settings_appendWholeContent: true
    });
    init_dzstabsandaccordions();
    reskin_select();
    arrange_layers();

    setTimeout(update_fields, 1000);


    function click_preview(){
        if(_areaCanvas.hasClass('inited')){
            _areaCanvas.css('height', $('input[name="bars[mainsettings][height]"]').eq(0).val());
            _areaCanvas.get(0).api_restart_and_reinit();
        }else{
            _areaCanvas.css('opacity',1);
            _areaCanvas.css('height', $('input[name="bars[mainsettings][height]"]').eq(0).val());
            _areaCanvas.progressbars({
                'initon' : 'init'
                ,'maxperc' : $('input[name="bars[mainsettings][maxperc]"]').eq(0).val()
                ,'maxnr' : $('input[name="bars[mainsettings][maxnr]"]').eq(0).val()
                ,'animation_time' : $('input[name="bars[mainsettings][animation_time]"]').eq(0).val()
            });
        }
    }

    function click_layer_in_canvas(){
        var _t = $(this);
        var _par = _t.parent();
        var ind = _par.children().index(_t);

        _areaLayers.children().eq(ind).addClass('active');
        $('html').animate({
            scrollTop: _areaLayers.children().eq(ind).offset().top
        }, 300);
    }
    function click_layer_delete(){
        var r = confirm("Confirm Item Delete");
        var _t = $(this);
        var _par = _t.parent().parent();
        if (r == true) {
            _par.remove();
        }
        arrange_layers();
        update_fields();
    }
    function click_save_skin(){

        var mainarray = $('form[name="builder-form"]').serialize();
//        console.info(mainarray);
        var data = {
            action: 'dzsprg_saveskin'
            ,postdata: mainarray
            ,currSkin: dzsprg_builder_settings.currSkin
        };

        $('.saveconfirmer').html('Saving...');
        $('.saveconfirmer').addClass('active');
//        return false;
        $.post(ajaxurl, data, function(response) {
            if(window.console != undefined){
                console.log('Got this from the server: ' + response);
            }
            if(response.indexOf('success - ')>-1){
                $('.saveconfirmer').html('Options saved.');
            }else{

                $('.saveconfirmer').html('Seems there was a error saving....');
            }
            setTimeout(function(){

                $('.saveconfirmer').removeClass('active');
            },2000);
        });
        return false;
    }
    function change_skin_name(){
        var _t = $(this);
        $('form#create-custom-skin').attr('action', ''+window.dzsprg_builder_settings.adminpageurl+'&skin='+_t.val());
    }
    function click_add_btn(e){
//        console.info(_areaCanvas);
        var _t = $(this);

        if(_t.hasClass('builder-add-rect')){
            _areaLayers.append(''+dzsprg_builder_settings.struct_layer+'');
        }
        if(_t.hasClass('builder-add-text')){
            _areaLayers.append(''+dzsprg_builder_settings.struct_layer_text+'');
            _areaLayers.children().last().find('.the-title').html('text');
            _areaLayers.children().last().find('input[name*="[text]"]').html('text');
            _areaLayers.children().last().removeClass('type-rect').addClass('type-text');

            var _c = _areaLayers.children().last().find('textarea.with-tinymce');
            if(_c.hasClass('activated')==false){

                _areaLayers.children().last().find('textarea.with-tinymce').addClass('activated').tinymce(tinymce_settings);
            }
        }
        if(_t.hasClass('builder-add-circ')){
            _areaLayers.append(''+dzsprg_builder_settings.struct_layer_circ+'');

        }

        init_dzstabsandaccordions();
        arrange_layers();
        reskin_select();
        update_fields();

    }

    function click_show_select(){
        var _t = $(this);
        var _par = _t.parent();

        _par.toggleClass('active');
        $('.super-select--options').eq(0)[0].reinit();
    }

    function change_builder_field(){
        var _t = $(this);
        var _t_name = _t.attr('name');

        if(_t_name.indexOf('[top]')>-1){
            if(_t.val()!='auto'){
                _t.parent().parent().parent().find('input[name*="[bottom]"]').eq(0).val('auto');
            }
        }
        if(_t_name.indexOf('[bottom]')>-1){
            if(_t.val()!='auto'){
                _t.parent().parent().parent().find('input[name*="[top]"]').eq(0).val('auto');
            }
        }
        if(_t_name.indexOf('[left]')>-1){
            if(_t.val()!='auto'){
                _t.parent().parent().parent().find('input[name*="[right]"]').eq(0).val('auto');
            }
        }
        if(_t_name.indexOf('[right]')>-1){
            if(_t.val()!='auto'){
                _t.parent().parent().parent().find('input[name*="[left]"]').eq(0).val('auto');
            }
        }
        update_fields();
    }

    function update_fields(){
        _areaLayers.children('.builder-layer').each(function(){
            var _t = $(this);
            var ind = _t.parent().children().index(_t);
            var _ite = _areaCanvas.children().eq(ind);

//            console.info(_t);
            _t.find('.builder-field').each(function(){
                var _t2 = $(this);
                var props_obj = {};
                if(typeof _ite.attr('data-animprops')!='undefined' && _ite.attr('data-animprops')!=''){
                    props_obj = JSON.parse(_ite.attr('data-animprops'));
                }

                if(typeof _t2.attr('name')!='undefined' && _t2.attr('name')!=''){
                    var arr_labels = ['width','height','top', 'left','bottom','right','margin_top', 'margin_left','margin_bottom','margin_right','border_radius','border','opacity','font_size','color','extra_classes'];

                    for(i=0;i<arr_labels.length;i++){

                        if(String(_t2.attr('name').indexOf('['+arr_labels[i]+']')) > -1){

                            var aux = arr_labels[i];
                            var val = _t2.val();
                            if( (aux=='top' || aux=='right' || aux=='bottom' || aux=='left' || aux=='width' || aux=='height') && val.indexOf('%')==-1 && val.indexOf('px')==-1 && val!='auto' ){
                                val+='px';
                            }
                            if((aux=='margin_top' || aux=='margin_right' || aux=='margin_bottom' || aux=='margin_left' || aux=='border_radius' || aux=='font_size')){

                                aux = aux.replace('_', '-');
                                if(val.indexOf('%')==-1 && val.indexOf('px')==-1 && val!='auto' ){

                                    val+='px';
                                }
                            }

                            if(aux=='text-align'){
//                                console.info(val);
                            }
                            if(aux=='extra_classes'){
                                _ite.addClass(val);
                            }

                            if(val.indexOf('{{')==-1){
                                _ite.css(aux, val);

                            }else{
                                _ite.css(aux, '');
//                                console.info(_ite.attr('animprops'));


                                props_obj[aux] = _t2.val();


                            }

                        }
                    }
                    var arr_labels_circ = ['circle_outside_fill','circle_inside_fill','circle_outer_width','circle_line_width'];
                    if(_t.hasClass('type-circ')){

                        for(j=0;j<arr_labels_circ.length;j++){

                            if(String(_t2.attr('name').indexOf('['+arr_labels_circ[j]+']')) > -1){

                                var aux = arr_labels_circ[j];
                                var val = _t2.val();

                                props_obj[aux] = _t2.val();
                            }
                        }
                    }
//                    if(String(_t2.attr('name').indexOf('[text_align]')) > -1){
//                        _areaCanvas.children().eq(ind).css({
//                            'text-align' : _t2.val()
//                        })
//
//                    }
                    if(String(_t2.attr('name').indexOf('[position_type]')) > -1){

                        _areaCanvas.children().eq(ind).css({
                            'position' : _t2.val()
                        })

                    }
                    if(_t2.hasClass('with-tinymce') && _t2.hasClass('activated')){
//                        console.info(_t2, _t2.tinymce());
                        if(_t2.tinymce()==null){
                            setTimeout(function(argind, arg_t){
//                                console.info(argind, arg_t);
                                if(arg_t.tinymce()!=null) {
                                    var aux = arg_t.tinymce().getContent({format: 'raw'});
                                    _areaCanvas.children().eq(argind).html(aux);
                                    //-- " conflicts with JSON stringify so lets remove dat
                                    aux = aux.replace(/"/g, "");
//                                    props_obj['text'] = aux;
                                }
                            }, 300, ind, _t2)
                        }else{

                            var aux = _t2.tinymce().getContent({format: 'raw'});
//                            console.info(aux);
                            _areaCanvas.children().eq(ind).html(aux);
                            //-- " conflicts with JSON stringify so lets remove dat
                            aux = aux.replace(/"/g, "");
//                            props_obj['text'] = aux;
                        }
//

                    }
                    if(String(_t2.attr('name').indexOf('[background_color]')) > -1){

                        _areaCanvas.children().eq(ind).css({
                            'background-color' : _t2.val()
                        })

                    }

                    _ite.attr('data-animprops', JSON.stringify(props_obj));
                }
            })
        })


        var aux='';
        aux+='&lt;div class="dzs-progress-bar auto-init skin-'+dzsprg_builder_settings.currSkin+'" style="';

        var arr_labels = ['width','height','margin_top', 'margin_left','margin_bottom','margin_right'];

//        console.info($('input[name*="bars[mainsettings]"]'));
        $('input[name*="bars[mainsettings]"]').each(function(){
            var _t2 = $(this);

//            console.info(_t2, aux);
            for(i=0;i<arr_labels.length;i++) {

                if (String(_t2.attr('name').indexOf('[' + arr_labels[i] + ']')) > -1) {

                    var aux_lab = arr_labels[i];
                    var val = _t2.val();
                    if ((aux_lab == 'margin_top' || aux_lab == 'margin_right' || aux_lab == 'margin_bottom' || aux_lab == 'margin_left')) {

                        aux_lab = aux_lab.replace('_', '-');
                        if (val.indexOf('%') == -1 && val != 'auto') {

                            val += 'px';
                        }
                    }
                    aux+=''+aux_lab+':'+val+';';
                }
            }
        })
        aux+='"';

        aux+=' data-animprops=\'{';

        var auxlab = 'animation_time';
        var firstset = false;
        if($('input[name*="bars[mainsettings]['+auxlab+']"]').length>0 && $('input[name*="bars[mainsettings]['+auxlab+']"]').val()!=''){
            aux+='"'+auxlab+'":"'+$('input[name*="bars[mainsettings]['+auxlab+']"]').val()+'"';
            firstset=true;
        }

        auxlab = 'maxperc';
        if($('input[name*="bars[mainsettings]['+auxlab+']"]').length>0 && $('input[name*="bars[mainsettings]['+auxlab+']"]').val()!=''){
            if(firstset){ aux+=','; };
            aux+='"'+auxlab+'":"'+$('input[name*="bars[mainsettings]['+auxlab+']"]').val()+'"';
            firstset=true;
        }
        auxlab = 'maxnr';
        if($('input[name*="bars[mainsettings]['+auxlab+']"]').length>0 && $('input[name*="bars[mainsettings]['+auxlab+']"]').val()!=''){
            if(firstset){ aux+=','; };
            aux+='"'+auxlab+'":"'+$('input[name*="bars[mainsettings]['+auxlab+']"]').val()+'"';
            firstset=true;
        }
        auxlab = 'initon';
        if($('select[name*="bars[mainsettings]['+auxlab+']"]').length>0 && $('select[name*="bars[mainsettings]['+auxlab+']"]').val()!=''){
            if(firstset){ aux+=','; };
            aux+='"'+auxlab+'":"'+$('select[name*="bars[mainsettings]['+auxlab+']"]').val()+'"';
            firstset=true;
        }


        aux+='}\''

        aux+='&gt;';

        var aux_items = htmlEncode(_areaCanvas.html());
//        console.info(aux_items);
        aux_items = aux_items.replace(/data-animprops="(.*?)" /g ,"data-animprops='$1' ");
        aux_items = aux_items.replace(/&amp;quot;/g,'"');
//        console.info(aux_items);
        aux+=aux_items ;
        aux+='&lt;/div&gt;';

        $('.dzsprg-output-div').html(aux);
        click_preview();
    }

    function init_dzstabsandaccordions(){

        _areaLayers.children().find('.dzs-tabs').dzstabsandaccordions({
            'design_tabsposition' : 'top'
            ,design_transition: 'fade'
            ,design_tabswidth: 'fullwidth'
            ,toggle_breakpoint : '4000'
            ,toggle_type: 'toggle'
            ,settings_appendWholeContent: true
        });

        reskin_select();
        window.farbtastic_reinit();


        $( ".jqueryui-slider.for-fontsize" ).slider({
            range: "max",
            min: 11,
            max: 72,
            value: 24,
            slide: function( e, ui ) {
//            $( "#amount" ).val( ui.value );
                var _t = $(this);
                var _par = _t.parent();
                _par.find('input').eq(0).val(ui.value);
                _par.find('input').eq(0).trigger('change');
            }
        });
    }

    function arrange_layers(){
        _areaCanvas.children().remove();

//        console.info(_areaLayers.children());
        for(i=0;i<_areaLayers.children().length;i++){
            var _layer = _areaLayers.children().eq(i);

            _layer.find('textarea.with-tinymce').each(function(){
                var _t = $(this);
                if(_t.hasClass('activated')==false){
                    _t.addClass('activated').tinymce(tinymce_settings);
                }
            })


            _layer.find('*[name^="bars["]').each(function(){
                var _t = $(this);
//                console.info(_t);

                var aux = _t.attr('name');

                aux = aux.replace(/bars\[(0|[1-9][0-9]*)\]/g, "bars["+i+"]");

                _t.attr('name',aux);
            })

//            console.info(_layer);

            var aux_type = _layer.find('.the-title').eq(0).html();

            if(aux_type=='circ'){
                _areaCanvas.append('<canvas class="progress-bars-item progress-bars-item--'+aux_type+'" data-type="'+aux_type+'"></canvas>')
            }else{
                _areaCanvas.append('<div class="progress-bars-item progress-bars-item--'+aux_type+'" data-type="'+aux_type+'"></div>')
            }

        }


//        console.info($('.dzsprg-output-con'), String(htmlEncode(_areaCanvas.html())));
    }

    function htmlEncode(value){
        //create a in-memory div, set it's inner text(which jQuery automatically encodes)
        //then grab the encoded contents back out.  The div never exists on the page.
        return $('<div/>').text(value).html();
    }

    function htmlDecode(value){
        return $('<div/>').html(value).text();
    }


    function click_layer_head(e){
        var _t = $(this);

        if(_t.find('.sortable-handle-con').has($(e.target)).length > 0){
            return ;
        }
        _t.parent().toggleClass('active');
    }

    function reskin_select(){
        $(document).undelegate(".select-wrapper select", "change");
        $(document).delegate(".select-wrapper select", "change",  change_select);

//        console.info($('select'));
        $('select.styleme').each(function(){

            var _cache = $(this);
//            console.log(_cache);

            if(_cache.parent().hasClass('select_wrapper') || _cache.parent().hasClass('select-wrapper')){
                return;
            }
            var sel = (_cache.find(':selected'));
//            console.info(sel, _cache.val());
            _cache.wrap('<div class="select-wrapper"></div>')
            _cache.parent().prepend('<span>' + sel.text() + '</span>');
            _cache.trigger('change');
        })


        function change_select(){
            var selval = ($(this).find(':selected').text());
            $(this).parent().children('span').text(selval);
        }

    }
});



/* @projectDescription jQuery Serialize Anything - Serialize anything (and not just forms!)
 * @author Bramus! (Bram Van Damme)
 * @version 1.0
 * @website: http://www.bram.us/
 * @license : BSD
 */

(function($) {

    $.fn.serializeAnything = function() {

        var toReturn    = [];
        var els         = $(this).find(':input').get();

        $.each(els, function() {
            if (this.name && !this.disabled && (this.checked || /select|textarea/i.test(this.nodeName) || /text|hidden|password/i.test(this.type))) {
                var val = $(this).val();
                toReturn.push( encodeURIComponent(this.name) + "=" + encodeURIComponent( val ) );
            }
        });

        return toReturn.join("&").replace(/%20/g, "+");

    }

})(jQuery);