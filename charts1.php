<?
include "db_info.php";
echo 'a';
$year = $_POST['year'];
$month = $_POST['month'];
$days = $_POST['days'];
$temp = $_POST['temp'];

$query = "SELECT * FROM sinbinet where date = '".$year."-".$month."-".$days."'";
$result_count = mysql_query($query,$conn);


$str_dis=array();
$str_time=array();   //��ǥ �߰� �� �迭���� �ϳ� �� ����
$i=0;

while ($row = mysql_fetch_array($result_count, MYSQL_ASSOC)) {

	$str_time[$i]=intval($row['time']);
	$str_dis[$i]=intval($row['distance']); //�׳� ������ string �������� �޾���
	$i++;									//intval ����ؾ� int�������� �ٲ�
											
}

$graph_data = array('categories'=>$str_time, 'impression'=>$str_dis);

echo json_encode($graph_data);
exit;
?>
