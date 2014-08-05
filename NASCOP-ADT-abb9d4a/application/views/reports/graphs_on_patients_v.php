<script type="text/javascript">
	$(document).ready(function() {
        var results = <?php echo json_encode($graphs); ?>; 
        var chart;
		var all_data = new Array();
		var single_data = new Array();
		var counter = 0;
		if(results.length == 0) {
			$("#chartdiv").append($("<b><h3 style=' margin-top:50px; padding:5px; border:1px solid #000; height:20px; background:#999;' align='center'>No Data available for this period</h3></b>"));
		}
		var new_array = [];
		for(var i = 0; i < results.length; i++) {
			new_array[results[i]['Enrollment']] =results[i]['Enrollment'];
		}
		var monthNames = ["No Month", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
		var chartDta = new Array();
		var chartDta_all = new Array();

		for( k = 1; k <= 12; k++) {
			art_total = 0;
			oi_total = 0;
			pep_total = 0;
			pmtct_total = 0;
			for( j = 1; j <= 4; j++) {
				if(results.length > counter) {

					var current = results[counter];
					var enroll = current['Enrollment'];
					var the_vals = "";
					if(j == 1) {
						if(enroll == "ART") {
							total_enrollment = current['TOTAL'];
							counter++;
						} else {
							total_enrollment = 0;
						}
						art_total = total_enrollment;
					}

					if(j == 2) {
						if(enroll == "OI Only") {
							total_enrollment = current['TOTAL'];
							counter++;
						} else {
							total_enrollment = 0;
						}
						oi_total = total_enrollment;
					}

					if(j == 3) {
						if(enroll == "PEP") {
							total_enrollment = current['TOTAL'];
							counter++;
						} else {
							total_enrollment = 0;
						}
						pep_total = total_enrollment;
					}

					if(j == 4) {
						if(enroll == "PMTCT") {
							total_enrollment = current['TOTAL'];
							counter++;
						} else {
							total_enrollment = 0;
						}
						pmtct_total = total_enrollment;
					}
				} else {

					break;
				}

			}
			chartDta[k] = ( {
				"ART" : art_total,
				"OI" : oi_total,
				"PEP" : pep_total,
				"PMTCT" : pmtct_total,
				"MONTH" : monthNames[k]
			});

			console.log(chartDta[k]);
			chartDta_all = chartDta_all.concat(chartDta[k]);

		}

		// SERIAL CHART
		chart = new AmCharts.AmSerialChart();
		chart.pathToImages = "../../assets/scripts/amcharts/images/";
		chart.dataProvider = chartDta_all;
		chart.categoryField = "MONTH";
		chart.zoomOutButton = {
			backgroundColor : '#000000',
			backgroundAlpha : 0.15
		};

		// AXES
		// category
		categoryAxis = chart.categoryAxis;
		categoryAxis.parseDates = false;
		// as our data is date-based, we set parseDates to true
		categoryAxis.minPeriod = "mm";
		// our data is daily, so we set minPeriod to DD
		categoryAxis.dashLength = 2;
		categoryAxis.gridAlpha = 0.15;
		categoryAxis.axisColor = "#DADADA";

		// first value axis (on the left)
		valueAxis1 = new AmCharts.ValueAxis();
		valueAxis1.axisColor = "#FF6600";
		valueAxis1.axisThickness = 2;
		valueAxis1.gridAlpha = 0;
		chart.addValueAxis(valueAxis1);

		// second value axis (on the right)
		valueAxis2 = new AmCharts.ValueAxis();
		valueAxis2.position = "right";
		// this line makes the axis to appear on the right
		valueAxis2.axisColor = "#FCD202";
		valueAxis2.gridAlpha = 0;
		valueAxis2.axisThickness = 2;
		chart.addValueAxis(valueAxis2);

		// third value axis (on the left, detached)
		valueAxis3 = new AmCharts.ValueAxis();
		valueAxis3.offset = 50;
		// this line makes the axis to appear detached from plot area
		valueAxis3.gridAlpha = 0;
		valueAxis3.axisColor = "#B0DE09";
		valueAxis3.axisThickness = 2;
		chart.addValueAxis(valueAxis3);

		// fourth value axis (on the left, detached)
		valueAxis4 = new AmCharts.ValueAxis();
		valueAxis4.offset = 30;
		valueAxis4.position = "right";
		// this line makes the axis to appear detached from plot area
		valueAxis4.gridAlpha = 0;
		valueAxis4.axisColor = "#0066FF";
		valueAxis4.axisThickness = 2;
		chart.addValueAxis(valueAxis4);

		// GRAPHS
		// first graph
		graph1 = new AmCharts.AmGraph();
		graph1.valueAxis = valueAxis1;
		// we have to indicate which value axis should be used
		graph1.title = "ART";
		graph1.valueField = "ART";
		graph1.bullet = "round";
		graph1.hideBulletsCount = 30;
		chart.addGraph(graph1);

		// second graph
		graph2 = new AmCharts.AmGraph();
		graph2.valueAxis = valueAxis2;
		// we have to indicate which value axis should be used
		graph2.title = "OI";
		graph2.valueField = "OI";
		graph2.bullet = "round";
		graph2.hideBulletsCount = 30;
		chart.addGraph(graph2);

		// third graph
		graph3 = new AmCharts.AmGraph();
		graph3.valueAxis = valueAxis3;
		// we have to indicate which value axis should be used
		graph3.valueField = "PEP";
		graph3.title = "PEP";
		graph3.bullet = "triangleUp";
		graph3.hideBulletsCount = 30;
		chart.addGraph(graph3);

		// fourth graph
		graph4 = new AmCharts.AmGraph();
		graph4.valueAxis = valueAxis4;
		// we have to indicate which value axis should be used
		graph4.valueField = "PMTCT";
		graph4.title = "PMTCT";
		graph4.bullet = "triangleUp";
		graph4.hideBulletsCount = 30;
		chart.addGraph(graph4);

		// CURSOR
		chartCursor = new AmCharts.ChartCursor();
		chartCursor.cursorPosition = "mouse";
		chart.addChartCursor(chartCursor);

		// SCROLLBAR
		chartScrollbar = new AmCharts.ChartScrollbar();
		chart.addChartScrollbar(chartScrollbar);

		// LEGEND
		legend = new AmCharts.AmLegend();
		legend.marginLeft = 110;
		chart.addLegend(legend);

		// WRITE
		chart.write("chartdiv");
	});
</script>
<style>
	#chartdiv {
		width: 80%;
		height: 450px;
		margin: 0 auto;
	}


</style>
<div id="wrapperd">
	<div  class="full-content">
		<?php $this->load->view("reports/reports_top_menus_v")
		?>
		<h4 style="text-align: center">Listing of Patients Enrolled for the Year <span style="width:50px;" id="display_year"><?php echo $year;?></span>
		<br>
		All Donors</h4>
		<div id="chartdiv"></div>
		<!-- Pop up Window -->
		<div class="result"></div>
	</div>
</div>
