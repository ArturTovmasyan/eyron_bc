'use strict';

angular.module('goalComponents', ['Interpolation',
  'Components',
  'angular-cache',
  'ui.select',
  'ngSanitize',
  'goalManage',
  'angulartics',
  'angulartics.google.analytics',
  'PathPrefix',
  'dndLists',
  'Facebook'
  ])
  .config(function(uiSelectConfig) {
    uiSelectConfig.theme = 'bootstrap';
  })
  .controller('goalFooter', ['$scope', '$timeout',
    function($scope, $timeout){
      $scope.completed = true;

      $scope.popoverByMobile = function(){
        $timeout(function(){
          angular.element('.navbar-toggle').click();
        }, 500);
      };
    }])
  .controller('overallProgressController', ['$scope', '$http', '$rootScope', 'UserGoalDataManager',
    function($scope, $http, $rootScope, UserGoalDataManager){
      $scope.currentPage = {};

      $rootScope.$on('lsJqueryModalClosedSaveGoal', function () {
        UserGoalDataManager.overall($scope.currentPage, function (data) {
          $scope.overallProgress = data.progress;
        });
      });

      $rootScope.$on('lsGoActivity', function () {
        $scope.overallProgress = 0;
      });

      $rootScope.$on('removeUserGoal', function () {
        UserGoalDataManager.overall($scope.currentPage, function (data) {
          $scope.overallProgress = data.progress;
        });
      });

      $scope.$on('doneGoal', function(){
        UserGoalDataManager.overall($scope.currentPage, function (data) {
          $scope.overallProgress = data.progress;
        });
      });

      $scope.$on('owned', function(){
        var post = {
          'owned' : true
        };
        $scope.currentPage = post;
        UserGoalDataManager.overall(post, function (data) {
          $scope.overallProgress = data.progress;
        });
      });

      $scope.$on('profileNextPage', function(ev, data){
        $scope.currentPage = data;
        UserGoalDataManager.overall($scope.currentPage, function (data) {
          $scope.overallProgress = data.progress;
        });
      });

    }])
  .controller('popularGoalsController', ['$scope', '$http', 'CacheFactory', 'envPrefix', 'refreshingDate',
    function($scope, $http, CacheFactory, envPrefix, refreshingDate){
    var path = envPrefix + "api/v1.0/top-ideas/{count}";
    var deg = 360;

    var popularCache = CacheFactory.get('bucketlist_by_popular');

    if(!popularCache){
      popularCache = CacheFactory('bucketlist_by_popular', {
        maxAge: 3 * 24 * 60 * 60 * 1000 ,// 3 day,
        deleteOnExpire: 'aggressive'
      });
    }

    $scope.castInt = function(value){
      return parseInt(value);
    };

    $scope.refreshPopulars = function () {
      angular.element('#popularLoad').css({
        '-webkit-transform': 'rotate('+deg+'deg)',
        '-ms-transform': 'rotate('+deg+'deg)',
        'transform': 'rotate('+deg+'deg)'
      });
      deg += 360;
      $http.get(path)
        .success(function(data){
          $scope.popularGoals = data;
          popularCache.put('top-ideas'+$scope.userId, data);
        });
    };

    $scope.getPopularGoals = function(id){
      path = path.replace('{count}', (window.innerWidth > 767 && window.innerWidth < 993)?2: $scope.count);

      var topIdeas = popularCache.get('top-ideas'+id);

      if (!topIdeas) {

        $http.get(path)
          .success(function(data){
            $scope.popularGoals = data;
            popularCache.put('top-ideas'+id, data);
          });
      }else {
        $scope.popularGoals = topIdeas;
      }
    };

    $scope.$on('addGoal', function(){
      angular.forEach($scope.popularGoals, function(item){
        if(item.id == refreshingDate.goalId){
          $scope.refreshPopulars();
        }
      });
    });

    $scope.$on('doneGoal', function(){
      angular.forEach($scope.popularGoals, function(item){
        if(item.id == refreshingDate.goalId){
          $scope.refreshPopulars();
        }
      });
    });

    $scope.$watch('userId', function(id){
      $scope.getPopularGoals(id);
    })
  }])
  .controller('topInLeaderboardController', ['$scope', '$http', 'CacheFactory', 'envPrefix', 'UserContext', '$timeout',
    function($scope, $http, CacheFactory, envPrefix, UserContext, $timeout){
      $scope.users = {};
      $scope.allUsers = [];
      $scope.index = 0;
      var img;
      $scope.currentUserId = UserContext.id;
      $scope.isMobile = (window.innerWidth < 768);
      $scope.isTouchdevice = (window.innerWidth > 600 && window.innerWidth < 992);
      var leaderboardCache = CacheFactory.get('bucketlist_by_leaderboard');

      if(!leaderboardCache){
        leaderboardCache = CacheFactory('bucketlist_by_leaderboard', {
          maxAge: 24 * 60 * 60 * 1000 ,// 1 day
          deleteOnExpire: 'aggressive'
        });
      }

      $scope.initUsers = function () {
        angular.forEach($scope.allUsers, function (k,item) {
          $scope.users[item] = ($scope.index < k.length)?k[$scope.index]:k[($scope.index % k.length)];
          angular.forEach(k, function (item) {
            if(item){
              img = new Image();
              img.src = item.user.image_path;
            }
          })
        });
      };
      
      $scope.refreshLeaderboard = function () {
        if($scope.normOfTop > 0) {
          $scope.index = ($scope.index == 9) ? 0 : $scope.index + 1;

          $scope.initUsers();
        }
      };

      $scope.getFullName = function (user) {
        var name = user.first_name + user.last_name,
            count = $scope.isTouchdevice?50:(($scope.isMobile || (window.innerWidth > 991 && window.innerWidth < 1170))?16:24);
          return (name.length > count)?(name.substr(0,count -3) + '...'):name;
      };

      $timeout(function () {
        var leaderboards = leaderboardCache.get('leaderboards');

        if(leaderboards && leaderboards.badges){
          $scope.allUsers = leaderboards.badges;
          $scope.minimums = leaderboards.min;
          $scope.normOfTop = $scope.minimums.innovator + $scope.minimums.motivator + $scope.minimums.traveller;
          $scope.initUsers();
        }
      }, 500);
    }])
  .controller('calendarController', ['$scope', '$http', 'CacheFactory', 'envPrefix', '$timeout',
    function($scope, $http, CacheFactory, envPrefix, $timeout){
      $scope.isHover = false;
      $scope.hoveredText = '';
      var path = envPrefix + 'api/v1.0/usergoal/calendar/data';
      $scope.getDaysInMonth = function(m, y) {
        return m===2 ? y & 3 || !(y%25) && y & 15 ? 28 : 29 : 30 + (m+(m>>3)&1);
      };

      $scope.dateByFormat = function (year, month, day, format) {
        var date = moment(year + '-' +((month > 9)?month:'0'+month)+'-'+((day > 9)?day:'0'+day));
        return format?date.format(format):date;
      };
      
      $scope.arrayBySize = function (size) {
        return new Array(size);
      };

      $scope.hoverIn = function (ev, text) {
        $scope.isHover = true;
        $scope.hoveredText = text;
        var left = $(ev.target).offset().left;
        var top  = $(ev.target).offset().top - 50;
        
        if(left > window.innerWidth/2){
          left = left - 100;
          $('.calendar-tooltip .arrow-up').css({left: 110});
        } else {
          $('.calendar-tooltip .arrow-up').css({left: 14});
        }
        $('.calendar-tooltip').css({top: top,left: left});
      };

      $scope.now = moment();
      $scope.type = 'month';
      $scope.days = [];
      $scope.myYears = [];
      $scope.myYAMonths = [];
      $scope.myDays = [];
      $scope.currentDay = $scope.now.format('D');
      $scope.currentMonth = $scope.now.format('M');
      $scope.currentYear = $scope.now.format('YYYY');

      $scope.initData =function (data) {
        angular.forEach(data, function (v,k) {
          var y = moment(k).format('YYYY'),
            m = moment(k).format('M'),
            d = moment(k).format('D');
          $scope.myYears[y] = $scope.myYears[y]?$scope.myYears[y]:{complete:0, deadline:0, current:0};
          $scope.myYAMonths[y] = $scope.myYAMonths[y]?$scope.myYAMonths[y]:[];
          $scope.myYAMonths[y][m] = $scope.myYAMonths[y][m]?$scope.myYAMonths[y][m]:{complete:0, deadline:0, current:0};
          $scope.myDays[y] = $scope.myDays[y]?$scope.myDays[y]:[];
          $scope.myDays[y][m] = $scope.myDays[y][m]?$scope.myDays[y][m]:[];
          $scope.myDays[y][m][d] = $scope.myDays[y][m][d]?$scope.myDays[y][m][d]:{complete:0, deadline:0, current:0};

          if(v.completion){
            $scope.myYears[y].complete = $scope.myYears[y].complete?($scope.myYears[y].complete + v.completion):(v.completion);
            $scope.myYAMonths[y][m].complete = $scope.myYAMonths[y][m].complete?($scope.myYAMonths[y][m].complete + v.completion):(v.completion);
            $scope.myDays[y][m][d].complete = $scope.myDays[y][m][d].complete?($scope.myDays[y][m][d].complete + v.completion):(v.completion);
          }

          if(v.active){
            if($scope.compareDates(k) === -1){
              $scope.myYears[y].deadline = $scope.myYears[y].deadline?($scope.myYears[y].deadline + v.active):(v.active);
              $scope.myYAMonths[y][m].deadline = $scope.myYAMonths[y][m].deadline?($scope.myYAMonths[y][m].deadline + v.active):(v.active);
              $scope.myDays[y][m][d].deadline = $scope.myDays[y][m][d].deadline?($scope.myDays[y][m][d].deadline + v.active):(v.active);
            } else {
              $scope.myYears[y].current = $scope.myYears[y].current?($scope.myYears[y].current + v.active):(v.active);
              $scope.myYAMonths[y][m].current = $scope.myYAMonths[y][m].current?($scope.myYAMonths[y][m].current + v.active):(v.active);
              $scope.myDays[y][m][d].current = $scope.myDays[y][m][d].current?($scope.myDays[y][m][d].current + v.active):(v.active);
            }
          }
        });
      };

      $scope.initDate =function () {
        for(var i = 1; i < 43; i++){
          $scope.days[i] = {day: i}
        }
        
        $scope.weekDay = $scope.dateByFormat($scope.currentYear, $scope.currentMonth, 1).weekday();
        $scope.dayDifferent = (-$scope.weekDay);
        $scope.prevMonthDay = $scope.getDaysInMonth(($scope.currentMonth == 1)?12:$scope.currentMonth -1, ($scope.currentMonth == 1)?$scope.currentYear - 1:$scope.currentYear);
        $scope.currentMonthDay = $scope.getDaysInMonth($scope.currentMonth, $scope.currentYear);

        angular.forEach($scope.days, function (v,k) {
          $scope.days[k].day = (k + $scope.dayDifferent > 0)?((k + $scope.dayDifferent <= $scope.currentMonthDay)?(k + $scope.dayDifferent):(k + $scope.dayDifferent - $scope.currentMonthDay)):(k + $scope.dayDifferent + $scope.prevMonthDay);
          $scope.days[k].status = (k + $scope.dayDifferent > 0 && k + $scope.dayDifferent <= $scope.currentMonthDay)?'active':'inActive';
          $scope.days[k].year = ($scope.days[k].status == 'active')?$scope.currentYear:((k + $scope.dayDifferent > $scope.currentMonthDay && $scope.currentMonth == 12)? (+$scope.currentYear + 1):(k + $scope.dayDifferent <= 0 && $scope.currentMonth == 1)?$scope.currentYear - 1:$scope.currentYear);
          $scope.days[k].month = ($scope.days[k].status == 'active')?$scope.currentMonth:(k + $scope.dayDifferent > $scope.currentMonthDay)? ($scope.currentMonth == 12)?1:(+$scope.currentMonth + 1):(k + $scope.dayDifferent <= 0)?($scope.currentMonth == 1)?12:($scope.currentMonth - 1):$scope.currentMonth;
        });

        $scope.noShowLast = ($scope.days[42].day != 42 && $scope.days[42].day >= 7);

      };
      
      $scope.initDate();

      $http.get(path)
        .success(function(data){
          $scope.initData(data);
        });

      $scope.prev = function () {
        switch ($scope.type){
          case 'month':
            if($scope.currentMonth == 1){
              if ($scope.currentYear <= 1966)return;
              $scope.currentMonth = 12;
              $scope.currentYear --;
            } else {
              $scope.currentMonth--;
            }

            $scope.initDate();
            break;
          case 'year':
            if ($scope.currentYear <= 1966)return;
            $scope.currentYear -= 2;
            $scope.initDate();
            break;
          case 'all':
            if ($scope.currentYear <= 1966)return;
            $scope.currentYear -= 12;
            $scope.initDate();
            break;
        }
      };

      $scope.next = function () {
        switch ($scope.type){
          case 'month':
            if($scope.currentMonth == 12){
              if ($scope.currentYear >= moment().add(50,'years').format('YYYY'))return;
              $scope.currentMonth = 1;
              $scope.currentYear ++;
            } else {
              $scope.currentMonth++;
            }

            $scope.initDate();
            break;
          case 'year':
            if ($scope.currentYear >= moment().add(50,'years').format('YYYY'))return;
            $scope.currentYear -= -2;
            $scope.initDate();
            break;
          case 'all':
            if ($scope.currentYear >= moment().add(50,'years').format('YYYY'))return;
            $scope.currentYear -= -12;
            $scope.initDate();
            break;
        }
      };

      $scope.compareDates = function(date1, date2){
        if(!date1){
          return null;
        }

        var d1 = new Date(date1);
        var d2 = date2 ? new Date(date2): new Date();

        if(d1 < d2){
          return -1;
        }
        else if(d1 === d2){
          return 0;
        }
        else {
          return 1;
        }
      };
    }])
  .controller('featureGoalsController', ['$scope', '$http', 'CacheFactory', 'envPrefix', 'refreshingDate',
    function($scope, $http, CacheFactory, envPrefix, refreshingDate){
      var path = envPrefix + "api/v1.0/goal/featured";

      var popularCache = CacheFactory.get('bucketlist_by_feature'),
          deg = 360;

      if(!popularCache){
        popularCache = CacheFactory('bucketlist_by_feature', {
          maxAge: 24 * 60 * 60 * 1000 ,// 1 day
          deleteOnExpire: 'aggressive'
        });
      }

      $scope.castInt = function(value){
        return parseInt(value);
      };

      $scope.refreshFeatures = function(){
        angular.element('#featuresLoad').css({
          '-webkit-transform': 'rotate('+deg+'deg)',
          '-ms-transform': 'rotate('+deg+'deg)',
          'transform': 'rotate('+deg+'deg)'
        });
        deg += 360;
        $http.get(path)
            .success(function(data){
              $scope.featureGoals = data;
              popularCache.put('features'+$scope.userId, data);
            });
      };

      $scope.getFeatureGoals = function(id){

        var features = popularCache.get('features'+id);

        if (!features || !features.length) {

          $http.get(path)
              .success(function(data){
                $scope.featureGoals = data;
                popularCache.put('features'+id, data);
              });
        }else {
          $scope.featureGoals = features;
        }
      };

      $scope.$on('addGoal', function(){
        angular.forEach($scope.featureGoals, function(item){
          if(item.id == refreshingDate.goalId){
            $scope.refreshFeatures();
          }
        });
      });

      $scope.$on('doneGoal', function(){
        angular.forEach($scope.popularGoals, function(item){
          if(item.id == refreshingDate.goalId){
            $scope.refreshFeatures();
          }
        });
      });

      $scope.$watch('userId', function(id){
        $scope.getFeatureGoals(id);
      })
    }])
  .controller('userStatesController', ['$scope', '$http', 'CacheFactory', 'envPrefix', 'UserContext',
    function($scope, $http, CacheFactory, envPrefix, UserContext){

      var statePath = envPrefix + "api/v1.0/users/{id}/states";

      $scope.$on('addGoal', function(){
        $scope.changeStates();
      });

      $scope.$on('doneGoal', function(){
        $scope.changeStates();
      });

      $scope.changeStates = function () {
        statePath = statePath.replace('{id}', UserContext.id);

        $http.get(statePath)
          .success(function(data){
            $scope.isChange = true;
            $scope.stats = data;
            // profileCache.put('user-states'+id, data);
          });
      };

  }])
  .controller('goalFriends', ['$scope', '$http', 'CacheFactory', 'envPrefix', '$timeout', function($scope, $http, CacheFactory, envPrefix, $timeout){
    var path = envPrefix + "api/v1.0/goal/random/friends";

    var profileCache = CacheFactory.get('bucketlist');
    var leaderboardCache = CacheFactory.get('bucketlist_by_leaderboard');
    var deg = 360;
    $scope.topUsers = [];

    if(!profileCache){
      profileCache = CacheFactory('bucketlist');
    }

    if(!leaderboardCache){
      leaderboardCache = CacheFactory('bucketlist_by_leaderboard');
    }

    $timeout(function () {
      var leaderboards = leaderboardCache.get('leaderboards');

      if (leaderboards) {
        $scope.haveTop = (leaderboards.users && leaderboards.users.length > 0);
        $scope.topUsers = leaderboards.users;
      } else {
        $scope.haveTop = false;
      }
    }, 500);

    $scope.inArray = function (user) {
      return (user.innovator || user.mentor || user.traveler)
    };

    $scope.getGaolFriends = function(id){

      var goalFriends = profileCache.get('goal-friends'+id);

      if (!goalFriends) {

        $http.get(path)
          .success(function(data){
            $scope.goalFriends = data[1];
            $scope.length = data['length'];
            profileCache.put('goal-friends'+id, data);
          });
      }else {
        $scope.goalFriends = goalFriends[1];
        $scope.length = goalFriends['length'];
      }
    };

    $scope.refreshGoalFriends = function () {
      angular.element('#goalFriendLoad').css({
        '-webkit-transform': 'rotate('+deg+'deg)',
        '-ms-transform': 'rotate('+deg+'deg)',
        'transform': 'rotate('+deg+'deg)'
      });
      deg += 360;
      $http.get(path)
        .success(function(data){
          var id = $scope.userId;
          $scope.length = data['length'];
          $scope.goalFriends = data[1];
          profileCache.put('goal-friends'+id, data);
        });
    };

    $scope.$on('addGoal', function(){
      $scope.refreshGoalFriends();
    });

    $scope.$on('doneGoal', function(){
      $scope.refreshGoalFriends();
    });

    $scope.$watch('userId', function(id){
      $scope.getGaolFriends(id);
    })
  }])
  .controller('goalDone', ['$scope',
    '$sce',
    '$timeout',
    '$window',
    'userGoalData',
    'UserGoalDataManager',
    'envPrefix',
    function($scope, $sce, $timeout, $window, userGoalData, UserGoalDataManager, envPrefix){

      var myDate = moment().format('YYYY');
      $scope.years = _.map($(Array(myDate - 1966)), function (val, i) { return myDate - i; });
      $scope.days = _.map($(Array(31)), function (val, i) { return i + 1; });
      $timeout(function () {
        $scope.years.unshift($scope.defaultYear);
        $scope.days.unshift($scope.defaultDay);
      },100);

      $scope.$watch('myMonths', function(m){
        $scope.months = _.values(m);
      });

      $timeout(function () {
        var date = new Date();
        $scope.month = $scope.myMonths[moment(date).format('M')];
        $scope.day = moment(date).format('D');
        $scope.year = moment(date).format('YYYY');
      }, 500);

      $scope.userGoal = userGoalData.doneData;
      $scope.noStory = false;
      $scope.invalidYear = false;
      $scope.uncompletedYear = false;
      $scope.newAdded = userGoalData.manage? false: true;
      $scope.goalLink = window.location.origin + envPrefix + 'goal/' +$scope.userGoal.goal.slug;
      $scope.files = [];
      $scope.uploadingFiles = [];
      $scope.successStory = {};
      var imageCount = 6;
      var clickable = true;

      $('body').on('focus', 'textarea[name=story]', function() {
        $('textarea[name=story]').removeClass('border-red');
        $scope.noStory = false;
        $scope.invalidYear = false;
        $scope.uncompletedYear = false;
      });

      $scope.isInValid = function () {
        $scope.noStory = false;
        var noDate = $scope.noData();
        if(!noDate){
          if(angular.isUndefined($scope.userGoal.story)
            || angular.isUndefined($scope.userGoal.story.story)
            || $scope.userGoal.story.story.length < 3 )$scope.noStory = true;

        }
      };

      $scope.getDaysInMonth = function(m, y) {
        return m===2 ? y & 3 || !(y%25) && y & 15 ? 28 : 29 : 30 + (m+(m>>3)&1);
      };

      $scope.compareDates = function(date1, date2){
        if(!date1){
          return null;
        }

        var d1 = new Date(date1);
        var d2 = date2 ? new Date(date2): new Date();

        if(d1 < d2){
          return -1;
        }
        else if(d1 === d2){
          return 0;
        }
        else {
          return 1;
        }
      };

      $scope.noData = function () {
        return ((angular.isUndefined($scope.userGoal.videos_array) || $scope.userGoal.videos_array.length < 2)&&
              (angular.isUndefined($scope.files) || !$scope.files.length )&&
              ( angular.isUndefined($scope.userGoal.story) || angular.isUndefined($scope.userGoal.story.story)))
      };

      $scope.dateByFormat = function (year, month, day, format) {
        return moment(year + '-' +((month > 9)?month:'0'+month)+'-'+((day > 9)?day:'0'+day)).format(format)
      };

      $scope.saveDate = function (status) {
        var comletion_date = {
          'goal_status'    : true,
          'completion_date': $scope.completion_date,
          'date_status'    : status
        };

        // if($scope.compareDates($scope.firefox_completed_date) === 1){
        //   $scope.invalidYear = true;
        //   return;
        // } else {
        //   $scope.invalidYear = false;
        // }

        UserGoalDataManager.manage({id: $scope.userGoal.goal.id}, comletion_date, function (){
          var selector = 'success' + $scope.userGoal.goal.id;
          if(angular.element('#'+ selector).length > 0) {
            var parentScope = angular.element('#' + selector).scope();
            if(!angular.isUndefined(parentScope.goalDate) && !angular.isUndefined(parentScope.dateStatus)) {
              parentScope.goalDate[$scope.userGoal.goal.id] = new Date($scope.firefox_completed_date);
              parentScope.dateStatus[$scope.userGoal.goal.id] = status;
            }
          }

          if($scope.noData()){
            $scope.noStory = false;
            $.modal.close();
          }
        });
      };
      
      $scope.save = function () {
        $scope.isInValid();
        if($scope.year && $scope.year != $scope.defaultYear &&
          $scope.month && $scope.month != $scope.defaultMonth &&
          $scope.day && $scope.day != $scope.defaultDay && $scope.newAdded){

            $scope.dayInMonth = $scope.getDaysInMonth($scope.months.indexOf($scope.month), $scope.year);
          
            if($scope.day > $scope.dayInMonth){
              $scope.invalidYear = true;
              return;
            }

            $scope.completion_date = $scope.dateByFormat($scope.year ,$scope.months.indexOf($scope.month),$scope.day,'MM-DD-YYYY');
            $scope.firefox_completed_date = $scope.dateByFormat($scope.year, $scope.months.indexOf($scope.month), $scope.day, 'YYYY-MM-DD');
            $scope.saveDate(1);
        }else if($scope.year && $scope.year != $scope.defaultYear){//when select only year
          var month = ($scope.month && $scope.month != $scope.defaultMonth)?$scope.months.indexOf($scope.month): moment().format('M');
          var day = 1;
          $scope.completion_date = $scope.dateByFormat($scope.year, month, day,'MM-DD-YYYY');
          $scope.firefox_completed_date = $scope.dateByFormat($scope.year, month, day,'YYYY-MM-DD');
          $scope.saveDate(($scope.month && $scope.month != $scope.defaultMonth)?3:2);
        }
        else if((($scope.month && $scope.month != $scope.defaultMonth) || ($scope.day && $scope.day != $scope.defaultDay)) && $scope.newAdded){
          $scope.uncompletedYear = true;
          return;
        }
        if($scope.noStory){
          angular.element('textarea[name=story]').addClass('border-red');
          return;
        }
        if(!clickable){
          return;
        }
        $timeout(function(){
          $scope.video_link = [];
          angular.forEach($scope.userGoal.videos_array, function (d) {
            if(!angular.isUndefined(d.link) && d.link){
              $scope.video_link.push(d.link);
            }
          });
          var data = {
            'story'     : $scope.userGoal.story.story,
            'videoLink' : $scope.video_link,
            'files'     : $scope.files
          };
          UserGoalDataManager.editStory({id: $scope.userGoal.goal.id}, data, null, function (){
            toastr.error('Sorry! Your success story has not been saved');
          });
          $.modal.close();
        }, 100)
      };

      // file uploads

      Dropzone.options.goalDropzone = false;

      $scope.initDropzone = function(url){
        if(!url){
          return;
        }

        $timeout(function(){
          $scope.goalDropzone = new Dropzone('#goalDropzone', {
            url: url,
            addRemoveLinks: true,
            uploadMultiple: false,
            maxThumbnailFilesize: 6,
            maxFiles: imageCount,
            dictMaxFilesExceeded: 'you cannot upload more than 6 files',
            previewTemplate: "<div class=\"dz-preview dz-file-preview\">\n  <div class=\"dz-details\">\n    <div class=\"dz-filename\"><span data-dz-name></span></div>\n    <div class=\"dz-size\" data-dz-size></div>\n    <img data-dz-thumbnail />\n  </div>\n  <div class=\"dz-progress\"><span class=\"dz-upload\" data-dz-uploadprogress></span></div>\n  <div class=\"dz-success-mark\"><span>✔</span></div>\n  <div class=\"dz-error-mark\" data-dz-remove><span>✘</span></div>\n  <div class=\"dz-error-message\"><span data-dz-errormessage></span></div>\n</div>",
            sending: function(){
              clickable = false;
            },
            removedfile: function(d){
              angular.element(d.previewElement).remove();
              if($scope.uploadingFiles.length){
                var uploadIndex = $scope.uploadingFiles.indexOf(d);
                if(uploadIndex != -1){
                  $scope.uploadingFiles.splice(uploadIndex, 1);
                }

                if($scope.uploadingFiles.length >= imageCount && $scope.uploadingFiles[imageCount -1].status == 'error'){
                  $scope.goalDropzone.processFile( $scope.uploadingFiles[imageCount -1]);
                  $scope.uploadingFiles[imageCount -1].accepted = true;
                }
              }
              var id = JSON.parse(d.xhr.responseText);
              var index = $scope.files.indexOf(id);
              if(index !== -1){
                $scope.files.splice(index, 1);
              }

              $scope.$apply();
            },
            complete: function(res){
              clickable = true;
              if(!res.xhr || res.xhr.status !== 200){
                return;
              }

              $scope.files = $scope.files.concat(JSON.parse(res.xhr.responseText));
              $scope.$apply();
            }
          });
          $scope.goalDropzone.on("thumbnail", function(file) {
            $scope.uploadingFiles = $scope.uploadingFiles.concat(file);
          });
          if(!angular.isUndefined($scope.userGoal.story) && !angular.isUndefined($scope.userGoal.story.files) && $scope.userGoal.story.files) {
            var existingFiles = $scope.userGoal.story.files;

            angular.forEach(existingFiles, function (value, kay) {
              if(kay < 6){
                $scope.files.push(value.id);

                var mockFile = {name: value.file_original_name, accepted : true, size: value.file_size, fileName: value.file_name, xhr: {responseText: value.id}};

                $scope.goalDropzone.files.push(mockFile);
                $scope.goalDropzone.emit("addedfile", mockFile);
                $scope.goalDropzone.emit("thumbnail", mockFile, value.image_path);
              }
            });
          }
        }, 500);
      };

      // end file uploads
      $scope.trustedUrl = function(url){
        return $sce.trustAsResourceUrl(url);
      };

  }])
  .controller('goalEnd', ['$scope',
    '$timeout',
    '$window',
    'UserGoalConstant',
    'GoalConstant',
    '$http',
    'userGoalData',
    'UserGoalDataManager',
    '$analytics',
    'AuthenticatorLoginService',
    function(
      $scope,
      $timeout,
      $window,
      UserGoalConstant,
      GoalConstant,
      $http,
      userGoalData,
      UserGoalDataManager,
      $analytics,
      AuthenticatorLoginService
    ){
      var myDate = moment(new Date()).format('YYYY');
      $scope.completedStepCount = 0;
      $scope.myStep = {};
      $scope.years = _.map($(Array(50)), function (val, i) { return +myDate + i; });
      $scope.completeYears = _.map($(Array(50)), function (val, i) { return myDate - i; });
      $scope.days = _.map($(Array(31)), function (val, i) { return i + 1; });
      $timeout(function () {
        $scope.years.unshift($scope.defaultYear);
        $scope.completeYears.unshift($scope.defaultYear);
        $scope.days.unshift($scope.defaultDay);
      },100);

      $scope.models = {
        selected: null
      };

      $scope.$watch('myMonths', function(m){
        $scope.months = _.values(m);
      });

      $scope.dateByFormat = function (year, month, day, format) {
        return moment(year + '-' +((month > 9)?month:'0'+month)+'-'+((day > 9)?day:'0'+day)).format(format)
      };

      $scope.moveElement = function (index) {
        if($scope.userGoal.formatted_steps.length -1 <= index)return;
        $scope.userGoal.formatted_steps.splice(index, 1);
      };
      
      $scope.dragoverCallback = function (event, index, external, type) {
        return $scope.userGoal.formatted_steps.length > index;
      };
      
      $scope.dropCallback = function (event, index, item, external, type, name) {
        return item;
      };

      $scope.getDaysInMonth = function(m, y) {
        return m===2 ? y & 3 || !(y%25) && y & 15 ? 28 : 29 : 30 + (m+(m>>3)&1);
      };

      $scope.updateDate = function (date, isNewDate) {
        if(date){
          $scope.month = ($scope.userGoal.date_status == 2 && !isNewDate)?$scope.defaultMonth:$scope.myMonths[moment(date).format('M')];
          $scope.day = ($scope.userGoal.date_status == 1 || isNewDate)?moment(date).format('D'):$scope.defaultDay;
          $scope.year = moment(date).format('YYYY');
        }else {
          $scope.month = $scope.defaultMonth;
          $scope.day   = $scope.defaultDay;
          $scope.year  = $scope.defaultYear;
        }
      };

      $scope.userGoal = userGoalData.data;
      $timeout(function(){
        if(!angular.isUndefined($scope.userGoal.do_date)){
          $scope.firefox_do_date = moment($scope.userGoal.do_date).format('YYYY-MM-DD');
        }

        if(!angular.isUndefined($scope.userGoal.completion_date) && $scope.userGoal.status == UserGoalConstant['COMPLETED']){
          $scope.updateDate($scope.userGoal.completion_date);
          $scope.userGoal.completion_date = moment($scope.userGoal.completion_date).format('MM-DD-YYYY');
        } else{
          if(!angular.isUndefined($scope.userGoal.do_date)){
            $scope.updateDate($scope.userGoal.do_date);
            $scope.userGoal.do_date = moment($scope.userGoal.do_date).format('MM-DD-YYYY');
            $scope.userGoal.do_date_status = $scope.userGoal.date_status;
          }
        }
      }, 500);
      angular.element('#goal-create-form').attr('data-goal-id', $scope.userGoal.goal.id);
      $scope.GoalConstant = GoalConstant;
      $scope.UserGoalConstant = UserGoalConstant;

      $scope.newCreated = (angular.element('#goal-create-form').length > 0 && window.location.href.indexOf('?id=') === -1);
      $scope.newAdded = $scope.newCreated;

      $scope.$on('addGoal', function(){
        $scope.newAdded = true;
      });

      $scope.stepsArray = [{}];

      if($scope.userGoal.formatted_steps.length == 1 && $scope.userGoal.formatted_steps[0].length == 0 ){
        $scope.userGoal.formatted_steps = [{}];
      }

      if(!$scope.userGoal.goal || !$scope.userGoal.goal.id){
        console.warn('undefined goal or goalId of UserGoal');
      }

      var switchChanged = false;
      var dateChanged = false;
      var isSuccess = false;
      var flag = false;

      $scope.$watch('complete.switch', function (d) {
        if( flag){
          switchChanged = !switchChanged;
          $scope.uncompletedYear = false;
          $scope.invalidYear = false;

          if(d == 1){
            $scope.updateDate(new Date(), true);
          }
          else{
            if(!angular.isUndefined($scope.userGoal.do_date)){
              if($scope.userGoal.do_date_status){
                $scope.userGoal.date_status = $scope.userGoal.do_date_status
              } else {
                $scope.userGoal.date_status = 1;
              }
              $scope.updateDate($scope.firefox_do_date);
            }
            else{
              $scope.updateDate(null);
            }
          }

        }
        else {
          flag = true;
          if(angular.element('#success' + $scope.userGoal.goal.id).length > 0) {
            isSuccess = angular.element('#success' + $scope.userGoal.goal.id).scope().success[$scope.userGoal.goal.id]?true:false;
          }
        }
      });

      $scope.openSignInPopup = function(){
        AuthenticatorLoginService.openLoginPopup()
      };

      $scope.compareDates = function(date1, date2){

        if(!date1){
          return null;
        }

        var d1 = new Date(date1);
        var d2 = date2 ? new Date(date2): new Date();

        if(d1 < d2){
          return -1;
        }
        else if(d1 === d2){
          return 0;
        }
        else {
          return 1;
        }
      };

      $scope.getCompleted = function(userGoal){
        if(!userGoal || !userGoal.formatted_steps){
          return 0;
        }
        var length = userGoal.formatted_steps.length - 1;

        var result = 0;
        angular.forEach(userGoal.formatted_steps, function(v){
          if(v.switch){
            result++;
          }
        });
        
        $scope.completedStepCount = result;

        return result * 100 / length;
      };

      $scope.momentDateFormat = function(date, format){
        return moment(date).format(format);
      };

      $scope.momentDateModify = function(date, value, key, format){
        var m = moment(date);

        if(key === 'day'){
          return m.day(value).format(format);
        }
      };

      $scope.getPriority = function(userGoal){
        if(!userGoal || !userGoal.id){
          return null;
        }

        if(userGoal.urgent && userGoal.important){
          return $scope.UserGoalConstant['URGENT_IMPORTANT'];
        }
        else if(userGoal.urgent && !userGoal.important){
          return $scope.UserGoalConstant['URGENT_NOT_IMPORTANT'];
        }
        else if(!userGoal.urgent && userGoal.important) {
          return $scope.UserGoalConstant['NOT_URGENT_IMPORTANT'];
        }
        else if(!userGoal.urgent && !userGoal.important){
          return $scope.UserGoalConstant['NOT_URGENT_NOT_IMPORTANT'];
        }

        return null;
      };

      $scope.getUrgentImportant = function(priority){
        if(priority === $scope.UserGoalConstant['URGENT_IMPORTANT']){
          return {urgent: true, important: true};
        }
        else if(priority === $scope.UserGoalConstant['URGENT_NOT_IMPORTANT']){
          return {urgent: true, important: false};
        }
        else if(priority === $scope.UserGoalConstant['NOT_URGENT_IMPORTANT']) {
          return {urgent: false, important: true};
        }
        else if(priority === $scope.UserGoalConstant['NOT_URGENT_NOT_IMPORTANT']){
          return {urgent: false, important: false};
        }
      };

      $scope.removeLocation = function(){
        angular.element(".location .location-hidden").val(null);
        angular.element(".location .location-hidden").attr('value',null);
        angular.element(".location .place-autocomplete").val('');
      };
      $scope.uncompletedYear = false;

      $scope.save = function () {
        $timeout(function(){

          $scope.uncompletedYear = false;
          if($scope.year && $scope.year != $scope.defaultYear &&
            $scope.month && $scope.month != $scope.defaultMonth &&
            $scope.day && $scope.day != $scope.defaultDay){

            dateChanged = true;
            $scope.userGoal.date_status = 1;
            $scope.dayInMonth = $scope.getDaysInMonth($scope.months.indexOf($scope.month), $scope.year);

            if($scope.day > $scope.dayInMonth){
                $scope.invalidYear = true;
                return;
            }

            if($scope.complete.switch){
              $scope.userGoal.completion_date = $scope.dateByFormat($scope.year, $scope.months.indexOf($scope.month), $scope.day, 'MM-DD-YYYY');
              $scope.firefox_completed_date = $scope.dateByFormat($scope.year, $scope.months.indexOf($scope.month), $scope.day, 'YYYY-MM-DD');

              if($scope.firefox_do_date){
                $scope.userGoal.do_date = moment($scope.firefox_do_date).format('MM-DD-YYYY');
              }
            } else{
              $scope.userGoal.do_date = $scope.dateByFormat($scope.year, $scope.months.indexOf($scope.month), $scope.day, 'MM-DD-YYYY');
              $scope.firefox_do_date = $scope.dateByFormat($scope.year, $scope.months.indexOf($scope.month), $scope.day, 'YYYY-MM-DD');
              $scope.userGoal.completion_date = null;
              $scope.userGoal.do_date_status = 1;
            }
          } else if($scope.year && $scope.year != $scope.defaultYear){
            //when select only year
            dateChanged = true;
            var month = ($scope.month && $scope.month != $scope.defaultMonth)?$scope.months.indexOf($scope.month): ($scope.complete.switch? moment().format('M'):12);
            var day = $scope.getDaysInMonth(month, $scope.year);

            $scope.userGoal.date_status = ($scope.month && $scope.month != $scope.defaultMonth)?3:2;

            if($scope.complete.switch){
              $scope.userGoal.completion_date = $scope.dateByFormat($scope.year, month, day, 'MM-DD-YYYY');
              $scope.firefox_completed_date = $scope.dateByFormat($scope.year, month, day, 'YYYY-MM-DD');

              if($scope.firefox_do_date){
                $scope.userGoal.do_date = moment($scope.firefox_do_date).format('MM-DD-YYYY');
              }
            } else {
              $scope.userGoal.do_date = $scope.dateByFormat($scope.year, month, day, 'MM-DD-YYYY');
              $scope.firefox_do_date = $scope.dateByFormat($scope.year, month, day, 'YYYY-MM-DD');
              $scope.userGoal.do_date_status = ($scope.month && $scope.month != $scope.defaultMonth)?3:2;
              $scope.userGoal.completion_date = null;
            }

          }
          else if(($scope.month && $scope.month != $scope.defaultMonth) || ($scope.day && $scope.day != $scope.defaultDay)){
            $scope.uncompletedYear = true;
            return;
          }
          $scope.invalidYear = false;
          // if($scope.userGoal.completion_date && $scope.compareDates($scope.firefox_completed_date) === 1){
          //   $scope.invalidYear = true;
          //   return;
          // }

          var selector = 'success' + $scope.userGoal.goal.id;
          if(angular.element('#'+ selector).length > 0) {
            var parentScope = angular.element('#' + selector).scope();
            if(!angular.isUndefined(parentScope.dateStatus)){
              parentScope.dateStatus[$scope.userGoal.goal.id] = $scope.userGoal.date_status;
            }
            //if goal status changed
            if (switchChanged) {
              parentScope.success[$scope.userGoal.goal.id] = !parentScope.success[$scope.userGoal.goal.id];
              parentScope.completed = !parentScope.completed;
              if(!angular.isUndefined(parentScope.goalDate)){
                //if goal changed  from success to active
                if (isSuccess) {
                  //and date be changed
                  if ($scope.userGoal.do_date) {
                    //change  doDate
                    parentScope.goalDate[$scope.userGoal.goal.id] = new Date($scope.firefox_do_date);
                    angular.element('.goal' + $scope.userGoal.goal.id).addClass("active-idea");
                  } else {
                    //infinity
                    parentScope.goalDate[$scope.userGoal.goal.id] = 'dreaming';
                    angular.element('.goal' + $scope.userGoal.goal.id).removeClass("active-idea");
                  }
                } else {
                  //new datetime for completed
                  angular.element('.goal' + $scope.userGoal.goal.id).removeClass("active-idea");
                  if($scope.userGoal.completion_date){
                    parentScope.goalDate[$scope.userGoal.goal.id] = new Date($scope.firefox_completed_date);
                  }else {
                    parentScope.goalDate[$scope.userGoal.goal.id] = new Date();
                  }
                }
              }
            } else {
              if (!isSuccess && dateChanged && $scope.userGoal.do_date && !angular.isUndefined(parentScope.goalDate)) {
                //change for doDate
                parentScope.goalDate[$scope.userGoal.goal.id] = new Date($scope.firefox_do_date);
                angular.element('.goal' + $scope.userGoal.goal.id).addClass("active-idea");
              } else{
                if(isSuccess && dateChanged && $scope.userGoal.completion_date){
                  angular.element('.goal' + $scope.userGoal.goal.id).removeClass("active-idea");
                  parentScope.goalDate[$scope.userGoal.goal.id] = new Date($scope.firefox_completed_date);
                }
              }
            }
          }

          $scope.userGoal.steps = {};
          angular.forEach($scope.userGoal.formatted_steps, function(v){
            if(v.text) {
              $scope.userGoal.steps[v.text] = v.switch ? v.switch : false;
            }
          });

          var ui = $scope.getUrgentImportant(parseInt($scope.userGoal.priority));
          if(ui){
            $scope.userGoal.urgent    = ui.urgent;
            $scope.userGoal.important = ui.important;
          }

          $scope.userGoal.goal_status = $scope.complete.switch;

          UserGoalDataManager.manage({id: $scope.userGoal.goal.id}, $scope.userGoal, function (res){
            $scope.$emit('lsJqueryModalClosedSaveGoal', res);
          }, function () {
            toastr.error('Sorry! Your goal has not been saved');
          });
          $.modal.close();
        }, 100)
      };

      $scope.removeUserGoal = function (id) {
        UserGoalDataManager.delete({id:id}, function (resource){
          $scope.$emit('removeUserGoal', id);
          if(resource[0] == 1){
            $analytics.eventTrack('Goal delete', {  category: 'Goal', label: 'Goal delete from Web' });
          }
        }, function () {
          toastr.error('Sorry! Your goal has not been removed');
        });
        $.modal.close();
      };

      $timeout(function(){

        angular.element('input.important-radio').iCheck({
          radioClass: 'iradio_minimal-purple',
          increaseArea: '20%'
        }).on('ifChanged', function (event) {
          var target = angular.element(event.target);
          angular.element(".priority-radio").removeClass('active-important');
          target.parents().closest('.priority-radio').addClass('active-important');
          $scope.userGoal.priority = target.val();
          target.trigger('change');
        });
      }, 100);
  }]);
