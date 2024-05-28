
'use strict';

angular.module('statistic',["chart.js", "Interpolation"])
  .config(['ChartJsProvider', function (ChartJsProvider) {
    // Configure all charts
    ChartJsProvider.setOptions({
      chartColors: ['#FF5252', '#00CCFF', '#00FF00', '#FFFF19'],
      responsive: false
    });
    // Configure all line charts
    ChartJsProvider.setOptions('line', {
      showLines: true
    });
  }])
  .controller("LineCtrl", ['$scope', '$http', '$timeout', function ($scope, $http, $timeout) {
    var envPrefix = (window.location.pathname.indexOf('app_dev.php') === -1) ? "/" : "/app_dev.php/",
      path = envPrefix + 'api/v1.0/statistic/{type}/{groupBy}/{start}/{end}';
    $scope.groupType = 'day';
    $scope.type = {name:'device'};
    $scope.dateTo = new Date();
    $scope.noResult = false;

     var startdate = moment().subtract(1, "months");

    // $scope.dateFrom = new Date(moment(new Date()).format('YYYY') + '-01-01');
    $scope.dateFrom = new Date(startdate);

    $scope.series = ['Read','Send'];

    $scope.onClick = function (points, evt) {
      // console.log(points, evt);
    };
    
    $scope.options = {
      legend: { display: true },
      elements : { line : { tension : 0 } }
    };

    $scope.filter = function () {
      $scope.noResult = false;
      var timeTo = $scope.dateTo?moment($scope.dateTo).format('YYYY-MM-DD'):null,
          timeFrom = $scope.dateFrom?moment($scope.dateFrom).format('YYYY-MM-DD'):null;
      $scope.readCount = 0;
      $scope.sendCount = 0;
      if(timeTo && timeFrom && $scope.groupType){
        var url = path.replace('{type}', $scope.type.name).replace('{groupBy}', $scope.groupType).replace('{start}', timeFrom).replace('{end}', timeTo);
        $http.get(url).success(function(res) {
          if(!res.length){
            $scope.noResult = true;
          }

          $scope.labels = [];
          $scope.data = [];
          $scope.series = [];

          switch ($scope.groupType){
            case 'month':
              angular.forEach(res, function (d) {
                $scope.labels.push(moment(d.created).format('MMMM'));
                calculate(d);
              });
              break;
            // case 'week':
              // angular.forEach(res, function (d) {
              //   $scope.labels.push(new Date(d.created).toLocaleString('en-us', {  weekday: 'long' }));
              //   calculate(d);
              // });
              // break;
            case 'day':
              angular.forEach(res, function (d) {
                var created = (d.created.indexOf('T') != -1 )?d.created.slice(0,d.created.indexOf('T')):d.created;
                $scope.labels.push(created);
                calculate(d);
              });
              break;
          }

          $scope.percent = $scope.sendCount?(100 * $scope.readCount/$scope.sendCount):0;

        })
      }
    };

    function calculate(d) {
      angular.forEach(d, function (v, k) {
        if(k != 'created'){
          if(k == 'read'){
            $scope.readCount += +v;
          } else if(k == 'send'){
            $scope.sendCount += +v;
          }

          if($scope.series.indexOf(k) === -1){
            $scope.series.push(k);
            $scope.data.push([]);
            $scope.data[$scope.data.length - 1].push(v);
          } else {
            $scope.data[$scope.series.indexOf(k)].push(v)
          }
        }
      });
    }

    $scope.filter();
  }]);