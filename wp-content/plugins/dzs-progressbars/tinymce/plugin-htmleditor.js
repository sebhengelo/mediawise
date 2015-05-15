window.htmleditor_sel = '';
window.mceeditor_sel = '';
top.dzsprg_startinit = '';
jQuery(document).ready(function($){


    if(typeof window.dzsprg_builder_settings == 'undefined'){
        return;
    }

    $('#wp-content-media-buttons').append('<a class="shortcode_opener" id="dzsprg_shortcode" style="cursor:pointer; display: inline-block; vertical-align: middle; background-size:cover; background-repeat: no-repeat; background-position: center center; width:25px; height:25px; background-image: url('+dzsprg_builder_settings.theurl+'tinymce/img/shortcodes-small-retina.png);"></a>');

    $('#dzsprg_shortcode').bind('click', function(){

        var parsel = '';
        var sel = '';
        top.dzsprg_startinit = '';
        window.htmleditor_sel = '';
        window.mceeditor_sel = '';

        if(window.tinyMCE == undefined || window.tinyMCE.activeEditor==null || jQuery('#content_parent').css('display')=='none'){
            var textarea = document.getElementById("content");
            var start = textarea.selectionStart;
            var end = textarea.selectionEnd;
            sel = textarea.value.substring(start, end);

            //console.log(sel);

            //textarea.value = 'ceva';
            if(sel!=''){
                //parsel+='&sel=' + encodeURIComponent(sel);
                window.htmleditor_sel = sel;
            }else{
                window.htmleditor_sel = '';
            }
        }else{
            //console.log(window.tinyMCE.activeEditor);
            var ed = window.tinyMCE.activeEditor;
            sel=ed.selection.getContent();

            if(sel!=''){
                //parsel+='&sel=' + encodeURIComponent(sel);
                window.mceeditor_sel = sel;
            }else{
                window.mceeditor_sel = '';
            }
            //console.log(aux);
        }

        top.dzsprg_startinit = sel;


        $.fn.zoomBox.open(dzsprg_builder_settings.adminurl + '?generatorpage=dzsprg_generatepage' + parsel, 'iframe', { bigwidth: 1100, bigheight: 500, dims_scaling:'fill' });


        return false;
    });


    $('.dzsprg-widget-generate').bind('click', function(){
        var _t = $(this);
        var _tar = _t.parent().find('*[name*=zoomtabs_content]').eq(0)

        var parsel = '';
        var sel = '';
        top.dzsprg_startinit = '';
        var textarea = _tar[0];
        sel = textarea.value

        window.htmleditor_sel = sel;


        top.dzsprg_startinit = sel;

        window.dzsprg_widget_target = textarea;
        $.fn.zoomBox.open(dzsprg_builder_settings.adminurl + '?dzsprg_show_generator=on' + parsel, 'iframe', { bigwidth: 1100, bigheight: 500, dims_scaling:'fill' });




        return false;
    })



})