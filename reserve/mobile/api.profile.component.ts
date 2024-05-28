
import { Component, OnInit, ViewChild, ElementRef, Renderer, OnDestroy, ViewEncapsulation, } from '@angular/core';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';
import { Broadcaster } from '../../tools/broadcaster';
import { CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';
import { ProjectService} from '../../project.service';
import { Profile} from '../profile';
import { MdDialog, MdDialogRef, MdSnackBar} from '@angular/material';
import { TranslateService} from 'ng2-translate';

import { MetadataService } from 'ng2-metadata';
import {CalendarComponent} from "../calendar/calendar.component";

@Component({
  selector: 'app-profile',
  templateUrl: './api.profile.component.html',
  styleUrls: ['./profile.component.less'],
  encapsulation: ViewEncapsulation.None

})

export class ProfileComponent extends Profile{
  public show:boolean = false;
  public isDream: boolean = true;
  public notUrgentImportant: boolean = true;
  public notUrgentNotImportant: boolean = true;
  public urgentNotImportant: boolean = true;
  public urgentImportant: boolean = true;
  public writeTimeout:any;
  public isMobile:Boolean= (window.innerWidth < 768);

  public priorities:any = {
    'isDream': null,
    'notUrgentImportant': null,
    'notUrgentNotImportant': null,
    'urgentNotImportant': null,
    'urgentImportant': null
  };
  constructor(
      protected metadataService: MetadataService,
      protected route: ActivatedRoute,
      protected _projectService: ProjectService,
      protected _cacheService: CacheService,
      protected broadcaster: Broadcaster,
      protected router:Router,
      protected renderer: Renderer,
      protected dialog: MdDialog,
      protected snackBar: MdSnackBar,
      protected _translate: TranslateService
  ) {
    super(metadataService, route, _projectService, _cacheService, broadcaster, router, renderer, snackBar,_translate);

  }
  toogleSelect(){
    if(this.show != true){
      this.writeTimeout = setTimeout(() =>{
        this.show = !this.show;
      }, 100)
    }
  }
  hideSelect(){
    if(this.show)this.show = false;
  }
  clendarShow(){
    let dialogRef: MdDialogRef<CalendarComponent>;
    dialogRef = this.dialog.open(CalendarComponent);
    this.broadcaster.on<any>('closeDialog')
        .subscribe( () =>{
          dialogRef.close()
        });
  }

  changeByDeviceType(isGet?:boolean){
    if(this.appUser){
      if(isGet){
        let data = this._cacheService.get('priority'+this.appUser.id);

        if(data){
          this.isDream = data.isDream;
          this.notUrgentImportant = data.notUrgentImportant;
          this.notUrgentNotImportant = data.notUrgentNotImportant;
          this.urgentNotImportant = data.urgentNotImportant;
          this.urgentImportant = data.urgentImportant;
        }

      } else{
        this.priorities.isDream = this.isDream;
        this.priorities.notUrgentImportant = this.notUrgentImportant;
        this.priorities.notUrgentNotImportant = this.notUrgentNotImportant;
        this.priorities.urgentNotImportant = this.urgentNotImportant;
        this.priorities.urgentImportant = this.urgentImportant;
        this._cacheService.set('priority'+this.appUser.id,this.priorities);
      }
    }
  }
}
