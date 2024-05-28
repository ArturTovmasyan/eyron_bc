'use strict';

angular.module('profile', ['Interpolation',
  'Google',
  'Components',
  'angular-cache',
  'ngSanitize',
  'infinite-scroll',
  'mgcrea.ngStrap.popover',
  'goalManage',
  'ngAnimate',
  'goalComponents',
  'manage',
  'notification',
  'activity',
  'PathPrefix'
  ])
  .constant('complaintType', {
    notificationsOf: 1,
    privateGoal: 2,
    googleSearch: 3,
    signOut: 4,
    deleteAccount: 5
  })
  .constant('deleteType', {
    elswhere: 1,
    moreNotification: 2,
    notExpected: 3,
    doneEverything: 4,
    other: 5
  })
  .directive('lsFollowManage',['envPrefix',
    '$http',
    function(envPrefix, $http){
      return {
        restrict: 'EA',
        scope: {
          lsUserId: '@',
          lsIsFollow: '='
        },
        link: function(scope, el){

          var path = envPrefix + 'api/v1.0/users/{user}/toggles/followings';
          path = path.replace('{user}', scope.lsUserId);

          el.bind('click', function(){
            $http.post(path).success(function(){
              scope.lsIsFollow = !scope.lsIsFollow;
            })
          });
        }
      }
    }
  ]);