import { Component, OnInit } from '@angular/core';
import {CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';

import { ProjectService } from '../../project.service';
import {Broadcaster} from '../../tools/broadcaster';
import {Story} from '../../interface/story';


@Component({
  selector: 'app-base-stories',
  templateUrl: './base-stories.component.html',
  styleUrls: ['./base-stories.component.less']
})
export class BaseStoriesComponent implements OnInit {
  stories:Story[] = null;
  errorMessage:string;
  light_box_open: boolean = false;
  lightBoxData: any;
  lightBoxType: string = null;
  
  config: Object = {
    observer: true,
    autoHeight: true,
    nextButton: '.swiper-button-next-home-story',
    prevButton: '.swiper-button-prev-home-story',
    spaceBetween: 30
  };

  constructor(
      private _projectService: ProjectService, 
      private _cacheService: CacheService,
      private broadcaster: Broadcaster
  ) { }

  ngOnInit() {
    let data = this._cacheService.get('baseStories');
    if (data) {
      this.stories = data;
    } else {
      this.getBaseStories()
    }
  }

  getBaseStories() {
    this._projectService.getBaseStories()
        .subscribe(
            stories => {
              this.stories = stories;
              this._cacheService.set('baseStories', stories, {maxAge: 3 * 24 * 60 * 60});
            },
            error => this.errorMessage = <any>error
        );
  }
  
  openLightBox(data: any, type?: string) {
    // this.lightBoxData = data;
    // this.lightBoxType = type;
    // this.light_box_open = true;
  }

  closeLightBox() {
    this.light_box_open = false;
  }

  openSignInPopover(story?){
    if (story && story.id && story.goal) {
      this._projectService.setAction({
        id: story.id,
        type: 'like',
        slug: story.goal.slug
      });
    }

    this.broadcaster.broadcast('openLogin', 'message');
  }

}
