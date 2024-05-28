import { Component, OnInit } from '@angular/core';
import { Broadcaster } from '../../tools/broadcaster';

@Component({
  selector: 'app-error',
  templateUrl: './error.component.html',
  styleUrls: ['./error.component.less']
})

export class ErrorComponent implements OnInit {

  errorMessage:any;

  constructor(private broadcaster: Broadcaster)
  {}

  ngOnInit() {
      this.getError();
  }

  getError() {
      this.broadcaster.on<any>('error')
          .subscribe(error => {
              this.errorMessage = error;
          });
  }
}
