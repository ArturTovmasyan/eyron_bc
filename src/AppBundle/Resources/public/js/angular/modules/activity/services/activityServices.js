'use strict';

angular.module('activity')
  .service('ActivityDataManager', ['$resource', 'envPrefix',
    function($resource, envPrefix){
      return $resource( envPrefix + 'api/v2.0/activities/:first/:count/:userId/:param', {}, {
        activities: {method:'GET', isArray: true, transformResponse: function (object) {
          return angular.fromJson(object);
        }},
        singleActivity: {method:'GET', isArray: true, transformResponse: function (object) {
          return angular.fromJson(object);
        }}
      });
    }])
  .value('storageDates', {
    name: 'activities_storage'
  })
  .factory('lsActivitiesItems', ['$http', 'localStorageService', 'ActivityDataManager', '$analytics', 'UserContext', 'storageDates',
    function($http, localStorageService, ActivityDataManager, $analytics, UserContext, storageDates) {
    var lsActivitiesItems = function(loadCount, userId) {
      this.items = [];
      this.users = [];
      this.id = userId? userId: 0;
      this.noItem = false;
      this.busy = false;
      this.isReset = false;
      this.storage_name = storageDates.name;
      this.request = 0;
      this.start = 0;
      this.reserve = [];
      this.count = loadCount ? loadCount : 9;
    };

    lsActivitiesItems.prototype.loadAddthis = function(){
      var olds = $('script[src="http://s7.addthis.com/js/300/addthis_widget.js#domready=1"]');
      olds.remove();

      var addthisScript = document.createElement('script');
      addthisScript.setAttribute('src', 'http://s7.addthis.com/js/300/addthis_widget.js#domready=1');
      return document.body.appendChild(addthisScript);
    };

    lsActivitiesItems.prototype.reset = function(){
      this.isReset = true;
      this.items = [];
      this.users = [];
      this.category = 'all';
      this.busy = false;
      this.reserve = [];
      this.request = 0;
      this.start = 0;
      this.slug = 0;
      this.search = '';
    };

    lsActivitiesItems.prototype.imageLoad = function(profile) {
      var img;
      this.busy = false;
      angular.forEach(this.reserve, function(item) {
        if (!angular.isUndefined(item.goals) && item.goals.length && item.goals[0].cached_image) {
          img = new Image();
          img.src = item.goals[0].cached_image;
        }
      });
    };

    lsActivitiesItems.prototype.newActivity = function(time, cb){
      ActivityDataManager.activities({ first: 0, count: this.count, time: time}, function (newData) {
        if(angular.isFunction(cb)){
          cb(newData);
        }
      });
    };

    lsActivitiesItems.prototype.addNewActivity = function(data, cb){
      var itemIds = [];

      // TODO needs to optimize
      angular.forEach(this.items, function (d) {
        itemIds.push(d.id);
      });

      var removingCount = 0,k;

      angular.element('#activities').addClass('comingByTop');

      // TODO needs to optimize
      for(var i = data.length - 1, j = 0; i >= 0; i--, j++){
        k = itemIds.indexOf(data[i].id);
        if(k !== -1){
          this.items.splice(k + j - removingCount, 1);
          removingCount++;
        }
        this.items.unshift(data[i]);
      }
      if(angular.isFunction(cb)){
        cb();
      }
      angular.element('#activities').removeClass('comingByTop');
    };

    lsActivitiesItems.prototype.getReserve = function() {
      angular.element('#activities').removeClass('comingByTop');
      this.busy = this.noItem;
      this.items = this.items.concat(this.reserve);
      this.nextReserve();
      if(angular.element('#activities').length > 0){
        $analytics.eventTrack('Activity load more', {  category: 'Activity', label: 'Load more from Web' });
      }
    };

    lsActivitiesItems.prototype.nextReserve = function() {

      if (this.busy) return;
      this.busy = true;

      var lastId = this.items[this.items.length - 1].id;
      var lastDate = this.items[this.items.length - 1].datetime;
      var first = lastId? 0 : this.start;
      
      if(this.id){
        ActivityDataManager.singleActivity({ first: first, count: this.count, userId: this.id, id: lastId, time: lastDate}, function (newData) {
          if(!newData.length){
            this.noItem = true;
          } else {
            this.reserve = newData;
            this.imageLoad();
            this.start += this.count;
            this.request++;
            this.busy = false;
          }
        }.bind(this));
      } else {
        ActivityDataManager.activities({ first: first, count: this.count, id: lastId, time: lastDate}, function (newData) {
          if(!newData.length){
            this.noItem = true;
          } else {
            this.reserve = newData;
            this.imageLoad();
            this.start += this.count;
            this.request++;
            this.busy = false;
          }
        }.bind(this));
      }
      
    };

    lsActivitiesItems.prototype.nextActivity = function() {
      if (this.busy) return;
      this.busy = true;

      this.noItem = false;
      
      if(this.request){
        this.getReserve();
      } else {
        if(this.id){
          ActivityDataManager.singleActivity({ first: this.start, count: this.count, userId: this.id}, function (newData) {
            if(!newData.length){
              this.noItem = true;
            } else {
              this.items = this.items.concat(newData);
              this.start += this.count;
              this.request++;
              this.busy = false;
              this.nextReserve();
            }
          }.bind(this));
        } else {
          if(UserContext.id && !this.isReset &&
            localStorageService.isSupported &&
            localStorageService.get(this.storage_name + UserContext.id))
          {
            var data = localStorageService.get(this.storage_name + UserContext.id);
            this.items = this.items.concat(data);

            // url = url.replace('{first}', 0).replace('{count}', this.count);
            ActivityDataManager.activities({first: 0, count: this.count}, function (newData) {
              if(newData.length){
                localStorageService.set(this.storage_name + UserContext.id, newData);
                if(data.length){
                  if(newData[0].datetime !== data[0].datetime ){
                    angular.element('#activities').addClass('comingByTop');

                    // TODO Change this
                    for(var i = this.count - 1; i >= 0; i--){
                      this.items.unshift(newData[i]);
                      this.items.pop();
                    }

                    this.reserve = [];
                  }
                } else {
                  this.items = this.items.concat(newData);
                }
              } else {
                if(!data.length){
                  this.noItem = true;
                }
              }

              this.start += this.count;
              this.request++;
              this.busy = data.length ? false : true;

              if(this.items.length){
                this.nextReserve();
              }
            }.bind(this));

          } else {
            ActivityDataManager.activities({first: this.start, count: this.count}, function (newData) {
              if (!newData.length) {
                this.noItem = true;
              } else {
                if (UserContext.id && localStorageService.isSupported) {
                  localStorageService.set(this.storage_name + UserContext.id, newData);
                }
                this.items = this.items.concat(newData);
                this.start += this.count;
                this.request++;
                this.busy = false;
                if(this.items.length){
                  this.nextReserve();
                }
              }
            }.bind(this));
          }
        }
      }
    };

  return lsActivitiesItems;
  }])
  .directive('lsActivities',['lsActivitiesItems',
    '$interval',
    '$timeout',
    '$http',
    'envPrefix',
    'UserContext',
    'localStorageService',
    'storageDates',
    'CacheFactory',
    function(lsActivitiesItems, $interval, $timeout, $http, envPrefix, UserContext, localStorageService, storageDates, CacheFactory){
      return {
        restrict: 'EA',
        scope: {
          lsUser: '@',
          lsCount: '@'
        },
        link: function(scope, el){
          scope.$parent.newActivity = false;
          scope.$parent.topUsers = [];
          var path = envPrefix + "api/v1.0/badges",
              leaderboardCache = CacheFactory.get('bucketlist_by_leaderboard'),
              inProces = false;

          scope.$parent.castInt = function(value){
            return parseInt(value);
          };

          if(!leaderboardCache){
            leaderboardCache = CacheFactory('bucketlist_by_leaderboard');
          }

          scope.$parent.haveTop = false;

          $http.get(path)
              .success(function(data){
                if(data.badges){
                  scope.$parent.haveTop = (data.users && data.users.length > 0);
                  scope.$parent.topUsers = data.users;
                  leaderboardCache.put('leaderboards', data);
                }});


          scope.$parent.inArray = function (user) {
            return (user.innovator || user.mentor || user.traveler)
          };
          
          scope.$parent.isVoting = function(isVoting, isStory){
            if(!isStory)return false;
              return isVoting;
          };

          scope.$parent.manageVote = function(id, isNotMyGoal){
            if(!isNotMyGoal || inProces)return;
            inProces = true;
            var url = (!scope.$parent.vote[id])?'api/v1.0/success-story/add-vote/{storyId}': 'api/v1.0/success-story/remove-vote/{storyId}';
            url = envPrefix + url;
            url = url.replace('{storyId}', id);
            $http.get(url).success(function() {
              inProces = false;
              if(UserContext.id && localStorageService.isSupported)
              {
                localStorageService.remove(storageDates.name + UserContext.id);
              }
              if(!scope.$parent.vote[id]){
                scope.$parent.count[id]++;
                scope.$parent.vote[id] = true;
              } else {
                scope.$parent.count[id]--;
                scope.$parent.vote[id] = false;
              }
            })
              .error(function (res) {
                if(res == "User not found") {
                  $(".modal-loading").show();
                  AuthenticatorLoginService.openLoginPopup();
                  $(".modal-loading").hide();
                }
              });
          };


          function newActivity() {
            if(!angular.isUndefined(scope.$parent.Activities.items) && !angular.isUndefined(scope.$parent.activityPage)){
              scope.$parent.Activities.newActivity(scope.$parent.Activities.items[0].datetime, function(data){
                if(data && data.length != 0){
                  scope.$parent.newData = data;
                  scope.$parent.newActivity = true;
                  $interval.cancel(interval);
                }
              });
            } else {
              $interval.cancel(interval);
            }
          }

          scope.$parent.loadImage = function (index) {
            var activeIndex = scope.$parent.Activities.items[index].activeIndex;
            if(!scope.$parent.Activities.items[index].reserveGoals[activeIndex] && scope.$parent.Activities.items[index].goals[activeIndex]){
              scope.$parent.Activities.items[index].reserveGoals.push(scope.$parent.Activities.items[index].goals[activeIndex]);
            }
          };

          $('body').on('click', '.ActivityPage', function() {
            if(scope.$parent.newActivity){
              scope.$parent.addNew();
            } else {
              $("html, body").animate({ scrollTop: 0 }, "slow");
            }
          });

          var interval = $interval(newActivity,120000);

          scope.$parent.addNew = function () {
            scope.$parent.newActivity = false;
            $("html, body").animate({ scrollTop: 0 }, "slow");
            $timeout(function(){
              scope.$parent.Activities.addNewActivity(scope.$parent.newData, slideInsert);
            }, 1000);
            interval = $interval(newActivity,120000);
          };

          scope.$parent.showComment = function (activity, goal) {
            if(!angular.isUndefined(activity)){
              activity.createComment = true;
              $timeout(function () {
                activity.showComment = !activity.showComment;
              },300);
            } else {
              goal.createComment = true;
              $timeout(function () {
                goal.showComment = !goal.showComment;
              },300);
            }
            
          };

          function slideInsert(){
            $timeout(function(){
              var activity_swiper = new Swiper('div.activity-slider:not(.swiper-container-horizontal)', {
                observer: true,
                autoHeight: true,
                onSlideNextStart: function (ev) {
                  scope.$parent.Activities.items[$(ev.container).data('index')].activeIndex++;
                  scope.$parent.Activities.items[$(ev.container).data('index')].createComment = false;
                  scope.$parent.Activities.items[$(ev.container).data('index')].showComment = false;
                  scope.$parent.loadImage($(ev.container).data('index'));
                  scope.$parent.$apply();
                  $timeout(function () {
                    ev.update(true);
                  }, 100)

                },
                onSlidePrevStart: function (ev) {
                  scope.$parent.Activities.items[$(ev.container).data('index')].createComment = false;
                  scope.$parent.Activities.items[$(ev.container).data('index')].showComment = false;
                  scope.$parent.Activities.items[$(ev.container).data('index')].activeIndex--;
                  scope.$parent.$apply();
                },

                // loop: true,
                nextButton: '.swiper-button-next',
                prevButton: '.swiper-button-prev',
                spaceBetween: 30
              })
            }, 2000);
          }
          scope.$parent.Activities = new lsActivitiesItems(scope.lsCount? scope.lsCount: 9, scope.lsUser);
          
          if(!scope.lsUser){
            scope.$parent.Activities.nextActivity();
          }
          
          scope.$parent.showNoActivities = false;

          scope.$parent.$watch('Activities.items', function(d) {
            if(!d.length){
              if(scope.$parent.Activities.noItem ){
                scope.$parent.showNoActivities = true;
                angular.element('#non-activity').css('display', 'block');
              }
            } else {
              slideInsert();
            }
          });

        }
      }
    }
  ]);