import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LeaderboardComponent } from './../indexes';
import { TranslateModule} from 'ng2-translate';
import { ComponentModule } from '../components/components.module';
import { MaterialModule } from '@angular/material';

import { LeaderboardRouting } from './leaderboard-routing';

@NgModule({
  imports: [
    CommonModule,
    LeaderboardRouting,
    ComponentModule,
    TranslateModule,
    MaterialModule.forRoot()
  ],
  declarations: [LeaderboardComponent]
})
export class LeaderboardModule { }
