import { ModuleWithProviders } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { SettingsComponent }    from './settings.component';

const SettingsRoutes: Routes = [
  { path: '',  component: SettingsComponent },
  { path: ':type',  component: SettingsComponent },
  { path: ':type/:secret/:addMail',  component: SettingsComponent }
];

export const SettingsRouting: ModuleWithProviders = RouterModule.forChild(SettingsRoutes);
