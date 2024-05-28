import { Component, OnInit } from '@angular/core';
import { Broadcaster } from '../../tools/broadcaster';

@Component({
  selector: 'app-not-active',
  templateUrl: './not-active.component.html',
  styleUrls: ['./not-active.component.less']
})

export class NotActiveComponent implements OnInit {
    
  constructor(private broadcaster: Broadcaster)
  {}

  ngOnInit() {
  }
}
