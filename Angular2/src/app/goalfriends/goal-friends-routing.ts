import { ModuleWithProviders } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { GoalfriendsComponent }    from './../indexes';

const GoalfriendsRoutes: Routes = [
  { path: '',  component: GoalfriendsComponent,
    data: {
      metadata: {
        title: 'GoalFriends',
        description: 'My GoalFriends'
      }
    }
  },
  { path: ':type',  component: GoalfriendsComponent,
    data: {
      metadata: {
        title: 'GoalFriends',
        description: 'My GoalFriends'
      }
    }
  },
  { path: ':type/:search',  component: GoalfriendsComponent,
    data: {
      metadata: {
        title: 'GoalFriends',
        description: 'My GoalFriends'
      }
    }
  }
];

export const GoalfriendsRouting: ModuleWithProviders = RouterModule.forChild(GoalfriendsRoutes);
