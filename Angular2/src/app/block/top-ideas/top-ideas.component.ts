import {Component, OnInit, Input, ViewEncapsulation, ViewChild} from '@angular/core';
import {CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';
import { ElementRef, Renderer} from '@angular/core';
import {Broadcaster} from '../../tools/broadcaster';

import { ProjectService } from '../../project.service';

import {Goal} from '../../interface/goal';
import {User} from "../../interface/user";

@Component({
  selector: 'top-ideas-block',
  templateUrl: './top-ideas.component.html',
  styleUrls: ['./top-ideas.component.less'],
  encapsulation: ViewEncapsulation.None
})

export class TopIdeasBlockComponent implements OnInit {
  @ViewChild('rotate')
  public rotateElementRef: ElementRef;
  @Input() type: string;
  goals:Goal[] = null;
  errorMessage:string;
  appUser:User;
  fresh:any;
  url:string;
  categories = ['top', 'suggest', 'featured'];
  degree:number = 360;

  constructor(
      private broadcaster: Broadcaster,
      private _projectService: ProjectService,
      private _cacheService: CacheService,
      private renderer: Renderer
  ) {}

  ngOnInit() {
    this.appUser = this._cacheService.get('user_');
    this.fresh = this.appUser?this._cacheService.get('fresh'+this.appUser.id):null;

    //check if category is featured
    if(this.type == this.categories[2]) {

      //set category url
      this.url = '/ideas/featured';
      let data = this._cacheService.get('featuredIdea');
      if (data && (!this.fresh || this.fresh['featuredIdea'])) {
        this.goals = data;
        this.refreshListener();
      } else {
        this.getFeaturedIdeas()
      }
    } else {

      //set category url
      this.url = '/ideas/most-popular';
      let data = this._cacheService.get('topIdea');
      if (data && (!this.fresh || this.fresh['topIdea'])) {
        this.goals = data;
        this.refreshListener();
      } else {
        this.getTopIdeas()
      }
    }
  }

  getTopIdeas() {
    this._projectService.getTopIdeas()
        .subscribe(
            goals => {
              this.goals = goals;
              this.refreshListener();
              this._cacheService.set('topIdea', goals, {maxAge: 24 * 60 * 60});
              if(this.fresh){
                this.fresh['topIdea'] = true;
                this._cacheService.set('fresh'+this.appUser.id, this.fresh);
              }
            },
            error => this.errorMessage = <any>error);
  }

  getFeaturedIdeas() {
    this._projectService.getFeaturedIdeas()
        .subscribe(
            goals => {
              this.goals = goals;
              this.refreshListener();
              this._cacheService.set('featuredIdea', goals, {maxAge: 24 * 60 * 60});
              if(this.fresh){
                this.fresh['featuredIdea'] = true;
                this._cacheService.set('fresh'+this.appUser.id, this.fresh);
              }
            },
                  error => this.errorMessage = <any>error
            );
  }

  refreshListener(){
    for(let goal of this.goals){
      this.broadcaster.on<any>('add_my_goal'+goal.id)
        .subscribe(() => {
            this.refreshIdeas();
        });
    }
  }
  refreshIdeas(){
    this.renderer.setElementStyle(this.rotateElementRef.nativeElement, 'transform','rotate('+this.degree+'deg)');
    this.degree += 360;
    if(this.type == this.categories[2]){
      this.getFeaturedIdeas()
    } else {
      this.getTopIdeas()
    }
  }

}
