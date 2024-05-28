import { Component, OnInit, Output, EventEmitter, Input, ViewEncapsulation } from '@angular/core';
import { ProjectService } from '../../project.service';
import {CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';
import { Router } from '@angular/router';
import {MdDialog, MdDialogRef} from '@angular/material';



@Component({
  selector: 'done-modal',
  templateUrl: './done.component.html',
  styleUrls: ['./done.component.less'],
  encapsulation: ViewEncapsulation.None
})
export class DoneComponent implements OnInit {
  public userGoal:any;
  public appUser:any;
  public story:string;
  public goalLink:string;
  public isOpen:boolean = false;
  public newAdded:boolean;
  public invalidYear:boolean;
  public uncompletedYear:boolean;
  public noStory:boolean = false;
  public imgPath:string = '';
  public dayInMonth:number;
  public serverPath:string = '';
  public load:boolean = false;
  public isMobile = (window.innerWidth < 720);

  public imageCount:number = 6;
  public uploadingFiles: any[] = [];
  public existingFiles: any[] = [];
  public clickable: boolean = true;

  public year: number = 0;
  public day: number = 0;
  public month: number = 0;
  public limit: number = 3;
  public completeYears: number[] = [];
  public days: number[] = [];
  public months:Array<string> = [
    'form.birth_date_month',
    'form.month_january',
    'form.month_february',
    'form.month_march',
    'form.month_april',
    'form.month_may',
    'form.month_june',
    'form.month_july',
    'form.month_august',
    'form.month_september',
    'form.month_october',
    'form.month_november',
    'form.month_december'
  ];

  public videos_array: any[] = [];
  public files: any[] = [];

  constructor(
              private ProjectService: ProjectService, 
              private router: Router,
              public dialogRef: MdDialogRef<DoneComponent>,
              private _cacheService: CacheService
  ) { }

  ngOnInit() {
    this.goalLink = window.location.origin + '/goal/' +this.userGoal.goal.slug;
    this.serverPath = this.ProjectService.getPath();
    if(!localStorage.getItem('apiKey')){
      this.router.navigate(['/']);
      this.isOpen = false;
      this.dialogRef.close();
      // this.modalHideEmitter.emit(null);
      
    } else {
      this.imgPath = this.serverPath + '/bundles/app/images/cover3.jpg';

      setTimeout(()=>{
        this.isOpen = true;
      },500);
      this.appUser = this.ProjectService.getMyUser();
      if (!this.appUser) {
        this.appUser = this._cacheService.get('user_');
        if(!this.appUser) {
          this.ProjectService.getUser()
              .subscribe(
                  user => {
                    this.appUser = user;
                    this._cacheService.set('user_', user, {maxAge: 3 * 24 * 60 * 60});
                  })
        }
      }

      let date = new Date();
      let currentYear = date.getFullYear();
      for(let i = 0 ; i < 50; i++){
        this.completeYears[i] = +currentYear - i;
        if(i < 31){
          this.days[i] = i + 1;
        }
      }

      let myDate = new Date();
      this.month = (myDate.getMonth() + 1);
      this.day = (myDate.getDate());
      this.year = (myDate.getFullYear());
      
      // if(this.userGoal.story){
      //   this.story = this.userGoal.story.story;
      //
      //   if(this.userGoal.story.video_link.length > 0){
      //     for(let link of this.userGoal.story.video_link){
      //       this.videos_array.push(link);
      //     }
      //   }else {
      //     this.videos_array.push('');
      //   }
      //
      //   if(this.userGoal.story.files) {
      //     this.existingFiles = this.userGoal.story.files;
      //     for(let file of this.existingFiles){
      //       this.files.push(file.id);
      //     }
      //   }
      // }else {
      //   this.videos_array.push('');
      // }

    }
  }
  
  dynamicChanges(){
    this.load = true;
    if(this.userGoal.story){
      this.story = this.userGoal.story.story;

      if(this.userGoal.story.video_link.length > 0){
        for(let link of this.userGoal.story.video_link){
          this.videos_array.push(link);
        }
      }else {
        this.videos_array.push('');
      }

      if(this.userGoal.story.files) {
        this.existingFiles = this.userGoal.story.files;
        for(let file of this.existingFiles){
          this.files.push(file.id);
        }
      }
    }else {
      this.videos_array.push('');
    }
  }
  closeModal(){
    if(!this.isOpen)return;
    this.isOpen = false;
    this.dialogRef.close();
  }
  getDaysInMonth(m, y) {
    return m===2 ? y & 3 || !(y%25) && y & 15 ? 28 : 29 : 30 + (m+(m>>3)&1);
  };

  compareDates(date1, date2){

    if(!date1){
      return null;
    }

    var d1 = new Date(date1);
    var d2 = date2 ? new Date(date2): new Date();

    if(d1 < d2){
      return -1;
    }
    else if(d1 === d2){
      return 0;
    }
    else {
      return 1;
    }
  };

  dateByFormat(month, day, year){
    return year + '-' + (month > 9?month:('0' + month)) + '-' + (day > 9?day:('0' + day));
  }

  isInValid() {
    this.noStory = false;
    let noDate = this.noData();
    if(!noDate){
      if(!this.story || this.story.length < 3 )this.noStory = true;

    }
  };
  
  noData() {
    return (!this.videos_array || this.videos_array.length < 2)&&
    (!this.files || !this.files.length )&&
    ( !this.userGoal.story || !this.story)
  };

  saveDate(status, completion_date) {
    let data = {
      'goal_status'    : true,
      'completion_date': completion_date,
      'date_status'    : status
    };

  // if(this.compareDates(this.firefox_completed_date) === 1){
  //   this.invalidYear = true;
  //   return;
  // } else {
  //   this.invalidYear = false;
  // }

    this.ProjectService.addUserGoal(this.userGoal.goal.id, data).subscribe(() => {
      if(this.noData()){
        this.noStory = false;
        this.dialogRef.close(this.userGoal);
      }
    });
  // UserGoalDataManager.manage({id: this.userGoal.goal.id}, data, function (){
  //   var selector = 'success' + this.userGoal.goal.id;
  //   if(angular.element('#'+ selector).length > 0) {
  //     var parentScope = angular.element('#' + selector).scope();
  //     if(!angular.isUndefined(parentScope.goalDate) && !angular.isUndefined(parentScope.dateStatus)) {
  //       parentScope.goalDate[this.userGoal.goal.id] = new Date(this.firefox_completed_date);
  //       parentScope.dateStatus[this.userGoal.goal.id] = status;
  //     }
  //   }
  //
  //   if(this.noData()){
  //     this.noStory = false;
  //     this.dialogRef.close(this.userGoal);
  //   }
  // });
};

  save(){
    this.isInValid();
    if(this.year &&
        this.month &&
        this.day && this.newAdded){

      this.dayInMonth = this.getDaysInMonth(this.month, this.year);

      if(this.day > this.dayInMonth){
        this.invalidYear = true;
        return;
      }

      let completion_date = this.dateByFormat(this.month ,this.day, this.year);
      this.userGoal.completion_date = new Date(this.year, this.month - 1, this.day);
      this.userGoal.goal_status = true;
      this.userGoal.date_status = 1;
      // this.firefox_completed_date = this.dateByFormat(this.year, this.months.indexOf(this.month), this.day, 'YYYY-MM-DD');
      this.saveDate(1, completion_date);
    }else if(this.year && this.newAdded){//when select only year
      var month = this.month?this.month: ((new Date()).getMonth() + 1);
      var day = 1;
      let completion_date = this.dateByFormat(month, day, this.year);
      this.userGoal.completion_date = new Date(this.year, month - 1, day);
      this.userGoal.goal_status = true;
      this.userGoal.date_status = (this.month)?3:2;
      // this.firefox_completed_date = this.dateByFormat(this.year, month, day,'YYYY-MM-DD');
      this.saveDate((this.month)?3:2, completion_date);
    }
    else if((this.month || this.day) && this.newAdded){
      this.uncompletedYear = true;
      return;
    }
    if(this.noStory){
      // angular.element('textarea[name=story]').addClass('border-red');
      return;
    } else {
      if(!this.story){
        this.dialogRef.close(this.userGoal);
        return;
      }
    }

    if(!this.clickable){
      return;
    }

    let video_link = [];

    for(let i = 0; i < this.videos_array.length; i++){
      if(this.videos_array[i]){
        video_link.push(this.videos_array[i]);
      }
    }

      let data = {
        'story'     : this.story,
        'videoLink' : video_link,
        'files'     : this.files
      };

    this.ProjectService.addUserGoalStory(this.userGoal.goal.id, data).subscribe(() => {

    });
      // UserGoalDataManager.editStory({id: this.userGoal.goal.id}, data, null, function (){
      //   toastr.error('Sorry! Your success story has not been saved');
      // });
    this.dialogRef.close(this.userGoal);
  }

}
