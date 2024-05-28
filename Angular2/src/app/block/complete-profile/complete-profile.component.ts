import { Component, OnInit , ViewEncapsulation, Input} from '@angular/core';
import { ProjectService } from '../../project.service';
import { CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';


@Component({
  selector: 'complete-profile-block',
  templateUrl: './complete-profile.component.html',
  styleUrls: ['./complete-profile.component.less'],
  encapsulation: ViewEncapsulation.None
})
export class CompleteProfileBlockComponent implements OnInit {
  public appUser: any;
  public locale: string = 'ru';
    constructor(
      private _projectService: ProjectService,
      private _cacheService: CacheService
  ) { }

  ngOnInit() {
    this.appUser = this._projectService.getMyUser();
    if (!this.appUser) {
      this.appUser = this._cacheService.get('user_');
      if(!this.appUser) {
        this._projectService.getUser()
            .subscribe(
                user => {
                  this.appUser = user;
                  this._cacheService.set('user_', user, {maxAge: 3 * 24 * 60 * 60});
                })
      }
    }
  }

}
