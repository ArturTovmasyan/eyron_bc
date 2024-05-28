//'use strict';
//
//$(document).ready(function(){
//    var options = {
//        $AutoPlay: true,
//        $FillMode: 5,
//        $ArrowKeyNavigation: false,
//        $BulletNavigatorOptions: {
//            $Class: $JssorBulletNavigator$,
//            $ChanceToShow: 2
//        },
//        $ArrowNavigatorOptions: {                       //[Optional] Options to specify and enable arrow navigator or not
//            $Class: $JssorArrowNavigator$,              //[Requried] Class to create arrow navigator instance
//            $ChanceToShow: 2,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
//            $AutoCenter: 2,                                 //[Optional] Auto center arrows in parent container, 0 No, 1 Horizontal, 2 Vertical, 3 Both, default value is 0
//            $Steps: 1                                       //[Optional] Steps to go for each navigation request, default value is 1
//        }
//    };
//
//    //if($('#main-slider').length){
//    //    var optMainSlider = {};
//    //
//    //    $.extend(optMainSlider, options);
//    //
//    //    var main = new $JssorSlider$('main-slider', optMainSlider);
//    //
//    //    var ScaleSliderMain = function() {
//    //        var parentWidth = $("#main-slider").parent().width();
//    //
//    //        if (parentWidth) {
//    //            main.$ScaleWidth(parentWidth);
//    //        }
//    //        else {
//    //            window.setTimeout(ScaleSliderMain, 30);  // Need to take attention on this
//    //        }
//    //    };
//    //
//    //    //Scale slider after document ready
//    //    ScaleSliderMain();
//    //
//    //    //Scale slider while window load/resize/orientationchange.
//    //    $(window).bind("load", ScaleSliderMain);
//    //    $(window).bind("resize", ScaleSliderMain);
//    //    $(window).bind("orientationchange", ScaleSliderMain);
//    //}
//
//    //if($('#main-slider-video').length){
//    //    var optMainVideo = {};
//    //
//    //    $.extend(optMainVideo, options);
//    //
//    //    optMainVideo.$SlideSpacing = 10;
//    //    optMainVideo.$DisplayPieces = 1;
//    //    //optMainVideo.$SlideWidth = 350;
//    //
//    //    var mainVideo = new $JssorSlider$('main-slider-video', optMainVideo);
//    //
//    //    var ScaleSliderMainVideo = function() {
//    //        var parentWidth = $("#main-slider-video").parent().width();
//    //
//    //        if (parentWidth) {
//    //            mainVideo.$ScaleWidth(parentWidth);
//    //        }
//    //        else {
//    //            window.setTimeout(ScaleSliderMainVideo, 30);  // Need to take attention on this
//    //        }
//    //    };
//    //
//    //    //Scale slider after document ready
//    //    ScaleSliderMainVideo();
//    //
//    //    //Scale slider while window load/resize/orientationchange.
//    //    $(window).bind("load", ScaleSliderMainVideo);
//    //    $(window).bind("resize", ScaleSliderMainVideo);
//    //    $(window).bind("orientationchange", ScaleSliderMainVideo);
//    //}
//
//
//    if($(".story-slider").length){
//        var optStoryImage = {};
//        var optStoryVideo = {};
//
//        $.extend(optStoryImage, options);
//        $.extend(optStoryVideo, options);
//
//        optStoryImage.$SlideSpacing = 10;
//        optStoryImage.$DisplayPieces = 2;
//        optStoryImage.$SlideWidth = 350;
//
//        //optStoryVideo.$SlideSpacing = 10;
//        //optStoryVideo.$SlideWidth = 350;
//
//        var storySliderEls = $(".story-slider");
//        var storySliders = [];
//
//        for(var i = 0; i < storySliderEls.length; i++){
//            if($(storySliderEls[i]).hasClass('video-slider')) {
//                storySliders.push(new $JssorSlider$($(storySliderEls[i]).attr('id'), optStoryVideo));
//                storySliders[i].isVideoSlider = true;
//            }
//            else {
//                storySliders.push(new $JssorSlider$($(storySliderEls[i]).attr('id'), optStoryImage));
//            }
//        }
//
//        var ScaleSliderStory = function() {
//            for(var i = 0; i < storySliders.length; i++){
//                var refSize = storySliders[i].$Elmt.parentNode.clientWidth;
//                var vl;
//                if(storySliders[i].isVideoSlider) {
//                    vl = refSize - 50;
//                }
//                else {
//                    vl = Math.min(refSize - 50, optStoryImage.$DisplayPieces * optStoryImage.$SlideWidth +
//                        optStoryImage.$DisplayPieces * optStoryImage.$SlideSpacing);
//                }
//
//                storySliders[i].$ScaleWidth(vl);
//            }
//        };
//
//        //Scale slider after document ready
//        ScaleSliderStory();
//
//        //Scale slider while window load/resize/orientationchange.
//        $(window).bind("load", ScaleSliderStory);
//        $(window).bind("resize", ScaleSliderStory);
//        $(window).bind("orientationchange", ScaleSliderStory);
//        storySliderEls.on("showMoreStories", ScaleSliderStory);
//    }
//});