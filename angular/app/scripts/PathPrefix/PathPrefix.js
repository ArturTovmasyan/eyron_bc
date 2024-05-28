'use strict';

angular.module("PathPrefix", [])
    .constant('envPrefix', (window.location.pathname.indexOf('app_dev.php') === -1) ? "/" : "/app_dev.php/")
    .constant('facebookId', (window.location.href.indexOf('bucketlist.loc') === -1) ? "486680294849466" : "999576146739877");