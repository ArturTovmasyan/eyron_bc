import { Component, OnInit, Input } from '@angular/core';
import { Broadcaster } from '../../tools/broadcaster';
import {ProjectService} from '../../project.service';
import {CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';

import { Goal } from '../../interface/goal';
import { Story } from '../../interface/story';
import { User } from '../../interface/user';
@Component({
  selector: 'goal-users',
  templateUrl: './goal-users.component.html',
  styleUrls: ['./goal-users.component.less']
})
export class GoalUsersComponent implements OnInit {
  @Input() goal: Goal;
  @Input() story: Story;
  @Input() user: User;
  @Input() type: number;
  voters_count: number;
  is_vote: boolean;
  appUser:User;
  busy:boolean = false;
  
  constructor(
      private _projectService: ProjectService,
      private _cacheService: CacheService,
      private broadcaster: Broadcaster
  ) { }

  ngOnInit() {
    if(this.story){
      this.voters_count = this.story.voters_count;
      this.is_vote = this.story.is_vote
    }

    if(localStorage.getItem('apiKey')){
      this.appUser = this._projectService.getMyUser();
      if(!this.appUser){
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
    }
  }


  openUsersModal(id:number, count:number, category: number){
    if(!localStorage.getItem('apiKey') || !this.appUser){
      this.broadcaster.broadcast('openLogin', 'some message');
      if (count) {
        this._projectService.setAction({
          id: id,
          type: this.type == 1 ? 'listed': this.type == 2 ? 'completed': 'likes',
          count: count,
          category: category
        });
      }
    } else {
      if(!count || count == 0)return;
      this.broadcaster.broadcast('usersModal', {itemId: id, count: count, category: category});
    }

  }
  
  setAction(id) {
    if (!this.appUser) {
      this._projectService.setAction({
        id: id,
        type: 'like',
        slug: this.goal.slug
      });
      this.broadcaster.broadcast('openLogin', 'some message');
    }
  }
  
  manageVote(id) {
    if(this.busy == true) return;
    this.busy = true;
    this.setAction(id);
    if(this.isMy())return;
    let type = (!this.is_vote)?'add':'remove';
    if(type == 'add'){
      this.voters_count += 1;
    }
    else this.voters_count -= 1;
    this._projectService[type + 'Vote'](id)
        .subscribe(
            () => {
              this.is_vote = !this.is_vote;
              this.busy = false;
            });

        // 'api/v1.0/success-story/add-vote/{storyId}': 'api/v1.0/success-story/remove-vote/{storyId}';

  }
  isMy(){
    return (!this.appUser || this.appUser.id == this.user.id);
  }
}
