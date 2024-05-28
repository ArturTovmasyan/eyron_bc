'use strict';

angular.module('main',['mgcrea.ngStrap.modal',
    'mgcrea.ngStrap.popover',
    'ng.deviceDetector',
    'ngAnimate',
    'manage',
    'goalComponents',
    'user',
    'Confirm',
    'videosharing-embed',
    'Components',
    'Interpolation',
    'Google',
    'PathPrefix',
    'Authenticator',
    'notification',
    'activity',
    'ngScrollbars',
    'ngSanitize'])
    .controller('MainController',['$scope',
        '$modal',
        '$timeout',
        'deviceDetector',
        '$filter',
        'AuthenticatorLoginService',
        'envPrefix',
        '$http',
        function($scope, $modal, $timeout, deviceDetector, $filter, AuthenticatorLoginService, envPrefix, $http){

        //if (deviceDetector.raw.os.android || deviceDetector.raw.os.ios) {
        //    // open modal
        //    $timeout(function(){
        //        $scope.$broadcast('openLsModal', 'mobileDetectModal');
        //    }, 500);
        //}
        $scope.capitalizeFirstLetter = function (string) {
            return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
        };

        $scope.dateToLocal = function(date){
            return $scope.capitalizeFirstLetter($filter('date')(new Date(date), "MMMM d 'at' hh:mm a"));
        };

        $scope.openSignInPopover = function(id, slug){
            if(!id){
                AuthenticatorLoginService.openLoginPopup();
            } else {
                window.location.href = "https://my.bucketlist127.com/login/like/" + id + '/' + slug;
                // var url = envPrefix + 'api/v1.0/success-story/add-vote/{storyId}';
                // url = url.replace('{storyId}', id);
                // $http.get(url).success(function() {})
                //     .error(function (res) {
                //         AuthenticatorLoginService.openLoginPopup();
                //     });
            }
        };

        $scope.triggerMap = function(mapSelector){
            if(!mapSelector){
                return;
            }

            $timeout(function(){
                var mapScope = angular.element(mapSelector).isolateScope();
                google.maps.event.trigger(mapScope.map, 'resize');

            }, 150);
        };

        $scope.onMarkerClick = function(goal){
            $scope.mapPopup = goal;
            $modal({scope: $scope, templateUrl: '/bundles/app/htmls/mapPopup.html',show: true});
        };

        var storyCount = $( ".swiper-wrapper" ).data('story-count');

        if(storyCount){
            for(var i = 0;i<storyCount;i++){
                $( '.swipebox-'+i ).swipebox({
                    useSVG : false
                });
                $( '.swipebox-video-'+i ).swipebox({
                    useSVG : false
                });
            }
        }

        $scope.scroller_config = {
            autoHideScrollbar: false,
            theme: 'minimal-dark',
            axis: 'y',
            advanced:{
                updateOnContentResize: true
            },
            callbacks:{
                onCreate: function(){
                    $(this).css({
                        'height': 'initial',
                        'max-height': '100px'
                    });
                }
            },
            setHeight: 200,
            scrollInertia: 0
        };

    }])
    .controller('goalFooter', ['$scope', '$timeout', '$http', 'loginPopoverService', function($scope, $timeout, $http, loginPopoverService){
        $scope.popoverByMobile = function(){
            $timeout(function(){
                angular.element('.navbar-toggle').click();
            }, 500);
        };
    }])
    .controller('mobileModal',['$scope', 'deviceDetector', function($scope, deviceDetector) {
        $scope.deviceDetector = deviceDetector;

        $scope.isRuLanguage = (window.navigator.language.toLowerCase() == 'ru');
    }]);