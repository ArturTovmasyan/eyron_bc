import { Component, OnInit, Input , ViewEncapsulation} from '@angular/core';

import { Goal } from '../../interface/goal';
import { Broadcaster } from '../../tools/broadcaster';
import { ProjectService } from '../../project.service';


@Component({
  selector: 'app-goal-footer',
  templateUrl: './goal-footer.component.html',
  styleUrls: ['./goal-footer.component.less'],
  encapsulation: ViewEncapsulation.None
})
export class GoalFooterComponent implements OnInit {
  @Input() goal: Goal;
  constructor(private broadcaster: Broadcaster, private _projectService: ProjectService) { }

  ngOnInit() {
  }

  addGoal(id){
    let key = localStorage.getItem('apiKey');
    if(!key){
      this.broadcaster.broadcast('openLogin', 'some message');
      this._projectService.setAction({
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
        
      this._projectService.addUserGoal(id, {}).subscribe((data) => {
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
      this._projectService.setAction({
         id: id,
         type: 'done'
      });
    } else {
      this.goal.is_my_goal = 2;
        this.broadcaster.broadcast('doneModal', {
            'userGoal': {'goal':this.goal},
            'newAdded' : true
        });
      this._projectService.setDoneUserGoal(id).subscribe(() => {
          this.broadcaster.broadcast('add_my_goal'+id, {});
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
