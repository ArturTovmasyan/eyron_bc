import { Component, OnInit, OnDestroy } from '@angular/core';
import { GoalFriend } from '../goal-friend';
import { ProjectService } from '../../project.service'
import { RouterModule, Routes, ActivatedRoute, Router, NavigationEnd } from '@angular/router';

@Component({
  selector: 'app-goalfriends',
  templateUrl: './api.goalfriends.component.html',
  styleUrls: ['./goalfriends.component.less']
})
export class GoalfriendsComponent extends GoalFriend{

  constructor(protected route: ActivatedRoute, protected _projectService: ProjectService, protected router:Router) {
    super(route, _projectService, router);
  }
}
