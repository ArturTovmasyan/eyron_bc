import { ModuleWithProviders } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { IdeasComponent }    from './ideas.component';

const IdeasRoutes: Routes = [
  { path: '',  component: IdeasComponent },
  { path: ':category', component: IdeasComponent },
  { path: ':category/:search', component: IdeasComponent }
];

export const IdeasRouting: ModuleWithProviders = RouterModule.forChild(IdeasRoutes);
