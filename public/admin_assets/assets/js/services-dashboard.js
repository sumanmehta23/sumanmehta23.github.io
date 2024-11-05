var options = {
    series: [{
        name: 'Total Revenue',
        data: [15, 18, 12, 14, 10, 15, 7, 14, 18, 12, 14, 10]
    }, {
        name: 'Total Cost',
        data: [10, 14, 10, 15, 9, 14, 15, 19, 14, 10, 15, 9]
    }],
    chart: {
        type: 'bar',
        height: 225
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
        width: 2,
        colors: ['transparent']
    },
    xaxis: {
        categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "July", "Aug", "Sep", "Oct", "Nov", "Dec"],
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
var chart4 = new ApexCharts(document.querySelector("#revenue"), options);
chart4.render();
function revenue() {
    chart4.updateOptions({
        colors: [ "#d43f8d","rgba(" + myVarVal + ", 1)" ],
  })
}

 /* start donut chart */
 var options13 = {
	series: [44, 55, 41],
	labels: ['Online', 'Ofline', 'Referals'],
	chart: {
		type: 'donut',
		height: 360
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
	
	colors: ["#089e60", "#1396cc", "#ff9933"],
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
var chart13 = new ApexCharts(document.querySelector("#Order-Status"), options13);
chart13.render();
/* end donut chart */