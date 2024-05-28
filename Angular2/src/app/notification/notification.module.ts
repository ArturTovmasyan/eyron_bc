import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NotificationComponent } from './notification.component';
import { ComponentModule } from '../components/components.module';
import { ActivityBlockModule } from '../block/activityBlock.module';
import { TranslateModule} from 'ng2-translate';
import { InfiniteScrollModule } from 'angular2-infinite-scroll';

import { NotificationRouting } from './notification-routing';

@NgModule({
  imports: [
    CommonModule,
    NotificationRouting,
    ComponentModule,
    ActivityBlockModule,
    TranslateModule,
    InfiniteScrollModule
  ],
  declarations: [
    NotificationComponent
  ]
})
export class NotificationModule { }
