'use strict';

angular.module('goalManage')
  .service('UserGoalDataManager', ['$resource', 'envPrefix', '$analytics', '$timeout', '$rootScope', 'refreshCacheService', 'UserContext', 'refreshingDate',
    function($resource, envPrefix, $analytics, $timeout, $rootScope, refreshCacheService, UserContext, refreshingDate){
    return $resource( envPrefix + 'api/:version/:path/:id/:where/:what/:param', {version: 'v1.0'}, {
      get: {method:'GET', params:{ path:'usergoals'}},
      creates: {method:'PUT', params:{ path:'usergoals'}, transformResponse: function (object) {
          $analytics.eventTrack('Goal create', {  category: 'Goal', label: 'Goal create from Web' });
          return angular.fromJson(object);
      }},
      add: {method:'PUT', params:{ path:'usergoals'}, transformResponse: function (object) {
          refreshCacheService.refreshCache(UserContext.id, refreshingDate.goalId);
          $timeout(function(){
            $rootScope.$broadcast('addGoal', refreshingDate.goalId);
          },600);
          $analytics.eventTrack('Goal add', {  category: 'Goal', label: 'Goal add from Web' });
          return angular.fromJson(object);
      }},
      manage: {method:'PUT', params:{ path:'usergoals'}, transformResponse: function (object) {
          $analytics.eventTrack('Goal manage', {  category: 'Goal', label: 'Goal manage from Web' });
          return angular.fromJson(object);
      }},
      profile: {method:'GET', params:{ path:'usergoals',id: 'bucketlists', version: 'v2.0'}, transformResponse: function (object) {
        return angular.fromJson(object);
      }}, 
      overall: {method:'GET', params:{ path:'user',id: 'overall'}},
      common: {method:'GET', params:{ path:'goals',where: 'common'}, transformResponse: function (object) {
        return angular.fromJson(object);
      }},
      owned: {method:'GET', params:{ path:'goals',where: 'owned'}, transformResponse: function (object) {
        return angular.fromJson(object);
      }},
      friends: {method:'GET', isArray: true, params:{ path:'user-list'}, transformResponse: function (object) {
        return angular.fromJson(object);
      }},
      report: {method:'PUT', isArray: true, params:{ path:'report'}},
      getReport: {method:'GET', params:{ path:'report'}, transformResponse: function (object) {
        return object?angular.fromJson(object):object;
      }},
      done: {method:'GET', params:{ path:'usergoals',where: 'dones', what: true }, transformResponse: function (object) {
        if(object == 1){
          refreshCacheService.refreshCache(UserContext.id, refreshingDate.goalId);
          $timeout(function(){
            $rootScope.$broadcast('doneGoal', refreshingDate.goalId);
          },600);
          $analytics.eventTrack('Goal done', {  category: 'Goal', label: 'Goal done from Web' }); 
        }
          return angular.fromJson(object);
      }},
      getStory: {method:'GET', params:{ path:'story'}},
      editStory: {method:'PUT', params:{ path:'goals', where: 'story'}, transformResponse: function (object) {
          $analytics.eventTrack('Success story', {  category: 'Success story', label: 'Add success story from Web' });
          return object;
      }},
      delete: {method:'DELETE', params:{ path:'usergoals'}, transformResponse: function (object) {
          refreshCacheService.refreshCache(UserContext.id);
          $analytics.eventTrack('Goal unlisted', {  category: 'Goal', label: 'Goal unlisted from Web' });
          return object;
      }}
    });
  }])
  .service('refreshCacheService', ['$timeout', 'CacheFactory', 'localStorageService', function($timeout, CacheFactory, localStorageService){
    function refreshCache(userId, goalId){
      var profileCache = CacheFactory.get('bucketlist');
      var popularCache = CacheFactory.get('bucketlist_by_popular');
      var activityCache = localStorageService.get('active_cache'+userId);

      if(!profileCache){
        profileCache = CacheFactory('bucketlist');
      }

      if(!popularCache){
        popularCache = CacheFactory('bucketlist_by_popular', {
          maxAge: 3 * 24 * 60 * 60 * 1000 ,// 3 day,
          deleteOnExpire: 'aggressive'
        });
      }

      //remove top ideas in cache if they are changed
      if(goalId){
        if(activityCache){
          angular.forEach(activityCache, function(activity) {
            angular.forEach(activity.goals, function(item) {
              if(item.id == goalId){
                localStorageService.remove('active_cache'+userId);
              }
            });
          });
        }
        var cache = popularCache.get('top-ideas' + userId);
        angular.forEach(cache, function(item) {
          if(item.id == goalId){
            popularCache.remove('top-ideas' + userId);
          }
        });
      }

      //remove goal friends on add or done event
      profileCache.remove('goal-friends'+ userId);
    }
    return {
      refreshCache: refreshCache
    }
  }]);
