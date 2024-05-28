/**
 * Created by gevor on 2/18/17.
 */

import { OnInit, ViewChild, ElementRef, Renderer, OnDestroy,ViewContainerRef } from '@angular/core';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';
import { Broadcaster } from '../tools/broadcaster';
import {CacheService} from 'ng2-cache/ng2-cache';
import {ProjectService} from '../project.service';

import {Location} from '../interface/location';
import {User} from "../interface/user";
import { MetadataService } from 'ng2-metadata';
import {MdSnackBar, MdSnackBarConfig} from '@angular/material';
import { TranslateService} from 'ng2-translate';

export class Profile implements OnInit, OnDestroy {
    @ViewChild("tooltip") public tooltipElementRef: ElementRef;
    public categories: string[]= ['all', 'active', 'completed'];
    public uId: string;
    public id: number;
    public type: string;
    public errorMessage: string;
    public filterVisibility: boolean = false;
    public showMap: boolean = false;
    public myProfile: boolean = false;
    public isDream: boolean = false;
    public showCalendar: boolean = false;
    public notUrgentImportant: boolean = false;
    public notUrgentNotImportant: boolean = false;
    public urgentNotImportant: boolean = false;
    public urgentImportant: boolean = false;
    public eventId: number = 0;
    public isHover: boolean = false;
    public busy: boolean = false;
    public busyInitial: boolean = false;
    public noGoals: boolean = false;
    public noItem: boolean = false;
    public isDestroy: boolean = false;
    public hoveredText: string = '';
    public oldUser: string;
    public initializeTimeout: any;
    public sub: any;
    public serverPath:string = '';
    public isTouchdevice:Boolean = (window.innerWidth > 600 && window.innerWidth < 992);
    public isMobile:Boolean= (window.innerWidth < 768);

    public start: number = 0;
    public count: number = 10;
    public locations:Location[] = [];
    public locationsIds = [];
    // public goals: Goal[];
    // public reserveGoals: Goal[];
    public userGoals: any[];
    public reserveUserGoals: any[];

    public overall:number;
    public appUser:User;
    public message:any = "Your goal has been succecfully  removed";

    constructor(
        protected metadataService: MetadataService,
        protected route: ActivatedRoute,
        protected _projectService: ProjectService,
        protected _cacheService: CacheService,
        protected broadcaster: Broadcaster,
        protected router:Router,
        protected renderer: Renderer,
        protected snackBar: MdSnackBar,
        protected _translate: TranslateService
    ) {
        this.sub = router.events.subscribe((event) => {
            if(event instanceof NavigationEnd ) {
                if (!this.isDestroy && this.eventId != event.id) {
                    this.eventId = event.id;
                    window.scrollTo(0, 0);
                    this.start = 0;
                    this.locationsIds = [];
                    this.locations = [];
                    this.uId = this.route.snapshot.paramMap.has('uId') ? this.route.snapshot.paramMap.get('uId') : 'my';
                    this.myProfile = this.uId == 'my';
                    this.type = this.route.snapshot.paramMap.has('type') ? this.route.snapshot.paramMap.get('type') : this.myProfile ? 'all' : 'activity';
                    this.metadataService.setTitle((this.myProfile ? 'My Profile' : 'Profile'));
                    this.metadataService.setTag('description', 'Profile for ' + this.type);
                    // this.goals = null;
                    this.noItem = false;
                    this.showCalendar = false;
                    this.userGoals = null;
                    // this.reserveGoals = null;
                    this.reserveUserGoals = null;
                    if (this.oldUser == this.uId) {
                        this.busyInitial = false;
                        this.changeByDeviceType(true);
                        this.getData();
                    } else {
                        this.busyInitial = true;
                    }
                }
            }
        })
    }

    ngOnDestroy(){
        this.sub.unsubscribe();
        this.isDestroy = true;
    }

