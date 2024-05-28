'use strict';

angular.module('goalManage', ['Interpolation',
    'Components',
    'LocalStorageModule',
    'angular-cache',
    'angulartics',
    'ngResource',
    'angulartics.google.analytics',
    'LocalStorageModule',
    'PathPrefix'
    ])
  .config(function (localStorageServiceProvider ) {
    localStorageServiceProvider
      .setPrefix('goal')
      .setNotify(false, false);
  })
  .value('template', { 
    addTemplate: '',
    doneTemplate: '',
    goalUsersTemplate: '',
    reportTemplate: '',
    removeProfileTemplate: '',
    commonTemplate: ''
  })
  .value('userGoalData', { 
      data: {},
      manage: "",
      doneData: {}
  })
  .value('userData', {
    data: {},
    report: {},
    itemId: 0,
    usersCount: 0,
    type: 1
  })
  .value('refreshingDate', {
    userId: '',
    goalId: ''
  });
