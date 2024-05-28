'use strict';

angular.module('goal', ['Interpolation',
        'Google',
        'user',
        'manage',
        'Authenticator',
        'profile',
        'goalComponents',
        'mgcrea.ngStrap.popover',
        'ngAnimate',
        'ngSanitize',
        'infinite-scroll',
        'Confirm',
        'videosharing-embed',
        'Components',
        'LocalStorageModule',
        'angular-cache',
        'ngResource',
        'angulartics',
        'angulartics.google.analytics',
        'PathPrefix',
        'slickCarousel',
        'notification',
        'activity',
        'adds',
        'comments',
        'videoComponent',
        'trans'
    ])
    .config(function (localStorageServiceProvider ) {
        localStorageServiceProvider
            .setPrefix('goal')
            .setNotify(false, false);
    })
    .config(['$httpProvider', function($httpProvider){
        $httpProvider.interceptors.push([function() {
            return {
                'response': function(response) {
                    // same as above
                    if(response.config.url.indexOf('/api/v2.0/activities/') !== -1){
                        angular.forEach(response.data, function(v){
                            if(v.goals.length > 2) {
                                v.reserveGoals = [v.goals[0], v.goals[1]];
                            } else {
                                v.reserveGoals = v.goals
                            }
                        });
                    }

                    return response;
                }
            };
        }]);
    }])
    .config(function(CacheFactoryProvider){
        angular.extend(CacheFactoryProvider.defaults, {
            maxAge: 24 * 60 * 60 * 1000, // Items added to this cache expire after 15 minutes.
            cacheFlushInterval: 60 * 60 * 1000, // This cache will clear itself every hour.
            deleteOnExpire: 'aggressive', // Items will be deleted from this cache right when they expire.
            storageMode: 'localStorage' // This cache will use `localStorage`.
        });
    })
    .factory('lsInfiniteItems', ['$http', 'localStorageService', 'envPrefix', '$analytics', function($http, localStorageService, envPrefix, $analytics) {
        var lsInfiniteItems = function(loadCount, storage_name) {
            this.items = [];
            this.busy = false;
            this.noItem = false;
            this.category = "";
            this.nearByPath = envPrefix + "api/v1.0/goals/nearby/{latitude}/{longitude}/{first}/{count}/{isCompleted}";
            this.isReset = false;
            this.request = 0;
            this.start = 0;
            this.reserve = [];
            this.storage_name = storage_name ? storage_name : 'reserved_items';
            this.count = loadCount ? loadCount : 7;
        };

        lsInfiniteItems.prototype.loadAddthis = function(){
            var olds = $('script[src="http://s7.addthis.com/js/300/addthis_widget.js#domready=1"]');
            olds.remove();

            var addthisScript = document.createElement('script');
            addthisScript.setAttribute('src', 'http://s7.addthis.com/js/300/addthis_widget.js#domready=1');
            return document.body.appendChild(addthisScript);
        };

        lsInfiniteItems.prototype.loadRandomItems = function(count){

        };

        lsInfiniteItems.prototype.reset = function(){
            this.isReset = true;
            this.items = [];
            this.reserve = [];
            this.busy = false;
            this.request = 0;
            this.start = 0;
            this.category = "";
        };

        lsInfiniteItems.prototype.nearBy = function(position){
            if (this.busy || angular.isUndefined(position) || !position.latitude || !position.longitude) {
                return;
            }

            var url = this.nearByPath.replace('{latitude}', position.latitude).replace('{longitude}', position.longitude).replace('{first}', this.start).replace('{count}', this.count).replace('{isCompleted}', position.isCompleted);

            $http.get(url).success(function(data) {
                if(!data.length){
                    this.noItem = true;
                }
                
                this.start += this.count;
                this.reserve = this.items.concat(data);
                this.busy = data.length ? false : true;
                this.getNearByReserve(position);
            }.bind(this));
        };

        lsInfiniteItems.prototype.getNearByReserve = function(position) {
            var url = this.nearByPath.replace('{latitude}', position.latitude).replace('{longitude}', position.longitude).replace('{first}', this.start).replace('{count}', this.count).replace('{isCompleted}', position.isCompleted);
            this.items = this.items.concat(this.reserve);

            $http.get(url).success(function(data) {
                this.reserve = data;
                this.busy = data.length ? false : true;
                this.start += this.count;
            }.bind(this));
            
            $analytics.eventTrack('Load more in select category', {  category: 'Goal', label: 'Load more in category nearBy from Web' });

        };

        lsInfiniteItems.prototype.getReserve = function(url, search, category) {
            this.items = this.items.concat(this.reserve);
            this.nextReserve(url, search, category);
            if(category){
                $analytics.eventTrack('Load more in select category', {  category: 'Goal', label: 'Load more in category ' + category + ' from Web' });
            }

        };
        lsInfiniteItems.prototype.nextReserve = function(url, search, category) {
            //if busy or in goal show page
            if (this.busy || (this.count == 3 && url == envPrefix + 'api/v1.0/goals/{first}/{count}')) {
                return;
            }

            if (!search) {
                search = "";
            }

            if (!category) {
                category = this.category;
            } else {
                this.category = category;
            }

            this.busy = true;
            url = url.replace('{first}', this.start).replace('{count}', this.count);
            url += '?search=' + search + '&category=' + category;

            $http.get(url).success(function(data) {
                this.reserve = data;
                this.busy = data.length ? false : true;

                angular.forEach(this.reserve, function(item) {
                    var img;
                    if(item.cached_image){
                        img = new Image();
                        img.src = item.cached_image;
                    } else {
                        if (!angular.isUndefined(item.goals) && item.goals[0] && item.goals[0].cached_image) {
                            img = new Image();
                            img.src = item.goals[0].cached_image;
                        }
                    }
                });

                this.start += this.count;
                this.request++;
            }.bind(this));
        };

        lsInfiniteItems.prototype.nextPage = function(url, search, category, userId , notReserve) {
            if (this.busy) {
                return;
            }
            if (!search) {
                search = "";
            }

            if (!category) {
                category = this.category;
            } else {
                this.category = category;
            }

            this.busy = true;
            this.noItem = false;
            var reserveUrl = url;

            url = url.replace('{first}', this.start).replace('{count}', this.count);
            url += '?search=' + search + '&category=' + category;
            $http.get(url).success(function (data) {
                //if get empty
                if(!data.length){
                    this.noItem = true;
                }
                this.items = this.items.concat(data);
                this.busy = data.length ? false : true;
                this.start += this.count;
                this.request++;

                if(!notReserve){
                    this.nextReserve(reserveUrl, search, category);
                }

                if (!this.items.length) {
                    this.loadRandomItems(this.count);
                }

            }.bind(this));
        };

        return lsInfiniteItems;
    }])
    .controller('goalAdd', ['$scope', 
        '$sce',
        '$timeout',
        'AuthenticatorLoginService',
        '$window',
        'envPrefix',
        'UserGoalDataManager',
        'template',
        'userGoalData',
        '$analytics',
        'lsInfiniteItems',
        '$translate',
        function($scope, $sce, $timeout, AuthenticatorLoginService, $window, envPrefix, UserGoalDataManager, template, userGoalData, $analytics, lsInfiniteItems, $translate){

        $scope.files = [];
        $scope.uploadingFiles = [];
        var imageCount = 6;

        $scope.slickConfig = {
            slidesToShow: 3,
            slidesToScroll: 3,
            arrows: false,
            method: {},
            responsive: [
                {
                    breakpoint: 769,
                    settings: {
                        arrows: false,
                        centerMode: true,
                        centerPadding: '4px',
                        slidesToShow: 2
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        arrows: false,
                        centerMode: true,
                        centerPadding: '4px',
                        slidesToShow: 1
                    }
                }
            ]
        };

        $scope.searchTimeoutPtr = null;
        $scope.disablePreview = false;
        $scope.isMore = false;
        $scope.Ideas = new lsInfiniteItems(9);

        $scope.haveIdeas = false;

        $scope.searchGoal = function(ev){
            $timeout.cancel($scope.searchTimeoutPtr);

            $scope.searchTimeoutPtr = $timeout(function(){
                $scope.Ideas.reset();
                $scope.Ideas.nextPage(envPrefix + "api/v1.0/goals/{first}/{count}", $scope.addTitle, null, null, true);
            }, 600);

        };

        $scope.$watch('Ideas.items', function(d) {
            if(d.length){
                $scope.isMore = d.length > 3;
                $scope.haveIdeas = $scope.addTitle? true: false;
            }else {
                $scope.isMore = false;
                $scope.haveIdeas = false;
            }
        });

        $scope.openSignInPopup = function(){
            AuthenticatorLoginService.openLoginPopup();
        };

        angular.element('input[type=checkbox]').iCheck({
            checkboxClass: 'icheckbox_square-purple',
            increaseArea: '20%'
        }).on('ifChanged', function (event) {
            $(event.target).trigger('change');
        });
        
            $translate(['cancel_upload', 'upload_error', 'remove_file']).then(function (translations) {
                $scope.cancelUpload = translations.cancel_upload;
                $scope.uploadError = translations.upload_error;
                $scope.removeFile = translations.remove_file;

            }, function (translationIds) {
                $scope.cancelUpload = translationIds.cancel_upload;
                $scope.uploadError = translationIds.upload_error;
                $scope.removeFile = translationIds.remove_file;

            });

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
                    dictMaxFilesExceeded: $scope.uploadError,
                    dictCancelUpload: $scope.cancelUpload,
                    dictRemoveFile: $scope.removeFile,
                    previewTemplate: "<div class=\"dz-preview dz-file-preview\">\n  <div class=\"dz-details\">\n    <div class=\"dz-filename\"><span data-dz-name></span></div>\n    <div class=\"dz-size\" data-dz-size></div>\n    <img data-dz-thumbnail />\n  </div>\n  <div class=\"dz-progress\"><span class=\"dz-upload\" data-dz-uploadprogress></span></div>\n  <div class=\"dz-success-mark\"><span>✔</span></div>\n  <div class=\"dz-error-mark\" data-dz-remove><span>✘</span></div>\n  <div class=\"dz-error-message\"><span data-dz-errormessage></span></div>\n</div>",
                    maxFiles: 6,
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

                $scope.goalDropzone.on('addedfile', function(){
                    $scope.disablePreview = true;
                    $scope.$apply();
                });

                $scope.goalDropzone.on('queuecomplete', function(){
                    $scope.disablePreview = false;
                    $scope.$apply();
                })
            },500);
        };

        // end file uploads

        $scope.trustedUrl = function(url){
            return $sce.trustAsResourceUrl(url);
        };

        // description Tagging

        $scope.$watch('description',function(d){
            if(!d){
                return;
            }

            var reg = /(#[a-z0-9][a-z0-9\-_]+)/ig;
            $scope.tags = d.match(reg);
        },true);
        
        angular.element(".draft-save-submit").click(function(){
            angular.element("#goal-create-form").ajaxForm({
                beforeSubmit: function(){
                    $scope.$apply();
                },
                error: function(res){
                    if(res.status === 401) {
                        AuthenticatorLoginService.openLoginPopup();
                    }
                },
                success: function(res, text, header){
                    if(header.status === 200){
                        $window.location.href = $window.location.origin + envPrefix + 'goal/my-ideas/drafts';
                    }
                }
            });
        
            if(!$scope.$$phase){
                $scope.$apply()
            }
        
        });

        // end description Tagging

        angular.element(".goal-create-submit").click(function(){
            angular.element("#goal-create-form").ajaxForm({
                beforeSubmit: function(){
                    $(".modal-loading").show();
                    $scope.$apply();
                },
                error: function(res){
                    if(res.status === 401) {
                        AuthenticatorLoginService.openLoginPopup();
                    }
                },
                success: function(res, text, header){
                    if(header.status === 200){
                        $scope.newId = res;
                        UserGoalDataManager.creates({id:res}, {}, function (resource){
                            userGoalData.data = resource;
                            $scope.goalSubmitTemplate = template.addTemplate;
                            $(".modal-loading").hide();
                            $timeout(function(){
                                $scope.$broadcast('openLsModal', 'goalSave');
                            },10);
                        });
                    }
                }
            });

            if(!$scope.$$phase){
                $scope.$apply()
            }

        });

        $('body').on('click', '.usergoal-save', function() {
            $scope.isSave = true;
        });

        angular.element(".goal-view-submit").click(function(){
            angular.element("#goal-create-form").ajaxFormUnbind();
        });

        $scope.$on('lsJqueryModalClosedgoalSave', function(){
            $scope.goalSubmitTemplate = '';
            if(window.location.href.indexOf('?id=') === -1 && !$scope.isSave){
                UserGoalDataManager.creates({id:$scope.newId}, {is_visible: true}, function (resource){
                    userGoalData.data = resource;
                    $window.location.href = $scope.redirectPath;
                });
            } else {
                $window.location.href = $scope.redirectPath;
            }
        })

    }])
    .controller('goalInner', ['$scope', '$rootScope', '$filter', '$timeout', 'lsInfiniteItems', 'AuthenticatorLoginService', 'envPrefix', '$http', '$window',
        function($scope, $rootScope, $filter, $timeout, lsInfiniteItems, AuthenticatorLoginService, envPrefix, $http, $window){

        $scope.successStoryShow = [];
        $scope.goal = {};
        $scope.showingStoriesCount = 20;
        $scope.successStoryImageKeys = [];
        $scope.successStoryActiveIndex = null;
        $scope.Ideas = new lsInfiniteItems(3);
        var storyRemovePath = envPrefix + 'api/v1.0/success-story/remove/{storyId}';

        $scope.openSignInPopover = function(){
            AuthenticatorLoginService.openLoginPopup();
        };

        $scope.isLate = function (date) {
            if(!date){
                return false;
            }

            var d1 = new Date(date);
            var d2 = new Date();

            return (d1 < d2);
        };

        $rootScope.$on('lsJqueryModalClosedSaveGoal', function (ev, userGoal) {
            if(!userGoal)return;

            $scope.goal.is_visible = userGoal.is_visible;
            $scope.goal.do_date = userGoal.do_date;
            $scope.goal.note = userGoal.note;
            $scope.goal.public = userGoal.goal.status;
            $scope.goal.steps = userGoal.formatted_steps.length > 0;
        });

        $rootScope.$on('removeUserGoal', function (ev, id) {
            if($scope.isAuthor){
                $window.location.href = $window.location.origin + envPrefix + 'profile';
            } else {
                $window.location.reload();
            }
        });

        $timeout(function () {
            if($scope.goalId){
                var url = envPrefix + 'api/v1.0/usergoals/' + $scope.goalId;

                $http.get(url).success(function(data) {
                    $scope.goal.is_visible = data.is_visible;
                    $scope.goal.do_date = data.do_date;
                    $scope.goal.note = data.note;
                    $scope.goal.steps = data.formatted_steps.length > 0;
                });
            }

            angular.forEach($scope.successStoryImageKeys, function (d) {
                $( '.swipebox-key-'+d ).swipebox({
                    useSVG : false
                });
            });
            
            var afilateHeight = $('.affiliate-right iframe').height();
            var afilateMobileHeight = $('.affiliate-right-mobile iframe').height();
            //$('.affiliate-right iframe').height(afilateHeight + 80);
            //$('.affiliate-right-mobile iframe').height(afilateMobileHeight + 80);
        }, 2000);


        $timeout(function(){
            if(window.location.hash && window.location.hash == "#/comments"){

                $('html, body').stop().animate( {
                    'scrollTop': $('#random_goals').offset().top - 800
                }, 900);
            }
        }, 3000);

        $scope.castInt = function(value){
            return parseInt(value);
        };

        var imageHeight;

        if(angular.element('.quote').length > 0){
            angular.element('.quote').css("height", angular.element('.ticker li').height() + 35 + 'px');
        }

        if(angular.element('.goal-image').length > 0 && angular.element('#main-slider').length > 0){
            var goalImageBottom = angular.element('.goal-image').offset().top + angular.element('.goal-image').outerHeight() ;
            var mainSliderBottom = angular.element('#main-slider').offset().top + angular.element('#main-slider').outerHeight();

            if(goalImageBottom != mainSliderBottom){
                var distance = goalImageBottom - mainSliderBottom;
                angular.element('.goal-image').css("height",angular.element('.goal-image').innerHeight() - distance);
                // angular.element('.overlay.swiper-slide').css("height",angular.element('.overlay.swiper-slide').innerHeight() - distance)
            }
        }

        $scope.openLogin = function () {
            $(".modal-loading").show();
            AuthenticatorLoginService.openLoginPopup();
            $(".modal-loading").hide();
        };
            
        $scope.manageVote = function(id){
            var url = (!$scope.vote[id])?'api/v1.0/success-story/add-vote/{storyId}': 'api/v1.0/success-story/remove-vote/{storyId}';
            url = envPrefix + url;
            url = url.replace('{storyId}', id);
            $http.get(url).success(function() {
                if(!$scope.vote[id]){
                    $scope.count[id]++;
                    $scope.vote[id] = true;
                } else {
                    $scope.count[id]--;
                    $scope.vote[id] = false;
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

        $scope.removeStory = function (id) {
            var url = storyRemovePath.replace('{storyId}', id);
            $http.delete(url).success(function() {
                $scope.showMyStory[id] = false;
            })

        };

        var imageResize = function () {
            imageHeight = angular.element('#main-slider img').height();
            if( (window.innerWidth < 768 && imageHeight < 190) ||
              (window.innerWidth > 767 && window.innerWidth < 992 && imageHeight < 414) ||
              (window.innerWidth > 991 && imageHeight < 435)){
                angular.element('#main-slider img').addClass("full-height");
            } else {
                angular.element('#main-slider img').removeClass("full-height")
            }
        };

        imageResize();

        $(window).resize(function(){
            // if(window.innerWidth > 991 && window.innerWidth < 1200){
            //     angular.element('#main-slider img').addClass("full-height");
            // }else{
            //     angular.element('#main-slider img').removeClass("full-height")
            // }
            angular.element('.quote').css("height", angular.element('.ticker li').height() + 30 + 'px');
            imageResize();

            if(angular.element('.goal-image').length > 0 && angular.element('#main-slider').length > 0) {
                goalImageBottom = angular.element('.goal-image').offset().top + angular.element('.goal-image').outerHeight();
                mainSliderBottom = angular.element('#main-slider').offset().top + angular.element('#main-slider').outerHeight();

                if (goalImageBottom != mainSliderBottom) {
                    var distance = goalImageBottom - mainSliderBottom;
                    angular.element('.goal-image').css("height",angular.element('.goal-image').innerHeight() - distance);
                    // angular.element('.overlay.swiper-slide').css("height",angular.element('.overlay.swiper-slide').innerHeight() - distance)
                }
            }
        });

        $scope.popoverByMobile = function(){
            $timeout(function(){
                angular.element('.navbar-toggle').click();
            }, 500);
        };

        $scope.openSignInPopup = function(){
            AuthenticatorLoginService.openLoginPopup();
        };

        $scope.showMoreSuccessStory = function(storiesLength){
            if($scope.successStoryActiveIndex === storiesLength){
                return;
            }
            
            if($scope.successStoryActiveIndex === null){
                $scope.successStoryActiveIndex = $scope.storiesCount;
            }

           var startIndex = $scope.successStoryActiveIndex;
            
            if($scope.storyLength > $scope.showingStoriesCount - 1){
                $scope.successStoryActiveIndex += $scope.showingStoriesCount;
                $scope.storyLength -= $scope.showingStoriesCount;
            }
            else {
                $scope.successStoryActiveIndex += $scope.storyLength;
                $scope.storyLength = 0;
            }

            for(var i = startIndex; i < $scope.successStoryActiveIndex; i++){
                $scope.successStoryShow[i] = true;
            }

            $timeout(function(){
                $(".story-slider").trigger('showMoreStories');
            }, 50);
        };

        $scope.capitalizeFirstLetter = function (string) {
            return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
        };

        $scope.dateToLocal = function(date){
            return $scope.capitalizeFirstLetter($filter('date')(new Date(date), "MMMM d 'at' hh:mm a"));
        };

        if(angular.element('.goal-information') && screen.width >= 992  && window.innerWidth >= 992) {
            $scope.isDesktop = true;
            $timeout(function () {
                angular.element('.goal-information').scrollToFixed({
                    marginTop: 85,
                    limit: function () {
                        return angular.element('#random_goals').offset().top - angular.element('.goal-information').outerHeight(true);
                    },
                    unfixed: function () {
                        var limit = angular.element('#random_goals').offset().top - angular.element('.goal-information').outerHeight(true) - 300;
                        angular.element('.goal-information').css('left', '0').css('top', limit);
                    },
                    fixed: function() { angular.element('.right-menu-scroll').css('position', 'static'); },
                    preFixed: function() { angular.element('.right-menu-scroll').css('position', 'absolute'); },
                    preUnfixed: function() { angular.element('.right-menu-scroll').css('position', 'absolute'); },
                    preAbsolute: function() { angular.element('.right-menu-scroll').css('position', 'static'); }
                });
            },1000);
        }

        if(angular.element('.suggest-input input')) {
            angular.element('.suggest-input input').iCheck({
                checkboxClass: 'icheckbox_square-purple',
                increaseArea: '20%'
            });
        }

        $( '.swipebox-main' ).swipebox({
            useSVG : false
        });
    }])
    .controller('goalList', ['$scope', 'lsInfiniteItems', '$timeout', 'envPrefix', '$location', 'CacheFactory', '$http',
        function($scope, lsInfiniteItems, $timeout, envPrefix, $location, CacheFactory, $http){
        var path = $location.$$path,
            url = envPrefix + 'usergoals/{goal}/toggles/interesteds',
            positionCache = CacheFactory.get('bucketlist_by_position');
            $scope.notAllowed = true;

        if(!positionCache){
            positionCache = CacheFactory('bucketlist_by_position', {
                maxAge: 3 * 24 * 60 * 60 * 1000 ,// 3 day,
                deleteOnExpire: 'aggressive'
            });
        }

        $scope.position = positionCache.get('position');

        if(!$scope.position || !Object.keys($scope.position).length){
            $scope.position = null;
        } else {
            $scope.notAllowed = false;
            $scope.userLocation = $scope.position.coords;
            $scope.userLocation.isCompleted = $scope.isCompletedGoals;
        }

        $scope.browseError = '';
        $scope.browserAllowed = false;
        $scope.isCompletedGoals = false;
        $scope.Ideas = new lsInfiniteItems();
        $scope.filterVisibility = false;
        $scope.locations = [];
        $scope.isHover = false;
        $scope.hoveredText = '';
        $scope.ideasTitle = true;
        $scope.noIdeas = false;
        $scope.isSearching = false;
        //$scope.placeholder = '';
        $scope.activeCategory = '';
        var locationsIds = [];
        
        $scope.scrollTop = function () {
            $("html, body").animate({ scrollTop: 0 }, "slow");
        };
            
        $scope.notInterest = function (goal) {
            var restPath = url.replace('{goal}', goal.id);

            $http.post(restPath).success(function() {
                goal.isInterested = false;
            });
        };

        if(path.length){
            $scope.Ideas.busy = true;
            $timeout(function () {
                path = path.slice(1);

                if(path == 'discover'){
                    path = ''
                }

                $scope.goTo(path);
            },100);
        } else {
            $timeout(function () {
                if(!$scope.activeCategory || $scope.activeCategory == 'nearby'){
                    $scope.goTo('nearby');
                }
            },100);
        }

        $scope.hoverIn = function (ev, text) {
            $scope.isHover = true;
            $scope.hoveredText = text;
            var left = $(ev.target).offset().left;
            var top  = $(ev.target).offset().top - 40;

            left = left - 60;
            $('.list-tooltip .arrow-up').css({left: 60});
            $('.list-tooltip').css({top: top,left: left});
        };

        $scope.allowBrowserLocation = function () {

            if (!navigator.geolocation){
                $scope.browseError = "Geolocation is not supported by your browser";
                return;
            }

            function success(position) {
                $scope.notAllowed = false;
                $scope.position = position;
                
                positionCache.put('position', {
                    coords: {
                        latitude: $scope.position.coords.latitude,
                        longitude: $scope.position.coords.longitude
                    }
                });
                
                $scope.userLocation = position.coords;
                $scope.userLocation.isCompleted = $scope.isCompletedGoals;
                $scope.Ideas.reset();
                $scope.Ideas.nearBy($scope.userLocation);

                $timeout(function(){
                    $scope.$emit('allowLocation', $scope.position);
                },10);
            }

            function error() {
                $scope.notAllowed = false;
                $scope.browseError = "Unable to retrieve your location";
            }
            
            navigator.geolocation.getCurrentPosition(success, error);
        };

        $scope.$on('location_place_changed', function (ev, data) {
            $scope.locations = [];
            locationsIds = [];
            data.isCompleted = $scope.isCompletedGoals;
            $scope.userLocation = data;
            $scope.Ideas.reset();
            $scope.Ideas.nearBy(data);
        });

        $scope.completedChange =  function () {
            $scope.locations = [];
            locationsIds = [];
            $scope.userLocation.isCompleted = $scope.isCompletedGoals;
            $scope.Ideas.reset();
            $scope.Ideas.nearBy($scope.userLocation);
        };

        $scope.goTo = function (path) {
            $scope.noIdeas = false;
            $scope.ideasTitle = false;
            angular.element('.idea-item').addClass('ideas-result');
            $scope.locations = [];
            locationsIds = [];
            $scope.activeCategory = path;
            $scope.Ideas.reset();
            $scope.search = '';
            if($scope.activeCategory != 'nearby'){
                $scope.Ideas.nextPage(envPrefix + "api/v1.0/goals/{first}/{count}", $scope.search,$scope.activeCategory);
            } else {
                $scope.$emit('location-resize');
                if(!$scope.browserAllowed && (!$scope.position || !$scope.position.coords)){
                    $scope.browserAllowed = true;
                    $scope.allowBrowserLocation();
                }
                if($scope.position && $scope.position.coords){
                    $scope.position.coords.isCompleted = $scope.isCompletedGoals;
                    $scope.Ideas.reset();
                    $scope.Ideas.nearBy($scope.position.coords);
                    $scope.userLocation = $scope.position.coords;

                    $timeout(function(){
                        $scope.$emit('allowLocation', $scope.position);
                    },10);
                } else if($scope.userLocation){
                    $timeout(function(){
                        $scope.$emit('allowLocation', $scope.userLocation);
                    },10);

                    $scope.userLocation.isCompleted = $scope.isCompletedGoals;
                    $scope.Ideas.reset();
                    $scope.Ideas.nearBy($scope.userLocation);

                    // $timeout(function () {
                    //     $('html, body').stop().animate( {
                    //         'scrollTop': $('div[data-autocomplete-map]').offset().top + $('div[data-autocomplete-map]').outerHeight()
                    //     }, 900);
                    // }, 1000);
                }
            }

        };

        $scope.castInt = function(value){
            return parseInt(value);
        };

        function slideInsert(count, miniDevice){
            $timeout(function(){
                var list_swiper = new Swiper('div.filters-slider:not(.swiper-container-horizontal)', {
                    observer: true,
                    autoHeight: true,
                    slidesPerView: count,
                    nextButton: '.swiper-button-next',
                    prevButton: '.swiper-button-prev',
                    spaceBetween: 10
                });

                $scope.filterVisibility = true;
                $scope.fadeMapIcon = true;

                if(miniDevice){
                    for (var i = 0, length = list_swiper.slides.length; i < length; i++) {
                        var slide = list_swiper.slides.eq(i);
                        if (slide.hasClass('active-category')) {
                            var index = slide.index();
                            list_swiper.slideTo((index>0)?(index-1):0, 0, true, true);
                        }
                    }
                }

            }, 100);
        }

        $timeout(function(){
            if(window.innerWidth < 766){
                slideInsert(3, true);
                $scope.isMobile = true;
                //$scope.placeholder = '';
            }
            else if(window.innerWidth < 992){
                slideInsert(($scope.categoriesLength < 8)?$scope.categoriesLength +1 : 8);
                $scope.isMobile = false;
                //$scope.placeholder = $scope.placeholderText;
            }
            else {
                slideInsert(($scope.categoriesLength < 10)?$scope.categoriesLength +1 : 10);
                $scope.isMobile = false;
                //$scope.placeholder = $scope.placeholderText;
            }
        }, 500);

        $scope.getParameterByName = function(name, href){
            name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
            var regexS = "[\\?&]"+name+"=([^&#]*)";
            var regex = new RegExp( regexS );
            var results = regex.exec( href );
            if(results == null){
                return "";
            }
            else {
                return decodeURIComponent(results[1].replace(/\+/g, " "));
            }
        };

        $scope.search = $scope.getParameterByName('search',window.location.href);

        $scope.doSearch = function(ev){
            // if(ev.which === 13 && screen.width < 768) {
            //     angular.element('.icon-remove-email').click();
            // }
            if(ev.which != 8 && ev.which != 46 && !$scope.isSearching){
                $scope.isSearching = true;
            } else if($scope.isSearching && (ev.which == 8 || ev.which != 46)){
                $timeout(function(){
                    if(!$scope.search.length)$scope.isSearching = false;
                },100);
            }
            $scope.noIdeas = false;
            $scope.ideasTitle = false;
            angular.element('.idea-item').addClass('ideas-result');
            if(ev.which == 13){
                ev.preventDefault();
                ev.stopPropagation();
                $scope.locations = [];
                locationsIds = [];

                var ptName = window.location.pathname;
                window.history.pushState("", "", ptName + "?search=" + $scope.search);
                $scope.Ideas.reset();
                $scope.Ideas.nextPage(envPrefix + "api/v1.0/goals/{first}/{count}", $scope.search, $scope.activeCategory);
            }
        };

        $scope.$watch('Ideas.items', function(d) {
            if(!d.length && $scope.activeCategory != 'nearby'){
                if($scope.Ideas.noItem ){
                    $scope.fadeMapIcon = false;
                    $scope.showMap = false;
                    var k = $scope.noIdeas;
                    $scope.noIdeas = true;
                    angular.element('.idea-item').removeClass('ideas-result');
                    if(!k){
                        $scope.Ideas.reset();
                        $scope.Ideas.nextPage(envPrefix + "api/v1.0/goals/{first}/{count}", '');
                    }
                }
            }

            if($scope.activeCategory == 'nearby'){
                angular.forEach(d, function(item) {
                    var location = {};
                    if(item.location && locationsIds.indexOf(item.id) == -1){
                        location.id = item.id;
                        locationsIds.push(location.id);
                        location.latitude = item.location.latitude;
                        location.longitude = item.location.longitude;
                        location.title = item.title;
                        location.slug = item.slug;
                        location.status = item.is_my_goal;
                        $scope.locations.push(location);
                    }
                });
            }

            // $timeout(function() {
            //     $scope.fadeMapIcon = ($scope.locations.length > 0);
            //     $scope.showMap = $scope.showMap && $scope.fadeMapIcon ;
            // }, 1000);
        });

        $scope.adventureText = function(slug, cJson){
            var item = null;

            angular.forEach(cJson, function(v){
                if(v.slug === slug){
                   item = v;
                }
            });

            return item;
        }

    }])
    .directive('delayAddClass',['$interval', function($interval){
        return {
            restrict: 'EA',
            scope: {
                delay: '=',
                className: '@'
            },
            link: function(scope, el){
                var dl = scope.delay ? scope.delay : 8000;
                var cl = scope.className ? scope.className: 'active';
                var items = el.children();
                var activeIndex = 0;

                if(items.length) {
                    angular.element(items[0]).addClass(cl);

                    if (items.length > 1) {
                        $interval(function () {
                            items.removeClass(cl);

                            if(activeIndex === items.length - 1){
                                activeIndex = 0;
                            }
                            else {
                                activeIndex++;
                            }

                            angular.element(items[activeIndex]).addClass(cl);

                        }, dl);
                    }
                }
            }
        }
    }]);
