import { Component, OnInit, Input } from '@angular/core';

@Component({
  selector: 'preload-img',
  templateUrl: './preload-img.component.html',
  styleUrls: ['./preload-img.component.less']
})
export class PreloadImgComponent implements OnInit {
  @Input() img: string;
  @Input() title: string;
  public initialize:boolean = false;

  constructor() { }

  ngOnInit() {
    let img = new Image();

    img.onload = () => {
      this.initialize = true;
    };
    
    img.src = this.img;
  }

}
