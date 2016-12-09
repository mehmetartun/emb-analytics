
		$(function () {
		    $('#pagecontent').highcharts({
		        chart: {
		            type: 'column'
		        },
		        title: {
		            text: 'Number of Users quoting'
		        },
		        subtitle: {
		            text: 'Only includes those users submitting quotes'
		        },
		        xAxis: {
		            type: 'category',
		            labels: {
		                rotation: -45,
		                style: {
		                    fontSize: '13px',
		                    fontFamily: 'Verdana, sans-serif'
		                }
		            }
		        },
		        yAxis: {
		            min: 0,
		            title: {
		                text: 'Number of Users'
		            }
		        },
		        legend: {
		            enabled: false
		        },
		        tooltip: {
		            pointFormat: '<b>{point.y:.1f} Users</b>'
		        },
		        series: [{
		            name: 'UserData',
		            data: [
		/*                ['Shanghai', 23.7],
		                ['Lagos', 16.1],
		                ['Instanbul', 14.2],
		                ['Karachi', 14.0],
		                ['Mumbai', 12.5],
		                ['Moscow', 12.1],
		                ['SÃ£o Paulo', 11.8],
		                ['Beijing', 11.7],
		                ['Guangzhou', 11.1],
		                ['Delhi', 11.1],
		                ['Shenzhen', 10.5],
		                ['Seoul', 10.4],
		                ['Jakarta', 10.0],
		                ['Kinshasa', 9.3],
		                ['Tianjin', 9.3],
		                ['Tokyo', 9.0],
		                ['Cairo', 8.9],
		                ['Dhaka', 8.9],
		                ['Mexico City', 8.9],
		                ['Lima', 8.9]
		*/
						['2015-11-16', 9],['2015-11-13', 10],['2015-11-12', 12],['2015-11-11', 9],['2015-11-10', 11],['2015-11-09', 9],['2015-11-06', 10],['2015-11-05', 12],['2015-11-04', 11],['2015-11-03', 9],['2015-11-02', 8],['2015-10-30', 8],['2015-10-29', 3],['2015-10-28', 6],['2015-10-27', 10],['2015-10-26', 8],['2015-10-23', 7],['2015-10-22', 10],['2015-10-21', 12],['2015-10-20', 13]		            ],
		            dataLabels: {
		                enabled: true,
		                rotation: -90,
		                color: '#FFFFFF',
		                align: 'right',
		                format: '{point.y:,.0f}', // one decimal
		                y: 10, // 10 pixels down from the top
		                style: {
		                    fontSize: '13px',
		                    fontFamily: 'Verdana, sans-serif'
		                }
		            }
		        }]
		    });
		});	

		