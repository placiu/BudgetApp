$(function(){
  'use strict'




  /********************* PIE CHART *********************/

  var sum = function(a, b) { return a + b };

  var data = {
    series: [5, 3, 4]
  };

  var pie1 = new Chartist.Pie('#chartPie', data, {
    labelInterpolationFnc: function(value) {
      return Math.round(value / data.series.reduce(sum) * 100) + '%';
    }
  });


  /**************** PIE CHART 2 *******************/

  var data2 = {
    series: [5, 3, 4, 6, 2]
  };

  var pie2 = new Chartist.Pie('#chartPie2', data2, {
    labelInterpolationFnc: function(value) {
      return Math.round(value / data.series.reduce(sum) * 100) + '%';
    }
  });


  // resize chart when container changest it's width
  new ResizeSensor($('.slim-mainpanel'), function(){
    pie1.update();
    pie2.update();
  });


  /**************** DONUT CHARTS ****************/
  var donut1 = new Chartist.Pie('#chartDonut1', {
    series: [20, 10, 30]
  }, {
    donut: true,
    donutWidth: 60,
    donutSolid: true,
    startAngle: 270,
    showLabel: true
  });

  var donut2 = new Chartist.Pie('#chartDonut2', {
    series: [20, 10, 30, 40, 25]
  }, {
    donut: true,
    donutWidth: 60,
    donutSolid: true,
    startAngle: 270,
    showLabel: true
  });

  // resize chart when container changest it's width
  new ResizeSensor($('.slim-mainpanel'), function(){
    donut1.update();
    donut2.update();
  });


});
