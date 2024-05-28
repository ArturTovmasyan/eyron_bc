import { Component} from '@angular/core';
import {ProjectService} from '../../project.service';
import { ActivatedRoute, Router } from '@angular/router';
import { Leaderboard } from '../leaderboard';
import { MetadataService } from 'ng2-metadata';
import { CacheService} from 'ng2-cache/ng2-cache';

@Component({
  selector: 'app-leaderboard',
  templateUrl: './api.leaderboard.component.html',
  styleUrls: ['./leaderboard.component.less']
})
export class LeaderboardComponent extends Leaderboard {

  constructor(
      protected metadataService: MetadataService,
      protected _projectService: ProjectService,
      protected router:Router,
      protected _cacheService: CacheService,
      protected route: ActivatedRoute
  ) {
      super(metadataService, _projectService, router, _cacheService, route);
  }
}
