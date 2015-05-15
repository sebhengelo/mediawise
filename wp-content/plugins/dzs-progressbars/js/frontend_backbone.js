window.InlineShortcodeView_dzsprogressbar = window.InlineShortcodeView.extend({
    render: function() {
        window.InlineShortcodeView_dzsprogressbar.__super__.render.call(this);
//        console.info(jQuery('.dzs-progress-bar'));

        var _tel = this.$el;

        _tel.find('.dzs-progress-bar').each(function(){
            var _t = jQuery(this);
//            console.info(_t);

            if(_t.hasClass('inited')){
                if(typeof(_t.get(0))!='undefined' && typeof(_t.get(0).api_restart_and_reinit)!='undefined'){
                    _t.get(0).api_restart_and_reinit();
                }

            }else{
                _t.progressbars();
            }



        })
        setTimeout(function(){
            jQuery(window).trigger('resize');
        },50);
        return this;
    }
});