import { Component, OnInit } from '@angular/core';
import {CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';


import { ProjectService } from '../../project.service';

import {Goal} from '../../interface/goal';

@Component({
  selector: 'app-discover-goal',
  templateUrl: './discover-goal.component.html',
  styleUrls: ['./discover-goal.component.less']
})
export class DiscoverGoalComponent implements OnInit {
  goals:Goal[] = null;
  errorMessage:string;

  constructor(private _projectService: ProjectService, private _cacheService: CacheService) {}

  ngOnInit() {
    let data = this._cacheService.get('discoverGoals');
    if (data) {
      this.goals = data;
    } else {
      this.getDiscoverGoals()
    }

  }
  
  getDiscoverGoals() {
    this._projectService.getDiscoverGoals()
        .subscribe(
            goals => {
              this.goals = goals;
              this._cacheService.set('discoverGoals', goals, {maxAge: 3 * 24 * 60 * 60});
            },
              error => this.errorMessage = <any>error
            );
  }
}
