(function($){
    $('li.smartcat-doc a').click(function(e){
        window.open($(this).attr('href'));
        return false;
    });
})(jQuery)