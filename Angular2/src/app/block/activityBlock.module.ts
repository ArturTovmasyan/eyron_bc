import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {TranslateModule} from 'ng2-translate';
import { RouterModule } from '@angular/router';
import { MaterialModule } from '@angular/material';

import { ComponentModule } from '../components/components.module';

import {GoalFriendsBlockComponent} from './goal-friends/goal-friends.component';
import {LeaderboardsBlockComponent} from './leaderboards/leaderboards.component';
import {MyListBlockComponent} from './my-list/my-list.component';
import {TopIdeasBlockComponent} from './top-ideas/top-ideas.component';
import {CompleteProfileBlockComponent} from './complete-profile/complete-profile.component';
import {ProfileHeaderComponent} from './../indexes';
import {CreateGoalComponent} from './create-goal/create-goal.component';

@NgModule({
  imports: [
    CommonModule,
    TranslateModule,
    RouterModule,
    ComponentModule,
    MaterialModule.forRoot()
  ],
  declarations: [
    GoalFriendsBlockComponent,
    LeaderboardsBlockComponent,
    MyListBlockComponent,
    TopIdeasBlockComponent,
    CompleteProfileBlockComponent,
    ProfileHeaderComponent,
    CreateGoalComponent,
  ],
  exports: [ GoalFriendsBlockComponent,
    LeaderboardsBlockComponent,
    MyListBlockComponent,
    TopIdeasBlockComponent,
    CompleteProfileBlockComponent,
    ProfileHeaderComponent,
    CreateGoalComponent,
  ]
})
export class ActivityBlockModule { }
