import { Component, OnInit, ViewEncapsulation, ElementRef, ViewChild, HostListener} from '@angular/core';
import { ProjectService } from '../project.service';
import { Broadcaster } from '../tools/broadcaster';
import { CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';
import { RouterModule, Routes, ActivatedRoute, Router, Params } from '@angular/router';
import { MetadataService } from 'ng2-metadata';

import { Goal} from '../interface/goal';
import { User} from '../interface/user';
import { Story} from '../interface/story';
import { UserGoal} from "../interface/userGoal";
import { MdDialog,MdDialogRef} from "@angular/material";
import { ShareComponent} from "../modals/share/share.component";
import { CommentComponent} from "../components/comment/comment.component";
import { Meta } from "@angular/platform-browser";

@Component({
  selector: 'app-inner',
  templateUrl: './inner.component.html',
  styleUrls: ['./inner.component.less','../goal-create/goal-create.component.less'],
  encapsulation: ViewEncapsulation.None
})
export class InnerComponent implements OnInit {
  @ViewChild('ticker') tickerView: ElementRef;
  @ViewChild('goalImage') goalImage: ElementRef;
  @ViewChild('container') container: ElementRef;
  @ViewChild('mainSlider') mainSlider: any;
  @ViewChild('sliderImage') sliderImage: ElementRef;
  quoteHeight: number;
  goalImageHeight: number;
  slideHeight: number = 435;
  fullHeight: boolean;
  seeAlsoShow: boolean;
  public goal:Goal = null;
  public errorMessage:string;
  public linkToShare:string;
  public sharePath:string;
  public serverPath:string = '';
  public type:string = 'inner';
  public imgPath:string = '';
  public aphorisms:any[];
  public aphorismIndex:number = 0;
  public delay:number = 8000;
  public listedByUsers:any[];
  public doneByUsers:any[];
  public isDesktop:boolean = (screen.width >= 992  && window.innerWidth >= 992);
  public stories:Story[];
  public appUser:User;
  public userGoal:UserGoal;
  public show:boolean = false;
  public lightbox:boolean = false;
  public scroll:boolean;
  public lightBoxData:any;
  public angularPath:any;

  public config: any = {
    pagination: '.swiper-pagination',
    paginationClickable: true,
    autoHeight: true,
    // loop: true,
    nextButton: '.swiper-button-next',
    prevButton: '.swiper-button-prev',
    spaceBetween: 30,
    autoplay: 3000
  };
  public videoConfig: Object = {
    pagination: '.swiper-pagination',
    paginationClickable: true,
    nextButton: '.swiper-button-next',
    prevButton: '.swiper-button-prev',
    spaceBetween: 30,
    autoplay: 3000
  };

  constructor(
      private metadataService: MetadataService,
      private meta: Meta,
      private router: Router,
      private _projectService: ProjectService,
      private _cacheService: CacheService,
      private broadcaster: Broadcaster,
      private route: ActivatedRoute,
      private  dialog: MdDialog
  ) {}

  @HostListener('window:resize', ['$event'])
  onResize(event) {
    // event.target.innerWidth;
    this.imageResize();
  }

  ngOnInit() {
      this.route.params.forEach((params:Params) => {
          this.goal = null;
          let goalSlug = params['slug'];
          this.seeAlsoShow = false;
          if(params['page']){
              this.type = params['page']
          }

          window.scrollTo(0,0);
          // load data
          this.getProject(goalSlug);
      });

      this.broadcaster.on<any>('menuScroll')
          .subscribe( data => {
              this.scroll = data;
          });

    this.sharePath = this._projectService.getPath();
    this.angularPath = this._projectService.getAngularPath();
    if(localStorage.getItem('apiKey')){
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

    this.serverPath = this._projectService.getPath();
    this.imgPath = this.serverPath + '/bundles/app/images/cover2.jpg';
  }

  closeLightBox(){
    this.lightbox = false;
  }
  /**
   *
   * @param slug
   */
  getProject(slug:string) {
    this._projectService.getGoal(slug)
        .subscribe(
            data => {
              this.seeAlsoShow = true;
              this.goal = data.goal;
              this.config.loop = (this.goal && this.goal.images && this.goal.images.length > 1);
              this.aphorisms = data.aphorisms;
              this.listedByUsers = Object.keys(data.listedByUsers).map(function(key) {
                return data.listedByUsers[key];
              });
              this.doneByUsers = Object.keys(data.doneByUsers).map(function(key) {
                return data.doneByUsers[key];
              });
              if(this.goal){
                  this.metadataService.setTitle(this.goal.title);

                  this.meta.addTag({ name: 'og:image', content: this.goal.cached_image });
                  this.meta.addTag({ name: 'description', content: this.goal.description });
                  this.meta.addTag({ name: 'og:description', content: this.goal.description });
                  this.meta.addTag({ name: 'og:title', content: this.goal.title });
                  this.meta.addTag({ name: 'title', content: this.goal.title });
                  // this.metadataService.setTag('title', this.goal.title);
                  // this.metadataService.setTag('og:image', this.goal.cached_image);
                  // this.metadataService.setTag('description', this.goal.description);
                  // this.metadataService.setTag('og:description', this.goal.description);
                  // this.metadataService.setTag('og:title', this.goal.title);
                // var allMetaElements = document.getElementsByTagName('meta');
                // for (var i=0; i<allMetaElements.length; i++) {
                //   if (allMetaElements[i].getAttribute("name") == "og:title" || allMetaElements[i].getAttribute("name") == "title") {
                //     allMetaElements[i].setAttribute('content', this.goal.title);
                //   }
                //   if (allMetaElements[i].getAttribute("name") == "og:description" || allMetaElements[i].getAttribute("name") == "description") {
                //     allMetaElements[i].setAttribute('content', this.goal.description);
                //   }
                //   if (allMetaElements[i].getAttribute("name") == "og:image") {
                //     allMetaElements[i].setAttribute('content', this.goal.cached_image);
                //   }
                // }
                this.linkToShare = this.sharePath + '/goal/' + this.goal.slug;
                setTimeout(()=>{
                  //twitter
                  var js,fjs=document.getElementsByTagName('script')[0],p=(location.protocol.indexOf('https') == -1?'http':'https');
                  if(!document.getElementById('twitter-wjs')){
                    js=document.createElement('script');
                    js.id='twitter-wjs';
                    js.src=p+'://platform.twitter.com/widgets.js';
                    fjs.parentNode.insertBefore(js,fjs);
                  }

                  //facebook
                  (function(d, s, id){
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) {return;}
                    js = d.createElement(s); js.id = id;
                    js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=486680294849466&version=v2.0";
                    fjs.parentNode.insertBefore(js, fjs);
                  }(document, 'script', 'facebook-jssdk'));
                },2000);

                this.stories = this.goal.success_stories;
                if(this.goal.is_my_goal == 1 || this.goal.is_my_goal == 2){
                  this._projectService.getUserGoal(this.goal.id)
                      .subscribe(
                          data => {
                            this.userGoal = data;
                          });
                }
              }

              if (this.aphorisms.length > 1) {
                setInterval(() => {

                  if(this.aphorismIndex === this.aphorisms.length - 1) {
                    this.aphorismIndex = 0;
                  } else {
                    this.aphorismIndex++;
                  }

                }, this.delay);
              }

              this.imageResize();
            },
            error => this.errorMessage = <any>error);
  }

  imageResize() {

    setTimeout(()=>{
      if(this.tickerView){
        this.quoteHeight = this.tickerView.nativeElement.children[0].offsetHeight + 35;
      }

      if(this.sliderImage){
        let imageHeight = this.sliderImage.nativeElement.offsetHeight;
        this.fullHeight = ( (window.innerWidth < 768 && imageHeight < 190) ||
        (window.innerWidth > 767 && window.innerWidth < 992 && imageHeight < 414) ||
        (window.innerWidth > 991 && imageHeight < 435))
      }

      if(this.goalImage && this.mainSlider){
        let slider = this.mainSlider.elementRef?this.mainSlider.elementRef:this.mainSlider;
        // let goalImageBottom = this.goalImage.nativeElement.offsetTop + this.goalImage.nativeElement.offsetHeight ;
        // let mainSliderBottom = slider.nativeElement.offsetTop + slider.nativeElement.offsetHeight;


        this.goalImageHeight = (this.quoteHeight?this.quoteHeight:this.container.nativeElement.children[0].offsetHeight)
            + this.container.nativeElement.children[1].offsetHeight + slider.nativeElement.offsetHeight;
      }
    }, 1000);
  };

  isLate(date){
    if(!date){
      return false;
    }

    var d1 = new Date(date);
    var d2 = new Date();

    return (d1 < d2);
  }

  manageGoal(){
    if(this.userGoal){
      let oldStatus = this.goal.is_my_goal;
      this.broadcaster.broadcast('addModal', {
        'userGoal': this.userGoal,
        'newAdded' : false,
        'newCreated' : false,
        'haveData': true
      });

      this.broadcaster.on<any>('saveUserGoal_' + this.userGoal.id)
          .subscribe(data => {
            this.userGoal = data;
            this.goal.is_my_goal = data.status;
            switch (oldStatus){
                  case 1:
                      if(data.status == 2){
                        this.goal.stats.listedBy--;
                        this.goal.stats.doneBy++;
                      }
                    break;
                  case 2:
                    if(data.status == 1){
                      this.goal.stats.listedBy++;
                      this.goal.stats.doneBy--;
                    }
                    break;
                }
          });

      this.broadcaster.on<any>('removeUserGoal_' + this.userGoal.id)
          .subscribe(data => {
            switch (oldStatus){
              case 1:
                this.goal.stats.listedBy--;
                break;
              case 2:
                this.goal.stats.doneBy--;
                break;
            }
            this.userGoal = null;
            this.goal.is_my_goal = 0;
          });
    }
  }

  add(id){
    let key = localStorage.getItem('apiKey');
    if(!key){
      this.broadcaster.broadcast('openLogin', 'some message');
        this._projectService.setAction({
            id: id,
            type: 'add'
        });
    } else {

      let oldStatus = this.goal.is_my_goal;

      this.broadcaster.broadcast('addModal', {
            'userGoal': {'goal': this.goal},
            'newAdded' : true,
            'newCreated' : false
      });

      this._projectService.addUserGoal(id, {}).subscribe((data) => {
        this.broadcaster.broadcast('addModalUserGoal', data);
        // this.broadcaster.broadcast('addModal', {
        //   'userGoal': data,
        //   'newAdded' : true,
        //   'newCreated' : false
        // });

        this.broadcaster.on<any>('addGoal' + this.goal.id)
            .subscribe(() => {
              this.userGoal = data;
              this.goal.is_my_goal = 1;
              this.goal.stats.listedBy++;
            });

        this.broadcaster.on<any>('saveUserGoal_' + data.id)
            .subscribe(data => {
              this.userGoal = data;
              this.goal.is_my_goal = data.status;

              if(data.status == 2){
                this.goal.stats.doneBy++;
              } else {
                this.goal.stats.listedBy++;
              }

            });
        });

        this.broadcaster.on<any>('removeGoal' + this.goal.id)
            .subscribe(data => {
                switch (oldStatus){
                    case 1:
                        this.goal.stats.listedBy--;
                        break;
                    case 2:
                        this.goal.stats.doneBy--;
                        break;
                }
                this.userGoal = null;
                this.goal.is_my_goal = 0;
            });
    }
    this.goal.is_my_goal = 1;
  }

  completeGoal(id, isManage){
    let oldStatus = this.goal.is_my_goal;
    this.goal.is_my_goal = 2;

    if(isManage){
        this.broadcaster.broadcast('doneModal', {
            'userGoal': {'goal':this.goal},
            'newAdded' : false
        });
      this._projectService.getStory(id).subscribe((data)=> {
          this.broadcaster.broadcast('doneModalUserGoal', data);
        // this.broadcaster.broadcast('doneModal', {
        //   'userGoal': data,
        //   'newAdded' : false
        // });
        if(!this.userGoal){
          this._projectService.getUserGoal(this.goal.id)
              .subscribe(
                  data => {
                    this.userGoal = data;
                  });
        }
      })
    } else {
      switch (oldStatus){
        case 1:
          this.goal.stats.doneBy++;
          this.goal.stats.listedBy--;
          break;
        case 0:
          this.goal.stats.doneBy++;
          break;
      }

        this.broadcaster.broadcast('doneModal', {
            'userGoal': {'goal':this.goal},
            'newAdded' : true
        });

      this._projectService.setDoneUserGoal(id).subscribe(() => {
        this._projectService.getStory(id).subscribe((data)=> {
            this.broadcaster.broadcast('doneModalUserGoal', data);
          // this.broadcaster.broadcast('doneModal', {
          //   'userGoal': data,
          //   'newAdded' : true
          // });

          this.broadcaster.on<any>('doneGoal' + this.goal.id)
              .subscribe(() => {
                this._projectService.getUserGoal(this.goal.id)
                    .subscribe(
                        data => {
                          this.userGoal = data;
                        });
              });
        })
      });
    }

  }

  save(id){
      this.broadcaster.broadcast('addModal', {
          'userGoal': {'goal':this.goal},
          'newAdded' : true,
          'newCreated' : true
      });
    this._projectService.addUserGoal(id, {}).subscribe((data) => {
        this.broadcaster.broadcast('addModalUserGoal', data);
      // this.broadcaster.broadcast('addModal', {
      //   'userGoal': data,
      //   'newAdded' : true,
      //   'newCreated' : true
      // });
      this.broadcaster.on<any>('saveUserGoal_' + data.id)
          .subscribe(data => {
            let messages = this._cacheService.get('flash_massage');
            messages = messages?messages:[];
            messages.push((!this.goal.status)?'goal.was_created.private' : 'goal.was_created.public');
            this._cacheService.set('flash_massage', messages, {maxAge: 3 * 24 * 60 * 60});
            this.router.navigate(['/profile/my/all']);
          });

      this.broadcaster.on<any>('addGoal' + id)
          .subscribe(data => {
            let messages = this._cacheService.get('flash_massage');
            messages = messages?messages:[];
            messages.push((!this.goal.status)?'goal.was_created.private' : 'goal.was_created.public');
            this._cacheService.set('flash_massage', messages, {maxAge: 3 * 24 * 60 * 60});
            this.router.navigate(['/profile/my/all']);
          });
    });

  }

  isEmpty(object){
    return (!object || !Object.keys(object).length);
  };

  openUsersModal(id:number, count:number, category: number){
    if(!localStorage.getItem('apiKey') || !this.appUser){
      this.broadcaster.broadcast('openLogin', 'some message');
        if (count) {
            this._projectService.setAction({
                id: id,
                type: category == 1 ? 'listed': category == 2 ? 'completed': 'likes',
                count: count,
                category: category
            });
        }
    } else {
      if(!count)return;
      this.broadcaster.broadcast('usersModal', {itemId: id, count: count, category: category});
    }

  }
    openShare(){
        let dialogRef: MdDialogRef<ShareComponent>;
        dialogRef = this.dialog.open(ShareComponent);
        dialogRef.componentInstance.linkToShare = this.linkToShare;
    }
    openComment(){
        let dialogRef: MdDialogRef<CommentComponent>;
        let data = {id: this.goal.id, slug:this.goal.slug,inner:true};
        dialogRef = this.dialog.open(CommentComponent);
        dialogRef.componentInstance.data = data;
        dialogRef.componentInstance.isModal = true;

        setTimeout(() => {
            this.broadcaster.broadcast('commentshow');
        },800);

    }
    // closeDropdown(){
    //     this.menu1 = false;
    // }
    showComment(){
        this.show = !this.show;
        if(this.show){
            this.broadcaster.broadcast('commentshow');
        }
    }
    openLightBox(data){
    //     if(data.images && data.images.length > 0){
    //         this.lightBoxData = data.images;
    //         this.lightbox = true;
    //     }
    //     else if(data.files && data.files.length > 0 ){
    //         this.lightBoxData = data.files;
    //         this.lightbox = true;
    //     }else {
    //         this.lightBoxData = null;
    //         this.lightbox = false;
    //         this.lightbox = false;
    //
    //     }
    }
}
