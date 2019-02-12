
(function($){
    $(function(){
        $('li.smartcat a').click(function(e){
            e.preventDefault();
            if($(this).attr('href').indexOf('https://smartcat.ai/projects') === 0){
                window.open($(this).attr('href'));
                return false;
            }
            $.post($(this).attr('href'),
                function(resp){
                    console.log(resp)
                    window.location.reload(true);
                }
            )
        });
    })
})(jQuery)