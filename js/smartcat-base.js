(function($){
    $('li.smartcat-doc a').click(function(e){
        window.open($(this).attr('href'));
        return false;
    });

    $('li.smartcat-disabled a').click(function(e){
        return false;
    });
})(jQuery)