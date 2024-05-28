import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { TranslateModule} from 'ng2-translate';
import { RouterModule } from '@angular/router';
import { MaterialModule } from '@angular/material';

import {GoalUsersComponent} from './goal-users/goal-users.component';
import {GoalFooterComponent} from './goal-footer/goal-footer.component';
import {GoalComponent} from './goal/goal.component';
import {LeaderboardComponent} from './leaderboard/leaderboard.component';
import {GoalFriendComponent} from './goal-friend/goal-friend.component';
import { UserComponent } from './user/user.component';
import { CommentComponent } from './comment/comment.component';
import { ProfileGoalComponent } from './profile-goal/profile-goal.component';
import { ControlMessagesComponent } from './control-messages/control-messages.component';
import { SeeAlsoComponent } from './see-also/see-also.component';

import { SliceEmailPipe } from '../pipes/sliceEmail.pipe';
import { RoundPipe } from '../pipes/round.pipe';
import { RemoveTagPipe } from '../pipes/removeTag.pipe';
import { SafeHtmlPipe } from '../pipes/safeHtml.pipe';
import { SortArrayPipe } from '../pipes/sortArray.pipe';
import { HtmlDirective } from '../tools/html.directive';
import { EmbedVideoComponent } from './embed-video/embed-video.component';
import { InputVideoComponent } from './embed-video/input-video.component';
import { MyDropzoneComponent } from './my-dropzone/my-dropzone.component';
import { FbShareComponent } from './fb-share/fb-share.component';
import { PreloadImgComponent } from './preload-img/preload-img.component';
import { PerfectScrollbarModule, PerfectScrollbarConfigInterface } from 'angular2-perfect-scrollbar';
import { BlLoadingComponent } from './bl-loading/bl-loading.component';

const PERFECT_SCROLLBAR_CONFIG: PerfectScrollbarConfigInterface = {
  suppressScrollX: true
};


@NgModule({
  imports: [
    CommonModule,
    TranslateModule,
    RouterModule,
    MaterialModule.forRoot(),
    FormsModule,
    PerfectScrollbarModule.forChild(),
  ],
  declarations: [
    GoalUsersComponent,
    GoalComponent,
    GoalFooterComponent,
    LeaderboardComponent,
    GoalFriendComponent,
    SliceEmailPipe,
    RoundPipe,
    SortArrayPipe,
    RemoveTagPipe,
    SafeHtmlPipe,
    HtmlDirective,
    UserComponent,
    CommentComponent,
    ProfileGoalComponent,
    ControlMessagesComponent,
    EmbedVideoComponent,
    InputVideoComponent,
    SeeAlsoComponent,
    MyDropzoneComponent,
    FbShareComponent,
    PreloadImgComponent,
    BlLoadingComponent
  ],
  exports: [ GoalUsersComponent,
    GoalComponent,
    GoalFooterComponent,
    LeaderboardComponent,
    GoalFriendComponent,
    SliceEmailPipe,
    RoundPipe,
    UserComponent,
    CommentComponent,
    ProfileGoalComponent,
    RemoveTagPipe,
    SortArrayPipe,
    SafeHtmlPipe,
    HtmlDirective,
    ControlMessagesComponent,
    EmbedVideoComponent,
    InputVideoComponent,
    SeeAlsoComponent,
    MyDropzoneComponent,
    FbShareComponent,
    PreloadImgComponent,
    BlLoadingComponent
  ],
  entryComponents:[
    CommentComponent
  ]
})
export class ComponentModule { }
