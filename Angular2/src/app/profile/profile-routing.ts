import { ModuleWithProviders } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { ProfileComponent }    from './../indexes';

const ProfileRoutes: Routes = [
  { path: '',  component: ProfileComponent },
  { path: ':uId',  component: ProfileComponent },
  { path: ':uId/:type',  component: ProfileComponent }
];

export const ProfileRouting: ModuleWithProviders = RouterModule.forChild(ProfileRoutes);
