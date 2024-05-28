import { ModuleWithProviders } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { GoalCreateComponent }    from './goal-create.component';

const GoalCreateRoutes: Routes = [
  { path: '',  component: GoalCreateComponent,
    data: {
      metadata: {
        title: 'Goal Create',
        description: 'Create Your Goal'
      }
    }
  },
  { path: ':id',  component: GoalCreateComponent,
    data: {
      metadata: {
        title: 'Goal Edit',
        description: 'Edit Your Goal'
      }
    }
  },
  { path: ':id/:status',  component: GoalCreateComponent,
    data: {
      metadata: {
        title: 'Goal Edit',
        description: 'Edit Your Goal'
      }
    }
  }
];

export const GoalCreateRouting: ModuleWithProviders = RouterModule.forChild(GoalCreateRoutes);
