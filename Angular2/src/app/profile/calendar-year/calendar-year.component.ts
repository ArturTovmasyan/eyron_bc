import { Component, OnInit, Input,ViewChildren} from '@angular/core';
import { MdTooltip } from '@angular/material';
@Component({
  selector: 'calendar-year',
  templateUrl: './calendar-year.component.html',
  styleUrls: ['./calendar-year.component.less']
})
export class CalendarYearComponent implements OnInit {
  public colArray: number[] = [0,1];
  public trArray: number[] = [0,1,2,3];
  public tdArray: number[] = [0,1,2];
  @Input() myYAMonths: any;
  @Input() currentYear: number;
  @ViewChildren(MdTooltip) myTouch;
  constructor() { }

  ngOnInit() {
  }

  dateByFormat(year, month, day) {
    return new Date(year, month, day);
    // moment(year + '-' +((month > 9)?month:'0'+month)+'-'+((day > 9)?day:'0'+day));
    // return format?date.format(format):date;
  };
  mouseDown(ev: MouseEvent,id){
    for(let i of this.myTouch._results){
      if(i._elementRef.nativeElement.id == id){
        i.showDelay = 300;
        i.position = 'right';
        i.show();
      }
    }
  }
  mouseUp(ev: MouseEvent,id){
    for(let i of this.myTouch._results){
      if(i._elementRef.nativeElement.id == id){
        i.hideDelay = 300;
        i.hide();
      }
    }
  }
}