    ngOnInit() {
        this.serverPath = this._projectService.getPath();

        // $rootScope.$on('removeUserGoal', function () {
        //   UserGoalDataManager.overall($scope.currentPage, function (data) {
        //     $scope.overallProgress = data.progress;
        //   });
        // });

        // $scope.$on('doneGoal', function(){
        //   UserGoalDataManager.overall($scope.currentPage, function (data) {
        //     $scope.overallProgress = data.progress;
        //   });
        // });
        // $rootScope.$on('lsJqueryModalClosedSaveGoal', function () {
        //   UserGoalDataManager.overall($scope.currentPage, function (data) {
        //     $scope.overallProgress = data.progress;
        //   });
        // });    

        this.broadcaster.on<User>('pageUser')
            .subscribe(user => {
                this.appUser = user;
                this.id = user.id;
                if(this.busyInitial){
                    this.busyInitial = false;
                    this.oldUser = this.uId;
                    this.changeByDeviceType(true);
                    this.getData();
                }
            });
        this.broadcaster.on<any>('snackbar')
            .subscribe( data =>{
                if(this.isDestroy)return;
               if(data == 0){
                   this.message = this._translate.instant('goal.was_deleted');
                   this.snackBar.open(this.message, '', <any>{
                       duration : 2000
                   });
                   setTimeout(() => {
                       (<any>document.querySelectorAll('.cdk-global-overlay-wrapper')).forEach(function(element) {
                           element.className += " flex-md-left";
                       });
                       document.getElementsByTagName("snack-bar-container")[0].className += " snackbar_style";
                   }, 100);

               }
                 // document.querySelector('.cdk-global-overlay-wrapper').className += "flex-md-left";
                  
            });
    }

    updateUserGoal(userGoal, i) {
        if (this.userGoals && this.userGoals[i]) {
            this.userGoals[i].status = userGoal.status;
            this.userGoals[i].do_date = userGoal.do_date;
        }
    }

    getData(){
        this.start = 0;
        this.noItem = false;
        this.changeByDeviceType();
        let index = this.categories.indexOf(this.type);
        if(index != -1){
            this.getGoals(index);
            if(this.uId == 'my'){
                this.getOverall(index);
            }
        } else {
            switch (this.type){
                case 'common':
                    // this.busy = true;
                    this.busy = false;
                    this.getCommon();
                    break;
                case 'activity':
                    this.busy = true;
                    this.overall = 0;
                    this.busyInitial = false;
                    // $scope.profile.status = UserGoalConstant.ACTIVITY_PATH;
                    // $scope.Activities.nextActivity();
                    // $scope.$emit('lsGoActivity');
                    break;
                case 'owned':
                    this.busy = false;
                    this.getOwned();
                    if(this.uId == 'my'){
                        this.getOverall(null,true);
                    }
                    break;
            }
        }
    }
    
    changeByDeviceType(isGet?:boolean){
    }

    getReserve(){
        this.userGoals = this.userGoals.concat(this.reserveUserGoals);
        this.calculateLocations(this.reserveUserGoals);
        let index = this.categories.indexOf(this.type);
        if(index != -1){
            this.getGoalsReserve(index);
        } else {
            switch (this.type){
                case 'common':
                    this.getCommonReserve();
                    break;
                case 'owned':
                    this.getOwnedReserve();
                    break;
            }
        }
    }

    getGoals(condition){
        let c = condition;
        this._projectService.profileGoals(
            condition, this.count, this.start, this.isDream, this.notUrgentImportant, this.notUrgentNotImportant,
            this.urgentImportant, this.urgentNotImportant, ((this.type == 'all')?'': (this.type + '-goals')),((this.myProfile)?0:this.id) )
            .subscribe(
                data => {
                    this.noItem = !data.user_goals.length;
                    this.userGoals = data.user_goals;
                    this.calculateLocations(this.userGoals);
                    this.start += this.count;
                    this.getGoalsReserve(c);
                });
    }

