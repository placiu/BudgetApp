$(function(){
    'use strict';

    $('#link-homepage').addClass('active');

    var daysInMonth = ['31', '29', '31', '30', '31', '30', '31', '31', '30', '31', '30', '31'];
    var labels = [];
    for(var x = 0; x < daysInMonth[workingMonth - 1]; x++) {
        labels.push(x + 1);
    }

    console.log(labels);

    var ctx1 = document.getElementById('chartBar1').getContext('2d');
    var myChart1 = new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Day\'s outcome',
                data: data,
                backgroundColor: '#27AAC8'
            }]
        },
        options: {
            maintainAspectRatio: false,
            legend: {
                display: false,
                labels: {
                    display: false
                }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero:true,
                        fontSize: 10,
                        max: 500
                    }
                }],
                xAxes: [{
                    ticks: {
                        beginAtZero:true,
                        fontSize: 11
                    }
                }]
            }
        }
    });

});