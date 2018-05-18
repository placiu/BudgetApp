$(function() {

    $('#link-budget').addClass('active');

    var chart = new Chartist.Pie('.ct-chart', {
        series: series,
        labels: labels
    }, {
        chartPadding: 30,
        donut: true,
        showLabel: true,
        labelOffset: 50,
        labelDirection: 'explode'
    });

    chart.on('draw', function (data) {
        if (data.type === 'slice') {

            var pathLength = data.element._node.getTotalLength();

            data.element.attr({
                'stroke-dasharray': pathLength + 'px ' + pathLength + 'px'
            });

            var animationDefinition = {
                'stroke-dashoffset': {
                    id: 'anim' + data.index,
                    dur: 500,
                    from: -pathLength + 'px',
                    to: '0px',
                    easing: Chartist.Svg.Easing.easeOutQuint,
                    fill: 'freeze'
                }
            };

            if (data.index !== 0) {
                animationDefinition['stroke-dashoffset'].begin = 'anim' + (data.index - 1) + '.end';
            }

            data.element.attr({
                'stroke-dashoffset': -pathLength + 'px'
            });

            data.element.animate(animationDefinition, false);
        }
    });

});