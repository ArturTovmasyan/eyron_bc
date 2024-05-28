$(document).ready(function() {
    var showChar = 60;
    var ellipsestext = "...";
    var lesstext = "close";

    $('.show-more').each(function() {
        var content = $(this).html();

        if(content.length > showChar) {
            var c = content.substr(0, showChar);
            var h = content.substr(showChar-1, content.length - showChar);
            var html = c + '&nbsp;<span class="more-content"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="morelink">' + ellipsestext + '</a></span>';
            $(this).html(html);
        }
    });

    $(".morelink").click(function(){
        if($(this).hasClass("less")) {
            $(this).removeClass("less");
            $(this).html('...');
        } else {
            $(this).addClass("less");
            $(this).html(lesstext);
        }
        $(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
    });
});