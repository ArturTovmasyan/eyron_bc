import { Component, OnInit, Input } from '@angular/core';

import { Activity } from '../../interface/activity';

@Component({
  selector: 'activity-goal',
  templateUrl: './activity-goal.component.html',
  styleUrls: ['./activity-goal.component.less',]
})
export class ActivityGoalComponent implements OnInit {
  @Input() activity: Activity;
  constructor() { }

  ngOnInit() {
  }

}
