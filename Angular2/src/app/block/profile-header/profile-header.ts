/**
 * Created by ani on 2/22/17.
 */
import { Component, OnInit, Input, Output, EventEmitter , ViewEncapsulation, OnChanges } from '@angular/core';
import { ProjectService } from '../../project.service';
import { CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';
import { Broadcaster } from '../../tools/broadcaster';
import { Uploader }      from 'angular2-http-file-upload';
import { MyUploadItem }  from '../../components/my-dropzone/my-upload';
import { MdSnackBar, MdSnackBarConfig} from '@angular/material';
import { TranslateService} from 'ng2-translate';
import { Router } from '@angular/router';


import {User} from "../../interface/user";

export class ProfileHeader implements OnInit {
    @Input() userInfo: string ;
    @Input() type: string;
    // @Output('onHover') hoverEmitter: EventEmitter<any> = new EventEmitter();
    public profileUser:User;
    public file:any;
    public current:any;
    public appUser:User;
    public serverPath:string = '';
    public message:string = '';
    public imgPath: string = '';
    path:string = '/api/v1.0/user/upload-file';
    // public nameOnImage: string = '';
    public listedBy;
    public active;
    public doneBy;
    public badgesData : any;
    public errorMessage:any;
    public flashBag:any;
    public badges: any[];
    public isTouchdevice:Boolean = (window.innerWidth > 600 && window.innerWidth < 992);
    public isMobile:Boolean= (window.innerWidth < 768);
    public isFollow:Boolean;
    constructor(
        protected broadcaster: Broadcaster,
        protected _projectService: ProjectService,
        protected _cacheService: CacheService,
        protected uploaderService: Uploader,
        protected snackBar: MdSnackBar,
        protected _translate: TranslateService,
        protected router: Router
    ) { }

    ngOnChanges(){
        if(this.userInfo && this.current != this.userInfo){
            this.profileUser = null;
            this.init();
        }
    }
    ngOnInit() {
        this.serverPath = this._projectService.getPath();
        this.imgPath = this.serverPath + '/bundles/app/images/cover3.jpg';

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
                            this.broadcaster.broadcast('getUser', user);
                        })
                }
            }
        } else {
            this.broadcaster.broadcast('logout', 'some message');
        }

        // this.init();

    }

    init(){
        this.current = this.userInfo;
        if(this.userInfo == 'my'){
            this.flashBag = this._cacheService.get('flash_massage');
            if(this.flashBag && this.flashBag.length > 0){
                setTimeout(() => {
                    this.message = this._translate.instant('goal.was_created.public');
                    this.snackBar.open(this.message, '', <any>{
                        duration : 2000
                    });
                    document.querySelector('.cdk-global-overlay-wrapper').className += " flex-md-left";
                    document.getElementsByTagName("snack-bar-container")[0].className += " snackbar_style";
                },500);
            }
            this._cacheService.set('flash_massage', [], {maxAge: 3 * 24 * 60 * 60});
            setTimeout(()=>{
                this.flashBag = 0;
            },6000);

            this.profileUser = this._cacheService.get('user_');
        } else {
            this.profileUser = this._cacheService.get('user'+this.userInfo);
        }

        if(this.profileUser){

            // if (this.profileUser.enabled === false) {
            //     this.router.navigate(['/not-active']);
            // }

            this.badgesData = this.profileUser.badges;
            this.active = this.profileUser.stats.active;
            this.listedBy = this.profileUser.stats.listedBy;
            this.doneBy = this.profileUser.stats.doneBy;
        }

        this._projectService.getUserByUId(this.userInfo)
            .subscribe(
                user => {
                if(this.userInfo == 'my'){
                    this._cacheService.set('user_', user, {maxAge: 3 * 24 * 60 * 60});
                } else {
                    this._cacheService.set('user'+this.userInfo, user, {maxAge: 3 * 24 * 60 * 60});
                }
                this.profileUser = user;

                if (this.profileUser.enabled === false) {
                    this.router.navigate(['/not-active']);
                }

                //get all
                this.badgesData = user.badges;

                //generate normalizer score data and return its for badges
                this._projectService.getMaxScore(this.badgesData)
                  .subscribe(
                    (data) => {
                      this.badges = data;
                    },
                    error => {
                      this.errorMessage = JSON.parse(error._body);
                    }
                  );

                this.broadcaster.broadcast('pageUser', this.profileUser);
                this.active = this.profileUser.stats.active;
                this.listedBy = this.profileUser.stats.listedBy;
                this.doneBy = this.profileUser.stats.doneBy;
            });
    }

    toggleFollow(){
        this._projectService.toggleFollow(1).subscribe(
                user => {
                this.isFollow = !this.isFollow;
            });
    }

    showUploadedImage(event){

        let input = event.target;

        if (input.files && input.files[0]) {

            this.file = input.files[0];

            this.saveImage();

            let reader = new FileReader();

            reader.onload = (e:any) => {
                if(e && e.target){
                    this.profileUser.cached_image = e.target.result;
                    let user = this._cacheService.get('user_');
                    user.cached_image = e.target.result;
                    this._cacheService.set('user_', user, {maxAge: 3 * 24 * 60 * 60});
                }
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    saveImage(){
        if(this.file){
            let myUploadItem = new MyUploadItem(this.file, this._projectService.getPath() + this.path);
            // myUploadItem.formData = { FormDataKey: 'Form Data Value' };  // (optional) form data can be sent with file

            this.uploaderService.onSuccessUpload = (item, response, status, headers) => {
            };
            this.uploaderService.onErrorUpload = (item, response, status, headers) => {
                this.errorMessage = response;
            };
            this.uploaderService.onCompleteUpload = (item, response, status, headers) => {
            };
            this.uploaderService.upload(myUploadItem);
        }
    }

    closeFlashBug(index){
        this.flashBag.splice(index,1);
    }
}
