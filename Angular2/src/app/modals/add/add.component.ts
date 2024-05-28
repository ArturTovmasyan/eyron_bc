import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import { ProjectService } from '../../project.service';
import {CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';
import {MdDialog, MdDialogRef} from '@angular/material';
import { Router } from '@angular/router';
import { Broadcaster } from '../../tools/broadcaster';


@Component({
  selector: 'add-modal',
  templateUrl: './add.component.html',
  styleUrls: ['./add.component.less'],
  encapsulation: ViewEncapsulation.None
})
export class AddComponent implements OnInit {
  public userGoal: any;
  public appUser: any;
  public isOpen:boolean = false;
  public year: number = 0;
  public years: number[] = [];
  public completeYears: number[] = [];
  public days: number[] = [];
  public month: number = 0;
  public day: number = 0;
  public dayInMonth: number = 30;
  public noStory:boolean = false;
  public invalidYear:boolean = false;
  public uncompletedYear:boolean = false;
  public defaultMonth:any;
  public imgPath:string = '';
  public serverPath:string = '';
  public isMobile:Boolean= (window.innerWidth < 768);
  public newAdded:boolean;
  public newCreated:boolean;
  public dateChanged:boolean;
  public switchChanged:boolean = false;
  public complatedPercent: number;
  public first:boolean = true;
  public second:boolean = false;
  public load:boolean = false;
  // public newAdded:boolean = userGoal.manage? false: true;
  public completedStepCount: number;
  public complete:any = {
    switch: 0
  };
  public months_3:Array<string> = [
    'form.birth_date_month',
    'form.month_january_3',
    'form.month_february_3',
    'form.month_march_3',
    'form.month_april_3',
    'form.month_may_3',
    'form.month_june_3',
    'form.month_july_3',
    'form.month_august_3',
    'form.month_september_3',
    'form.month_october_3',
    'form.month_november_3',
    'form.month_december_3'

  ];
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

  constructor(
      private broadcaster: Broadcaster,
      public dialogRef: MdDialogRef<AddComponent>,
      private ProjectService: ProjectService,
      private _cacheService: CacheService,
      private router: Router) {
  }

  ngOnInit() {
    if(this.isMobile){
      this.months = this.months_3;
    }
    if(!localStorage.getItem('apiKey')){
      this.router.navigate(['/']);
      this.isOpen = false;
      this.dialogRef.close();
    } else {

      this.serverPath = this.ProjectService.getPath();
      this.imgPath = this.serverPath + '/bundles/app/images/cover1.jpg';

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
        this.years[i] = +currentYear + i;
        this.completeYears[i] = +currentYear - i;
        if(i < 31){
          this.days[i] = i + 1;
        }
      }

      // if(this.userGoal.completion_date && this.userGoal.status == 2){
      //   this.updateDate(this.userGoal.completion_date);
      // } else{
      //   if(this.userGoal.do_date){
      //     this.updateDate(this.userGoal.do_date);
      //     this.userGoal.do_date_status = this.userGoal.date_status;
      //   }
      // }
      // if(this.userGoal.formatted_steps && this.userGoal.formatted_steps[0]){
      //   if(this.userGoal.formatted_steps[this.userGoal.formatted_steps.length - 1].text){
      //     this.generateStep(true,this. userGoal.formatted_steps, this.userGoal.formatted_steps.length -1);
      //   } else {
      //     this.complatedPercent = this.getCompleted(this.userGoal);
      //   }
      // }
    }
  }
  
  dynamicChanges(){
    this.load = true;
    this.complete.switch = this.userGoal.status == 2?1:0;
    
    if(this.userGoal.completion_date && this.userGoal.status == 2){
      this.updateDate(this.userGoal.completion_date);
    } else{
      if(this.userGoal.do_date){
        this.updateDate(this.userGoal.do_date);
        this.userGoal.do_date_status = this.userGoal.date_status;
      }
    }
    if(this.userGoal.formatted_steps && this.userGoal.formatted_steps[0]){
      if(this.userGoal.formatted_steps[this.userGoal.formatted_steps.length - 1].text){
        this.generateStep(true,this. userGoal.formatted_steps, this.userGoal.formatted_steps.length -1);
      } else {
        this.complatedPercent = this.getCompleted(this.userGoal);
      }
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

  getCompleted(userGoal){
    if(!userGoal || !userGoal.formatted_steps){
      return 0;
    }
    let length = userGoal.formatted_steps.length - 1;

    if(!length)return 0;
    let result = 0;
    for(let v of userGoal.formatted_steps) {
      if (v.switch) {
        result++;
      }
    }

    this.completedStepCount = result;

    return (result * 100)/length;
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

  switchChanges(){
    this.uncompletedYear = false;
    this.invalidYear = false;
    this.switchChanged = !this.switchChanged;

    if(this.complete.switch == 1){
      this.updateDate(new Date(), true);
    }
    else{
      if(this.userGoal.do_date){
        if(this.userGoal.do_date_status){
          this.userGoal.date_status = this.userGoal.do_date_status
        } else {
          this.userGoal.date_status = 1;
        }
        this.updateDate(this.userGoal.do_date);
      }
      else{
        this.updateDate(null);
      }
    }
  }

  updateDate(date, isNewDate?) {
    if(date){
      let myDate = new Date(date);
      this.month = (this.userGoal.date_status == 2 && !isNewDate)?0:(myDate.getMonth() + 1);
      this.day = (this.userGoal.date_status == 1 || isNewDate)?(myDate.getDate()):0;
      this.year = (myDate.getFullYear());
    }else {
      this.month = 0;
      this.day   = 0;
      this.year  = 0;
    }
  };

  generateStep(value, array, key){

    if(value === ''){
      array[key].text = value;
      if(key === 0){
        if(array.length > 1) {
          array.splice(key, 1);
        }
      }
      else {
        array.splice(key, 1);
      }
      this.complatedPercent = this.getCompleted(this.userGoal);
    }
    else {
      if(typeof(value) == "string"){
        array[key].text = value;
      }
        if(!array[key + 1]) {
          array[key + 1] = {switch:false,text:''};
          this.complatedPercent = this.getCompleted(this.userGoal);
        }
    }
  }

  dateByFormat(month, day, year){
    return year + '-' + (month > 9?month:('0' + month)) + '-' + (day > 9?day:('0' + day));
  }

  save(){
    this.uncompletedYear = false;
    this.userGoal.completed = this.complatedPercent;
    if(this.year && this.month && this.day ){
      this.dateChanged = true;
      this.userGoal.date_status = 1;
      this.dayInMonth = this.getDaysInMonth(this.month, this.year);

      if(this.day > this.dayInMonth){
        this.invalidYear = true;
        return;
      }

      if(this.complete.switch){
        this.userGoal.completion_date = new Date(this.year, this.month - 1, this.day);

      } else{
        this.userGoal.do_date = new Date(this.year, this.month - 1, this.day);
        this.userGoal.completion_date = null;
        this.userGoal.do_date_status = 1;
      }
    } else if(this.year){
      //when select only year
      this.dateChanged = true;
      var month = (this.month)?this.month: (this.complete.switch? ((new Date()).getMonth() + 1):12);
      var day = this.getDaysInMonth(month, this.year);

      this.userGoal.date_status = this.month?3:2;

      if(this.complete.switch){
        this.userGoal.completion_date = new Date(this.year, month - 1, day);
      } else {
        this.userGoal.do_date = new Date(this.year, month - 1, day);
        this.userGoal.do_date_status = (this.month)?3:2;
        this.userGoal.completion_date = null;
      }

    }
    else if(this.month || this.day){
      this.uncompletedYear = true;
      return;
    }
    this.invalidYear = false;
    // if(this.userGoal.completion_date && this.compareDates(this.firefox_completed_date) === 1){
    //   this.invalidYear = true;
    //   return;
    // }

    this.userGoal.steps = {};

    for(let value of this.userGoal.formatted_steps){
      if(value.text) {
        this.userGoal.steps[value.text] = value.switch ? value.switch : false;
      }
    }

    this.userGoal.goal_status = this.complete.switch;
    this.userGoal.status = +this.complete.switch + 1;

    let data = this.userGoal;

    if(this.userGoal.completion_date){
      let myDate = new Date(this.userGoal.completion_date);
      data.completion_date = this.dateByFormat(myDate.getMonth() + 1, myDate.getDate(), myDate.getFullYear());
    }

    if(this.userGoal.do_date){
      let myDate = new Date(this.userGoal.do_date);
      data.do_date = this.dateByFormat(myDate.getMonth() + 1, myDate.getDate(), myDate.getFullYear());
    }

    this.ProjectService.addUserGoal(this.userGoal.goal.id, data).subscribe(() => {

    });
    // UserGoalDataManager.manage({id: this.userGoal.goal.id}, this.userGoal, function (res){
    //   this.$emit('lsJqueryModalClosedSaveGoal', res);
    // }, function () {
    //   toastr.error('Sorry! Your goal has not been saved');
    // });
    this.dialogRef.close(this.userGoal);
  }
  
  removeUserGoal(){
    this.ProjectService.removeUserGoal(this.userGoal.id).subscribe((data) => {
      this.broadcaster.broadcast('snackbar',0);
    });
    this.dialogRef.close({'remove': this.userGoal.id});
  }
}
