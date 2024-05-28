import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { HttpModule, JsonpModule, Http } from '@angular/http';
import { TranslateModule, TranslateLoader, TranslateStaticLoader} from 'ng2-translate';
import { InfiniteScrollModule } from 'angular2-infinite-scroll';
import { DndModule} from 'ng2-dnd';
import { NotificationDropdownComponent } from './components/notification-dropdown/notification-dropdown.component';
import { PerfectScrollbarModule, PerfectScrollbarConfigInterface } from 'angular2-perfect-scrollbar';
import { MaterialModule } from '@angular/material';
import { Angulartics2Module, Angulartics2GoogleAnalytics } from 'angulartics2';
import { ValidationService } from './validation.service';
import { Broadcaster} from './tools/broadcaster';
// import { ClickOutsideDirective} from './tools/outside';
import { CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';

import { ToolsSharingModule} from './tools/tools-sharing.module';
import { AngularFireModule } from 'angularfire2';
import { AngularFireDatabaseModule, AngularFireDatabase, FirebaseListObservable } from 'angularfire2/database';
import { AngularFireAuthModule, AngularFireAuth } from 'angularfire2/auth';

import { SwiperModule } from 'angular2-useful-swiper';
import { Uploader }      from 'angular2-http-file-upload';
import { MetadataModule } from 'ng2-metadata';

const PERFECT_SCROLLBAR_CONFIG: PerfectScrollbarConfigInterface = {
  suppressScrollX: true
};

import { AgmCoreModule } from "angular2-google-maps/core";

// Must export the config
export const firebaseConfig = {
  apiKey: "AIzaSyDS4TuFB7Uj-M0exn1qWHVpaUhUwwKanlQ",
  authDomain: "bucketlist-f143c.firebaseapp.com",
  databaseURL: "https://bucketlist-f143c.firebaseio.com",
  storageBucket: "bucketlist-f143c.appspot.com",
  messagingSenderId: "264286375978"
};

import { AppComponent } from './indexes';
import { AuthGuard }    from './common/auth.guard';
import { LoginGuard }    from './common/login.guard';
import { appRouting }   from './app-routing';
import { ProjectService } from './project.service';

import { DashboardComponent } from './dashboard/dashboard.component';
import { RegisterComponent } from './components/register/register.component';
import { LoginComponent } from './login/login.component';
import { DiscoverGoalComponent } from './components/discover-goal/discover-goal.component';
import { BaseStoriesComponent } from './components/base-stories/base-stories.component';
import { HomeFooterComponent } from './components/home-footer/home-footer.component';
import { ComponentModule } from './components/components.module';
import { ResettingRequestComponent } from './components/resetting-request/resetting-request.component';
import { PageComponent } from './page/page.component';
import { ErrorComponent } from './components/error/error.component';
import { NotActiveComponent } from './components/not-active/not-active.component';
import { RegistrationConfirmComponent } from './components/registration-confirm/registration-confirm.component';



//modals
import { ReportComponent } from './modals/report/report.component';
import { CommonComponent } from './modals/common/common.component';
import { UsersComponent } from './modals/users/users.component';
import { AddComponent } from './modals/add/add.component';
import { DoneComponent } from './modals/done/done.component';
import { ModalsModule } from './modals/modals.module';

export function createTranslateLoader(http: Http) {
  return new TranslateStaticLoader(http, './assets/i18n', '.json');
}

@NgModule({
  declarations: [
    AppComponent,
    DashboardComponent,
    DiscoverGoalComponent,
    BaseStoriesComponent,
    HomeFooterComponent,
    NotificationDropdownComponent,
    LoginComponent,
    RegisterComponent,
    ResettingRequestComponent,

    PageComponent,
    ReportComponent,

    CommonComponent,
    UsersComponent,
    AddComponent,
    DoneComponent,
    // ClickOutsideDirective,
    ErrorComponent,
    NotActiveComponent,
    RegistrationConfirmComponent
  ],
  imports: [
    BrowserModule,
    FormsModule,
    ReactiveFormsModule,
    AgmCoreModule.forRoot({
      apiKey: "AIzaSyBN9sWpmv-6mArNqz_oSStVdpuCTt-lu6g",
      libraries: ["places"]
    }),
    ComponentModule,
    InfiniteScrollModule,
    HttpModule,
    JsonpModule,
    appRouting,
    MetadataModule.forRoot(),
    SwiperModule,
    ToolsSharingModule,
    BrowserAnimationsModule,
    Angulartics2Module.forRoot([ Angulartics2GoogleAnalytics ]),
    AngularFireModule.initializeApp(firebaseConfig),
    AngularFireDatabaseModule,
    AngularFireAuthModule,
    PerfectScrollbarModule.forRoot(PERFECT_SCROLLBAR_CONFIG),
    DndModule.forRoot(),
    MaterialModule.forRoot(),
    ModalsModule,
    TranslateModule.forRoot({
      provide: TranslateLoader,
      useFactory: (createTranslateLoader),
      deps: [Http]
    })
  ],
  entryComponents: [
    AddComponent,
    DoneComponent,
    UsersComponent,
    CommonComponent,
    ReportComponent
  ],
  providers: [
    ProjectService,
    AuthGuard,
    LoginGuard,
    Broadcaster,
    ValidationService,
    CacheService,
    Uploader,
    // ClickOutsideDirective
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }