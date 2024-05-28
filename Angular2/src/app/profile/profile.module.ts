import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {TranslateModule} from 'ng2-translate';
import { ProfileComponent } from './../indexes';
import { ActivityBlockModule } from '../block/activityBlock.module';
import { FormsModule } from '@angular/forms';
import { MapModule }        from '../tools/map/map.module';
import { MaterialModule } from '@angular/material';
import { ActivitySharingModule } from '../activity/activity-sharing.module';
import { InfiniteScrollModule } from 'angular2-infinite-scroll';
import {ToolsSharingModule} from '../tools/tools-sharing.module';

import { ProfileRouting } from './profile-routing';
import { ComponentModule } from '../components/components.module';
import { CalendarComponent } from './calendar/calendar.component';
import { CalendarAllYearComponent } from './calendar-all-year/calendar-all-year.component';
import { CalendarMonthComponent } from './calendar-month/calendar-month.component';
import { CalendarYearComponent } from './calendar-year/calendar-year.component';
import { OverallComponent } from '../block/overall/overall.component';

@NgModule({
  imports: [
    CommonModule,
    ProfileRouting,
    ComponentModule,
    TranslateModule,
    ActivityBlockModule,
    FormsModule,
    MapModule,
    ActivitySharingModule,
    InfiniteScrollModule,
    ToolsSharingModule,
    MaterialModule.forRoot()
  ],
  declarations: [
    ProfileComponent,
    CalendarComponent,
    CalendarAllYearComponent,
    CalendarMonthComponent,
    CalendarYearComponent,
    OverallComponent,
  ],
  entryComponents: [
    CalendarComponent
  ]
})
export class ProfileModule { }
