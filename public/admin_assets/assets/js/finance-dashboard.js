 /* Revenue vs expenses chart */
 var options = {
    series: [
        {
            name: 'Sales',
            type: 'bar',
            data: [30.0, 42.3, 60.2, 70.3, 60.8, 19.8, 27.8, 85.63, 52.63, 14.25, 63.25, 12.32]
        },
        {
            name: 'Growth',
            type: 'bar',
            data: [16.6, 40.9, 50.0, 16.4, 28.7, 80.7, 178.6, 188.2, 48.7, 18.8, 6.0, 2.3]
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
    labels: ['Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
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
      max: 210,
      stepSize: 30,
    },
    plotOptions: {
        bar: {
            columnWidth: "60%",
            borderRadius: 0
        }
    },
    colors: ['#0774f8', '#d43f8d'],
};
document.querySelector("#revenueexpenses").innerHTML = ""
var chart12 = new ApexCharts(document.querySelector("#revenueexpenses"), options);
chart12.render();
function revenueexpenses() {
    chart12.updateOptions({
        colors: [ "#d43f8d","rgba(" + myVarVal + ", 1)" ],
    })
}
/* Revenue vs expenses chart */

// Activity chart //
var options = {
    series: [{
            name: 'Job Applied',
            type: 'line',
            data: [2666, 2778, 4912, 3767, 6810, 15670, 4820, 15073, 10687, 8432]
        },
        {
            name: 'dgjfdkn',
            type: 'line',
            data: [2666, 2294, 1969, 13597, 1914, 4293, 3795, 5967, 4460, 5713]
        }
    ],
    chart: {
        height: 310,
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
    colors: ["#0774f8", "#d43f8d"],
    grid: {
        borderColor: '#e9edf4',
        padding: {
            top: 10,
            right: 0,
            bottom: 0,
            left: 0
        },
    },
    stroke: {
        width: [3,3],
        curve: 'smooth',
    },
    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul","Aug", "Sep", "Oct", "Nov", "Dec"],
    markers: {
        size: 4,
        hover: {
            size: 5
        }
    },
    fill: {
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
        show: false,
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
            max: 140,
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
document.querySelector("#activitychart").innerHTML = ""
var chart2 = new ApexCharts(document.querySelector("#activitychart"), options);
chart2.render();
function activitychart() {
    chart2.updateOptions({
    colors: ["rgba(" + myVarVal + ", 1)", "#0774f8", ],
  })
}
// Activity chart //

// Income Budget //

var options = {
    chart: {
        height: 170,
        type: "radialBar",
    },
  
    series: [75],
    colors: ["#33ce7b"],
    plotOptions: {
        radialBar: {
            hollow: {
                margin: 0,
                size: "55%",
                background: "#fff"
            },
            dataLabels: {
                name: {
                    offsetY: -10,
                    color: "#4b9bfa",
                    fontSize: ".625rem",
                    show: false
                },
                value: {
                    offsetY: 5,
                    color: "#4b9bfa",
                    fontSize: ".875rem",
                    show: true,
                    fontWeight: 600
                }
            }
        }
    },
    stroke: {
        lineCap: "round"
    },
    labels: ["Status"]
  };
var chart = new ApexCharts(document.querySelector("#incomebudget"), options);
chart.render();
// Income Budget //

// Expense Budget //

var options = {
    chart: {
        height: 170,
        type: "radialBar",
    },
  
    series: [65],
    colors: ["#1cc5ef"],
    plotOptions: {
        radialBar: {
            hollow: {
                margin: 0,
                size: "55%",
                background: "#fff"
            },
            dataLabels: {
                name: {
                    offsetY: -10,
                    color: "#4b9bfa",
                    fontSize: ".625rem",
                    show: false
                },
                value: {
                    offsetY: 5,
                    color: "#4b9bfa",
                    fontSize: ".875rem",
                    show: true,
                    fontWeight: 600
                }
            }
        }
    },
    stroke: {
        lineCap: "round"
    },
    labels: ["Status"]
  };
var chart = new ApexCharts(document.querySelector("#expensebudget"), options);
chart.render();
// Expense Budget //

var options = {
    series: [{
        name: 'Job Applied',
        type: 'line',
        data: [30, 70, 30, 100, 50, 130, 100, 140]
    }],
    chart: {
        height: 120,
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
    colors: ["#0774f8",],
    grid: {
        show: false,
    },
    stroke: {
        width: [4],
        curve: 'smooth',
        dashArray: [0, 4]
    },
    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
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
            show: false,
            rotate: -90,
            axisBorder: {
                show: false,
            }
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
        axisBorder: {
            show: false,
        },
        labels: { 
            show: false,
            min: 0,
            max: 140,
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
document.querySelector("#areaChart1").innerHTML = ""
var chart3 = new ApexCharts(document.querySelector("#areaChart1"), options);
chart3.render();
function areaChart1() {
    chart3.updateOptions({
    colors: ["rgba(" + myVarVal + ", 1)"],
  })
}

var options = {
    series: [{
        name: 'Job Applied',
        type: 'line',
        data: [24, 18, 28, 21, 32, 28,30]
    }],
    chart: {
        height: 120,
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
    colors: ["#d43f8d",],
    grid: {
        show: false,
    },
    stroke: {
        width: [4],
        curve: 'smooth',
        dashArray: [0, 4]
    },
    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
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
            show: false,
            rotate: -90,
            axisBorder: {
                show: false,
            }
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
        axisBorder: {
            show: false,
        },
        labels: { 
            show: false,
            min: 0,
            max: 140,
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
document.querySelector("#areaChart2").innerHTML = ""
var chart = new ApexCharts(document.querySelector("#areaChart2"), options);
chart.render();

var options = {
    series: [{
        name: 'Job Applied',
        type: 'line',
        data: [30, 70, 30, 100, 50, 130, 100, 140]
    }],
    chart: {
        height: 120,
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
    colors: ["#09ad95",],
    grid: {
        show: false,
    },
    stroke: {
        width: [4],
        curve: 'smooth',
        dashArray: [0, 4]
    },
    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
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
            show: false,
            rotate: -90,
            axisBorder: {
                show: false,
            }
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
        axisBorder: {
            show: false,
        },
        labels: { 
            show: false,
            min: 0,
            max: 140,
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
document.querySelector("#areaChart3").innerHTML = ""
var chart = new ApexCharts(document.querySelector("#areaChart3"), options);
chart.render();

var options = {
    series: [{
        name: 'Job Applied',
        type: 'line',
        data: [24, 18, 28, 21, 32, 28,30]
    }],
    chart: {
        height: 120,
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
    colors: ["#f7b731",],
    grid: {
        show: false,
    },
    stroke: {
        width: [4],
        curve: 'smooth',
        dashArray: [0, 4]
    },
    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
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
            show: false,
            rotate: -90,
            axisBorder: {
                show: false,
            }
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
        axisBorder: {
            show: false,
        },
        labels: { 
            show: false,
            min: 0,
            max: 140,
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
document.querySelector("#areaChart4").innerHTML = ""
var chart = new ApexCharts(document.querySelector("#areaChart4"), options);
chart.render();

    
    var options13 = {
        series: [44, 55, 41],
        labels: ['Technology', 'Furniture', 'Office Suppliers'],
        chart: {
            type: 'donut',
            height: 395,
        },
        legend: {
            position: 'bottom'
        },
        stroke: {
            show: true,
            curve: 'smooth',
            lineCap: 'butt',
            colors: 'transparent',
            width: 2,
            dashArray: 0,      
        },
        
        colors: ['#0774f8', '#d43f8d', '#09ad95'],
        dropShadow: {
            enabled: false,
        },
        dataLabels: {
            size: 0,
            dropShadow: {
                enabled: false,
            }
        },
        plotOptions: {
            pie: {
                size: 50
            }
        }
    };
    var chart13 = new ApexCharts(document.querySelector("#saleschart"), options13);
    chart13.render();
    function saleschart() {
        chart13.updateOptions({
        colors: ["rgba(" + myVarVal + ", 1)",'#d43f8d', '#09ad95'],
    })
    }
// Customer Satisfaction //