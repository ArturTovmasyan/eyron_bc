import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { GoalCreateComponent } from './goal-create.component';
import {TranslateModule} from 'ng2-translate';
import { FormsModule } from '@angular/forms';
import {ComponentModule} from '../components/components.module';
import { SwiperModule } from 'angular2-useful-swiper';
import { GoalCreateRouting } from './goal-create-routing';
import { MaterialModule } from '@angular/material';

@NgModule({
  imports: [
    CommonModule,
    GoalCreateRouting,
    TranslateModule,
    ComponentModule,
    FormsModule,
    SwiperModule,
    MaterialModule.forRoot()
  ],
  declarations: [
    GoalCreateComponent
  ]
})
export class GoalCreateModule { }
