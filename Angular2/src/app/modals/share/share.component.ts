import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import {MdDialog, MdDialogRef} from '@angular/material';


@Component({
  selector: 'share-modal',
  templateUrl: './share.component.html',
  styleUrls: ['./share.component.less'],
  encapsulation: ViewEncapsulation.None
})
export class ShareComponent implements OnInit {

  public linkToShare:string;
  public isOpen:boolean = false;
  fbInner = "<img src='../../assets/images/facebook-share.svg'> <span>Facebook</span>";
  twitterInner = "<img src='../../assets/images/twitter-share.svg'> <span>Twitter</span>";
  pintInner = "<img src='../../assets/images/pinterest-share.svg'> <span>Pinterest</span>";
  inInner = "<img src='../../assets/images/linkedin-share.svg'> <span>LinkedIn</span>";
  googleInner = "<img src='../../assets/images/google-plus-share.svg'> <span>Google Plus</span>";
  constructor(
      public dialogRef: MdDialogRef<ShareComponent>
  ) {}

  ngOnInit() {
    if(document.querySelector('.mat-dialog-container')){
      (<any>document.querySelector('.mat-dialog-container')).position = "static";
    }
    setTimeout(()=>{
      this.isOpen = true;
    },1000)
  }
  closeModal1(){
    if(!this.isOpen)return;
    this.isOpen = false;
    this.dialogRef.close();
  }
}

  
  