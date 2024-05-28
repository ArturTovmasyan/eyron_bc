import { Component, OnInit, Input , ViewEncapsulation, Output, EventEmitter} from '@angular/core';
import {UserGoal} from "../../interface/userGoal";
import {Goal} from "../../interface/goal";
import {User} from "../../interface/user";
import {CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';

import { ProjectService } from '../../project.service'
import { Broadcaster } from '../../tools/broadcaster';

@Component({
  selector: 'profile-goal',
  templateUrl: './profile-goal.component.html',
  styleUrls: ['./profile-goal.component.less'],
  encapsulation: ViewEncapsulation.None
})
export class ProfileGoalComponent implements OnInit {
  @Input() userGoal: UserGoal;
  @Input() last: boolean;
  @Input() first: boolean;
  @Output('onUserGoalChange') onUserGoalChange: EventEmitter<any> = new EventEmitter();

  public goal:Goal;
  public change:number = 0;
  public doDate:any;
  public dateStatus:number;
  public status:number;
  public goalStatus:boolean;
  public success:boolean;
  public removed:boolean = false;
  public goalDate:any;
  public appUser:User;
  public isMobile:Boolean= (window.innerWidth < 768);
  constructor(
      private broadcaster: Broadcaster,
      private _cacheService: CacheService,
      private _projectService: ProjectService) { }

  ngOnInit() {
    if(localStorage.getItem('apiKey')){
      this.appUser = this._projectService.getMyUser();
      if (!this.appUser) {
        this.appUser = this._cacheService.get('user_');
        if(!this.appUser) {
          this._projectService.getUser()
              .subscribe(
                  user => {
                    this.appUser = user;
                    this._cacheService.set('user_', user, {maxAge: 3 * 24 * 60 * 60});
                    this.broadcaster.broadcast('getUser', user);
                  })
        }
      }
    } else {
      this.broadcaster.broadcast('logout', 'some message');
    }

    this.initData();

    if(this.userGoal){
      this.broadcaster.on<any>('saveUserGoal_' + this.userGoal.id)
          .subscribe(data => {
            this.userGoal = data;
            this.initData();
            this.onUserGoalChange.emit(this.userGoal);
          });
      this.broadcaster.on<any>('removeUserGoal_' + this.userGoal.id)
          .subscribe(data => {
            this.removed = true;
          });
    }

  }
  
  initData(){
    if(this.userGoal && this.userGoal.goal){

      if(this.goal){
        let image = this.goal.cached_image;
        this.goal = this.userGoal.goal;
        this.goal.cached_image = image;
      } else {
        this.goal = this.userGoal.goal;
      }

      this.dateStatus = this.userGoal.date_status;
      this.status = this.userGoal.status;
      this.goalStatus = (this.status == 2);
      this.success = this.goalStatus;
      this.goalDate = ((this.userGoal.do_date && this.status != 2)? this.userGoal.do_date: (this.userGoal.completion_date? this.userGoal.completion_date: 'dreaming'));
    }
  }

  isEmpty(object){
    return (!object || !Object.keys(object).length);
  };

  isLate(date){
    if(!date){
      return false;
    }

    var d1 = new Date(date);
    var d2 = new Date();

    return (d1 < d2);
  };

  manageGoal(id){
    this.broadcaster.broadcast('addModal', {
      'userGoal': {'goal':this.goal},
      'newAdded' : false,
      'newCreated' : false
    });
    this._projectService.getUserGoal(id).subscribe((data) => {
      this.broadcaster.broadcast('addModalUserGoal', data);
      // this.broadcaster.broadcast('addModal', {
      //   'userGoal': data,
      //   'newAdded' : false,
      //   'newCreated' : false
      // });
    });
  }

  completeGoal(id, isManage){
    this.success = true;
    
    if(isManage){
      this.broadcaster.broadcast('doneModal', {
        'userGoal': {'goal':this.goal},
        'newAdded' : false
      });
      this._projectService.getStory(id).subscribe((data)=> {
        this.broadcaster.broadcast('doneModalUserGoal', data);
        // this.broadcaster.broadcast('doneModal', {
        //   'userGoal': data,
        //   'newAdded' : false
        // });
      })
    } else {
      this.broadcaster.broadcast('doneModal', {
        'userGoal': {'goal':this.goal},
        'newAdded' : true
      });
      this._projectService.setDoneUserGoal(id).subscribe(() => {
        this._projectService.getStory(id).subscribe((data)=> {
          this.broadcaster.broadcast('doneModalUserGoal', data);
          // this.broadcaster.broadcast('doneModal', {
          //   'userGoal': data,
          //   'newAdded' : true
          // });
        })
      });
    }
    
  }
  
}
