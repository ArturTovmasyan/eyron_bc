import { Component, OnChanges, Input } from '@angular/core';
// import { Router } from '@angular/router';

@Component({
  selector: 'home-footer',
  templateUrl: './home-footer.component.html',
  styleUrls: ['./home-footer.component.less']
})

export class HomeFooterComponent implements OnChanges {
  @Input() privacyMenu;
  slug:string;
  name:string;

  constructor() { }

  ngOnChanges() {
    if(this.privacyMenu && this.privacyMenu.isTerm){
      this.slug = this.privacyMenu.slug;
      this.name = this.privacyMenu.name;
    }
  }

}
