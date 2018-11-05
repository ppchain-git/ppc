<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends CommonController {
	
	// 2018-11-3 YHL 网站首页
	public function index() {
		$userid = session('userid');
		$coin = D('coin')->where("`coin_id` = 1")->find();
		$coin['ucoins'] = M('ucoins')->where('user_id = '.$userid.' AND coin_id = '.$coin['coin_id'])->getField('coin_nums');
		
        //我的钱包地址 没有则自动生成
        $waadd = M('user')->where(array('userid'=>$userid))->getField('wallet_add');
        if(empty($waadd) || $waadd == ''){
            $waadd = build_wallet_add();
            M('user')->where(array('userid'=>$userid))->setField('wallet_add',$waadd);
        }
       	
     	$this->assign('coin',$coin);
        $this->assign('waadd',$waadd);
        $this->assign('uid',$uid);
		
        $this->display();
	}
	
	// 2018-11-3 YHL 转账页面
	public function turnout(){
		$coin_type = D('coin')->where("`status` <> 1 AND is_turn = 1")->field('coin_id,coin_name,en_coin_name')->order('coin_id asc')->select();
		$this->assign('coin_type',$coin_type);
		$this->display();
	}
	
	// 2018-11-3 YHL 转账--验证码数据
    public function checkuser(){
        $paynums = I('paynums','float',0);
        $getu = trim(I('moneyadd'));
        $coinid = trim(I('coinid'));
        $userid = session('userid');
		
		$coin_name = M('coin')->where('`status` <> 1 AND coin_id = '.$coinid)->getField('coin_name');
        $mwenums = M('ucoins')->where(array('user_id'=>$userid,'coin_id'=>1))->getField('coin_nums');
        if($paynums > $mwenums) ajaxReturn('亲，您当前暂无足够 '.$coin_name.' 资产~',0);
       
        $where['userid|mobile|wallet_add'] = $getu;
        $uinfo = M('user')->where($where)->Field('userid,username')->find();
        if($uinfo['userid'] == $userid) ajaxReturn('亲，您不能给自己转账哦~',0);
        if(empty($uinfo) || $uinfo == '') ajaxReturn('您输入的转出地址有误哦',0);
        
        $getmsg = array('uname'=>$uinfo['username'],'getuid'=>$uinfo['userid'],'coin_name'=>$coin_name,'coin_id'=>$coinid);
        ajaxReturn($getmsg,1);
    }
	
	// 2018-11-3 YHL 转账--处理数据
    public function wegetin(){
        $paynums = I('paynums','float',0);
        $getu = trim(I('moneyadd'));
        $pwd = trim(I('pwd'));
        $coinid = 1;
        $userid = session('userid');
		
        $mwenums = M('ucoins')->where(array('user_id'=>$userid,'coin_id'=>$coinid))->getField('coin_nums');
        $coin_name = M('coin')->where(array('coin_id'=>$coinid))->getField('coin_name');
        if($paynums > $mwenums) ajaxReturn('亲，您当前暂无足够 '.$coin_name.' 资产~',0);
        
        $where['userid|mobile|wallet_add'] = $getu;
        $uinfo = M('user')->where($where)->Field('userid,username')->find();
		if($uinfo['userid'] == $userid) ajaxReturn('亲，您不能给自己转账哦~',0);
		if(empty($uinfo) || $uinfo == '') ajaxReturn('您输入的转出地址有误哦',0);
        
        //验证交易密码
        $minepwd = M('user')->where(array('userid' => $userid))->Field('account,mobile,safety_pwd,safety_salt')->find();
		$user_object = D('Home/User');
        $user_info = $user_object->Trans($minepwd['account'], $pwd);
	
        //转入的加钱
        $issetgetu = M('ucoins')->where(array('user_id'=>$uinfo['userid'],'coin_id'=>$coinid))->count(1);
        if($issetgetu <= 0){
            $coinone['coin_id'] = $coinid;
            $coinone['coin_nums'] = 0.0000;
            $coinone['user_id'] = $uinfo['userid'];
            M('ucoins')->add($coinone);
        }
		
		// 扣手续费
		$app_charge = D('config')->where("name='app_charge'")->getField("value");
        $paymoney = $paynums * $app_charge / 100;
        $deduction = $paynums - $paymoney;
		
        $datapay['coin_nums'] = array('exp', 'coin_nums + ' . $deduction);
        $res_pay = M('ucoins')->where(array('user_id'=>$uinfo['userid'],'coin_id'=>$coinid))->save($datapay);//转出+
        //转出的扣钱
        $payout['coin_nums'] = array('exp', 'coin_nums - ' . $paynums);
        $res_pay = M('ucoins')->where(array('user_id'=>$userid,'coin_id'=>$coinid))->save($payout);//转出-

        $jifen_dochange['pay_id'] = $userid;
        $jifen_dochange['get_id'] = $uinfo['userid'];
        $jifen_dochange['get_nums'] = $paynums;
        $jifen_dochange['get_time'] = time();
        $jifen_dochange['get_type'] = $coinid;
		$jifen_dochange['duction'] = $paymoney;
        $res_tran = M('wetrans')->add($jifen_dochange);
        if($res_tran){
            ajaxReturn('转账成功',1,"index");
        }else{
            ajaxReturn('转账失败',0);
        }
    }
	
	// 2018-11-3 YHL 转账记录
	public function trans(){
        $type = I('type','intval',0);
        $traInfo = M('wetrans');
        $uid = session('userid');
         $where['get_type'] = 1;
		if($type == 1){
            $where['pay_id'] = $uid;
        }else{
            $where['get_id'] = $uid;
        }

        $Chan_info = $traInfo->where($where)->order('id desc')->select();
        foreach ($Chan_info as $k => $v) {
            $Chan_info[$k]['get_timeymd'] = date('Y-m-d', $v['get_time']);
            $Chan_info[$k]['get_timedate'] = date('H:i', $v['get_time']);
            $Chan_info[$k]['uid'] = $v['get_id'];
            $Chan_info[$k]['outinfo'] = M('user')->where(array('userid'=>$v['get_id']))->getField('username');
            $Chan_info[$k]['ininfo'] = M('user')->where(array('userid'=>$v['pay_id']))->getField('username');
            $Chan_info[$k]['coin_name'] = M('coin')->where(array('coin_id'=>$v['get_type']))->getField('coin_name');
            $Chan_info[$k]['en_coin_name'] = M('coin')->where(array('coin_id'=>$v['get_type']))->getField('en_coin_name');

            //转入转出
            if ($type == 1) {
                $Chan_info[$k]['trtype'] = 1;
            } else {
				$Chan_info[$k]['get_nums'] = $v['get_nums'] - $v['duction'];
                $Chan_info[$k]['trtype'] = 2;
            }
        }
        if (IS_AJAX) {
            if (count($Chan_info) >= 1) {
                ajaxReturn($Chan_info, 1);
            } else {
                ajaxReturn(L('zwjl1'), 0);
            }
        }
        $this->assign('page', $page);
        $this->assign('Chan_info', $Chan_info);
        $this->assign('type',$type);
        $this->display();
    }

}