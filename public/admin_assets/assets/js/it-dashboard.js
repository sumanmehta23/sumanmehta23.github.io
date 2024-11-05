var options = {
    series: [{
        name: 'Job Applied',
        type: 'line',
        data: [20, 20, 36, 18, 15, 20, 25, 20, 40]
    }],
    chart: {
        height: 210,
        type: 'line',
        stacked: false,
        toolbar: {
            show: false
        },
        dropShadow: {
            enabled: true,
            enabledOnSeries: undefined,
            top: 7,
            left: 1,
            blur: 3,
            color: '#000',
            opacity: 0.2
        },
    },
    colors: ["#d43f8d", "#d43f8d"],
    grid: {
        show: false,
    },
    stroke: {
        width: [2],
        curve: 'smooth',
        dashArray: [0, 4]
    },
    labels: ['2011', '2012', '2013', '2014', '2015', '2016', '2017', '2018', '2019'],
    markers: {
        size: 4,
        hover: {
            size: 5
        }
    },
    fill: {
        opacity: [0.85, 0.25, 1],
        gradient: {
            inverseColors: false,
            shadeIntensity: 1,
            shade: 'light',
            type: "vertical",
            opacityFrom: 0.85,
            opacityTo: 0.55,
            stops: [0, 100, 100, 100]
        }
    },
    legend: {
        show: true,
        position: 'top',
        fontFamily: 'Inter, sans-serif',
        markers: {
            width: 10,
            height: 10,
        }   
    },
    xaxis: {
        type: 'week',
        axisBorder: {
            show: true,
            color: 'rgba(119, 119, 142, 0.05)',
            offsetX: 0,
            offsetY: 0,
        },
        axisTicks: {
            show: true,
            borderType: 'solid',
            color: 'rgba(119, 119, 142, 0.05)',
            width: 6,
            offsetX: 0,
            offsetY: 0
        },
        labels: {
            rotate: -90
        }
    },
    yaxis: {
        title: {
            style: {
                color: '#adb5be',
                fontSize: '14px',
                fontFamily: 'Inter, sans-serif',
                fontWeight: 600
            },
        },
        labels: { 
            min: 0,
            max: 40,
            stepSize: 10,
            formatter: function (y) {
                return y.toFixed(0) + "";
            }
        },
       
    },
    tooltip: {
        shared: true,
        intersect: false,
        y: {
            formatter: function (y) {
                if (typeof y !== "undefined") {
                    return y.toFixed(0) + " points";
                }
                return y;

            }
        }
    }
};
document.querySelector("#yearlybudget").innerHTML = ""
var chart = new ApexCharts(document.querySelector("#yearlybudget"), options);
chart.render();

/*---- Apex Chart -----*/
var randomizeArray = function (arg) {
    var array = arg.slice();
    var currentIndex = array.length,
        temporaryValue, randomIndex;
    while (0 !== currentIndex) {
        randomIndex = Math.floor(Math.random() * currentIndex);
        currentIndex -= 1;

        temporaryValue = array[currentIndex];
        array[currentIndex] = array[randomIndex];
        array[randomIndex] = temporaryValue;
    }
    return array;
}
var sparklineData = [47, 45, 54, 38, 56, 24, 65, 31, 37, 39, 62, 51, 35, 41, 35, 27, 93, 53, 61, 27, 54, 43, 19, 46,47, 45, 54, 38, 56, 24, 65, 31, 37, 39, 62, 51, 35, 41];

 //Spark1
 var spark1 = {
    chart: {
        type: 'area',
        height: 90,
        width: '100%',
        sparkline: {
            enabled: true
        },
    },
    stroke: {
        curve: 'smooth',
        width: 2
    },
    fill: {
        opacity: 0.3,
        gradient: {
            enabled: false
        }
    },
    series: [{
        name: 'Production Volume',
        data: randomizeArray(sparklineData)
    }],
    yaxis: {
        min: 0
    },
    colors: ['#0774f8'],

}
var spark1 = new ApexCharts(document.querySelector("#spark1"), spark1);
spark1.render();

 /* Project Budget */
 var options = {
    series: [
        {
            name: 'Sales',
            type: 'bar',
            data: [10, 15, 9, 18, 10]
        },
        {
            name: 'Growth',
            type: 'bar',
            data: [10, 14, 10, 15, 9]
        },
    ],
    chart: {
        toolbar: {
            show: false
        },
        type: 'line',
        height: 290,
    },
    grid: {
        borderColor: '#f1f1f1',
        strokeDashArray: 3
    },
    labels: ['2014', '2015', '2016', '2017', '2018'],
    dataLabels: {
        enabled: false
    },
    stroke: {
        width: [1, 1.1],
        curve: ['straight', 'smooth'],
    },
    legend: {
        show: false,
        position: 'bottom',
    },
    xaxis: {
        axisBorder: {
            color: '#e9e9e9',
        },
    },
    yaxis: {
      min: 0,
      max: 25,
      stepSize: 5,
    },
    plotOptions: {
        bar: {
            columnWidth: "60%",
            borderRadius: 0
        }
    },
    colors: ['#d43f8d', "#0774f8"],
};
document.querySelector("#projectbudget").innerHTML = ""
var chart2 = new ApexCharts(document.querySelector("#projectbudget"), options);
chart2.render();
function projectbudget() {
    chart2.updateOptions({
        colors: ["rgba(" + myVarVal + ", 1)", '#23b7e5'],
    })
}
/* Project Budget */