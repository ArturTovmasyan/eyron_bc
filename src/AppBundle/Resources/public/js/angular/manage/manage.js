'use strict';

angular.module('manage', ['Interpolation',
    'Components',
    'LocalStorageModule',
    'angular-cache',
    'angulartics',
    'ngResource',
    'goalManage',
    'angulartics.google.analytics',
    'PathPrefix',
    'Authenticator',
    'angular-cache'
    ])
    .value('socialInfo', {
        isSocial: '0'   
    })
    .config(function(CacheFactoryProvider){
      angular.extend(CacheFactoryProvider.defaults, {
          maxAge: 24 * 60 * 60 * 1000, // Items added to this cache expire after 15 minutes.
          cacheFlushInterval: 60 * 60 * 1000, // This cache will clear itself every hour.
          deleteOnExpire: 'aggressive', // Items will be deleted from this cache right when they expire.
          storageMode: 'localStorage' // This cache will use `localStorage`.
      });
    })
    .run(['$http', 'envPrefix', 'template', 'UserContext', 'CacheFactory', '$rootScope', 'UserGoalDataManager',
        function($http, envPrefix, template, UserContext, CacheFactory, $rootScope, UserGoalDataManager){
        var addUrl = envPrefix + "goal/add-modal",
            doneUrl = envPrefix + "goal/done-modal",
            commonUrl = envPrefix + "user/common",
            reportUrl = envPrefix + "user/report",
            goalUsersUrl = envPrefix + "goal/users",
            removeProfileUrl = envPrefix + "remove-profile/template",
            id = UserContext.id,
            locale = UserContext.locale,
            changedLanguage = false,
            cacheVersion = 15;

        var templateCache = CacheFactory.get('bucketlist_templates_v' + cacheVersion);

        if(!templateCache){
            templateCache = CacheFactory('bucketlist_templates_v' + cacheVersion, {
                maxAge: 3 * 24 * 60 * 60 * 1000 ,// 3 day,
                deleteOnExpire: 'aggressive'
            });
        }

        if(id){
            var addTemplate = templateCache.get('add-template'+id),
                doneTemplate = templateCache.get('done-template'+id),
                commonTemplate = templateCache.get('common-template'+id),
                reportTemplate = templateCache.get('report-template'+id),
                goalUsersTemplate = templateCache.get('goal-users-template'+id),
                removeProfileTemplate = templateCache.get('goal-remove-profile-template'+id),
                localeInCache = templateCache.get('locale-language'+id);

            if (localeInCache && localeInCache != locale) {
                changedLanguage = true;
            }
            
            templateCache.put('locale-language'+id, locale);

            if (!addTemplate || changedLanguage) {
                $http.get(addUrl).success(function(data){
                    template.addTemplate = data;
                    templateCache.put('add-template'+id, data);
                })
            }else {
                template.addTemplate = addTemplate;
            }

            if (!doneTemplate || changedLanguage) {
                $http.get(doneUrl).success(function(data){
                    template.doneTemplate = data;
                    templateCache.put('done-template'+id, data);
                })
            }else {
                template.doneTemplate = doneTemplate;
            }

            if (!commonTemplate || changedLanguage) {
                $http.get(commonUrl).success(function(data){
                    template.commonTemplate = data;
                    templateCache.put('common-template'+id, data);
                })
            }else {
                template.commonTemplate = commonTemplate;
            }

            if (!reportTemplate || changedLanguage) {
                $http.get(reportUrl).success(function(data){
                    template.reportTemplate = data;
                    templateCache.put('report-template'+id, data);
                })
            }else {
                template.reportTemplate = reportTemplate;
            }

            if (!goalUsersTemplate || changedLanguage) {
                $http.get(goalUsersUrl).success(function(data){
                    template.goalUsersTemplate = data;
                    templateCache.put('goal-users-template'+id, data);
                })
            }else {
                template.goalUsersTemplate = goalUsersTemplate;
            }
            
            if (!removeProfileTemplate || changedLanguage) {
                $http.get(removeProfileUrl).success(function(data){
                    template.removeProfileTemplate = data;
                    templateCache.put('goal-remove-profile-template'+id, data);
                })
            }else {
                template.removeProfileTemplate = removeProfileTemplate;
            }

        }
    }])
    .directive('lsGoalManage',['$compile',
        '$http',
        '$rootScope',
        'AuthenticatorLoginService',
        'template',
        'userGoalData',
        'UserGoalDataManager',
        '$timeout',
        'refreshingDate',
        function($compile, $http, $rootScope, AuthenticatorLoginService, template, userGoalData, UserGoalDataManager, $timeout, refreshingDate){
            return {
                restrict: 'EA',
                scope: {
                    lsGoalId: '@',
                    lsType: '@',
                    lsInitialRun: '=',
                    lsUserId: '@'
                },
                link: function(scope, el){

                    if(scope.lsInitialRun){
                        $timeout(function(){
                            scope.run();
                        }, 1000);
                    }

                    el.bind('click', function(){
                        scope.run();
                    });

                    scope.run = function(){
                        $(".modal-loading").show();

                        if(scope.lsType){
                            scope.closePrefix = false;
                            UserGoalDataManager.get({id: scope.lsGoalId}, function (uGoal){
                                scope.runCallback(uGoal);
                            }, function(res){
                                if(res.status === 401){
                                    window.location.href = "https://my.bucketlist127.com/login/add/" + scope.lsGoalId;
                                    // AuthenticatorLoginService.openLoginPopup();
                                    $(".modal-loading").hide();
                                }
                            });
                        }
                        else {
                            scope.closePrefix = true;
                            refreshingDate.goalId = scope.lsGoalId;
                            
                            if(scope.lsUserId){
                                //for refresh cache event
                                refreshingDate.userId = scope.lsUserId;
                            }
                            
                            if(scope.lsInitialRun){
                                UserGoalDataManager.get({id: scope.lsGoalId}, function (uGoal){
                                    if(uGoal.id){
                                        scope.runCallback(uGoal);
                                    }
                                    else {
                                        UserGoalDataManager.add({id: scope.lsGoalId}, {}, function (uGoal){
                                            scope.runCallback(uGoal);
                                        }) 
                                    }
                                })
                            }
                            else {
                                UserGoalDataManager.add({id: scope.lsGoalId}, {}, function (uGoal){
                                    scope.runCallback(uGoal);
                                }, function(res){
                                    if(res.status === 401){
                                        window.location.href = "https://my.bucketlist127.com/login/add/" + scope.lsGoalId;
                                        // AuthenticatorLoginService.openLoginPopup();
                                        $(".modal-loading").hide();
                                    }
                                });
                            }
                        }
                    };

                    scope.runCallback = function(uGoal){
                        userGoalData.data = uGoal;
                        if(userGoalData.data.do_date){
                            // userGoalData.data.do_date = moment(userGoalData.data.do_date).format('MM-DD-YYYY');
                        }

                        var sc = $rootScope.$new();
                        var tmp = $compile(template.addTemplate)(sc);
                        scope.openModal(tmp);
                        $(".modal-loading").hide();
                    };

                    $rootScope.$on('addGoal', function(){
                        scope.isSave = false;
                    });

                    $rootScope.$on('lsJqueryModalClosedSaveGoal', function(){
                        scope.isSave = true;
                    });

                    scope.openModal = function(tmp){

                        scope.isSave = false;
                        angular.element('body').append(tmp);
                        tmp.modal({
                            fadeDuration: 300
                        });

                        tmp.on($.modal.CLOSE, function(){
                            if(scope.closePrefix){
                                $timeout(function () {
                                    if(!scope.isSave){
                                        UserGoalDataManager.creates({id: scope.lsGoalId}, {is_visible: true}, function (resource){
                                            // userGoalData.data = resource;
                                        });
                                    } else {
                                        scope.isSave = false;
                                    }
                                }, 1000);
                            }
                            tmp.remove();
                        })
                    }

                }
            }
    }])
  .directive('lsUserGoalManage',['$compile',
      '$http',
      '$rootScope',
      'AuthenticatorLoginService',
      'template',
      'userGoalData',
      'UserGoalDataManager',
      '$timeout',
      'refreshingDate',
      function($compile, $http, $rootScope, AuthenticatorLoginService, template, userGoalData, UserGoalDataManager, $timeout, refreshingDate){
          return {
              restrict: 'EA',
              scope: {
                  lsGoalId: '@',
                  lsType: '@',
                  lsInitialRun: '=',
                  lsUserId: '@'
              },
              link: function(scope, el){
    
                  if(scope.lsInitialRun){
                      $timeout(function(){
                          scope.run();
                      }, 1000);
                  }
    
                  el.bind('click', function(){
                      scope.run();
                  });
    
                  scope.run = function(){
                      $(".modal-loading").show();
    
                      if(scope.lsType){
                          UserGoalDataManager.getStory({id: scope.lsGoalId}, function (uGoal){
                              userGoalData.manage = "done";
                              scope.runCallback(uGoal);
                          }, function(res){
                              if(res.status === 401){
                                  window.location.href = "https://my.bucketlist127.com/done/add/" + scope.lsGoalId;
                                  // AuthenticatorLoginService.openLoginPopup();
                                  $(".modal-loading").hide();
                              }
                          });
                      }
                      else {
                          userGoalData.manage = "";
                          refreshingDate.goalId = scope.lsGoalId;
    
                          if(scope.lsUserId){
                              //for refresh cache event
                              refreshingDate.userId = scope.lsUserId;
                          }
                          
                          UserGoalDataManager.done({id: scope.lsGoalId}, function (){
                              UserGoalDataManager.getStory({id: scope.lsGoalId}, function (goal) {
                                  scope.runCallback(goal);
                              });
                          }, function(res){
                              if(res.status === 401){
                                  window.location.href = "https://my.bucketlist127.com/login/done/" + scope.lsGoalId;
                                  // AuthenticatorLoginService.openLoginPopup();
                                  $(".modal-loading").hide();
                              }
                          });
                      }
                  };
    
                  scope.runCallback = function(uGoal){
                      userGoalData.doneData = uGoal;
                      userGoalData.doneData.videos_array = [];

                      if(uGoal.story && uGoal.story.video_link.length > 0){
                          angular.forEach(userGoalData.doneData.story.video_link, function(v){
                              userGoalData.doneData.videos_array.push({link: v});
                          });
                      }else {
                          userGoalData.doneData.videos_array.push({});
                      }

                      var sc = $rootScope.$new();
                      var tmp = $compile(template.doneTemplate)(sc);
                      scope.openModal(tmp);
                      $(".modal-loading").hide();
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
  }])
  .directive('lsCommonManage',['$compile',
      '$http',
      '$rootScope',
      'AuthenticatorLoginService',
      'template',
      'userData',
      'UserGoalDataManager',
      function($compile, $http, $rootScope, AuthenticatorLoginService, template, userData, UserGoalDataManager){
          return {
              restrict: 'EA',
              scope: {
                  lsUser: '@'
              },
              link: function(scope, el){

                  el.bind('click', function(){
                      scope.run();
                  });

                  scope.run = function(){
                      $(".modal-loading").show();

                      UserGoalDataManager.common({id: scope.lsUser}, function (data){
                          userData.data = data.goals;
                          scope.runCallback();
                      }, function(res){
                          if(res.status === 401){
                              AuthenticatorLoginService.openLoginPopup();
                              $(".modal-loading").hide();
                          }
                      });
                  };

                  scope.runCallback = function(){
                      var sc = $rootScope.$new();
                      var tmp = $compile(template.commonTemplate)(sc);
                      scope.openModal(tmp);
                      $(".modal-loading").hide();
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
  .directive('lsGoalUsers',['$compile',
    '$http',
    '$rootScope',
    'AuthenticatorLoginService',
    'template',
    'userData',
    'UserGoalDataManager',
    'UserContext',
    '$timeout',
    function($compile, $http, $rootScope, AuthenticatorLoginService, template, userData, UserGoalDataManager, UserContext, $timeout){
        return {
            restrict: 'EA',
            scope: {
                lsGoalId: '@',
                lsItemId: '@',
                lsCount: '@',
                lsCategory: '@'
            },
            link: function(scope, el){

                el.bind('click', function(){
                    scope.run();
                });

                scope.run = function(){
                    $(".modal-loading").show();

                    if(UserContext.id){
                        userData.type = scope.lsCategory?scope.lsCategory: 2;
                        userData.itemId = scope.lsGoalId?scope.lsGoalId:scope.lsItemId;
                        userData.usersCount = scope.lsCount;
                        scope.runCallback();
                    } else {
                        AuthenticatorLoginService.openLoginPopup();
                        $(".modal-loading").hide();
                    }
                };

                scope.runCallback = function(){
                    var sc = $rootScope.$new();
                    var tmp = $compile(template.goalUsersTemplate)(sc);
                    $timeout(function(){
                        $(".modal-loading").hide();
                        scope.openModal(tmp);
                    }, 500);
                };

                scope.openModal = function(tmp){

                    angular.element('body').append(tmp);
                    tmp.modal({
                        fadeDuration: 300
                    });

                    tmp.on($.modal.CLOSE, function(){
                        $rootScope.$broadcast('lsGoalUsersModalClosed');
                        tmp.remove();
                    })
                }
            }
        }
    }
  ])
  .directive('videoLink', ['$sce', function($sce){
      return {
          restrict: 'EA',
          scope: {
              array: '=',
              key: '=',
              link: '=',
              limit: '='
          },
          templateUrl: '/bundles/app/htmls/videoLink.html',
          link: function(scope){

              scope.lm = scope.limit ? scope.limit : 3;

              scope.$watch('link',function(d){
                  if(angular.isUndefined(d)){
                      return;
                  }

                  if(d === ''){
                      scope.removeItem();
                  }
                  else {
                      if(!scope.array[scope.key + 1] && Object.keys(scope.array).length < scope.lm){
                          scope.array[scope.key + 1] = {};
                      }
                  }
              }, true);

              scope.removeItem = function(){
                  if(scope.array[scope.array.length - 1].link){
                      scope.array[scope.array.length] = {};
                  }

                  if(scope.key === 0){
                      if(scope.array.length > 1){
                          scope.array.splice(scope.key, 1);
                      }
                  }
                  else {
                      scope.array.splice(scope.key, 1);
                  }
              };

              scope.isVideoLink = function(url){
                  return !(!angular.isString(url) || url.indexOf("https:/") == -1);
              };

              scope.trustedUrl = function(url){
                  return $sce.trustAsResourceUrl(url);
              };
          }
      }
  }])
  .directive('step',[function(){
      return {
          restrict: 'EA',
          scope: {
              ngModel: '=',
              array: '=',
              key: '='
          },
          compile: function(){
              return function(scope){
                  scope.$watch('ngModel',function(d){
                      if(angular.isUndefined(d)){
                          return;
                      }

                      if(d === ''){
                          if(scope.key === 0){
                              if(scope.array.length > 1) {
                                  scope.array.splice(scope.key, 1);
                              }
                          }
                          else {
                              scope.array.splice(scope.key, 1);
                          }
                      }
                      else {
                          if(!scope.array[scope.key + 1]) {
                              scope.array[scope.key + 1] = {};
                          }
                      }
                  },true);
              }
          }
      }
  }])
  .directive('blRemoveProfile',['$compile',
    '$http',
    '$rootScope',
    'template',
    'socialInfo',
    function($compile, $http, $rootScope, template, socialInfo){
        return {
            restrict: 'EA',
            scope: {
                blIsSocial: '='
            },
            link: function(scope, el){

                el.bind('click', function(){
                    scope.run();
                });

                socialInfo.isSocial = scope.blIsSocial;
                scope.run = function(){
                    $(".modal-loading").show();
                    var sc = $rootScope.$new();
                    var tmp = $compile(template.removeProfileTemplate)(sc);
                    scope.openModal(tmp);
                    $(".modal-loading").hide();
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
  ]);


$(function(){
    jQuery('img.svg').each(function(){
        var $img = jQuery(this);
        var imgID = $img.attr('id');
        var imgClass = $img.attr('class');
        var imgURL = $img.attr('src');

        jQuery.get(imgURL, function(data) {
            // Get the SVG tag, ignore the rest
            var $svg = jQuery(data).find('svg');

            // Add replaced image's ID to the new SVG
            if(typeof imgID !== 'undefined') {
                $svg = $svg.attr('id', imgID);
            }
            // Add replaced image's classes to the new SVG
            if(typeof imgClass !== 'undefined') {
                $svg = $svg.attr('class', imgClass+' replaced-svg');
            }

            // Remove any invalid XML tags as per http://validator.w3.org
            $svg = $svg.removeAttr('xmlns:a');

            // Check if the viewport is set, else we gonna set it if we can.
            if(!$svg.attr('viewBox') && $svg.attr('height') && $svg.attr('width')) {
                $svg.attr('viewBox', '0 0 ' + $svg.attr('height') + ' ' + $svg.attr('width'))
            }

            // Replace image with new SVG
            $img.replaceWith($svg);

        }, 'xml');

    });
});