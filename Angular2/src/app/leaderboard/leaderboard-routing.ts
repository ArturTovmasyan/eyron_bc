import { ModuleWithProviders } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { LeaderboardComponent }    from './../indexes';

const LeaderboardRoutes: Routes = [
  { path: '',  component: LeaderboardComponent },
  { path: ':type',  component: LeaderboardComponent }
];

export const LeaderboardRouting: ModuleWithProviders = RouterModule.forChild(LeaderboardRoutes);
