import { Component, OnInit, OnDestroy} from '@angular/core';
import { RouterModule, Routes, ActivatedRoute, Router, NavigationEnd } from '@angular/router';
import {ProjectService} from '../project.service';
import {Goal} from '../interface/goal';
import { MetadataService } from 'ng2-metadata';

@Component({
  selector: 'drafts',
  templateUrl: './drafts.component.html',
  styleUrls: ['./drafts.component.css']
})
export class DraftsComponent implements OnInit, OnDestroy {
    public eventId: number = 0;
    public type: string ;
    public sub: any ;
    public start: number = 0;
    public count: number = 9;
    public goals: Goal[];
    public errorMessage:string;
    public empty:boolean = false;
    public busy: boolean = false;
    public isDestroy: boolean = false;
    public reserve: Goal[];
    
    constructor(
      private metadataService: MetadataService,
      private _projectService: ProjectService,
      private router:Router,
      private route: ActivatedRoute
    ){
        this.sub = router.events.subscribe((event) => {
            if(event instanceof NavigationEnd ) {
                if (!this.isDestroy && this.eventId != event.id) {
                    this.eventId = event.id;
                    this.type = (this.route.snapshot.paramMap.has('slug') && this.route.snapshot.paramMap.get('slug') == 'drafts') ? 'drafts' : 'private';
                    this.metadataService.setTitle(this.type);
                    this.metadataService.setTag('description', this.type);
                    this.start = 0;
                    this.goals = null;
                    this.reserve = null;
                    window.scrollTo(0, 0);
                    this.getGoals();
                }
            }
        })
    }
    ngOnDestroy(){
        this.sub.unsubscribe();
        this.isDestroy = true;
    }
    
    ngOnInit() {
    }
    
    getGoals(){
   this._projectService.getMyIdeas(this.type, this.start,this.count)
       .subscribe(
           goals =>{
               this.empty = (goals.length == 0);
               this.goals = goals;
               this.start += this.count;
               this.setReserve();
           },
      error => this.errorMessage = <any>error);
       
  }
    setReserve(){
        this._projectService.getMyIdeas(this.type, this.start,this.count)
            .subscribe(
                goals =>{
                    this.reserve = goals;
                    this.start += this.count;
                    this.busy = false;
                }
            )
        
    }
    getReserve(){
        this.goals = this.goals.concat(this.reserve);
        this.setReserve();
        
    }
    onScroll(){
        if(this.busy || !this.reserve || !this.reserve.length)return;
        this.busy = true;
        this.getReserve();
    }

}
