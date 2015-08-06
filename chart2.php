<?php 
	define("ADAY",(60*60*24)); 

	$nowArray = getdate();  
	$month = $nowArray["mon"];
	$year= $nowArray["year"];
	$days= $nowArray["mday"];
?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="//code.highcharts.com/highcharts.js"></script>
<script>
function InitHighChart()
{
	//$("#chart").html("Wait, Loading graph..."); //로딩 시 보여지는 화면
	
	var options = {
		chart: {
			renderTo: 'chart',
		},
		credits: {
			enabled: false
		},
		title: {
			text: 'Distance', //맨 위에 제목
			x: -20
		},
		xAxis: {
			categories: [{}]
		},
		tooltip: { //마우스 올렸을 때 나오는 표시창
            formatter: function() {
                var s = '<b>'+ this.x +'</b>'; //표시창 맨 위 제목
                
                $.each(this.points, function(i, point) {
                    s += '<br/>'+point.series.name+': '+point.y;
                });
                
                return s;
            },
            shared: true
        },
		series: [{}] //여러개 도표 할시 series: [{},{},{}]
	};
	
	$.ajax({
		url: "charts1.php",
		data:$('form').serialize(), //지금 이건 그냥 임시로 문자열 값 보낸거임, 의미없음
		type:'post',
		dataType: "json",
		success: function(data){
			//document.write(data.impression);
			options.xAxis.categories = data.categories;
			options.series[0].name = 'Distance';
			options.series[0].data = data.impression;
			
			//options.series[1].name = 'Click';
			//options.series[1].data = data.clicks;
			var chart = new Highcharts.Chart(options);			
		}
	});
}

</script>

<!-- 달력 부분 -->
<form method="post">
<div id="chart"></div>

<select name="year"> 
<?php 
for($x=1980;$x<=2020;$x++){ 
   echo "<option "; 
   if($x==$year){ 
      echo "selected"; 
   } 
   echo ">$x</option>"; 
} 
?> 
</select> 
<select name="month"> 
<?php 
for($x=1;$x<=12;$x++){ 
   echo "<option "; 
   if($x==$month){ 
      echo "selected"; 
   } 
   echo ">$x</option>"; 
} 
?> 
</select> 
<select name="days"> 
<?php 
for($x=1;$x<=31;$x++){ 
   echo "<option "; 
   if($x==$days){ 
      echo "selected"; 
   } 
   echo ">$x</option>"; 
} 
?> 
</select> 
</form>
<input type="button" value="Enter" onclick="InitHighChart();" />