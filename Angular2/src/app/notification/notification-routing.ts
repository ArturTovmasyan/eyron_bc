import { ModuleWithProviders } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { NotificationComponent }    from './notification.component';

const NotificationRoutes: Routes = [
  { path: '',  component: NotificationComponent,
    data: {
      metadata: {
        title: 'Notifications',
        description: 'My Notifications'
      }
    }
  }
];

export const NotificationRouting: ModuleWithProviders = RouterModule.forChild(NotificationRoutes);
