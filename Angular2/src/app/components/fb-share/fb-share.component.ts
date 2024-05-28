import { Component, OnInit, Input } from '@angular/core';
declare  var FB;

@Component({
  selector: 'fb-share',
  templateUrl: './fb-share.component.html',
  styleUrls: ['./fb-share.component.less']
})
export class FbShareComponent implements OnInit {
  @Input() name: string;
  @Input() link: string;
  @Input() caption: string;
  @Input() picture: string;
  @Input() message: string;
  @Input() description: string;
  constructor() { }

  ngOnInit() {
    // (window.location.href.indexOf('bucketlist.loc') === -1)?"486680294849466" : "999576146739877",
    (<any>window).fbAsyncInit = function() {
      FB.init({
        appId      : "486680294849466",
        xfbml      : true,
        version    : 'v2.10'
      });
    };
    (function(d, s, id){
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) {return;}
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_US/sdk.js";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
  }

  share(){
    FB.ui({
      method: 'share',
      name: this.name,
      href: this.link,
      picture: this.picture,
      caption: this.caption,
      description: this.description,
      message: this.message
    })
  }
}
