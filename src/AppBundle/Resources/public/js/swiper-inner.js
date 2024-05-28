'use strict';

$(document).ready(function(){

    // Main
    var main_swiper = new Swiper('#main-slider', {
        pagination: '.swiper-pagination',
        paginationClickable: true,
        autoHeight: true,
        loop: true,
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        spaceBetween: 30,
        autoplay: 3000
    });

    // homepage story
    setTimeout(function() {
        var main_swiper = new Swiper('#story-slider-homepage-container', {
            observer: true,
            autoHeight: true,
            // loop: true,
            nextButton: '.swiper-button-next-home-story',
            prevButton: '.swiper-button-prev-home-story',
            spaceBetween: 30
            // autoplay: 3000
        });
    }, 1000);


    var main_swiper_video = new Swiper('#main-slider-video', {
        pagination: '.swiper-pagination',
        paginationClickable: true,
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        spaceBetween: 30,
        autoplay: 3000
    });

    // Story Slider
    var story_image_slider = new Swiper('.story-slider.image-slider', {
        pagination: '.swiper-pagination',
        paginationClickable: true,
        slidesPerView: 2,
        freeMode: true,
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        spaceBetween: 30,
        autoplay: 3000
    });

    var story_video_slider = new Swiper('.story-slider.video-slider', {
        pagination: '.swiper-pagination',
        paginationClickable: true,
        nextButton: '.swiper-button-next',
        prevButton: '.swiper-button-prev',
        spaceBetween: 30,
        autoplay: 3000
    });

    $(".story-slider").on('showMoreStories', function(){
        for(var i = 0; i < story_image_slider.length; i++){
            story_image_slider[i].update(true);
        }

        for(var i = 0; i < story_video_slider.length; i++){
            story_video_slider[i].update(true);
        }
    });

});