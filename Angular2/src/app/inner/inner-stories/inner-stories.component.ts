import { Component, OnInit ,Output, Input, ViewContainerRef, EventEmitter} from '@angular/core';
import { Broadcaster } from '../../tools/broadcaster';
import { ConfirmComponent} from '../../modals/confirm/confirm.component';
import { MdDialog, MdDialogRef, MdDialogConfig} from '@angular/material';
import { ProjectService} from '../../project.service';

import { Story} from '../../interface/story';
import { User} from '../../interface/user';
import { Goal} from '../../interface/goal';

@Component({
  selector: 'inner-stories',
  templateUrl: './inner-stories.component.html',
  styleUrls: ['./inner-stories.component.less']
})
export class InnerStoriesComponent implements OnInit {

  @Input() stories:Story[];
  @Input() appUser:User;
  @Input() goal:Goal;
  @Output() onStoryClick: EventEmitter<any> = new EventEmitter();

  public storiesCount:number = 20;
  public activeIndex:number = null;
  public storyLength:number;
  public fileConfig: Object = {
    pagination: '.swiper-pagination',
    paginationClickable: true,
    slidesPerView: 2,
    freeMode: true,
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
      private viewContainerRef: ViewContainerRef,
      private _projectService : ProjectService,
      private broadcaster: Broadcaster,
      public dialog: MdDialog
  ){}

  ngOnInit() {
    if(this.stories){
      this.storyLength = this.stories.length - this.storiesCount;
      for(let i = 0;i < this.stories.length; i++){
        this.stories[i].show = (i < this.storiesCount);
      }
    }
  }
  report(contentType, contentId){
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
  
  showMoreSuccessStory(){
    if(this.activeIndex === this.storyLength){
      return;
    }

    if(this.activeIndex === null){
      this.activeIndex = this.storiesCount;
    }

    var startIndex = this.activeIndex;

    if(this.storyLength > this.storiesCount - 1){
      this.activeIndex += this.storiesCount;
      this.storyLength -= this.storiesCount;
    }
    else {
      this.activeIndex += this.storyLength;
      this.storyLength = 0;
    }

    for(var i = startIndex; i < this.activeIndex; i++){
      this.stories[i].show = true;
    }

  }

  openDialog(id, index){

    let dialogRef: MdDialogRef<ConfirmComponent>;
    let config = new MdDialogConfig();
    config.viewContainerRef = this.viewContainerRef;
    dialogRef = this.dialog.open(ConfirmComponent, config);
    dialogRef.componentInstance.lsText = 'success_story.delete_confirm';
    dialogRef.afterClosed().subscribe(result => {
      if(result == 'yes'){
        this._projectService.removeStory(id)
            .subscribe(
                () => {}
            );
        this.stories.splice(index,1);
      }
    });
  }
  
  openLightBox(story){
    this.onStoryClick.emit(story);
  }
}
