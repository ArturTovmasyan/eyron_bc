'use strict';

angular.module('goal')
  .controller('goalAddToMe', ['$scope',
    '$timeout',
    '$window',
    'UserGoal',
    'Goal',
    '$http',
    function($scope, $timeout, $window, UserGoalConstant, GoalConstant, $http){

      $scope.GoalConstant = GoalConstant;
      $scope.UserGoalConstant = UserGoalConstant;

      $http.get('/api/v1.0/usergoals/60').success(function(userGoal){
        console.log(userGoal, 'User Goal');
      });

      $scope.stepsArray = [{}];

      var switchChanged = false;
      var dateChanged = false;
      var isSuccess = false;

      $scope.$watch('complete.switch', function (d) {
        if( d !== 0 && d !== 1){
          switchChanged = !switchChanged
        }else {
          if(angular.element('#success' + $scope.goalId).length > 0) {
            isSuccess = angular.element('#success' + $scope.goalId).scope()['success' + $scope.goalId]?true:false;
          }
        }
      });

      $scope.openSignInPopover = function(){
        var middleScope = angular.element(".sign-in-popover").scope();
        var popoverScope = middleScope.$$childHead;

        if(!popoverScope.$isShown){
          popoverScope.$show();
          middleScope.joinToggle2 = !middleScope.joinToggle2;
        }
      };

      $scope.initSteps = function(json){
        $scope.stepsArray = json;
      };

      $scope.removeLocation = function(){
        angular.element(".location .location-hidden").val(null);
        angular.element(".location .location-hidden").attr('value',null);
        angular.element(".location .place-autocomplete").val('');
      };


      $timeout(function(){
        var doDate = angular.element(".hidden_date_value").val();
        angular.element('#goal-create-form').attr('data-goal-id', $scope.goalId);
        angular.element("#goal-add-form").ajaxForm({
          beforeSubmit: function(){
            var selector = 'success' + $scope.goalId;
            if(angular.element('#'+ selector).length > 0) {
              var parentScope = angular.element('#' + selector).scope();
              //if goal status changed
              if (switchChanged) {
                parentScope[selector] = !parentScope[selector];
                //if goal changed  from success to active
                if (isSuccess) {
                  //and date be changed
                  if (dateChanged && doDate) {
                    //change  doDate
                    parentScope['change' + $scope.goalId] = 2;
                    parentScope['doDate' + $scope.goalId] = new Date(doDate);
                    angular.element('.goal' + $scope.goalId).addClass("active-idea");
                  } else {
                    if(doDate){
                      parentScope['change' + $scope.goalId] = 2;
                      parentScope['doDate' + $scope.goalId] = new Date(doDate);
                      angular.element('.goal' + $scope.goalId).addClass("active-idea");
                    }else {
                      //infinity
                      parentScope['change' + $scope.goalId] = 1;
                      angular.element('.goal' + $scope.goalId).removeClass("active-idea");
                    }
                  }
                } else {
                  //new datetime for completed
                  parentScope['change' + $scope.goalId] = 2;
                  angular.element('.goal' + $scope.goalId).removeClass("active-idea");
                  parentScope['doDate' + $scope.goalId] = new Date();
                }
              } else {
                if (!isSuccess && dateChanged && doDate) {
                  //change for doDate
                  parentScope['change' + $scope.goalId] = 2;
                  parentScope['doDate' + $scope.goalId] = new Date(doDate);
                  angular.element('.goal' + $scope.goalId).addClass("active-idea");
                }
              }
            }
            $scope.$apply();
          },
          success: function(res, text, header){
            if(header.status === 200){
              angular.element('#cancel').click();
              $scope.$apply();

            }
          }
        });
        angular.element("#goal-add-for-create-form").ajaxForm({
          beforeSubmit: function(){
            $scope.$apply();
          },
          success: function(res, text, header){
            if(header.status === 200){
              $window.location.href = $scope.redirectPath;
              $scope.$apply();
            }
          }
        });
        angular.element('#datepicker').datepicker({
          beforeShowDay: function(){
            var cond = angular.element('#datepicker').data('datepicker-disable');
            if(cond){
              return false;
            }
            else {
              return true;
            }
          },
          todayHighlight: true
        });
        angular.element('#secondPicker').datepicker({
          beforeShowDay: function(){
            var cond = angular.element('#datepicker').data('datepicker-disable');
            if(cond){
              return false;
            }
            else {
              return true;
            }
          },
          todayHighlight: true
        });

        angular.element("#secondPicker").find( "td" ).removeClass("active");

        angular.element("#datepicker").on("changeDate", function() {
          angular.element("#secondPicker").find( "td" ).removeClass("active");
          $scope.datepicker_title = true;
          doDate =  angular.element("#datepicker").datepicker('getFormattedDate');
          angular.element(".hidden_date_value").val(doDate);
          dateChanged = true;
          $scope.$apply();
        });
        angular.element("#secondPicker").on("changeDate", function() {
          angular.element("#datepicker").find( "td" ).removeClass("active");
          $scope.datepicker_title = true;
          doDate = angular.element("#secondPicker").datepicker('getFormattedDate');
          angular.element(".hidden_date_value").val(doDate);
          dateChanged = true;
          $scope.$apply();
        });

        angular.element('input.important-radio').iCheck({
          radioClass: 'iradio_minimal-purple',
          increaseArea: '20%'
        }).on('ifChanged', function (event) {
          var target = angular.element(event.target);
          angular.element(".priority-radio").removeClass('active-important');
          target.parents().closest('.priority-radio').addClass('active-important');

          target.trigger('change');
        });

      }, 500);

    }])