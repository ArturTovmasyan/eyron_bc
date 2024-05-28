import { Component, OnInit, Input, ViewContainerRef,ViewChild, ViewEncapsulation,ElementRef } from '@angular/core';
import {MdDialog, MdDialogRef, MdDialogConfig, MdSidenav} from '@angular/material';
import { TranslateService} from 'ng2-translate';
import { Broadcaster} from '../tools/broadcaster';
import { ProjectService} from '../project.service';
import { Router } from '@angular/router';
import { CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';
import { Angulartics2, Angulartics2GoogleAnalytics} from 'angulartics2';
import { App} from '../app';
import {  HostListener, Inject} from "@angular/core";
import { DOCUMENT } from '@angular/platform-browser';

@Component({
  selector: 'app-root',
  templateUrl: './api.app.component.html',
  styleUrls: ['./api.app.component.less'],
  encapsulation: ViewEncapsulation.None
})

export class AppComponent extends App {
    @ViewChild('sidenav') sidenav: MdSidenav;
    @ViewChild('footer')
    public footer: ElementRef;
    public scroll:boolean;
    public before:number = 0;
    public sOpen:boolean = false;
    public nearbyscroll:boolean = false;
    //public userDrop : boolean = false;
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
    //sidenavOpenClose(){
    //    this.userDrop = !this.userDrop;
    //    if(this.userDrop){
    //        this.sidenav.open()
    //    } else {
    //        this.sidenav.close();
    //    }
    //}


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

        this.upButton = (number > 250);

        this.myTop = number;
        if(number <= 68){
            this.doScroll(0);
        } else {
            if(number < this.before){
                this.doScroll(0);
                this.before = number;
            } else if(number > this.before){
                this.doScroll(1);
                this.before = number;
            }
        }

        if(number > 400 ){
            this.sOpen = false;
            this.check();
        }
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

    doScroll(type:number) {
        this.scroll = (type == 1);
        this.scrollInner = this.scroll;
        this.broadcaster.broadcast('menuScroll', this.scroll );
    }
    check(){
        if(this.sidenav && this.sidenav._isOpened){
            this.sidenav.close()
        }
    }
    onClose(){
        this.sOpen=false;
    }
}