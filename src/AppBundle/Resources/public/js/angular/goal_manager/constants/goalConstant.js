'use strict';

angular.module('goalManage').constant('GoalConstant',{
  // constants for privacy status
  PUBLIC_PRIVACY: true,
  PRIVATE_PRIVACY: false,
  // constants for readinessStatus
  DRAFT: false,
  TO_PUBLISH: true,
  // constants for inner page
  INNER: "inner",
  VIEW:"view",
  COMMENT: 0,
  STORY: 1
});