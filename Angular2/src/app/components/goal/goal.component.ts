import { Component, OnInit, Input, ViewEncapsulation, Output, EventEmitter } from '@angular/core';

import { Goal } from '../../interface/goal';
import { ProjectService } from '../../project.service';

@Component({
  selector: 'app-goal',
  templateUrl: './goal.component.html',
  styleUrls: ['./goal.component.less'],
  encapsulation: ViewEncapsulation.None
})
export class GoalComponent implements OnInit {
  @Input() goal: Goal;
  @Input() type: string;
  @Input() userLocation: any;
  public hideDisableNearBy:boolean = false;
  public isLoggedIn:boolean = false;
  @Output('onHover') hoverEmitter: EventEmitter<any> = new EventEmitter();

  constructor(private _projectService: ProjectService) { }

  ngOnInit() {
    if (localStorage.getItem('apiKey')) {
      this.isLoggedIn = true;
    }
  }

  notInterest(){
    this._projectService.resetNearByGoal(this.goal.id).subscribe(
        data => {
        });
    this.hideDisableNearBy = true;
  }

}
