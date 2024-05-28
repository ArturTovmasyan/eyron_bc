'use strict';

angular.module('Authenticator', ['PathPrefix', 'Interpolation'])
  .config(['$httpProvider', function($httpProvider){
    //$httpProvider.interceptors.push('AuthenticatorInterceptor');
  }])
  .run(['$rootScope', 'AuthenticatorLoginService', function($rootScope, AuthenticatorLoginService){
    console.log("run");

    $rootScope.$on('openLoginPopup', function(){
      AuthenticatorLoginService.openLoginPopup();
    });

  }])
  .service('AuthenticatorLoginService', [
    '$http',
    '$compile',
    '$rootScope',
    'envPrefix',
    '$httpParamSerializer',
    function(
      $http,
      $compile,
      $rootScope,
      envPrefix,
      $httpParamSerializer
    ){

    function openModal(tmp){
      var scope = $rootScope.$new();

      tmp = $compile(tmp)(scope);

      angular.element('body').append(tmp);
      tmp.modal({
        fadeDuration: 300
      });

      tmp.on($.modal.CLOSE, function(){
        tmp.remove();
      });
    }

    return {
      openLoginPopup: function(){
        window.location.href = "https://my.bucketlist127.com/login";
        // $http.get('/login')
        //   .success(function(res){
        //     openModal(angular.element(res));
        //     $rootScope.$broadcast('showAuthenticatorLoginButton', true)
        //   });
      },
      login: function(data){
        return $http({
          method: 'POST',
          url: envPrefix + 'api/v1.0/users/logins',
          data: $httpParamSerializer(data),
          headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        })
      }
    }
  }])
  .service('AuthenticatorInterceptor', ['$q', function($q){
    return {
      request: function(config){
        return config;
      },
      response: function(config){
        return config;
      },
      responseError: function(config){
        return $q.reject(config)
      }
    }
  }])
  .directive('authenticatorLoginTrigger',[
    'AuthenticatorLoginService',
    function(
      AuthenticatorLoginService
    ){
    return {
      restrict: 'EA',
      scope: {},
      link: function(scope, el){
        scope.$on('showAuthenticatorLoginButton', function(event, data){
          if(data){
            el.fadeIn(300);
            $('.user-popover').hide();
          }
          else {
            el.fadeOut(300);
            $('.user-popover').show();
          }
        });

        el.click(function(){
          // AuthenticatorLoginService.openLoginPopup();
          window.location.href = "https://my.bucketlist127.com/login";
        });
      }
    }
  }])
  .controller('AuthenticatorLoginController', [
    '$scope',
    'AuthenticatorLoginService',
    'envPrefix',
    '$timeout',
    '$http',
    function(
      $scope,
      AuthenticatorLoginService,
      envPrefix,
      $timeout,
      $http
    ){

    $scope.envPrefix  = envPrefix;
    $scope.login_form = {};

    $timeout(function(){
      angular.element("#login-form").ajaxForm({
        beforeSubmit: function(){
          $scope.$apply();
        },
        success: function(res, text, header){
          window.location.reload();
        },
        error: function(res){
          $scope.error = 'Bad credentials';
          $scope.$apply();
        }
      });
    },500);

    // $scope.login = function(){
    //   AuthenticatorLoginService.login($scope.login_form)
    //     .success(function(){
    //       window.location.reload();
    //     })
    //     .error(function(res){
    //       $scope.error = res;
    //     });
    // }
  }]);
























