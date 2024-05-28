'use strict';

angular.module('notification')
  .controller('notificationController',['$scope', '$timeout', 'NotificationManager', '$compile', '$window', '$sce', '$interval',
    function ($scope, $timeout, NotificationManager, $compile, $window, $sce, $interval) {
       $scope.notifies = [];
      $scope.newNotCount = 0;
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
              'max-height': '385px'
            });
          }
        },
        setHeight: 400,
        scrollInertia: 0
      };
      $scope.dropdownOpen = function(){
        if(window.innerWidth < 766){
          $('#notification ul.dropdown-menu').css('min-width', window.innerWidth);
          $('#notification ul.dropdown-menu').offset({left: 0})
        }
      };

      $scope.$on('tooltip.hide', function () {
        $scope.joinToggle1 = false;
      });

      NotificationManager.getAll({id: 0,where: 10}, function (res) {
        $scope.newNotCount = res.unreadCount?res.unreadCount:0;
        $scope.notifies = res.userNotifications;
      });

      function newNotifications() {
        var lastId = $scope.notifies.length ? $scope.notifies[0].id :0;
        NotificationManager.getAll({id: 0,where: 10, what: (-1)*lastId}, function (res) {
          if(res.userNotifications.length){
            $scope.notifies = res.userNotifications.concat($scope.notifies);
            $scope.newNotCount -= (-1) * res.userNotifications.length;
          }
        });
      }

      var interval = $interval(newNotifications,30000);

      $scope.bodyInHtml = function(body) {
        var words = body.split(" "),
            lastWord = words[words.length -1];
        return $sce.trustAsHtml(body.slice(0, -1 * lastWord.length) + "<a href='#'>"+ lastWord + "</a>");
      };
      
      $scope.delete = function(id, index){
        NotificationManager.delete({id: id}, function () {}, function () {
          toastr.error('Sorry! Your notification is not deleted');
        });
        if(!$scope.notifies[index].is_read){
          $scope.newNotCount --;
        }
        $scope.notifies.splice(index, 1);
      };

      $scope.readAll = function(){
        NotificationManager.readAll({}, function () {},function () {
          toastr.error('Sorry! Your notifications are not read');
        });
        $scope.newNotCount = 0;
        angular.forEach($scope.notifies, function (not) {
          not.is_read = true;
        });
      };

      $scope.singleRead = function(id, index){
        NotificationManager.readSingle({id: id}, function () {}, function () {
          toastr.error('Sorry! Your notification is not read');
        });
        $scope.newNotCount = $scope.notifies[index].is_read?$scope.newNotCount:($scope.newNotCount - 1);
        $scope.notifies[index].is_read = true;
      };

      $scope.getInterval = function (lastActivity) {
        var result = {'time' : -1, 'title' : null};

        if (!lastActivity) {
          return result;
        }

        var ms = moment().diff(moment(lastActivity));
        var d = moment.duration(ms),
        // y = Math.floor(d.asYears()),
        // m = Math.floor(d.asMonths()),
          dd = Math.floor(d.asDays()),
          h = Math.floor(d.asHours()),
          mm = Math.floor(d.asMinutes());

        // activity result
        if (!angular.isUndefined(d)) {
          if(dd > 1) {
            result = {'time': 0, 'title': 'datetime'};
          } else if(dd > 0) {
            result = {'time': 0 , 'title': 'yesterday'};
          } else  if(h > 0) {
            result = {'time': h , 'title': 'hr'};
          } else if(mm > 1){
            result = {'time': mm, 'title': 'minute'};
          } else {
            result = {'time': 1, 'title': 'now'};
          }
        }

        return result;
      };

      $scope.goNotificationPage = function (notify, index) {
        $scope.singleRead(notify.id, index);
        $window.location.href = notify.notification.link;
      }
      
    }
  ])
  .controller('notificationInnerController',['$scope', '$timeout', 'NotificationManager', '$compile', '$window', '$sce',
    function ($scope, $timeout, NotificationManager, $compile, $window, $sce) {
      $scope.busy = false;
      $scope.notifies = [];
      $scope.start = 0;
      $scope.request = 0;
      $scope.count = 10;
      $scope.reserve = [];
      $scope.noNotification = false;

      $scope.getReserve = function() {
        $scope.busy = $scope.noNotification;
        $scope.notifies = $scope.notifies.concat($scope.reserve);
        $scope.nextReserve();
      };

      $scope.nextReserve = function () {
        if ($scope.busy) return;
        $scope.busy = true;
        var lastId = $scope.reserve.length?$scope.reserve[$scope.reserve.length -1].id:$scope.notifies[$scope.notifies.length -1].id;

        NotificationManager.getAll({id: $scope.start,where: $scope.count, what: lastId}, function (res) {
          if(!res.userNotifications.length){
            $scope.noNotification = true;
          } else {
            $scope.reserve = res.userNotifications;
            // $scope.start += $scope.count;
            $scope.request++;
            $scope.busy = false;
          }
        });
      };
      
      $scope.nextNotifications = function () {
        if ($scope.busy) return;
        $scope.busy = true;
        // $scope.noItem = false;

        if($scope.request){
          $scope.getReserve();
        } else {
          NotificationManager.getAll({id: $scope.start,where: $scope.count}, function (res) {
              // if get empty
              if(!res.userNotifications.length){
                $scope.noNotification = true;
              } else {
                $scope.notifies = $scope.notifies.concat(res.userNotifications);
                // $scope.start += $scope.count;
                $scope.request++;
                $scope.busy = false;
                $scope.nextReserve();
              }
          });
        }
      };
      
      $scope.getInterval = function (lastActivity) {
        var result = {'time' : -1, 'title' : null};

        if (!lastActivity) {
          return result;
        }

        var ms = moment().diff(moment(lastActivity));
        var d = moment.duration(ms),
          // y = Math.floor(d.asYears()),
          // m = Math.floor(d.asMonths()),
          dd = Math.floor(d.asDays()),
          h = Math.floor(d.asHours()),
          mm = Math.floor(d.asMinutes());

        // activity result
        if (!angular.isUndefined(d)) {
          if(dd > 1) {
            result = {'time': 0, 'title': 'datetime'};
          } else if(dd > 0) {
            result = {'time': 0 , 'title': 'yesterday'};
          } else  if(h > 0) {
            result = {'time': h , 'title': 'hr'};
          } else if(mm > 1){
            result = {'time': mm, 'title': 'minute'};
          } else {
            result = {'time': 1, 'title': 'now'};
          }
        }

        return result;
      };

      $scope.nextNotifications();

      $scope.bodyInHtml = function(body) {
        var words = body.split(" "),
          lastWord = words[words.length -1];
        return $sce.trustAsHtml(body.slice(0, -1 * lastWord.length) + "<a href='#'>"+ lastWord + "</a>");
      };

      $scope.delete = function(id, index){
        NotificationManager.delete({id: id}, function () {}, function () {
          toastr.error('Sorry! Your notification is not deleted');
        });
        $scope.notifies.splice(index, 1);
      };

      $scope.readAll = function(){
        NotificationManager.readAll({}, function () {}, function () {
          toastr.error('Sorry! Your notifications are not read');
        });
        angular.forEach($scope.notifies, function (not) {
          not.is_read = true;
        });
        if($scope.reserve.length){
          angular.forEach($scope.reserve, function (not) {
            not.is_read = true;
          });
        }
      };

      $scope.singleRead = function(id, index){
        NotificationManager.readSingle({id: id}, function () {}, function (res) {
          toastr.error('Sorry! Your notification is not read');
        });
        $scope.notifies[index].is_read = true;
      };

      $scope.goNotificationPage = function (notify, index) {
        $scope.singleRead(notify.id, index);
        $window.location.href = notify.notification.link;
      }

    }
  ]);