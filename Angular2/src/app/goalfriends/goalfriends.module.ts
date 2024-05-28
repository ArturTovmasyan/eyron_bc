import { NgModule } from '@angular/core';
import { FormsModule }    from '@angular/forms';
import { CommonModule } from '@angular/common';
import { GoalfriendsComponent } from './../indexes';
import { TranslateModule} from 'ng2-translate';
import { ComponentModule } from '../components/components.module';
import { ActivityBlockModule } from '../block/activityBlock.module';
import { InfiniteScrollModule } from 'angular2-infinite-scroll';
import { SwiperModule }     from 'angular2-useful-swiper';
import { MaterialModule } from '@angular/material';

import { GoalfriendsRouting } from './goal-friends-routing';

@NgModule({
  imports: [
    ComponentModule,
    CommonModule,
    FormsModule,
    GoalfriendsRouting,
    TranslateModule,
    InfiniteScrollModule,
    ActivityBlockModule,
    SwiperModule,
    MaterialModule.forRoot()
  ],
  declarations: [GoalfriendsComponent]
})
export class GoalfriendsModule { }
