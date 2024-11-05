// Graph Profit Margin //
var options = {
    series: [{
        name: 'Job Applied',
        type: 'line',
        data: [30, 70, 30, 100, 50, 130, 100, 140]
    }],
    chart: {
        height: 150,
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
var chart = new ApexCharts(document.querySelector("#areaChart1"), options);
chart.render();
// Graph Profit Margin //

// Opex Ratio //
var options = {
    series: [{
        name: 'Job Applied',
        type: 'line',
        data: [24, 18, 28, 21, 32, 28,30]
    }],
    chart: {
        height: 150,
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
// Opex Ratio //

// Operating Profit Margin //
var options = {
    series: [{
        name: 'Job Applied',
        type: 'line',
        data: [30, 70, 30, 100, 50, 130, 100, 140]
    }],
    chart: {
        height: 150,
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
// Operating Profit Margin //

// Net Profit Margin //
var options = {
    series: [{
        name: 'Job Applied',
        type: 'line',
        data: [24, 18, 28, 21, 32, 28,30]
    }],
    chart: {
        height: 150,
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
// Net Profit Margin //

// Staff //
var options = {
    series: [85],
    chart: {
        height: 130,
        type: 'radialBar',
    },
    colors: ["#d43f8d"],
    plotOptions: {
        radialBar: {
            hollow: {
                size: '55%',
            }
        },
    },
    stroke: {
        lineCap: 'round'
    },
    labels: [''],
};
var chart = new ApexCharts(document.querySelector("#circle"), options);
chart.render();
// Staff //

// Devices //
var options = {
    series: [64],
    chart: {
        height: 130,
        type: 'radialBar',
    },
    colors: ["#09ad95"],
    plotOptions: {
        radialBar: {
            hollow: {
                size: '55%',
            }
        },
    },
    stroke: {
        lineCap: 'round'
    },
    labels: [''],
};
var chart = new ApexCharts(document.querySelector("#circle-1"), options);
chart.render();
// Devices //

// Licenses //
var options = {
    series: [74],
    chart: {
        height: 130,
        type: 'radialBar',
    },
    colors: ["#f7b731"],
    plotOptions: {
        radialBar: {
            hollow: {
                size: '55%',
            }
        },
    },
    stroke: {
        lineCap: 'round'
    },
    labels: [''],
};
var chart = new ApexCharts(document.querySelector("#circle-2"), options);
chart.render();
// Licenses //

// Running cost //
var options = {
    series: [55],
    chart: {
        height: 130,
        type: 'radialBar',
    },
    colors: ["#f5334f"],
    plotOptions: {
        radialBar: {
            hollow: {
                size: '55%',
            }
        },
    },
    dataLabels: {
        enabled: false,
    },
    stroke: {
        lineCap: 'round'
    },
    labels: [''],
};
var chart = new ApexCharts(document.querySelector("#circle-3"), options);
chart.render();
// Running cost //

// Average sales //
var options = {
    series: [75],
    chart: {
        height: 180,
        type: 'radialBar',
    },
    colors: ["#5e2dd8"],
    plotOptions: {
        radialBar: {
            hollow: {
                size: '60%',
            }
        },
    },
    dataLabels: {
        enabled: false,
    },
    stroke: {
        lineCap: 'round'
    },
    labels: [''],
};
var chart = new ApexCharts(document.querySelector("#chart-circle"), options);
chart.render();
// Average sales //