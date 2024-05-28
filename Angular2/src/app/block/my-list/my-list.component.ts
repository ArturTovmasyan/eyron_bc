import { Component, OnInit } from '@angular/core';
import {User} from "../../interface/user";
import { ProjectService } from '../../project.service';
import {CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';

@Component({
  selector: 'my-list-block',
  templateUrl: './my-list.component.html',
  styleUrls: ['./my-list.component.less']
})
export class MyListBlockComponent implements OnInit {

  public appUser:User;
  constructor(
      private _projectService: ProjectService,
      private _cacheService: CacheService) { }

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
