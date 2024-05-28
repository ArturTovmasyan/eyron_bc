import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'bl-loading',
  templateUrl: './bl-loading.component.html',
  styleUrls: ['./bl-loading.component.less']
})
export class BlLoadingComponent implements OnInit {
  public step:number = 0;
  public color:string;
  public interval:any;

  constructor() { }

  ngOnInit() {
    var that = this;
    this.interval = setInterval (() =>{
        if(that.step % 3 == 1){
            that.color = 'accent';
        }
        else if(that.step % 3 == 2) {
            that.color = 'primary';
        }
        else {
            that.color = 'warn';
        }
        that.step++;
     },900);
  }
  ngOnDestroy(){
    clearInterval(this.interval);
  }

}
