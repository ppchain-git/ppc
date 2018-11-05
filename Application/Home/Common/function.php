<?php
// 2018-11-3 YHL 检测用户是否登录
function user_login() {
	return D('Home/user')->user_login();
}

// 2018-11-3 YHL 验证码
function set_verify() {
	ob_clean();
	$config = array(
		'codeSet' =>  '0123456789',
		'fontSize'    =>    30,    // 验证码字体大小
		'length'      =>    4,     // 验证码位数
		'fontttf'     =>   '5.ttf',
		'useCurve'    => false,
		'expire'    =>  1800,//过期时间
	);
	$Verify = new \Think\Verify($config);
	$Verify->entry();
}

// 2018-11-3 YHL ajax提示款
function ajaxReturn($message,$status=0, $url ='',$extra='') {
	header('Content-Type:application/json; charset=utf-8');
	$result = array(
		'message' => $message,
		'status'  =>  $status,
		'url' => $url,
		'result'  =>  $extra
	);
	exit(json_encode($result));
}

// 2018-11-3 YHL 打印测试数据
function print_arr($array) {
	echo "<pre>";print_r($array);exit;	
}