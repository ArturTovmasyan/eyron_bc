import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {TranslateModule} from 'ng2-translate';

import { ActivityComponent } from './../indexes';
import { ActivitySharingModule } from './activity-sharing.module';
import { ComponentModule } from '../components/components.module';
import { ActivityBlockModule } from '../block/activityBlock.module';

import { ActivityRouting } from './activity-routing';

@NgModule({
  imports: [
    CommonModule,
    ActivityRouting,
    TranslateModule,
    ComponentModule,
    ActivityBlockModule,
    ActivitySharingModule
  ],
  declarations: [
    ActivityComponent
  ]
})
export class ActivityModule { }
