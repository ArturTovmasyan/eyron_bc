'use strict';

angular.module('notification')
  .service('NotificationManager', ['$resource', 'envPrefix', '$timeout', '$rootScope', 'UserContext',
    function($resource, envPrefix){
      return $resource( envPrefix + 'api/v1.0/:path/:id/:where/:what/:param', {}, {
        readAll: {method:'GET', params:{ path:'notification', id: 'all', where: 'read'},  transformResponse: function (object) {
          return object;
        }},
        readSingle: {method:'GET', params:{ path:'notifications', where: 'read'},  transformResponse: function (object) {
          return object;
        }},
        getAll: {method:'GET', params:{ path:'notifications'}, transformResponse: function (object) {
          return angular.fromJson(object);
        }},
        delete: {method:'DELETE', params:{ path:'notifications'}, transformResponse: function (object) {
          return object;
        }}
      });
    }]);