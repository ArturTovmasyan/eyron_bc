import { ModuleWithProviders } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { ActivityComponent }    from './../indexes';

const ActivityRoutes: Routes = [
  { path: '',  component: ActivityComponent,
    data: {
      metadata: {
        title: 'Activity',
        description: 'My Activity'
      }
    }
  }
];

export const ActivityRouting: ModuleWithProviders = RouterModule.forChild(ActivityRoutes);
