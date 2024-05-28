import { Component, OnInit } from '@angular/core';
import { ProjectService } from '../../project.service';
import {Goal} from '../../interface/goal';

@Component({
  selector: 'see-also',
  templateUrl: './see-also.component.html',
  styleUrls: ['./see-also.component.less']
})
export class SeeAlsoComponent implements OnInit {
  public start: number = 0;
  public count: number = 3;
  public search: string = '';
  public category: string = '';
  public errorMessage: string;
  public items: Goal[];
  
  constructor(
      private _projectService: ProjectService
  ) { }

  ngOnInit() {
    this.getGoals();
  }
  getGoals(){
    this._projectService.getIdeaGoals(this.start,this.count,this.search,this.category)
        .subscribe(
            goals =>{
              this.items = goals;
            },
            error => this.errorMessage = <any>error);
        
  }

}
