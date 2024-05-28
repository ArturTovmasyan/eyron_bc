import {Component, OnInit, Input, ViewChildren} from '@angular/core';
import { MdTooltip } from '@angular/material';

@Component({
  selector: 'calendar-all-year',
  templateUrl: './calendar-all-year.component.html',
  styleUrls: ['./calendar-all-year.component.less']
})
export class CalendarAllYearComponent implements OnInit {
  public colArray: number[] = [0,1,2,3,4,5,6,7,8,9,10,11];
  @Input() currentYear: any;
  @Input() myYears: number;
  @ViewChildren(MdTooltip) myTouch;
  constructor() {
  }

  ngOnInit() {
      }
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
