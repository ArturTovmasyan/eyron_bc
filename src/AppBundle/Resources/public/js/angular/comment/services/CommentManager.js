'use strict';

angular.module('comments')
  .service('CommentManager', ['$resource', 'envPrefix',
    function($resource, envPrefix){
      return $resource( envPrefix + 'api/v1.0/:path/:param1/:param2', {}, {
        add: {method:'PUT', transformResponse: function (object) {
          return angular.fromJson(object);
        }},
        comments: {method:'GET', isArray: true, transformResponse: function (object) {
          return angular.fromJson(object);
        }}
      });
  }]);
