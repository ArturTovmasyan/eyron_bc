import { Component, OnInit, Input , ViewEncapsulation} from '@angular/core';

import {User} from "../../interface/user";

@Component({
  selector: 'leaderboard',
  templateUrl: './leaderboard.component.html',
  styleUrls: ['./leaderboard.component.less'] ,
  encapsulation: ViewEncapsulation.None
})
export class LeaderboardComponent implements OnInit {

  @Input() badge: any;
  @Input() index: Number;
  user:User;
  score:Number;
  points:Number;
  categories = ['innovator','motivator', 'traveller'];
  isTouchdevice:Boolean = (window.innerWidth > 600 && window.innerWidth < 992);
  isMobile:Boolean= (window.innerWidth < 768);

  constructor() { }

  ngOnInit() {
    if(this.badge){
      this.user = this.badge.user;
      this.score = this.badge.score;
      this.points = this.badge.points;
    }
  }

  getFullName(user){
    let name = user.first_name + user.last_name,
        count = this.isTouchdevice?50:((this.isMobile || (window.innerWidth > 991 && window.innerWidth < 1170))?16:24);
    return (name.length > count)?(name.substr(0,count -3) + '...'):name;
  }

}
