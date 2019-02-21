
(function($){
    $(function(){
        $('li.smartcat a').click(function(e){
            if($(this).attr('href').indexOf('https://smartcat.ai/projects') === 0){
                window.open($(this).attr('href'));
                return false;
            }
        });
    })
})(jQuery)