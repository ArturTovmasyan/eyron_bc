import { Component, OnInit, Input } from '@angular/core';

@Component({
  selector: 'create-goal',
  templateUrl: './create-goal.component.html',
  styleUrls: ['./create-goal.component.less']
})
export class CreateGoalComponent implements OnInit {
  @Input() myProfile: string ;
  @Input() myIdeasCount: string ;
  constructor() { }

  ngOnInit() {
  }

}
