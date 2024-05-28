import { Component, OnInit, Output, Input, EventEmitter } from '@angular/core';
import {ProjectService} from '../../project.service';
import { Broadcaster} from '../../tools/broadcaster';
import { Router } from '@angular/router';

@Component({
  selector: 'notification-dropdown',
  templateUrl: './notification-dropdown.component.html',
  styleUrls: ['./notification-dropdown.component.less']
})
export class NotificationDropdownComponent implements OnInit {
    @Output('changeNote') noteHideEmitter: EventEmitter<any> = new EventEmitter();
    @Output('count') notCount: EventEmitter<any> = new EventEmitter();
    public notifications: any[];
    public start: number = 0;
    public width: number = 430;
    public end: number = 10;
    public count: number;
    public errorMessage:string;
    public busy: boolean = false;
    public reserve: any[];
    public serverPath:string = '';
    public time: any[];
    public userPage:boolean = false;
    constructor(
      private _projectService: ProjectService,
      private router: Router,
      private broadcaster: Broadcaster

    ) { }

    ngOnInit() {
        if(window.innerWidth < 768){
            this.width = window.innerWidth;
        }
        this.serverPath = this._projectService.getPath();
        this.getNotifications();
          this.broadcaster.on<any>('updateNoteCount')
            .subscribe(index => {
              this.count = index;
              if(index == 0){
                for(let i in this.notifications){
                  this.notifications[i].is_read = true;
                }
              }
    
            });
        this.broadcaster.on<any>('removeFromPage')
            .subscribe(index => {
              for(let i in this.notifications){
                if(this.notifications[i].id == index){
                  this.notifications.splice(+i,1);
                  break;
                }
              }
            });
    
        setInterval(() => {
          this.getNewNotifications();
        }, 30000);

    }
    getNotifications() {
        this._projectService.getNotifications(this.start, this.end)
            .subscribe(

                notify => {
                  this.notifications = notify.userNotifications;
                  this.count = notify.unreadCount;
                  this.notCount.emit(this.count);
                },
                error => this.errorMessage = <any>error);
    }
    getNewNotifications(){
        let lastId = this.notifications.length ? this.notifications[0].id : 0;
        this._projectService.getNewNotifications(this.start,this.end,(-1)*lastId)
            .subscribe(
                notify =>{
                  this.notifications = notify.userNotifications.concat(this.notifications);
                  this.count -= (-1) * notify.userNotifications.length;
                  this.notCount.emit(this.count);
                },
                error => this.errorMessage = <any>error);
    }

    bodyInHtml(body) {
        let words = body.split(" "),
            lastWord = words[words.length -1];
        return body.slice(0, -1 * lastWord.length) + "<a>"+ lastWord + "</a>";
    };

    delete(id, index){
        this._projectService.deleteNotifications(id)
            .subscribe(
                () => {}
            );
        this.broadcaster.broadcast('removeFromDrop',this.notifications[index].id);
        if(this.notifications[index].is_read == false){
          this.count -= 1;
          this.notCount.emit(this.count);
        }
        this.notifications.splice(index,1);
    };

    readAll(){
        this._projectService.readAllNotifications()
            .subscribe(
                () => {}
            );
        for(var i in this.notifications){
          this.notifications[i].is_read = true;
        }
        this.count = 0;
        this.notCount.emit(this.count);
        this.broadcaster.broadcast('markAllAsRead');
    }

    singleRead(id,index){
        this._projectService.readSigle(id)
            .subscribe(
                () => {}
            );
        if(this.notifications[index].is_read == false){
            this.notifications[index].is_read = true;
            this.count -= 1;
            this.notCount.emit(this.count);
        }
    }

    getInterval = function (lastActivity) {
        let result = {'time' : -1, 'title' : null};
        let one_day=1000*60*60*24;
        let one_hour=1000*60*60;
        let one_minute=1000*60;

        if (!lastActivity) {
            return result;
        }

        let now = (new Date()).getTime();
        let ms = (new Date(lastActivity)).getTime();

        let d = now - ms,
            dd = Math.floor(d/one_day),
            h = Math.floor(d/one_hour),
            mm = Math.floor(d/one_minute);

        // activity result
        if (d) {
            if(dd > 1) {
                result = {'time': 0, 'title': 'datetime'};
            } else if(dd > 0) {
                result = {'time': 0 , 'title': 'yesterday'};
            } else  if(h > 0) {
                result = {'time': h , 'title': 'hr'};
            } else if(mm > 1){
                result = {'time': mm, 'title': 'minute'};
            } else {
                result = {'time': 1, 'title': 'now'};
            }
        }

        return result;
    };

  goNotificationPage(notify,index){
      if(!this.userPage){
          this.singleRead(notify.id,index);
          this.router.navigate([notify.notification.link]);
          this.hideNote();
      }
  }
  hideNote(){
    this.noteHideEmitter.emit(null)
  }
  goToUserPage(){
      this.userPage = true;
      this.noteHideEmitter.emit(null)
    }
}

