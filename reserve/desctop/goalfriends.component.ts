import { Component, OnInit, OnDestroy } from '@angular/core';
import { GoalFriend } from '../goal-friend';
import { ProjectService } from '../../project.service'
import { RouterModule, Routes, ActivatedRoute, Router, NavigationEnd } from '@angular/router';

// import { User } from '../interface/user'


@Component({
  selector: 'app-goalfriends',
  templateUrl: './goalfriends.component.html',
  styleUrls: ['./goalfriends.component.less']
})
export class GoalfriendsComponent extends GoalFriend{

  // public users:User[];
  // public reserve:User[];
  // public eventId: number = 0;
  // public busy: boolean = false;
  //
  // public start: number = 0;
  // public count: number = 20;
  // public search: string = '';
  //
  // public type:string = '';
  // public noItem:boolean = false;
  // public isDestroy: boolean = false;
  // public serverPath:string = '';
  // public errorMessage:string;

  constructor(protected route: ActivatedRoute, protected _projectService: ProjectService, protected router:Router) {
    super(route, _projectService, router);
    // router.events.subscribe((val) => {
    //   if(!this.isDestroy && this.eventId != val.id && val instanceof NavigationEnd){
    //     this.eventId = val.id;
    //     this.start = 0;
    //     this.type = this.route.snapshot.params['type']?this.route.snapshot.params['type']:'all';
    //     // this.search = this.route.snapshot.params['search']?this.route.snapshot.params['search']:'';
    //     this.users = null;
    //     this.reserve = null;
    //     this.noItem = false;
    //     window.scrollTo(0,0);
    //     this.getUserList();
    //     this.busy = false;
    //   }
    // })
  }

  // ngOnDestroy(){
  //   this.isDestroy = true;
  // }
  //
  // ngOnInit() {
  //   this.serverPath = this._projectService.getPath();
  //   this.search = this.route.snapshot.params['search']?this.route.snapshot.params['search']:'';
  // }
  //
  // getUserList() {
  //   this._projectService.getUserList(this.start, this.count, this.search, this.type)
  //       .subscribe(
  //           users => {
  //             this.noItem = !users.length;
  //             this.users = users;
  //             this.start += this.count;
  //             this.setReserve();
  //           },
  //           error => this.errorMessage = <any>error);
  // }
  //
  // setReserve(){
  //   this._projectService.getUserList(this.start, this.count, this.search, this.type)
  //       .subscribe(
  //           users => {
  //             this.reserve = users;
  //
  //             for(let item of this.reserve){
  //               let img;
  //               if(item.image_path){
  //                 img = new Image();
  //                 img.src = this.serverPath + item.image_path;
  //               }
  //             }
  //             this.start += this.count;
  //             this.busy = false;
  //           },
  //           error => this.errorMessage = <any>error);
  // }
  //
  // doSearch(){
  //   this.router.navigate(['/goal-friends/' + this.type + '/' + this.search]);
  // }
  //
  // resetFriends(){
  //   this.search = '';
  //   this.router.navigate(['/goal-friends/' + this.type]);
  // }
  //
  // getReserve(){
  //   this.users = this.users.concat(this.reserve);
  //   this.setReserve();
  // }
  //
  // onScroll(){
  //   if(this.busy || !this.reserve || !this.reserve.length)return;
  //   this.busy = true;
  //   this.getReserve();
  // }
}
