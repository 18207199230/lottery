<?php
/**
 * oscshop2 B2C电子商务系统
 *
 * ==========================================================================
 * @link      http://www.oscshop.cn/
 * @copyright Copyright (c) 2015-2016 oscshop.cn. 
 * @license   http://www.oscshop.cn/license.html License
 * ==========================================================================
 *
 * @author    李梓钿
 * 积分,积分兑换
 */
 
namespace osc\mobile\controller;
use think\Db;
class Balance extends MobileBase
{
	protected function _initialize(){
		parent::_initialize();						
		define('UID',osc_service('mobile','user')->is_login());	
		//手机版
		if(!UID){
			if(!in_wechat()){
				$this->redirect('login/login');	
			}else{
				$this->error('系统错误');
			}			
		}		
	}
	
	function index(){

		$this->assign('SEO',['title'=>'积分兑换']);
		
		$this->assign('top_title','积分兑换');
		$this->assign('flag','user');
		return $this->fetch();
	}
	
	public function ajax_goods_list(){

        $page=input('param.page');//页码

        $limit = (6 * $page) . ",6";
		
        $list=Db::name('goods')->where('is_points_goods',1)->order('goods_id desc')->limit($limit)->select();
		
		if(isset($list)&&is_array($list)){
				foreach ($list as $k => $v) {				
					$list[$k]['image']=resize($v['image'], 250, 250);		
				}
		}
		
        return  $list;
    }
	function balance_list(){
		$this->assign('user_info',Db::name('member')->where(array('uid'=>UID))->find());	
		$this->assign('list',Db::name('balance')->where(array('uid'=>UID))->select());
		$this->assign('empty',"<span style='margin-left:20px;'>没有数据</span>");
		$this->assign('top_title','我的金豆');
		return $this->fetch();
	}
}