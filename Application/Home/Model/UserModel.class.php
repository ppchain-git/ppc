<?php
namespace Home\Model;
class UserModel extends \Think\Model {
	protected $tableName = 'user';
	
	// 2018-11-3 YHL 检测用户是否登录
	public function user_login() {
		$user = session('user_login');
		if (empty($user)) {
			return 0;
		} else {
			if (session('user_login_sign') == $this->data_auth_sign($user)) {
				return $user['userid'];
			} else {
				return 0;
			}
		}
	}
	
	// 2018-11-3 YHL 数据签名认证
	public function data_auth_sign($data) {
		// 数据类型检测
		if (!is_array($data)) {
			$data = (array)$data;
		}
		ksort($data); //排序
		$code = http_build_query($data); // url编码并生成query字符串
		$sign = sha1($code); // 生成签名
		return $sign;
	}
	
	// 2018-11-3 YHL 用户登录
	public function login($account, $password, $map = null) {
		//去除前后空格
		$account = trim($account);
		if (!isset($account) || empty($account)) {
			$this->error = L('xhzhangjhem');
			return false;
		}
		if (!isset($password) || empty($password)) {
			$this->error = L('wqmmbnwk');
			return false;
		}
		
		$map['mobile|account|userid'] = array('eq', $account); // 手机号登陆
		$user_info = $this->where($map)->find(); //查找用户
		if (!$user_info) {
			$this->error = L('xhzhmimcwu');
			return false;
		} elseif ($user_info['status'] <= 0) {
			$this->error = L('xhzhsuod');
			return false;
		} else {
			if ($this->pwdMd5($password, $user_info['login_salt']) !== $user_info['login_pwd']) {
				$this->error = L('xhzhmimcwu');
				return false;
			} else {
				$session_id = session_id();
				$this->where($map)->setField('session_id', $session_id);
				return $user_info;
			}
		}
		return false;
	}
	
	// 2018-11-3 YHL 用户密码加密
	public function pwdMd5($value, $salt) {
		$user_pwd = md5(md5($value) . $salt);
		return $user_pwd;
	}
	
	// 2018-11-3 YHL 用户登录
	public function Quicklogin($account) {
		//去除前后空格
		$account = trim($account);
		if (!isset($account) || empty($account)) {
			$this->error = L('xhzhangjhem');
			return false;
		}
		$map['mobile|account|userid'] = array('eq', $account); // 手机号登陆
		$user_info = $this->where($map)->find(); //查找用户
		if ($user_info['status'] <= 0) {
			$this->error = L('xhzhsuod');
			return false;
		} else{
			$session_id = session_id();
			$this->where($map)->setField('session_id', $session_id);
			return $user_info;
		}
		return false;
	}
	
	// 2018-11-3 YHL 设置登录状态
	public function auto_login($user) {
		// 记录登录SESSION和COOKIES
		$auth = array(
			'userid' => $user['userid'],
			'account' => $user['account'],
			'mobile' => $user['mobile'],
			'username' => $user['username'],
		);
		session('userid', $user['userid'], 43200);
	
		session('user_login', $auth, 43200);
		session('user_login_sign', $this->data_auth_sign($auth), 43200);
		return $this->user_login();
	}
	
	// 2018-11-3 YHL 用户交易
	public function Trans($account, $password, $map = null)	{
		$map['mobile|account'] = array('eq', $account); // 手机号登陆
		$user_info = $this->where($map)->find(); //查找用户
		if (!$user_info) {
			ajaxReturn(L('xhnshurs'),0);
		} elseif ($user_info['status'] <= 0) {
			ajaxReturn(L('xhzhsuod'),0);
		} else {
			$ispwd = $this->pwdMd5($password, $user_info['safety_salt']);
			if ($ispwd == $user_info['safety_pwd']) {
			}else{
				ajaxReturn(L('xhjsk'),0);
			}
		}
		return false;
	}
}
