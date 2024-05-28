import { Component, OnInit, Input, ViewChildren} from '@angular/core';
import { MdTooltip } from '@angular/material';
@Component({
  selector: 'calendar-month',
  templateUrl: './calendar-month.component.html',
  styleUrls: ['./calendar-month.component.less']
})
export class CalendarMonthComponent implements OnInit {
  public trArray: number[] = [0,1,2,3,4,5]; 
  public tdArray: number[] = [0,1,2,3,4,5,6];
  @Input() myDays: any;
  @Input() days: any;
  @ViewChildren(MdTooltip) myTouch;
  constructor() { }

  ngOnInit() {}
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
