
    // current deals //
    var options = {
        series: [{
            data: [16, 14, 12, 14, 16, 15, 12, 14],
            name: 'Revenue',
        }],
        chart: {
            type: 'bar',
            height: 235,
            toolbar: {
                show: false
            },
            dropShadow: {
                enabled: true,
                enabledOnSeries: undefined,
                top: 6,
                left: 6,
                blur: 3,
                color: '#000',
                opacity: 0.05
            },
        },
        plotOptions: {
            bar: {
                columnWidth: '35%',
                borderRadius: 0,
                horizontal: false,
                colors: {
                    ranges: [{
                        from: 41,
                        to: Infinity,
                        color: "#09ad95"
                    },
                    {
                        from: 0,
                        to: 40,
                        color: "#09ad95"
                    }]
                },
            }
        },
        dataLabels: {
            enabled: false
        },
        grid: {
            show: false,
            borderColor: 'transparent',
            padding: {
                top: 0,
                right: 0,
                bottom: 0,
                left: 0
            },
            yaxis: {
                lines: {
                    show: false
                }
            },
        },
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'July', 'Aug'],
            colors: '#fff',
            axisBorder: {
                show: true
            },
            axisTicks: {
                show: false
            },
            labels: {
                rotate: -90,
                style: {
                    fontFamily: 'Inter, sans-serif',
                },
            }
        },
        yaxis: {
            colors: '#fff',
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            },
            labels: {
                show: false
            }
        }
    };
    document.querySelector("#deals").innerHTML = ""
    var chart = new ApexCharts(document.querySelector("#deals"), options);
    chart.render();
    // current deals //

    // Total Transactions //
    var options = {
        series: [{
            name: 'Job Applied',
            type: 'line',
            data: [0, 50, 0, 100, 50, 130, 100, 140, 50, 0, 100, 50,]
        }],
        chart: {
            height: 300,
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
        colors: ["#0774f8", "#0774f8"],
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
            width: [4],
            curve: 'smooth',
            dashArray: [0, 4]
        },
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul","Aug", "Sep", "Oct", "Nov", "Dec"],
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
    document.querySelector("#total-transcations").innerHTML = ""
    var chart = new ApexCharts(document.querySelector("#total-transcations"), options);
    chart.render();
    // Total Transactions //

    // Customer Satisfaction //
    
        var options13 = {
            series: [44, 55, 41],
            labels: ['Online', 'Ofline', 'Referals'],
            chart: {
                type: 'donut',
                height: 355,
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
        var chart13 = new ApexCharts(document.querySelector("#customer-chart"), options13);
        chart13.render();
        function customerchart() {
            chart13.updateOptions({
            colors: ["rgba(" + myVarVal + ", 1)", '#d43f8d', '#09ad95'],
          })
        }
    // Customer Satisfaction //
  
    // Sales Statistics //
    var options = {
        series: [{
            name: 'Total Profit',
            data: [15, 18, 12, 14, 10, 15]
        }, {
            name: 'Total Sales',
            data: [10, 14, 10, 15, 9, 14]
        }],
        chart: {
            type: 'bar',
            height: 300
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '50%',
                endingShape: 'rounded'
            },
        },
        grid: {
            borderColor: '#f2f5f7',
        },
        dataLabels: {
            enabled: false
        },
        colors: ["#d43f8d", "#0774f8"],
        stroke: {
            show: true,
            width: 10,
            colors: ['transparent']
        },
        xaxis: {
            categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
            labels: {
                show: true,
                style: {
                    colors: "#8c9097",
                    fontSize: '11px',
                    fontWeight: 600,
                    cssClass: 'apexcharts-xaxis-label',
                },
            }
        },
        yaxis: {
            labels: {
                show: true,
                style: {
                    colors: "#8c9097",
                    fontSize: '11px',
                    fontWeight: 600,
                    cssClass: 'apexcharts-xaxis-label',
                },
            }
        },
        legend: {
            show: false,
        },
        fill: {
            opacity: 1
        },
    };
    var chart12 = new ApexCharts(document.querySelector("#revenue"), options);
    chart12.render();
    function revenue() {
        chart12.updateOptions({
        colors: [ "#d43f8d","rgba(" + myVarVal + ", 1)" ],
      })
    }
    // Sales Statistics //

    // Graph Profit Margin //
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
    document.querySelector("#areaChart1").innerHTML = ""
    var chart = new ApexCharts(document.querySelector("#areaChart1"), options);
    chart.render();
    // Graph Profit Margin //
    
    // Total revenue //
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
    document.querySelector("#areaChart2").innerHTML = ""
    var chart = new ApexCharts(document.querySelector("#areaChart2"), options);
    chart.render();
    // Total revenue //

    