import { Component, OnInit } from '@angular/core';
import {MdDialog, MdDialogRef} from '@angular/material';

@Component({
  selector: 'app-confirm',
  templateUrl: './confirm.component.html',
  styleUrls: ['./confirm.component.less']
})
export class ConfirmComponent implements OnInit {

  public lsText: string;
  public isOpen:boolean = false;
  constructor(public dialogRef: MdDialogRef<ConfirmComponent>) { }

  ngOnInit() {
    setTimeout(()=>{
      this.isOpen = true;
    },1000)
  }
  closeModal(){
    if(!this.isOpen)return;
    this.isOpen = false;
    this.dialogRef.close();
  }
}
