import { Component, OnInit, Input } from '@angular/core';

@Component({
  selector: 'overall-block',
  templateUrl: './overall.component.html',
  styleUrls: ['./overall.component.less']
})
export class OverallComponent implements OnInit {

  @Input() overall:number;
  constructor() { }

  ngOnInit() {
  }

}
