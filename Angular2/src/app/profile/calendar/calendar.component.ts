import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import {ProjectService} from '../../project.service';
import {Broadcaster} from '../../tools/broadcaster';

@Component({
  selector: 'calendar',
  templateUrl: './calendar.component.html',
  styleUrls: ['./calendar.component.less'],
  encapsulation: ViewEncapsulation.None
})
export class CalendarComponent implements OnInit {

  public year:number;
  public month:number;
  public day:number;
  public currentMonthDay:number;
  public dayDifferent:number;
  public prevMonthDay:number;
  public noShowLast:boolean;
  public animation:boolean;
  public isMobile:Boolean= (window.innerWidth < 768);
  public now = new Date();
  public type = 'month';
  public days = [];
  public myYears = [];
  public myYAMonths = [];
  public myDays = [];
  public currentDay = this.now.getDate();
  public currentMonth = this.now.getMonth();
  public currentYear = this.now.getFullYear();

  constructor(
      private _projectService: ProjectService,
      private broadcaster: Broadcaster
  ) { }

  ngOnInit() {
    this.animation = true;
    this.initDate();
    this._projectService.getCalendarData()
        .subscribe(
        data => {
          this.initData(data);
        });
  }

  dateByFormat(year, month, day) {
    return new Date(year, month, day);
    // moment(year + '-' +((month > 9)?month:'0'+month)+'-'+((day > 9)?day:'0'+day));
    // return format?date.format(format):date;
  };

  getDaysInMonth(m, y) {
    return m===2 ? y & 3 || !(y%25) && y & 15 ? 28 : 29 : 30 + (m+(m>>3)&1);
  };

  arrayBySize(size) {
    return new Array(size);
  };

  initData(data) {
    for(let k in data){
      let y = new Date(k).getFullYear(),
          m = new Date(k).getMonth(),
          d = new Date(k).getDate();
      this.myYears[y] = this.myYears[y]?this.myYears[y]:{complete:0, deadline:0, current:0};
      this.myYAMonths[y] = this.myYAMonths[y]?this.myYAMonths[y]:[];
      this.myYAMonths[y][m] = this.myYAMonths[y][m]?this.myYAMonths[y][m]:{complete:0, deadline:0, current:0};
      this.myDays[y] = this.myDays[y]?this.myDays[y]:[];
      this.myDays[y][m] = this.myDays[y][m]?this.myDays[y][m]:[];
      this.myDays[y][m][d] = this.myDays[y][m][d]?this.myDays[y][m][d]:{complete:0, deadline:0, current:0};

      if(data[k].completion){
        this.myYears[y].complete = this.myYears[y].complete?(this.myYears[y].complete + data[k].completion):(data[k].completion);
        this.myYAMonths[y][m].complete = this.myYAMonths[y][m].complete?(this.myYAMonths[y][m].complete + data[k].completion):(data[k].completion);
        this.myDays[y][m][d].complete = this.myDays[y][m][d].complete?(this.myDays[y][m][d].complete + data[k].completion):(data[k].completion);
      }

      if(data[k].active){
        if(this.compareDates(k) === -1){
          this.myYears[y].deadline = this.myYears[y].deadline?(this.myYears[y].deadline + data[k].active):(data[k].active);
          this.myYAMonths[y][m].deadline = this.myYAMonths[y][m].deadline?(this.myYAMonths[y][m].deadline + data[k].active):(data[k].active);
          this.myDays[y][m][d].deadline = this.myDays[y][m][d].deadline?(this.myDays[y][m][d].deadline + data[k].active):(data[k].active);
        } else {
          this.myYears[y].current = this.myYears[y].current?(this.myYears[y].current + data[k].active):(data[k].active);
          this.myYAMonths[y][m].current = this.myYAMonths[y][m].current?(this.myYAMonths[y][m].current + data[k].active):(data[k].active);
          this.myDays[y][m][d].current = this.myDays[y][m][d].current?(this.myDays[y][m][d].current + data[k].active):(data[k].active);
        }
      }
    };
  };

  initDate =function () {
    for(var i = 1; i < 43; i++){
      this.days[i] = {day: i}
    }

    this.weekDay = this.dateByFormat(this.currentYear, this.currentMonth, 1).getDay();
    this.dayDifferent = (-this.weekDay);
    this.prevMonthDay = this.getDaysInMonth((this.currentMonth == 0)?11:this.currentMonth -1, (this.currentMonth == 0)?this.currentYear - 1:this.currentYear);
    this.currentMonthDay = this.getDaysInMonth(this.currentMonth, this.currentYear);

    this.days.forEach((v,k) => {
      this.days[k].day = (k + this.dayDifferent > 0)?((k + this.dayDifferent <= this.currentMonthDay)?(k + this.dayDifferent):(k + this.dayDifferent - this.currentMonthDay)):(k + this.dayDifferent + this.prevMonthDay);
      this.days[k].status = (k + this.dayDifferent > 0 && k + this.dayDifferent <= this.currentMonthDay)?'active':'inActive';
      this.days[k].year = (this.days[k].status == 'active')?this.currentYear:((k + this.dayDifferent > this.currentMonthDay && this.currentMonth == 11)? (+this.currentYear + 1):(k + this.dayDifferent <= 0 && this.currentMonth == 0)?this.currentYear - 1:this.currentYear);
      this.days[k].month = (this.days[k].status == 'active')?this.currentMonth:(k + this.dayDifferent > this.currentMonthDay)? (this.currentMonth == 11)?0:(+this.currentMonth + 1):(k + this.dayDifferent <= 0)?(this.currentMonth == 0)?11:(this.currentMonth - 1):this.currentMonth;
    });

    this.noShowLast = (this.days[42].day != 42 && this.days[42].day >= 7);

  };

  prev() {
    switch (this.type){
      case 'month':
        if(this.currentMonth == 0){
          if (this.currentYear <= 1966)return;
          this.currentMonth = 11;
          this.currentYear--;
        } else {
          this.currentMonth--;
        }

        this.initDate();
        break;
      case 'year':
        if (this.currentYear <= 1966)return;
        this.currentYear -= 2;
        this.initDate();
        break;
      case 'all':
        if (this.currentYear <= 1966)return;
        this.currentYear -= 12;
        this.initDate();
        break;
    }
  };

  next() {
    switch (this.type){
      case 'month':
        if(this.currentMonth == 11){
          if (this.currentYear >= (this.now.getFullYear() + 50))return;
          this.currentMonth = 0;
          this.currentYear++;
        } else {
          this.currentMonth++;
        }

        this.initDate();
        break;
      case 'year':
        if (this.currentYear >= (this.now.getFullYear() + 50))return;
        this.currentYear -= -2;
        this.initDate();
        break;
      case 'all':
        if (this.currentYear >= (this.now.getFullYear() + 50))return;
        this.currentYear -= -12;
        this.initDate();
        break;
    }
  };

  compareDates(date1, date2?){
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
  dialogClose(){
    this.broadcaster.broadcast('closeDialog');
  }
}
