import { Component, OnInit } from '@angular/core';
import { MdDialog, MdDialogRef} from '@angular/material';
import { Broadcaster} from "../../tools/broadcaster";
import { User} from "../../interface/user";
import { TranslateService} from 'ng2-translate';
import { ProjectService} from "../../project.service";
import { Router } from '@angular/router';

export enum complaintTypes {
  notificationsOf = 1,
  privateGoal = 2,
  googleSearch = 3,
  signOut = 4,
  deleteAccount = 5
}

export enum deleteTypes {
  elswhere = 1,
  moreNotification = 2,
  notExpected = 3,
  doneEverything = 4,
  other = 5
}

@Component({
  selector: 'app-remove-profile',
  templateUrl: './remove-profile.component.html',
  styleUrls: ['./remove-profile.component.less']
})
export class RemoveProfileComponent implements OnInit {
  public isMobile:boolean = (window.innerWidth < 768);
  public isOpen:boolean = false;
  public appUser: User;
  public step = 1;
  public complaintTypes: typeof complaintTypes = complaintTypes;
  public deleteTypes: typeof deleteTypes = deleteTypes;
  public complaintType: number = null;
  public deleteType: number = null;
  public deleteReason = null;
  public isInvalid: boolean = false;
  public password: string = '';
  public badPassword: boolean = false;
  public isLoad: boolean = false;
  public deleted: boolean = false;

  constructor(
      public dialogRef: MdDialogRef<RemoveProfileComponent>,
      private broadcaster: Broadcaster,
      private _translate: TranslateService,
      private _projectService: ProjectService,
      private router: Router
  ) { }

  ngOnInit() {
    setTimeout(()=>{
      this.isOpen = true;
    },1000)
  }

  nextStep() {
    this.step++;
  };

  stay() {
    if(this.complaintType == this.complaintTypes.deleteAccount) {
      this.closeModal();
    } else {
      switch (this.complaintType) {
        case this.complaintTypes.notificationsOf:
            this._projectService.switchNotificationsOff()
                .subscribe(
                    () => {
                      this.router.navigate(['/ideas']);
                    });
          break;
        case this.complaintTypes.privateGoal:
          this._projectService.invisibleAllGoals()
              .subscribe(
                  () => {
                    this.router.navigate(['/ideas']);
                  });
          break;
        case this.complaintTypes.signOut:
          this.broadcaster.broadcast('log-Out');
          break;
        default:
          this.closeModal();
      }
    }
  };

  continue() {
    if(this.step) {
      if(this.step == 1) {
        if(this.complaintType == this.complaintTypes.deleteAccount) {
          this.nextStep();
        }
      } else {
        if(this.deleteType) {
          if(this.deleteType == this.deleteTypes.other && !this.deleteReason ) {
            this.isInvalid = true;
          } else {
            if (this.appUser.gplus_uid || this.appUser.facebook_uid || this.appUser.twitter_uid) {
              this.deleteAccount('');
            } else {
              this.nextStep();
            }
          }
        }
      }
    }
  }

  deleteAccount = function (password) {
    let data = {
      'password' : password,
      'reasone' : 'no Reason'
    };

    switch (this.deleteType) {
      case this.deleteTypes.elswhere:
        data.reasone = this._translate.instant('delete_profile.something_else');
        break;
      case this.deleteTypes.moreNotification:
        data.reasone = this._translate.instant('delete_profile.many_notify');
        break;
      case this.deleteTypes.notExpected:
        data.reasone = this._translate.instant('delete_profile.not_expected');
        break;
      case this.deleteTypes.doneEverything:
        data.reasone = this._translate.instant('delete_profile.done_everything');
        break;
      case this.deleteTypes.other:
        data.reasone = this.deleteReason;
        break;
    }

    this.isLoad = true;
    this._projectService.removeProfile(data)
      .subscribe(
          () => {
            this.deleted = true;
            this.broadcaster.broadcast('isDeleted');
            this.isLoad = false;
            setTimeout(() =>{
              this.broadcaster.broadcast('log-Out');
            },5000);
          }, () => {
            this.isLoad = false;
            this.badPassword = true;
          }
      );
  };

  checkAccount = function () {
    this.badPassword = false;
    this.deleteAccount(this.password);
  };
  
  closeModal(){
    if(!this.isOpen)return;
    this.isOpen = false;
    this.dialogRef.close();
  }

  ngOnDestroy() {
    if(this.deleted){
      this.broadcaster.broadcast('log-Out');
    }
    this.dialogRef.close();
  }
}
