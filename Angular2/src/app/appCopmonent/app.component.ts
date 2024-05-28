import { Component, OnInit, Input, ViewContainerRef, ViewChild,ElementRef } from '@angular/core';
import { MdDialog, MdDialogRef, MdDialogConfig} from '@angular/material';
// import { AddComponent} from './modals/add/add.component';
// import { DoneComponent} from './modals/done/done.component';
// import { UsersComponent} from './modals/users/users.component';
// import { CommonComponent} from './modals/common/common.component';
// import { ReportComponent} from './modals/report/report.component';
import { TranslateService} from 'ng2-translate';
import { Broadcaster} from '../tools/broadcaster';
import { ProjectService} from '../project.service';
import { Router } from '@angular/router';
import { CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';
import { Angulartics2, Angulartics2GoogleAnalytics} from 'angulartics2';
import { App} from '../app';
import {  HostListener, Inject} from "@angular/core";
import { DOCUMENT } from '@angular/platform-browser';
// import {User} from './interface/user';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.less']
})

export class AppComponent extends App {
    @ViewChild('footer')
    public footer: ElementRef;
    public nearbyscroll:boolean = false;
    // @Input() count:number;
    // public translatedText: string;
    // public supportedLanguages: any[];
    // public joinShow:boolean = false;
    // public show:boolean = false;
    // public newNotCount:number = 0;
    // public menus: any[];
    // public privacyMenu: any;
    // public serverPath:string = '';
    // public isTouchdevice:Boolean = (window.innerWidth > 600 && window.innerWidth < 992);
    // public isMobile:Boolean= (window.innerWidth < 768);
    // errorMessage:string;
    // public appUser:User;
    // public updatedEmail:any;
    // public busy:boolean = false;
    // public projectName:any;

  //  modal
  //   public reportModal:boolean = false;
  //   public commonId:number = 0;
  //   public reportData:any;
  //   public usersData:any;
  //   public fresh:any;
  //   public addData:any;
  //   public doneData:any;
  //   public writeTimeout:any;
  //   public regConfirmMenu:boolean = true;

    constructor(
        protected angulartics2GoogleAnalytics: Angulartics2GoogleAnalytics,
        protected angulartics2: Angulartics2,
        protected _translate: TranslateService,
        protected broadcaster: Broadcaster,
        protected _projectService: ProjectService,
        protected _cacheService: CacheService,
        protected router: Router,
        protected viewContainerRef: ViewContainerRef,
        protected dialog: MdDialog,
        @Inject(DOCUMENT) protected document: any
    ) { 
        super(
            angulartics2GoogleAnalytics, 
            angulartics2,_translate, 
            broadcaster, 
            _projectService,
            _cacheService,
            router, 
            viewContainerRef,
            dialog);
    }
    @HostListener("window:scroll", [])
    onWindowScroll() {
        this.broadcaster.on<any>('ideaShowMore')
            .subscribe(() =>{
                this.nearbyscroll = false;
            });
        this.nearbyscroll = false;
        let footerOffset =  this.footer.nativeElement.offsetTop -200;
        let number = this.document.body.scrollTop;
        if(number + 380 > footerOffset){
            this.nearbyscroll = true;
        }
        if(number > 250){
            this.upButton = true;
        } else {this.upButton = false;}
    }
    goUp(){
        let k = this.document.body.scrollTop;
        let point = Math.round(k/500) + 8;
        for(let i = 0; i < 500 ; i++){
            setTimeout(()=>{
                window.scroll(0,k);
                k -= point;
            },1)
        }
        setTimeout(() =>{
            this.upButton = false;
        },200);
    }

