import { Component, OnInit, Input, EventEmitter, Output } from '@angular/core';
import { Broadcaster } from '../../tools/broadcaster';

@Component({
  selector: 'lightbox',
  templateUrl: './lightbox.component.html',
  styleUrls: ['./lightbox.component.less']
})
export class LightboxComponent implements OnInit {
  @Input() data: any;
  @Input() type: string;
  @Output() onClose: EventEmitter<any> = new EventEmitter();
  
  public img_path: any;

  public config: any = {
    pagination: '.swiper-pagination',
    paginationClickable: true,
    autoHeight: true,
    // loop: true,
    nextButton: '.swiper-button-next',
    prevButton: '.swiper-button-prev',
    spaceBetween: 30,
    // autoplay: 3000
  };

  constructor(private broadcaster: Broadcaster) { }

  ngOnInit() {
  }
  
  closeLightBox(){
    this.onClose.emit(null);
  }
}
