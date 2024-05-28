'use strict';

angular.module('activity', ['Interpolation',
  'Google',
  'user',
  'Authenticator',
  'mgcrea.ngStrap.popover',
  'ngAnimate',
  'ngSanitize',
  'infinite-scroll',
  'Confirm',
  'Components',
  'LocalStorageModule',
  'angular-cache',
  'ngResource',
  'angulartics',
  'angulartics.google.analytics',
  'PathPrefix',
  'comments',
  'slickCarousel'
])
  .config(function (localStorageServiceProvider ) {
    localStorageServiceProvider
      .setPrefix('goal')
      .setNotify(false, false);
  })
  .config(['$httpProvider', function($httpProvider){
    $httpProvider.interceptors.push([function() {
      return {
        'response': function(response) {
          // same as above
          if(response.config.url.indexOf('/api/v2.0/activities/') !== -1){
            angular.forEach(response.data, function(v){
              if(v.goals.length > 2) {
                v.reserveGoals = [v.goals[0], v.goals[1]];
              } else {
                v.reserveGoals = v.goals
              }
            });
          }

          return response;
        }
      };
    }]);
  }])
  .config(function(CacheFactoryProvider){
    angular.extend(CacheFactoryProvider.defaults, {
      maxAge: 24 * 60 * 60 * 1000, // Items added to this cache expire after 15 minutes.
      cacheFlushInterval: 60 * 60 * 1000, // This cache will clear itself every hour.
      deleteOnExpire: 'aggressive', // Items will be deleted from this cache right when they expire.
      storageMode: 'localStorage' // This cache will use `localStorage`.
    });
  });
