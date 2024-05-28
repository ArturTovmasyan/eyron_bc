'use strict';

angular.module('goalManage').constant('UserGoalConstant',{
  // constants for status
  ACTIVE: 1,
  COMPLETED: 2,
  ACTIVE_PATH: 'active-goals',
  COMPLETED_PATH: 'completed-goals',
  COMMON_PATH: 'common-goals',
  ACTIVITY_PATH: 'activity',
  OWNED_PATH: 'owned',
  // constants for filter in twig
  URGENT_IMPORTANT: 1,
  URGENT_NOT_IMPORTANT: 2,
  NOT_URGENT_IMPORTANT: 3,
  NOT_URGENT_NOT_IMPORTANT: 4,
  // constants for steps
  TO_DO: 0,
  DONE: 1
});