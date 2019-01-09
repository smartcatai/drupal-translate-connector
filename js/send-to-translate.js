
(function($){
    $(function(){
        $('li.smartcat a').click(function(e){
            e.preventDefault();
            console.log($(this).attr('href'));
            $.post($(this).attr('href'),
                function(resp){
                    console.log(resp)
                }
            )
        });
    })
})(jQuery)