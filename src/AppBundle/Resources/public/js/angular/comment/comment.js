'use strict';

angular.module('comments', ['Interpolation',
  'Components',
  'ngResource',
  'goalManage',
  'PathPrefix',
  'trans'
]).directive('lsCommentManage',['$compile',
  '$rootScope',
  '$timeout',
  'CommentManager',
  'UserContext',
  function($compile, $rootScope, $timeout, CommentManager, UserContext){
    return {
      restrict: 'EA',
      scope: {
        lsGoalId: '@',
        lsBlogId: '@',
        lsTitle: '@',
        lsSlug: '@',
        lsInner: '@',
        lsReply: '@',
        lsReplied: '@',
        lsLogged: '@',
        lsReportTitle: '@',
        lsUserImage: '@'
      },
      templateUrl: '/bundles/app/htmls/comment.html',
      link: function(scope, el){
        var showStepCount = 5;
        var forEnd = 0;
        var busy = false;
        scope.currentUserId = UserContext.id;
        scope.commentsDefaultCount = 5;

        CommentManager.comments({path: 'comments', param1:(scope.lsGoalId?'goal_':'blog_') + scope.lsSlug}, function (resource){
          scope.comments = resource;
          scope.commentsLength = scope.comments.length - scope.commentsDefaultCount;

          if(scope.lsBlogId){
            $timeout(function () {
              window.parent.postMessage({
                sentinel: 'amp',
                type: 'embed-size',
                height: document.body.scrollHeight
              }, '*');
            }, 1000);
          }
        });
        
        scope.showMoreComment = function () {
          if(scope.commentsLength === forEnd){
            return;
          }
          if(angular.isUndefined(scope.commentIndex)){
            scope.commentIndex = scope.comments.length - scope.commentsDefaultCount - 1;
          }

          var startIndex = scope.commentIndex;

          if(scope.commentsLength > showStepCount){
            scope.commentsLength -= showStepCount;
            scope.commentIndex -= showStepCount;
          } else {
            scope.commentIndex -= scope.commentsLength;
            scope.commentsLength = forEnd;
          }

          for(var i = startIndex; i > scope.commentIndex; i--){
            scope.comments[i].visible = true;
          }

          if(scope.lsBlogId) {
            $timeout(function () {
              window.parent.postMessage({
                sentinel: 'amp',
                type: 'embed-size',
                height: document.body.scrollHeight
              }, '*');
            }, 1000);
          }
        };

        scope.writeReply = function(ev, comment){
          if(ev.which == 13 && comment.replyBody.length) {
            ev.preventDefault();
            ev.stopPropagation();
            if(!busy) {
              busy = true;
              CommentManager.add({
                param1: (scope.lsGoalId?scope.lsGoalId:scope.lsBlogId),
                param2: comment.id,
                path: (scope.lsGoalId?'comments':'blog-comment')
              }, {'commentBody': comment.replyBody}, function (data) {
                comment.reply = true;
                comment.replyBody = '';
                busy = false;
                comment.children.push(data);

                if(scope.lsBlogId) {
                  $timeout(function () {
                    window.parent.postMessage({
                      sentinel: 'amp',
                      type: 'embed-size',
                      height: document.body.scrollHeight
                    }, '*');
                  }, 1000);
                }
              });
            }
          }
        };
        
        scope.writeComment = function (ev) {
          if(ev.which == 13 && scope.commentBody.length){
            ev.preventDefault();
            ev.stopPropagation();
            if(!busy){
              busy = true;
              CommentManager.add({param1:(scope.lsGoalId?scope.lsGoalId:scope.lsBlogId), path: (scope.lsGoalId?'comments':'blog-comment')}, {'commentBody': scope.commentBody},function (data) {
                scope.commentBody = '';
                busy = false;
                scope.comments.push(data);

                if(scope.lsBlogId) {
                  $timeout(function () {
                    window.parent.postMessage({
                      sentinel: 'amp',
                      type: 'embed-size',
                      height: document.body.scrollHeight
                    }, '*');
                  }, 1000);
                }

              });
            }
          }
        }
        
      }
    }
  }])
  .directive('lsReport',['$compile',
    '$http',
    '$rootScope',
    'template',
    'userData',
    'envPrefix',
    function($compile, $http, $rootScope, template, userData, envPrefix){
      return {
        restrict: 'EA',
        scope: {
          lsType: '@',
          lsComment: '@'
        },
        link: function(scope, el){

          el.bind('click', function(){
            scope.run();
          });

          scope.run = function(){
            $(".modal-loading").show();
            userData.report = {
              type: scope.lsType,
              comment: scope.lsComment
            };
            scope.runCallback()
          };

          scope.runCallback = function(){
            var sc = $rootScope.$new();

            if (!template.reportTemplate) {
              var reportUrl = envPrefix + "user/report";
              $http.get(reportUrl).success(function(data){
                template.reportTemplate = data;
                var tmp = $compile(template.reportTemplate)(sc);
                scope.openModal(tmp);
                $(".modal-loading").hide();
              })
            } else {
              var tmp = $compile(template.reportTemplate)(sc);
              scope.openModal(tmp);
              $(".modal-loading").hide();
            }
          };

          scope.openModal = function(tmp){

            angular.element('body').append(tmp);
            tmp.modal({
              fadeDuration: 300
            });

            tmp.on($.modal.CLOSE, function(){
              tmp.remove();
            })
          }

        }
      }
    }
  ])
  .controller('reportController',['$scope', 'userData', 'UserGoalDataManager', '$timeout',
  function ($scope, userData, UserGoalDataManager, $timeout) {
    $scope.reportDate = {};
    $scope.reportDate.contentId = userData.report.comment;
    $scope.reportDate.contentType = userData.report.type;
    $scope.isReported = false;
    UserGoalDataManager.getReport({type: userData.report.type, commentId: userData.report.comment}, function (data) {
      if(data.content_id){
        $scope.reportOption = data.report_type?data.report_type:null;
        $scope.reportText = data.message?data.message:'';
      }
    });

    $scope.report = function(){
      if(!($scope.reportOption || $scope.reportText))return;

      $scope.reportDate.reportType = $scope.reportOption?$scope.reportOption:null;
      $scope.reportDate.message = $scope.reportText?$scope.reportText:null;

      UserGoalDataManager.report({}, $scope.reportDate, function () {
        $scope.isReported = true;
        $timeout(function(){
          $('#report-modal .close-icon').click();
        },1500);
      })
    }

  }
]);
