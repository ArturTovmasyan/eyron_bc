import { Component, OnInit, Input, ViewChild, ElementRef } from '@angular/core';
import { ProjectService } from '../../project.service'
import { Broadcaster } from '../../tools/broadcaster';
import { Router } from '@angular/router';
import {CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';

import { Comment } from '../../interface/comment';

@Component({
  selector: 'app-comment',
  templateUrl: './comment.component.html',
  styleUrls: ['./comment.component.less']
})
export class CommentComponent implements OnInit {
  @ViewChild('myScroll')
  public myScroll: any;
  public serverPath:string = '';
  @Input() data: any;
  public isInner: boolean = false;
  public appUser: any;
  public isMobile = (window.innerWidth < 768);
  public busy: boolean = false;
  public isModal: boolean = false;
  public ready: boolean = false;
  public showStepCount: number = 5;
  public forEnd: number = 0;
  public commentsDefaultCount: number = 5;
  public commentsLength: number;
  public commentBody: string;
  public commentIndex: any = null;
  public comments: Comment[];
  
  constructor(
      private broadcaster: Broadcaster, 
      private _projectService: ProjectService,
      private _cacheService: CacheService,
      private router: Router) { }

  ngOnInit() {
    if(document.querySelector('.mat-dialog-container')){
      (<any>document.querySelector('.mat-dialog-container')).style.height = "62%";
    }
    //   let p = 0;
    // setInterval(()=>{
    //   let containerPos = this.findPos(document.getElementById("scroll-container"));
    //   let position = this.findPos(document.getElementById("comment-"+p)) - containerPos;
    //   this.myScroll.scrollTo(position);
    //   p +=1;
    // },5000);
    if(!localStorage.getItem('apiKey')){
      // this.router.navigate(['/']);
    } else {
      this.appUser = this._projectService.getMyUser();
      if(!this.appUser){
        this.appUser = this._cacheService.get('user_');
        if(!this.appUser){
          this._projectService.getUser()
              .subscribe(
                  user => {
                    this.appUser = user;
                    this._cacheService.set('user_', user, {maxAge: 3 * 24 * 60 * 60});
                    this.broadcaster.broadcast('getUser', user);
                  })
        }
      }
      
      if(this.data && this.data.slug){
        this._projectService.getComments(this.data.slug).subscribe(
            comments => {
              this.comments  = comments;
              this.ready = true;
              this.broadcaster.on<any>('commentshow')
                  .subscribe( () => {
                    setTimeout(()=>{
                      let p = this.comments.length - 1;
                      let containerPos = this.findPos(document.getElementById(this.data.slug));
                      let position: number = this.findPos(document.getElementById("comment-"+p+'-'+this.data.slug));
                      if(!containerPos && !position)return;
                      position -= containerPos;
                      this.doScroll();
                    },1000);

                  });
              setTimeout(()=> {
                this.broadcaster.on<any>('activityComment'+this.data.slug)
                    .subscribe(data =>{
                      setTimeout(()=> {
                       this.doScroll();
                      },500);
                      });

                this.doScroll()
              },500);
              this.commentsLength = this.comments.length - this.commentsDefaultCount;
              for(let i = 0;i < this.comments.length; i++){
                this.comments[i].visible = (i > this.comments.length - this.commentsDefaultCount - 1);
                this.comments[i].reply = true;
              }
            });

      }
    }
    this.serverPath = this._projectService.getPath();
    this.isInner = this.data.inner;
  }

  findPos(obj:any){
    if (!obj) return;
    let curtop = 0;
    if (obj.offsetParent) {
      do {
        curtop += obj.offsetTop;
      } while (obj = obj.offsetParent);
      return curtop;
    }

    return 0;
  }

  showMoreComment () {
    if(this.commentsLength === this.forEnd){
      return;
    }
    if(this.commentIndex == null){
      this.commentIndex = this.comments.length - this.commentsDefaultCount - 1;
    }

    let startIndex = this.commentIndex;

    if(this.commentsLength > this.showStepCount){
      this.commentsLength -= this.showStepCount;
      this.commentIndex -= this.showStepCount;
    } else {
      this.commentIndex -= this.commentsLength;
      this.commentsLength = this.forEnd;
    }

    for(let i = startIndex; i > this.commentIndex; i--){
      this.comments[i].visible = true;
    }
  };
  
  writeReply(ev, comment,isClick?){
    if((ev.which == 13 || isClick) && comment.replyBody.length) {
      ev.preventDefault();
      ev.stopPropagation();
      if(!this.busy) {
        this.busy = true;
        this._projectService.putComment(this.data.id, comment.replyBody, comment.id).subscribe(
            data => {
              comment.reply = true;
                comment.replyBody = '';
                this.busy = false;
                comment.children.push(data);
            });
        // CommentManager.add({
        //   param1: (this.data.id),
        //   param2: comment.id,
        //   path: (this.lsGoalId?'comments':'blog-comment')
        // }, {'commentBody': comment.replyBody}, function (data) {
        //
        // });
      }
    }
  };

  writeComment = function (ev, isClick?) {
    if((ev.which == 13 || isClick) && this.commentBody.length){
      ev.preventDefault();
      ev.stopPropagation();
      if(!this.busy){
        this.busy = true;
        this._projectService.putComment(this.data.id, this.commentBody).subscribe(
            data => {
              this.commentBody = '';
                this.busy = false;
                data.visible = true;
                data.reply = true;
                this.comments.push(data);
            });
        // CommentManager.add({param1:(id), path: ('comments')}, {'commentBody': this.commentBody},function (data) {
        //   this.commentBody = '';
        //   this.busy = false;
        //   this.comments.push(data);
        //
        // });
      }
    }
  };
  
  report( contentType, contentId){
    if(!localStorage.getItem('apiKey')){
      this.broadcaster.broadcast('openLogin', 'some message');
      this._projectService.setAction({
        id: {contentType,contentId},
        type: 'report'
      });
    } else {
      this.broadcaster.broadcast('reportModal', {contentType,contentId});
    }
  }
  doScroll(){
    if(this.comments.length > 0){
      window.scroll(0,this.findPos(document.getElementById(this.data.slug)));
    }
    else {
      window.scroll(0,window.scrollY + 50);
    }

    let k = 0;
    for(let i = 0;i<500; i++){
      setTimeout(()=>{
        this.myScroll.scrollTo(k);
        k +=2;
      },1)
    }
  }
}
