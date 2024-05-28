'use strict';

angular.module('Components',[])
    .service('loginPopoverService', ['$timeout', '$rootScope', function($timeout, $rootScope){

        function openLoginPopover(dir){
            $timeout(function(){
                var logged = angular.element(".user");
                var notLogged = angular.element('.sign-in-popover');
                var openPopover = function(notLogged, logged, dir){
                    var middleScopeSignIn = notLogged.scope();
                    var popoverScopeSignIn = middleScopeSignIn.$$childTail;

                    if(logged.length) {
                        var middleScopeSigned = logged.scope();
                        var popoverScopeSigned = middleScopeSigned.$$childTail;
                    }

                    if(!dir) {
                        if (!popoverScopeSignIn.$isShown) {
                            popoverScopeSignIn.$show();
                            middleScopeSignIn.joinToggle2 = !middleScopeSignIn.joinToggle2;
                        }

                        if (popoverScopeSigned && popoverScopeSigned.$isShown) {
                            popoverScopeSigned.$hide();
                            middleScopeSigned.joinToggle1 = !middleScopeSigned.joinToggle1;
                        }
                    }
                    else {
                        if (popoverScopeSigned && !popoverScopeSigned.$isShown) {
                            popoverScopeSigned.$show();
                            middleScopeSigned.joinToggle1 = !middleScopeSigned.joinToggle1;
                        }

                        if (popoverScopeSignIn.$isShown) {
                            popoverScopeSignIn.$hide();
                            middleScopeSignIn.joinToggle2 = !middleScopeSignIn.joinToggle2;
                        }
                    }
                };

                if(logged.length){
                    if(!dir) {
                        logged.hide();
                    }
                    else {
                        logged.fadeIn();
                    }
                }

                if(notLogged.length) {

                    if(!dir) {
                        notLogged.fadeIn();
                    }
                    else {
                        notLogged.hide();
                    }

                    $rootScope.$on('openClosePopover', function(){
                        openPopover(notLogged, logged, dir);
                    });
                    $timeout(function(){
                        openPopover(notLogged, logged, dir);
                    },1500);
                }
            }, 300);
        }

        return {
            openLoginPopover: openLoginPopover
        }
    }])
    .directive('ngEnterSubmit',[function(){
        return {
            restrict: 'EA',
            scope: {
                ngEnterSubmit: '@'
            },
            link: function(scope, el){

                el.bind('keyup',function(ev){

                    if(ev.which === 13 && !ev.shiftKey){
                        angular.element(scope.ngEnterSubmit).submit();
                    }
                })
            }
        }
    }])
    .directive('openPopover',['$timeout', 'loginPopoverService', function($timeout, loginPopoverService){
        return {
            restrict: 'EA',
            scope: {
                dir: '='
            },
            compile: function(){
                return function(scope){
                    loginPopoverService.openLoginPopover(scope.dir);
                }
            }
        }
    }])
    .directive('lsScrollTo',[function(){
        return {
            restrict: 'EA',
            scope: {
                targetSelector: '@'
            },
            link: function(scope, el){
                el.bind('click', function(){
                    var target = angular.element(scope.targetSelector);
                    angular.element(window).scrollTo(target, 700);
                })
            }
        }
    }])
    .directive('lsJqueryModal',['$compile',
        '$http',
        '$rootScope',
        'loginPopoverService',
        function($compile, $http, $rootScope, loginPopoverService){
        return {
            restrict: 'EA',
            scope: {
                lsTemplate: '@',
                lsTemplateUrl: '@',
                lsIdentity: '@'
            },
            link: function(scope, el){

                el.bind('click', function(){
                    scope.run();
                });

                // for non angular events
                el.on('openLsModal', function(event, dataId){
                    if(dataId === scope.lsIdentity){
                        scope.run();
                        scope.$apply();
                    }
                });

                // for angular events
                scope.$on('openLsModal', function(event, dataId){
                    if(dataId === scope.lsIdentity){
                        scope.run();
                    }
                });

                scope.run = function(){
                    if(scope.lsTemplate){
                        var tmp = $compile(scope.lsTemplate)(scope);
                        scope.openModal(tmp);
                    }
                    else if(scope.lsTemplateUrl){
                        $http.get(scope.lsTemplateUrl)
                            .success(function(res){
                                var tmp = $compile(res)(scope);
                                scope.openModal(tmp);
                            })
                            .error(function(res, status){
                                if(status === 401) {
                                    loginPopoverService.openLoginPopover();
                                }
                            });
                    }
                };

                scope.openModal = function(tmp){

                    angular.element('body').append(tmp);
                    tmp.modal({
                        fadeDuration: 300
                    });

                    $rootScope.$broadcast('lsJqueryModalOpened' + scope.lsIdentity);
                    el.trigger('lsJqueryModalOpened' + scope.lsIdentity);

                    tmp.on($.modal.CLOSE, function(){
                        tmp.remove();
                        $rootScope.$broadcast('lsJqueryModalClosed' + scope.lsIdentity);
                        el.trigger('lsJqueryModalClosed' + scope.lsIdentity);
                    })
                }

            }
        }
    }])
    .directive('lsFileUploadPreview',[function(){
        return {
            restrict: 'EA',
            scope: {
                imageUrl: '='
            },
            link: function(scope, el){

                scope.readURL = function(input) {

                    if (input.files && input.files[0]) {
                        var reader = new FileReader();

                        reader.onload = function (e) {
                            scope.imageUrl = e.target.result;
                            scope.$apply();
                        };

                        reader.readAsDataURL(input.files[0]);
                    }
                };

                el.change(function(){
                    scope.readURL(el[0]);
                });

            }
        }
    }])
    .directive('lsChecked',[function(){
        return {
            restrict: 'EA',
            require: '^ngModel',
            link: function(scope, el, attr, ngModel){

                scope.$watch(attr.lsChecked, function(){
                    var val = scope.$eval(attr.lsChecked);
                    if(val) {
                        el.prop('checked', val);
                        el.val(val);
                    }
                }, true);

                el.change(function(){
                    ngModel.$setViewValue(el.is(':checked') ? 1:0);
                    scope.$apply();
                });
            }
        }
    }])
    .directive('lsMobileOnMenuClick',['$timeout', function($timeout){
        return {
            restrict: 'EA',
            scope: {
                lsOpen: '=',
                lsTrigger: '='
            },
            link: function(scope, el){
                el.bind('click',function(){
                    if(!scope.lsTrigger){
                        $timeout(function(){
                            scope.$emit('openClosePopover');
                        }, 10);
                    }

                    scope.lsTrigger = !scope.lsTrigger;
                    scope.$apply();
                });
            }
        }
    }])
    .animation('.slide', function() {
        var NG_HIDE_CLASS = 'ng-hide';
        return {
            beforeAddClass: function(element, className, done) {
                if(className === NG_HIDE_CLASS) {
                    element.slideUp(done);
                }
            },
            removeClass: function(element, className, done) {
                if(className === NG_HIDE_CLASS) {
                    element.hide().slideDown(done);
                }
            }
        }
    });