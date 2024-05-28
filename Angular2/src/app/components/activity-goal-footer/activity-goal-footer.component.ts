import { Component, OnInit, Input } from '@angular/core';
import { Broadcaster } from '../../tools/broadcaster';
import { ProjectService } from '../../project.service';

import { Goal } from '../../interface/goal';
import { Activity } from '../../interface/activity';

@Component({
  selector: 'activity-goal-footer',
  templateUrl: './activity-goal-footer.component.html',
  styleUrls: ['./activity-goal-footer.component.less']
})
export class ActivityGoalFooterComponent implements OnInit {
  @Input() goal: Goal;
  @Input() activity: Activity;
  constructor(private broadcaster: Broadcaster, private projectService: ProjectService) { }

  ngOnInit() {
  }

  addGoal(id){
    let key = localStorage.getItem('apiKey');
    if(!key){
      this.broadcaster.broadcast('openLogin', 'some message');
      this.projectService.setAction({
        id: id,
        type: 'add'
      });
    } else {
      this.goal.is_my_goal = 1;
      this.broadcaster.on<any>('saveGoal'+this.goal.id)
          .subscribe(data => {
            this.goal.is_my_goal = data.status;
          });

      this.broadcaster.on<any>('addGoal'+this.goal.id)
          .subscribe(data => {
            this.goal.is_my_goal = 1;
          });

      this.broadcaster.on<any>('removeGoal'+this.goal.id)
          .subscribe(data => {
            this.goal.is_my_goal = 0;
          });

      this.broadcaster.broadcast('addModal', {
        'userGoal': {'goal':this.goal},
        'newAdded' : true,
        'newCreated' : false
      });

      this.projectService.addUserGoal(id, {}).subscribe((data) => {
        this.broadcaster.broadcast('addModalUserGoal', data);
        // this.broadcaster.broadcast('addModal', {
        //   'userGoal': data,
        //   'newAdded' : true,
        //   'newCreated' : false
        // });
        this.broadcaster.broadcast('add_my_goal'+id, {});
      });
    }
  }

  completeGoal(id){
    let key = localStorage.getItem('apiKey');
    if(!key){
      this.broadcaster.broadcast('openLogin', 'message');
      this.projectService.setAction({
        id: id,
        type: 'done'
      });
    } else {
      this.goal.is_my_goal = 2;
      this.broadcaster.broadcast('doneModal', {
        'userGoal': {'goal':this.goal},
        'newAdded' : true
      });
      this.projectService.setDoneUserGoal(id).subscribe(() => {
        this.broadcaster.broadcast('add_my_goal'+id, {});
        this.projectService.getStory(id).subscribe((data)=> {
          this.broadcaster.broadcast('doneModalUserGoal', data);
          // this.broadcaster.broadcast('doneModal', {
          //   'userGoal': data,
          //   'newAdded' : true
          // });
        })
      });
    }
  }

  showComment(activity, goal){
    if(activity){
      if(activity.createComment && !activity.showComment){
        this.broadcaster.broadcast('activityComment'+goal.slug, '')
      }
      activity.createComment = true;
      // $timeout(function () {
      activity.showComment = !activity.showComment;
      // },300);
    } else {
      goal.createComment = true;
      // $timeout(function () {
        goal.showComment = !goal.showComment;
      // },300);
    }
  }
  
}
