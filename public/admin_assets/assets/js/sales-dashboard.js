

/* Earnings Report */
var options = {
  series: [
      {
          name: 'Sales',
          type: 'bar',
          data: [10, 15, 9, 18, 10]
      },
      {
          name: 'Profit',
          type: 'line',
          data: [8, 5, 25, 10, 10]
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
      height: 373,
      columnWidth: '95%',
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
      width: [10, 2, 10],
      curve: ['straight', 'smooth'],
      color: ['transparent', 'transparent', ],
  },
  legend: {
      show: true,
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
  colors: ["#5e2dd8", '#299c94', '#d43f8d'],
};
document.querySelector("#earnings").innerHTML = ""
var chart4 = new ApexCharts(document.querySelector("#earnings"), options);
chart4.render();
function earnings() {
  chart4.updateOptions({
      colors: ["rgba(" + myVarVal + ", 1)", '#299c94', '#d43f8d'],
  })
}
/* Earnings Report */

(function () {
    "use strict";
  
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
var sparklineData = [47, 45, 54, 38, 56, 24, 65, 31, 37, 39, 62, 51, 35, 41, 35, 27, 93, 53, 61, 27, 54, 43, 19, 46];

  
//Spark1
var spark1 = {
  chart: {
    type: 'area',
    height: 50,
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

//Spark2
var spark2 = {
  chart: {
    type: 'area',
    height: 50,
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
    name: 'Sales Revenue',
    data: randomizeArray(sparklineData)
  }],
  yaxis: {
    min: 0
  },
  colors: ['#d43f8d'],

}
var spark2 = new ApexCharts(document.querySelector("#spark2"), spark2);
spark2.render();

//Spark3
var spark3 = {
  chart: {
    type: 'area',
    height: 50,
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
    name: 'Total Orders',
    data: randomizeArray(sparklineData)
  }],
  yaxis: {
    min: 0
  },
  colors: ['#09ad95'],

}
var spark3 = new ApexCharts(document.querySelector("#spark3"), spark3);
spark3.render();

//Spark4
var spark4 = {
  chart: {
    type: 'area',
    height: 50,
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
    name: 'Total profit',
    data: randomizeArray(sparklineData)
  }],
  yaxis: {
    min: 0
  },
  colors: ['#f82649'],

}
var spark4 = new ApexCharts(document.querySelector("#spark4"), spark4);
spark4.render();

//Spark5
var spark5 = {
  chart: {
    type: 'area',
    height: 50,
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
    name: 'Total profit',
    data: randomizeArray(sparklineData)
  }],
  yaxis: {
    min: 0
  },
  colors: ['#f82649'],

}
var spark5 = new ApexCharts(document.querySelector("#spark5"), spark5);
spark5.render();

// Total expenses chart //
var spark2 = {
    chart: {
      type: "bar",
      height: 88,
      width: 100,
      columnWidth: '5%',
      sparkline: {
        enabled: true,
      },
      dropShadow: {
        enabled: false,
        enabledOnSeries: undefined,
        top: 0,
        left: 0,
        blur: 0,
        color: "#000",
        opacity: 0,
      },
    },
    grid: {
      show: false,
      xaxis: {
        lines: {
          show: false,
        },
      },
      yaxis: {
        lines: {
          show: false,
        },
      },
    },
    stroke: {
      show: true,
      curve: "smooth",
      colors: undefined,
      width: 0.5,
      dashArray: 0,
    },
    fill: {
      gradient: {
        enabled: false,
      },
    },
    series: [
      {
        name: "Value",
        data: [2, 4, 3, 4, 5, 4, 5, 0],
      },
    ],
    yaxis: {
      min: 0,
      show: false,
    },
    xaxis: {
      show: false,
      axisTicks: {
        show: false,
      },
      axisBorder: {
        show: false,
      },
    },
    yaxis: {
      axisBorder: {
        show: false,
      },
    },
    colors: ["#d43f8d"],
  };
  document.getElementById("analytics-visitors").innerHTML = "";
  var spark2 = new ApexCharts(
    document.querySelector("#analytics-visitors"),
    spark2
  );
  spark2.render();
  // Total expenses chart //
      
//   Members Online chart //
  var options = {
    series: [{
    name: 'series1',
    data: [24, 30, 20, 28, 39, 22, 40],
    
  }],
    chart: {
    height: 150,
    type: 'area',
    toolbar: {
        show: false,
    },
  },
  fill: {
    type: 'solid',
    colors: ['#5ea6fb'] // Background color for the area under the line
},
  grid: {
    show: false,
  },
  dataLabels: {
    enabled: false
  },
  stroke: {
    curve: 'smooth',
    width: '3'
  },
  xaxis: {
    type: 'month',
    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
    axisBorder: {
        show: false
    },
    labels: {
        show: false
    },
  },
  yaxis: {
    axisBorder: {
        show: false
    },
    min: 0,
    show: false
},
  tooltip: {
    x: {
      format: 'dd/MM/yy HH:mm'
    },
  },
  };
  var chart = new ApexCharts(document.querySelector("#widgetChart1"), options);
  chart.render();

//   Current Serverload Chart //
var options = {
    series: [{
    name: 'series1',
    data: [24, 30, 20, 28, 39, 22, 40],
    
  }],
    chart: {
    height: 150,
    type: 'area',
    toolbar: {
        show: false,
    },
  },
  fill: {
    type: 'solid',
    colors: ['#e183b6'] // Background color for the area under the line
},
  grid: {
    show: false,
  },
  dataLabels: {
    enabled: false
  },
  stroke: {
    curve: 'smooth',
    width: '3',
    colors: ['#d43f8d']
  },
  xaxis: {
    type: 'month',
    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
    axisBorder: {
        show: false
    },
    labels: {
        show: false
    },
  },
  yaxis: {
    axisBorder: {
        show: false
    },
    min: 0,
    show: false
},
  tooltip: {
    x: {
      format: 'dd/MM/yy HH:mm'
    },
  },
};
var chart1 = new ApexCharts(document.querySelector("#widgetChart2"), options);
chart1.render();

//   To Days Revenue Chart //
var options = {
    series: [{
    name: 'series1',
    data: [24, 30, 20, 28, 39, 22, 40],
    
  }],
    chart: {
    height: 150,
    type: 'area',
    toolbar: {
        show: false,
    },
  },
  fill: {
    type: 'solid',
    colors: ['#66d6c6'] // Background color for the area under the line
},
  grid: {
    show: false,
  },
  dataLabels: {
    enabled: false
  },
  stroke: {
    curve: 'smooth',
    width: '3',
    colors: ['#0eaf98']
  },
  xaxis: {
    type: 'month',
    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
    axisBorder: {
        show: false
    },
    labels: {
        show: false
    },
  },
  yaxis: {
    axisBorder: {
        show: false
    },
    min: 0,
    show: false
},
  tooltip: {
    x: {
      format: 'dd/MM/yy HH:mm'
    },
  },
};
var chart1 = new ApexCharts(document.querySelector("#widgetChart3"), options);
chart1.render();

  })();