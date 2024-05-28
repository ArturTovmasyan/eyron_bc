import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { DraftsComponent } from './drafts.component';
import { TranslateModule} from 'ng2-translate';
import { ActivityBlockModule } from '../block/activityBlock.module';
import { ComponentModule} from '../components/components.module'
import { DraftFooterComponent } from '../components/draft-footer/draft-footer.component';
import { ModalsModule} from '../modals/modals.module';
import { MaterialModule } from '@angular/material';
import { InfiniteScrollModule } from 'angular2-infinite-scroll';

import { DraftRouting } from './draft-routing';

@NgModule({
  imports: [
    CommonModule,
    DraftRouting,
    TranslateModule,
    ComponentModule,
    ActivityBlockModule,
    ModalsModule,
    InfiniteScrollModule,
    MaterialModule.forRoot()
  ],
  declarations: [
    DraftsComponent,
    DraftFooterComponent
  ]
})
export class DraftsModule { }
