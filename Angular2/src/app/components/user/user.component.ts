import { Component, OnInit, Input , ViewEncapsulation} from '@angular/core';
import { Broadcaster } from '../../tools/broadcaster';
import { ProjectService } from '../../project.service'

import { User } from '../../interface/user';

@Component({
  selector: 'app-user',
  templateUrl: './user.component.html',
  styleUrls: ['./user.component.less'],
  encapsulation: ViewEncapsulation.None
})
export class UserComponent implements OnInit {
  @Input() user: User;
  public serverPath:string = '';
  constructor(private broadcaster: Broadcaster, private _projectService: ProjectService) { }

  ngOnInit() {
    this.serverPath = this._projectService.getPath();
  }

  openCommons(id){
    if(!localStorage.getItem('apiKey')){
      this.broadcaster.broadcast('openLogin', 'some message');
    } else {
      this.broadcaster.broadcast('commonModal', {id:this.user.id, count: this.user.common_goals_count });
    }
  }

}