    // ngOnInit() {
    //     this.projectName = this._projectService.getAngularPath();
    //     this.serverPath = this._projectService.getPath();
    //     // standing data
    //     this.supportedLanguages = [
    //       { display: 'English', value: 'en' },
    //       { display: 'Russian', value: 'ru' }
    //     ];
    //
    //     this.selectLang('en');
    //     this._cacheService.set('supportedLanguages', this.supportedLanguages, {maxAge: 3 * 24 * 60 * 60});
    //
    //     let data = this._cacheService.get('footerMenu');
    //     if(data){
    //         this.menus = data[0];
    //         this.privacyMenu = data[1];
    //     }else {
    //         this.getBottomMenu();
    //     }
    //
    //     this.broadcaster.on<any>('regConfirmMenu')
    //         .subscribe((data) => {
    //             this.regConfirmMenu = data;
    //            
    //             if(this.appUser) {
    //                 this.updatedEmail = this._cacheService.get('confirmRegEmail' + this.appUser.id);
    //             }
    //         });
    //
    //     if(localStorage.getItem('apiKey')){
    //         this.appUser = this._cacheService.get('user_');
    //         if(!this.appUser) {
    //             this._projectService.getUser()
    //                 .subscribe(
    //                     user => {
    //                         this.appUser = user;
    //                         this.selectLang((user && user.language)?user.language:'en');
    //                         this._cacheService.set('user_', user, {maxAge: 3 * 24 * 60 * 60});
    //                         this.broadcaster.broadcast('getUser', user);
    //                         this.purgeFresh();
    //                     },
    //                     error => localStorage.removeItem('apiKey'));
    //         } else {
    //             this.selectLang((this.appUser.language)?this.appUser.language:'en');
    //         }
    //     }
    //
    //     this.purgeFresh();
    //     if(this.appUser) {
    //         this.updatedEmail = this._cacheService.get('confirmRegEmail' + this.appUser.id);
    //
    //         if(!this.updatedEmail) {
    //             this.updatedEmail = this.appUser.username;
    //         }
    //     }
    //
    //       this.broadcaster.on<any>('updateNoteCount')
    //           .subscribe(count => {
    //               this.newNotCount = count;
    //           });
    //
    //     this.broadcaster.on<User>('login')
    //         .subscribe(user => {
    //           this.appUser = user;
    //           this.purgeFresh();
    //           this.selectLang((this.appUser.language)?this.appUser.language:'en');
    //           this._cacheService.set('user_', user, {maxAge: 3 * 24 * 60 * 60});
    //           this.broadcaster.broadcast('getUser', user);
    //         });
    //
    //     this.broadcaster.on<string>('logout')
    //         .subscribe(message => {
    //           this.appUser = null;
    //         });
    //
    //     this.broadcaster.on<string>('openLogin')
    //         .subscribe(message => {
    //           this.appUser = null;
    //           this.joinShow = true;
    //         });
    //
    //     //modals
    //       this.broadcaster.on<number>('commonModal')
    //           .subscribe(id => {
    //               this.commonId = id;
    //               let dialogRef: MdDialogRef<CommonComponent>;
    //               let config = new MdDialogConfig();
    //               // config.height = '600px';
    //               config.viewContainerRef = this.viewContainerRef;
    //               dialogRef = this.dialog.open(CommonComponent, config);
    //               dialogRef.componentInstance.id = id;
    //               dialogRef.afterClosed().subscribe(result => {
    //
    //               });
    //               // this.commonModal = true;
    //           });
    //
    //       this.broadcaster.on<any>('reportModal')
    //           .subscribe(data => {
    //               this.reportData = data;
    //               let dialogRef: MdDialogRef<ReportComponent>;
    //               let config = new MdDialogConfig();
    //               config.height = '400px';
    //               config.viewContainerRef = this.viewContainerRef;
    //               dialogRef = this.dialog.open(ReportComponent, config);
    //               dialogRef.componentInstance.data = data;
    //               dialogRef.afterClosed().subscribe(result => {
    //
    //               });
    //               this.reportModal = true;
    //           });
    //
    //       this.broadcaster.on<any>('usersModal')
    //           .subscribe(data => {
    //               this.usersData = data;
    //               let dialogRef: MdDialogRef<UsersComponent>;
    //               let config = new MdDialogConfig();
    //               config.height = '600px';
    //               config.viewContainerRef = this.viewContainerRef;
    //               dialogRef = this.dialog.open(UsersComponent, config);
    //               dialogRef.componentInstance.data = data;
    //               dialogRef.afterClosed().subscribe(result => {
    //
    //               });
    //               // this.usersModal = true;
    //           });
    //
    //       this.broadcaster.on<any>('addModal')
    //           .subscribe(data => {
    //               if(this.busy)return;
    //               this.busy = true;
    //               // this.addData = data;
    //               if(!this.appUser.activity){
    //                   this._projectService.getUser()
    //                       .subscribe(
    //                           user => {
    //                               console.log(user);
    //                               this.appUser = user;
    //                               this._cacheService.set('user_', user, {maxAge: 3 * 24 * 60 * 60});
    //                               this.broadcaster.broadcast('getUser', user);
    //                           },
    //                           error => localStorage.removeItem('apiKey'));
    //               }
    //
    //               if(data.newCreated){
    //                   this.angulartics2.eventTrack.next({ action: 'Goal create', properties: { category: 'Goal', label: 'Goal create from angular2'}});
    //               }
    //               if(data.newAdded){
    //                   this.angulartics2.eventTrack.next({ action: 'Goal add', properties: { category: 'Goal', label: 'Goal add from angular2'}});
    //               } else {
    //                   this.angulartics2.eventTrack.next({ action: 'Goal manage', properties: { category: 'Goal', label: 'Goal manage from angular2'}});
    //               }
    //
    //               let dialogRef: MdDialogRef<AddComponent>;
    //               let config = new MdDialogConfig();
    //               config.viewContainerRef = this.viewContainerRef;
    //               //config.height = '600px';
    //               dialogRef = this.dialog.open(AddComponent, config);
    //               dialogRef.componentInstance.newCreated = data.newCreated;
    //               dialogRef.componentInstance.newAdded = data.newAdded;
    //               dialogRef.componentInstance.userGoal = data.userGoal;
    //               dialogRef.afterClosed().subscribe(result => {
    //                   this.busy = false;
    //                   if(result){
    //                       if(result.remove){
    //                           if(data.userGoal.goal.author && data.userGoal.goal.author.id == this.appUser.id){
    //                               this.angulartics2.eventTrack.next({ action: 'Goal delete', properties: { category: 'Goal', label: 'Goal delete from angular2'}});
    //                           }else {
    //                               this.angulartics2.eventTrack.next({ action: 'Goal unlisted', properties: { category: 'Goal', label: 'Goal unlisted from angular2'}});
    //                           }
    //                          
    //                           this.broadcaster.broadcast('removeUserGoal_' + result.remeove, result.remove);
    //                           this.broadcaster.broadcast('removeGoal', result.remove);
    //                           this.broadcaster.broadcast('removeGoal'+data.userGoal.goal.id, data.userGoal.goal.id);
    //                       } else {
    //                           this.broadcaster.broadcast('saveUserGoal_' + result.id, result);
    //                           this.broadcaster.broadcast('saveGoal', result);
    //                           this.broadcaster.broadcast('saveGoal'+result.goal.id, result);
    //                       }
    //                   } else {
    //                       this.broadcaster.broadcast('addGoal', data.userGoal);
    //                       this.broadcaster.broadcast('addGoal'+data.userGoal.goal.id, data.userGoal);
    //                   }
    //
    //                   this.testCache(data.userGoal.goal.id);
    //               });
    //               // this.addModal = true;
    //           });
    //
    //       this.broadcaster.on<any>('doneModal')
    //           .subscribe(data => {
    //               if(!this.appUser.activity){
    //                   this._projectService.getUser()
    //                       .subscribe(
    //                           user => {
    //                               this.appUser = user;
    //                               this._cacheService.set('user_', user, {maxAge: 3 * 24 * 60 * 60});
    //                               this.broadcaster.broadcast('getUser', user);
    //                           },
    //                           error => localStorage.removeItem('apiKey'));
    //               }
    //               if(data.newAdded){
    //                   this.angulartics2.eventTrack.next({ action: 'Goal done', properties: { category: 'Goal', label: 'Goal done from angular2'}});
    //               }
    //              
    //               this.broadcaster.broadcast('doneGoal', data);
    //               this.doneData = data;
    //               this.addData = data;
    //               let dialogRef: MdDialogRef<DoneComponent>;
    //               let config = new MdDialogConfig();
    //               // config.height = '600px';
    //               config.viewContainerRef = this.viewContainerRef;
    //               dialogRef = this.dialog.open(DoneComponent, config);
    //               dialogRef.componentInstance.newAdded = data.newAdded;
    //               dialogRef.componentInstance.userGoal = data.userGoal;
    //               dialogRef.afterClosed().subscribe(result => {
    //                   this.broadcaster.broadcast('doneGoal'+data.userGoal.goal.id, {});
    //                   this.testCache(data.userGoal.goal.id);
    //               });
    //               // this.doneModal = true;
    //           });
    //     this.broadcaster.on<any>('draftCount')
    //         .subscribe( () => {
    //             this.appUser.draft_count += 1;
    //         }
    //         );
    //     this.broadcaster.on<any>('removeDraft')
    //         .subscribe( () => {
    //                 this.appUser.draft_count -= 1;
    //             }
    //         );
    // }
    //
    // purgeFresh(){
    //     if(this.appUser) {
    //         this.fresh = this._cacheService.get('fresh'+this.appUser.id);
    //         if(!this.fresh){
    //             this.fresh = {
    //                 'activities':true,
    //                 'featuredIdea':true,
    //                 'topIdea':true
    //             }
    //         }
    //     }
    // }
    //
    // testCache(id){
    //     let featuredIdea = this._cacheService.get('featuredIdea');
    //     let topIdea = this._cacheService.get('topIdea');
    //     let activities = this._cacheService.get('activities' + this.appUser.id);
    //     this.changeCache(featuredIdea, 'featuredIdea', id);
    //     this.changeCache(topIdea, 'topIdea', id);
    //     this.changeCache(activities, 'activities', id);
    //     this._cacheService.set('fresh'+this.appUser.id, this.fresh);
    // }
    //
    // changeCache(dates, name, id){
    //     if(dates && dates.length){
    //         for(let data of dates){
    //             if(data.goals){
    //                 for(let goal of data.goals){
    //                     if(goal.id == id){
    //                         this.fresh[name] = false;
    //                         return;
    //                     }
    //                 }
    //             } else {
    //                 if(data.id == id){
    //                     this.fresh[name] = false;
    //                     return;
    //                 }
    //             }
    //         }
    //     }
    // }
    //
    // toogleNote(){
    //     if(this.show != true){
    //         this.writeTimeout = setTimeout(() =>{
    //             this.show = !this.show;
    //         }, 100)
    //     }
    // }
    // hideNote(ev){
    //     this.show = false;
    // }
    // newCount(ev){
    //     this.newNotCount = ev;
    // }
    // hideJoin(ev){
    //     this.joinShow = false;
    // }
    //
    // isCurrentLang(lang: string) {
    //     return lang === this._translate.currentLang;
    // }
    //
    // selectLang(lang: string) {
    //     // set default;
    //     this._translate.use(lang);
    // }
    //
    // closeDropdown(){
    //     if(this.show)this.show = false
    // }
    //
    // logout(){
    //   localStorage.removeItem('apiKey');
    //   this.router.navigate(['/']);
    //   this.appUser = null;
    // }
    //
    // getBottomMenu() {
    //     this._projectService.getBottomMenu()
    //         .subscribe(
    //             menus => {
    //               this.menus = menus;
    //
    //               for(let index in this.menus){
    //                 if (this.menus[index].isTerm) {
    //                   this.privacyMenu = this.menus[index];
    //                 }
    //               }
    //               this._cacheService.set('footerMenu', [menus, this.privacyMenu], {maxAge: 3 * 24 * 60 * 60});
    //             },
    //             error => this.errorMessage = <any>error);
    // }
}