    getGoalsReserve(condition){
        this._projectService.profileGoals(
            condition, this.count, this.start, this.isDream, this.notUrgentImportant, this.notUrgentNotImportant,
            this.urgentImportant, this.urgentNotImportant, ((this.type == 'all')?'': (this.type + '-goals')),((this.myProfile)?0:this.id) )
            .subscribe(
                data => {
                    this.reserveUserGoals = data.user_goals;
                    this.optimiseImages();
                    this.start += this.count;
                    this.busy = false;
                });
    }

    getOwned(){
        this._projectService.ownedGoals(
            this.id, this.count, this.start)
            .subscribe(
                data => {
                    this.noItem = !data.goals.length;
                    this.userGoals = data.goals;
                    this.calculateLocations(this.userGoals);
                    this.start += this.count;
                    this.getOwnedReserve();
                });
    }

    getOwnedReserve(){
        this._projectService.ownedGoals(
            this.id, this.count, this.start)
            .subscribe(
                data => {
                    this.reserveUserGoals = data.goals;
                    this.optimiseImages(true);
                    this.start += this.count;
                    this.busy = false;
                });
    }

    getCommon(){
        this._projectService.commonGoals(
            this.id, this.count, this.start)
            .subscribe(
                data => {
                    this.noItem = !data.goals.length;
                    this.userGoals = data.goals;
                    this.calculateLocations(this.userGoals);
                    this.start += this.count;
                    this.getCommonReserve();
                });
    }

    getCommonReserve(){
        this._projectService.commonGoals(
            this.id, this.count, this.start)
            .subscribe(
                data => {
                    this.reserveUserGoals = data.goals;
                    this.optimiseImages(true);
                    this.start += this.count;
                    this.busy = false;
                });
    }

    onScroll(){
        if(this.busy || !this.reserveUserGoals || !this.reserveUserGoals.length)return;
        this.busy = true;
        this.getReserve();
    }

    optimiseImages(isGoal?:boolean){
        if(isGoal){
            for(let item of this.reserveUserGoals){
                let img;
                if(item.cached_image){
                    img = new Image();
                    img.src = item.cached_image;
                }
            }
        } else {
            for(let item of this.reserveUserGoals){
                let img;
                if(item.goal.cached_image){
                    img = new Image();
                    img.src = item.goal.cached_image;
                }
            }
        }

    }

    calculateLocations(items){
        for(let data of items){
            let item = data.goal?data.goal:data;
            let location:any = {
                id: 0,
                latitude: 0,
                lat: 0,
                longitude: 0,
                lng: 0,
                slug: '',
                title: '',
                status: 0,
                isHover: false,
            };
            if(item.location && this.locationsIds.indexOf(item.id) == -1){
                location.id = item.id;
                this.locationsIds.push(location.id);
                location.latitude = item.location.latitude;
                location.lat = item.location.latitude;
                location.longitude = item.location.longitude;
                location.lng = item.location.longitude;
                location.title = item.title;
                location.slug = item.slug;
                location.status = item.is_my_goal;
                this.locations.push(location);
            }
        }
        this.broadcaster.broadcast('getLocation', this.locations);
    }

    getOverall(condition,owned?){
        this._projectService.getOverall(
            condition, this.count, this.start, this.isDream, this.notUrgentImportant, this.notUrgentNotImportant,
            this.urgentImportant, this.urgentNotImportant, ((this.type == 'all')?'': (this.type + '-goals')),((this.myProfile)?0:this.id),owned)
            .subscribe(
                data => {
                    this.overall = data.progress;
                });
    }

    hideJoin(event){
        if(event && event.val){
            this.hoveredText = event.val;
            this.isHover = true;
            let left = +event.ev.pageX - 60;
            let top  = event.ev.pageY - 60;
            this.renderer.setElementStyle(this.tooltipElementRef.nativeElement, 'left', left + 'px');
            this.renderer.setElementStyle(this.tooltipElementRef.nativeElement, 'top', top + 'px');

        } else {
            this.hoveredText = '';
            this.isHover = false
        }

    }
}
