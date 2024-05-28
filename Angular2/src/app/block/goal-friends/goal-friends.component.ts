import {Component, OnInit, ViewChild} from '@angular/core';
import {CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';
import { ElementRef, Renderer} from '@angular/core';


import { ProjectService } from '../../project.service';

import {User} from '../../interface/user';

@Component({
  selector: 'goal-friends-block',
  templateUrl: './goal-friends.component.html',
  styleUrls: ['./goal-friends.component.less']
})
export class GoalFriendsBlockComponent implements OnInit {
  @ViewChild("rotate")
  public rotateElementRef: ElementRef;
  degree:number = 360;

  users:User[];
  length:Number;
  reserve: any;
  errorMessage:string;
  appUser:any;

  constructor(private _projectService: ProjectService,
              private _cacheService: CacheService,
              public renderer: Renderer){}

  ngOnInit() {

    this.appUser = this._cacheService.get('user_');

    if(!this.appUser) {

      this._projectService.getUser()
          .subscribe(
              user => {
                this.appUser = user;
                this.getData();
              })
    } else {
      this.getData();
    }
  }

  getData(){
    let data = this._cacheService.get('goalFriendBox'+this.appUser.id);

    if(data){
      this.users = data[1];
      this.length = data.length;
      this.goalReserve();
    } else {
      this.goalFriends()
    }
  }
  
  goalFriends() {
    this._projectService.getGaolFriends()
        .subscribe(
            data => {
              this.users = data[1];
              this.length = data.length;
              this._cacheService.set('goalFriendBox'+this.appUser.id, data, {maxAge: 2 * 24 * 60 * 60});
            },
            error => this.errorMessage = <any>error);
    this.goalReserve();
  }

  goalReserve() {
    this._projectService.getGaolFriends()
        .subscribe(
            data => {
              this.reserve = data;
              for(let item of data[1]){
                let img;
                if(item.cached_image){
                  img = new Image();
                  img.src = item.cached_image;
                }
              }
              this._cacheService.set('goalFriendBox'+this.appUser.id, this.reserve, {maxAge: 2 * 24 * 60 * 60});
            },
            error => this.errorMessage = <any>error);
  }

  refreshGoalFriends(){
    this.renderer.setElementStyle(this.rotateElementRef.nativeElement, 'transform','rotate('+this.degree+'deg)');
    this.users = this.reserve[1];
    this.length = this.reserve.length;
    this.goalReserve();
    this.degree += 360;
  }
}
