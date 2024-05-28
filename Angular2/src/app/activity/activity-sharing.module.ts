import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { TranslateModule} from 'ng2-translate';
import { RouterModule } from '@angular/router';
import { ComponentModule } from '../components/components.module';
import { SwiperModule } from 'angular2-useful-swiper';
import { MaterialModule } from '@angular/material';
import { Angulartics2Module } from 'angulartics2';

import { MyActivityComponent } from './my-activity.component';
import { SliderComponent } from './slider.component';
import { ActivityGoalComponent } from '../components/activity-goal/activity-goal.component';
import { ActivityGoalFooterComponent } from '../components/activity-goal-footer/activity-goal-footer.component';

@NgModule({
  imports: [
    CommonModule,
    TranslateModule,
    RouterModule,
    FormsModule,
    ComponentModule,
    SwiperModule,
    MaterialModule.forRoot(),
    Angulartics2Module.forChild()
  ],
  declarations: [
    MyActivityComponent,
    ActivityGoalComponent,
    ActivityGoalFooterComponent,
    SliderComponent
  ],
  exports: [
    MyActivityComponent,
    ActivityGoalComponent,
    ActivityGoalFooterComponent,
    SliderComponent
  ]
})
export class ActivitySharingModule { }
