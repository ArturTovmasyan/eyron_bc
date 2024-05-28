import { Component, OnInit, Input , ViewEncapsulation} from '@angular/core';
import {User} from "../../interface/user";

@Component({
  selector: 'goal-friend',
  templateUrl: './goal-friend.component.html',
  styleUrls: ['./goal-friend.component.less'],
  encapsulation: ViewEncapsulation.None
})
export class GoalFriendComponent implements OnInit {
  @Input() user: User;
  name:String;
  constructor() { }

  ngOnInit() {
    this.name = this.user.first_name + ' ' + this.user.last_name;
  }

}
