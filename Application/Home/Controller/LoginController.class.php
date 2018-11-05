<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
	// 2018-11-3 YHL 登录页面
	public function login() {
		$this->display();
	}
	
	// 2018-11-3 YHL 登录验证数据
	public function checkLogin() {
		$account = I('account');
		$password = I('password');
		$code = I('verify');
		
		$verify = new \Think\Verify();
		$res = $verify->check($code);
		if(!$res) ajaxReturn(L('wqyzmgq'),0);
		
		// 验证用户名密码是否正确
		$user_object = D('Home/User');
		$user_info   = $user_object->login($account, $password);
		if (!$user_info) {
			ajaxReturn($user_object->getError(),0);
		}
		session('account',$account);
		
		$user_info = $user_object->Quicklogin($account);
		if (!$user_info) {
			ajaxReturn($user_object->getError(),0);
		}
		
		// 设置登录状态
		$uid = $user_object->auto_login($user_info);
		// 跳转
		if (0 < $uid && $user_info['userid'] === $uid) {
			session('in_time',time());
			ajaxReturn(L('wqdlcg'),1,U('Index/index'));
		}
	}
	
	// 2018-11-3 YHL 图片验证码生成，用于登录和注册
	public function verify() {
		set_verify();
	}
}