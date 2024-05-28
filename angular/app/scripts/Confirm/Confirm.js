'use strict';

angular.module('Confirm',['Interpolation', 'trans'])
    .directive('lsConfirm',['$window', '$http', '$compile', function($window, $http, $compile){
        return {
            restrict: 'EA',
            scope: {
                lsModalTitle: '@',
                lsHref: '@',
                lsText: '@',
                lsConfirm: '&'
            },
            link: function(scope, el){

                scope.yes = function(){
                    if(scope.lsHref){
                        $window.location.href = scope.lsHref;
                    }
                    else if(scope.lsConfirm){
                        scope.$eval(scope.lsConfirm);
                    }
                };

                el.bind('click',function(){
                    $http.get('/app/scripts/Confirm/confirm.html')
                        .success(function(res){
                            var tmp = $compile(res)(scope);

                            angular.element('body').append(tmp);
                            tmp.modal({
                                fadeDuration: 500
                            });

                            tmp.on($.modal.CLOSE, function(){
                                tmp.remove();
                            })
                        });
                })
            }
        }
    }]);