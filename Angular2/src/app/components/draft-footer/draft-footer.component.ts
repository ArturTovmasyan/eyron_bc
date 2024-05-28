import { Component, OnInit, ViewContainerRef, Input } from '@angular/core';
import { MdDialog, MdDialogRef, MdDialogConfig} from '@angular/material';
import { ConfirmComponent} from '../../modals/confirm/confirm.component';
import {ProjectService} from '../../project.service';
import {Goal} from "../../interface/goal";
import {Broadcaster} from "../../tools/broadcaster"


@Component({
  selector: 'draft-footer',
  templateUrl: './draft-footer.component.html',
  styleUrls: ['../goal-footer/goal-footer.component.less']
})
export class DraftFooterComponent implements OnInit {
    @Input() slug: string;
    @Input() goals: Goal[];
    @Input() index: number;
    
  constructor(
        private _projectService : ProjectService,
        private viewContainerRef: ViewContainerRef,
        public dialog: MdDialog,
        private broadcaster: Broadcaster
  ){}

  ngOnInit() {}

 openDialog(id){

     let dialogRef: MdDialogRef<ConfirmComponent>;
     let config = new MdDialogConfig();
     config.viewContainerRef = this.viewContainerRef;
     dialogRef = this.dialog.open(ConfirmComponent, config);
     // dialogRef.componentInstance.lsText = 'Hellooooo';
     dialogRef.afterClosed().subscribe(result => {
         if(result == 'yes'){
             this._projectService.deleteDrafts(id)
                 .subscribe(
                     () => {}
                 );
             this.broadcaster.broadcast('removeDraft');
             this.goals.splice(this.index,1);
         }
     });
 }
}
