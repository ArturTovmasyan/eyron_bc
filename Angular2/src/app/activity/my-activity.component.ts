import { Component, OnInit, Input , ViewEncapsulation, OnDestroy } from '@angular/core';
import {CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';
import { Activity } from '../interface/activity';
import { Broadcaster } from '../tools/broadcaster';
import { Angulartics2 } from 'angulartics2';

import { ProjectService } from '../project.service';
import {User} from "../interface/user";


@Component({
  selector: 'my-activity',
  templateUrl: './my-activity.component.html',
  styleUrls: ['./my-activity.component.less','../components/comment/comment.component.less','../components/goal/goal.component.less'],
  encapsulation: ViewEncapsulation.None
})
export class MyActivityComponent implements OnInit,OnDestroy {
    @Input() single: boolean;
    @Input() userId: number;
    public Activities:Activity[];
    public reserve:Activity[];
    public newData:Activity[];
    public start:number = 0;
    public count:number = 9;
    public interval:any;
    public appUser:User;
    public activeIndex:number[] = [];
    public createComment:boolean[] = [];
    public noActivities:boolean = false;
    public haveCache:boolean = false;
    public busy:boolean = false;
    public newActivity:boolean = false;
    errorMessage:string;
    
    constructor(
        private angulartics2: Angulartics2,
        private _projectService: ProjectService,
        private _cacheService: CacheService,
        private broadcaster: Broadcaster
    ) {}
    
    ngOnDestroy(){
        clearInterval(this.interval);
    }

    ngOnInit() {
        this.appUser = this._cacheService.get('user_');
        let fresh = this.appUser?this._cacheService.get('fresh'+this.appUser.id):null;
        let data = this.appUser?this._cacheService.get('activities'+this.appUser.id):null;
        if(data && !this.single && (!fresh || fresh['activities'])){
          this.Activities = data;
          this.noActivities = (!data || !data.length);
          this.newActivityFn(true);
        } else {
          if(fresh){
            fresh['activities'] = true;
            this._cacheService.set('fresh'+this.appUser.id, fresh);
          }
          this.getActivities();
        }

        this.broadcaster.on<any>('slide-change')
            .subscribe(data => {
                this.activeIndex[data.id] = data.index;
                this.Activities[data.number].createComment = false;
                this.Activities[data.number].showComment = false;
            });

        this.interval = setInterval(() => {
            if(this.Activities && this.Activities.length &&!this.single){
                this._projectService.getActivities(0, this.count, this.userId, this.Activities[0].datetime).subscribe(
                    data => {
                        if(data && data.length != 0){
                            this.newData = data;
                            this.newActivity = true;
                            clearInterval(this.interval);
                        }
                    });
            }else {
                clearInterval(this.interval);
            }
        }, 120000)
    }
    
    getActivities(){
        this.busy = true;
        this._projectService.getActivities(this.start, this.count, this.userId)
            .subscribe(
                activities => {
                  this.Activities = activities;
                  this.noActivities = (!activities || !activities.length);
                  for(let activity of this.Activities) {
                    if (activity.goals.length > 2) {
                      activity.reserveGoals = [activity.goals[0], activity.goals[1]];
                        this.optimizeReserveImages(activity.reserveGoals);
                    } else {
                      activity.reserveGoals = activity.goals
                    }
                  }
                  this.start += this.count;
                  this.busy = false;
                  this.setReserve();
                  this.appUser = this._cacheService.get('user_');
                  this._cacheService.set('activities'+this.appUser.id, this.Activities);
                },
                error => this.errorMessage = <any>error);
    }
    
    refreshCache(){
        this.busy = false;
          
        this._projectService.getActivities(this.start, this.count, this.userId)
            .subscribe(
                activities => {
                    if(activities && activities.length){
                        if(activities[0].datetime !== this.Activities[0].datetime ){
                            this.newData = activities;
                            this.haveCache = true;
                            this.newActivity = true;

                            this.reserve = [];
                        }

                        for(let activity of activities) {
                            if (activity.goals.length > 2) {
                                activity.reserveGoals = [activity.goals[0], activity.goals[1]];
                                this.optimizeReserveImages(activity.reserveGoals);
                            } else {
                                activity.reserveGoals = activity.goals
                            }
                        }
                        this.appUser = this._cacheService.get('user_');
                        this._cacheService.set('activities'+this.appUser.id, activities);
                        this.start += this.count;
                        this.busy = false;
                        this.setReserve();
                    }
                },
                error => this.errorMessage = <any>error);
    }

    setReserve(){
        this._projectService.getActivities(this.start, this.count, this.userId)
            .subscribe(
                activities => {
                  this.reserve = activities;
                  for(let activity of this.reserve) {
                    activity.forBottom = true;
                    if (activity.goals.length > 2) {
                        activity.reserveGoals = [activity.goals[0], activity.goals[1]];
                        this.optimizeReserveImages(activity.reserveGoals);
                    } else {
                        activity.reserveGoals = activity.goals
                    }
                  }
                  this.start += this.count;
                    this.busy = false;  
                },
                error => this.errorMessage = <any>error);
    }

    getReserve(){
        this.angulartics2.eventTrack.next({ action: 'Activity load more', properties: { category: 'Activity', label: 'Load more from angular2'}});
        this.busy = true;
        this.Activities = this.Activities.concat(this.reserve);
        this.setReserve();
    }

    addNew(){
        this.newActivity = false;
        if(this.haveCache){
            this.haveCache = false;
            this.purgeOldData();
        } else {
            this.addNewActivity();
            this.interval = setInterval(this.newActivityFn, 120000)
        }

    }

    purgeOldData(){
        for(let i = this.count - 1; i >= 0; i--){
            this.newData[i].forTop = true;
            this.Activities.unshift(this.newData[i]);
            this.Activities.pop();
        }
    }

    newActivityFn(haveReserve?:boolean) {
        if(!this.single){
            if(this.Activities && this.Activities[0]){
                if(haveReserve){
                    this.refreshCache();
                } else {
                    this._projectService.getActivities(0, this.count, this.userId, this.Activities[0].datetime).subscribe(
                        data => {
                            if(data && data.length != 0){
                                this.newData = data;
                                this.newActivity = true;
                                clearInterval(this.interval);
                            }
                        });
                }
            } else {
                this.getActivities();
            }

        }else {
            clearInterval(this.interval);
        }
    }

    addNewActivity(){
        let itemIds = [];

        for(let data of this.Activities){
            itemIds.push(data.id);
        }

        let removingCount = 0,k;

        for(let i = this.newData.length - 1, j = 0; i >= 0; i--, j++){
            k = itemIds.indexOf(this.newData[i].id);
            if(k !== -1){
                this.Activities.splice(k + j - removingCount, 1);
                removingCount++;
            }
            this.newData[i].forTop = true;
            if (this.newData[i].goals.length > 2) {
                this.newData[i].reserveGoals = [this.newData[i].goals[0], this.newData[i].goals[1]];
                this.optimizeReserveImages(this.newData[i].reserveGoals);
            } else {
                this.newData[i].reserveGoals = this.newData[i].goals
            }
            this.Activities.unshift(this.newData[i]);
        }
    };

    optimizeReserveImages(items){
        // for(let activity of this.reserve){
          for(let item of items) {
            let img;
            if (item.cached_image) {
              img = new Image();
              img.src = item.cached_image;
            }
          }
        // }
    }
}
