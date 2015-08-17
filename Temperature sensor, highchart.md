# 온,습도 센서를 이용한 highchart
- MySQL 버전 - 5.5.44
- Apache 버전 - 2.2.22
- php 버전 - 5.4.41

**라즈베리파이 APM 설치는 [여기](https://github.com/inyong-e/menual/blob/master/%EB%9D%BC%EC%A6%88%EB%B2%A0%EB%A6%AC%ED%8C%8C%EC%9D%B42%20APM%EC%84%A4%EC%B9%98.md)를 참조**

---

#### 센서 이용 - BerePi
BerePi 센서 모듈은 온습도, CO2, CO, DUST 등의 센서 등으로 이용 가능

![BerePi 사진1](http://postfiles3.naver.net/20150817_130/jiy5520_1439797601447OpzL2_PNG/rasberiPi1.png?type=w3)
![BerePi 사진2](http://postfiles3.naver.net/20150817_258/jiy5520_1439797602115txj5n_JPEG/rasberiPi2.jpg?type=w3)

**설치**-
BerePi 사용을 위한 OS img파일을 아래 주소에서 다운.
<https://github.com/jeonghoonkang/BerePi/blob/master/Install_Raspi_OS.md>

img파일을 Win32DiskImager를 이용하여 SD카드에 구우면 됨.

- **BerePi 소스 디렉토리** - /home/pi/devel/BerePi/apps

- **온,습도 센서 실행 소스** - /home/pi/devel/BerePi/apps/sht20/sht20.py

- **온,습도 센서 실행** - python sht20.py

#### 실행 결과
![sth20.py 실행 결과](http://blogfiles.naver.net/20150817_272/jiy5520_1439799896410Eusem_PNG/result1.png)
---
온,습도 값을 DB에 저장할 수 있도록 테이블 생성 후 sht20.py소스를 수정한다.

#### 테이블 구성
![Mysql테이블 구성](http://postfiles5.naver.net/20150817_212/jiy5520_1439797602682UgFsh_JPEG/result2.jpg?type=w3)

#### 온,습도python 파일과 DB연동
```sh
db = mdb.connect('DB서버ip','DB아아디‘,’DBpasswd','DBname')
cur = db.cursor() 
```
데이터값 DB에 insert(소스에서 온도 값은 'value[0]', 습도 값은 'value[1]')

```sh
def dbinsert(temp,humi):
    global timeset  //전역변수 시간
    sql = "insert into sinbinet values(0,now(),%s,%s,%s)"
    cur.execute(sql,(timeset,temp,humi))
    db.commit()
    timeset = timeset +１
    if timeset == 24:
        timset = 0
```

sth20.py를 실행시키면 mysql DB에 값이 들어가지는 것을 확인할 수 있음

![DB값 확인](http://postfiles16.naver.net/20150817_111/jiy5520_1439797602891mDlSA_JPEG/result3.jpg?type=w3)

---
#### DB 값을 이용한 highchart 출력

**db_info.php**
```sh
<?php
$mysql_host ='localhost';
$mysql_user = 'root';
$mysql_password='1q2w3e4r!';
$mysql_db = 'test';

$conn = mysql_connect($mysql_host, $mysql_user, $mysql_password);
$dbconn = mysql_select_db($mysql_db, $conn);
?>
```
**chart1.php** - JSON 형식 DB 데이터 값
```sh
<?php
include "db_info.php";

$year = $_POST['year'];
$month = $_POST['month'];
$days = $_POST['days'];
$temp = $_POST['temp'];


$query = "SELECT * FROM sinbinet where date = '".$year."-".$month."-".$days."'";
$result_count = mysql_query($query,$conn);

$str_time=array();   //도표 추가 시 배열벼수 하나 더 생성
$str_humidity=array();
$str_temperature=array();
$i=0;

while ($row = mysql_fetch_array($result_count, MYSQL_ASSOC)) {

	$str_time[$i]=intval($row['time']);//intval 사용해야 int형식으로 바뀜
	$str_temperature[$i]=floatval($row['temperature']);
	$str_humidity[$i]=floatval($row['humidity']); //그냥 받으면 string 형식으로 받아짐
	$i++;									
	
}
$graph_data = array('categories'=>$str_time, 'temperature'=>$str_temperature, 'humidity'=>$str_humidity); //도표 추가시 여기도 새 도표 추가

echo json_encode($graph_data);
exit;
?>
```
**chart2.php**
```sh
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
			text: 'Temperature/Humidity', //맨 위에 제목
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
		series: [{},{}] //여러개 도표 할시 series: [{},{},{}]
	};
	
	$.ajax({
		url: "charts1.php",
		data:$('form').serialize(), 
		type:'post',
		dataType: "json",
		success: function(data){

			options.xAxis.categories = data.categories;
			options.series[0].name = 'Temperature';  //도표의 이름 지정
			options.series[0].data = data.temperature;  //도표의 실제 데이터 지정
			
			options.series[1].name = 'Humidity';  
			options.series[1].data = data.humidity;
			
			var chart = new Highcharts.Chart(options);	 //새로 시작		
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
```
**결과 확인 창**

![DB값 확인](http://postfiles16.naver.net/20150817_239/jiy5520_1439797603133ksznJ_JPEG/tempResult.jpg?type=w3)
