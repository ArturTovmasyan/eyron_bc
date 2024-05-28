'use strict';

angular.module('Facebook',['PathPrefix'])
  .run(['facebookId', function(facebookId){
    window.fbAsyncInit = function() {
      FB.init({
        appId      : facebookId,
        xfbml      : true,
        version    : 'v2.10'
      });
    };
    (function(d, s, id){
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) {return;}
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_US/sdk.js";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
  }])
  .directive('fbShare',[function(){
    return {
      restrict: 'A',
      scope: {
        name: '=fbName',
        link: '=fbLink',
        caption: '@fbCaption',
        picture: '=fbPicture',
        description: '=fbDescription',
        message: '@fbMessage'
      },
      compile: function(){
        return function(scope,el){

          el.click(function(){
            FB.ui({
              method: 'share',
              name: scope.name,
              href: scope.link,
              picture: scope.picture,
              caption: scope.caption,
              description: scope.description,
              message: scope.message
            })
          })
        }
      }
    }
  }]);
