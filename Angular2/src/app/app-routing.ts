import { ModuleWithProviders } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { DashboardComponent } from './dashboard/dashboard.component';
import { RegisterComponent } from './components/register/register.component';
import { RegistrationConfirmComponent } from './components/registration-confirm/registration-confirm.component';
import { ResettingRequestComponent } from './components/resetting-request/resetting-request.component';
import { AuthGuard }      from './common/auth.guard';
import { LoginGuard }      from './common/login.guard';
import { PageComponent} from './page/page.component';
import { ErrorComponent} from "./components/error/error.component";
import { NotActiveComponent} from "./components/not-active/not-active.component";

const appRoutes: Routes = [
  { path: 'register', component: RegisterComponent },

  { path: 'resetting/:type', component: ResettingRequestComponent },
  { path: 'resetting/:type/:secret', component: ResettingRequestComponent },
  { path: 'user/confirm/:secret', component: RegistrationConfirmComponent },
  { path: 'page', component: PageComponent},
  { path: 'page/:name', component: PageComponent},
  { path: 'activity', loadChildren: './activity/activity.module#ActivityModule', canActivate: [AuthGuard]},
  { path: 'profile', loadChildren: './profile/profile.module#ProfileModule', canActivate: [AuthGuard] },
  { path: 'goal/my-ideas', loadChildren: './drafts/drafts.module#DraftsModule', canActivate: [AuthGuard]},
  { path: 'goal/create', loadChildren: './goal-create/goal-create.module#GoalCreateModule', canActivate: [AuthGuard]},
  { path: 'goal-friends', loadChildren: './goalfriends/goalfriends.module#GoalfriendsModule', canActivate: [AuthGuard]},
  { path: 'leaderboard', loadChildren: './leaderboard/leaderboard.module#LeaderboardModule' },
  { path: 'notifications', loadChildren: './notification/notification.module#NotificationModule', canActivate: [AuthGuard]},
  { path: 'edit', loadChildren: './settings/settings.module#SettingsModule', canActivate: [AuthGuard]},
  { path: 'goal/:slug', loadChildren: './inner/inner.module#InnerModule'},
  { path: 'ideas', loadChildren: './ideas/ideas.module#IdeasModule'},
  { path: '', component: DashboardComponent, canActivate: [LoginGuard] },
  { path: 'login', component: DashboardComponent, canActivate: [LoginGuard] },
  { path: 'login/:type/:id',  component: DashboardComponent},
  { path: 'login/:type/:id/:slug',  component: DashboardComponent},
  { path: 'error', component: ErrorComponent },
  { path: 'not-active', component: NotActiveComponent },
  { path: '**', component: ErrorComponent }
];

export const appRouting: ModuleWithProviders = RouterModule.forRoot(appRoutes);
