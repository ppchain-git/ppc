<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {
	public function _initialize(){
		$this->is_user();
	}
	
	protected function is_user(){
		$userid=user_login();
		$user=M('user');
		if(!$userid){
			$this->redirect('Login/login');
			exit();
		}
	
		$in_time=session('in_time');
		$time_now=time();
		$between=$time_now-$in_time;
		if($between > 3600 * 24 * 5){
			$this->redirect('Login/logout');
		}
	
		$where['userid']=$userid;
		$u_info=$user->where($where)->field('status,session_id')->find();
		$login_from_admin=session('login_from_admin');
		if($u_info['status'] == 0){
			if(IS_AJAX){
				ajaxReturn('你账号已锁定，请联系管理员',0);
			}else{
				success_alert('你账号已锁定，请联系管理员',U('Login/logout'));
				exit();
			}
		}
	
		$session_id=session_id();
		if($session_id != $u_info['session_id'] && empty($login_from_admin)){
	
			if(IS_AJAX){
				ajaxReturn('您的账号在他处登录，您被迫下线',0);
			}else{
				success_alert('您的账号在他处登录，您被迫下线',U('Login/logout'));
				exit();
			}
		}
		session('in_time',time());
	}
}