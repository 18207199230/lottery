<?php

namespace osc\mobile\service;

use think\Db;
use think\Exception;
use wechat\Curl;

//微信支付处理
class WeixinPay
{
//微信支付 package
    public static function  getBizPackage($data)
    {

        $wx = wechat();
        // 订单总额
        $totalFee = 1;
//        $totalFee = ($data['pay_total']) * 100;
        // 随机字符串
        $nonceStr = $wx->generateNonceStr();

        $config = payment_config('weixin');

        // 时间戳
        $timeStamp = strval(time());

        $pack = array(
            'appid' => $config['appid'],
            'body' => $data['subject'],
            'mch_id' => $config['weixin_partner'],
            'nonce_str' => $nonceStr,
            'notify_url' => request()->domain() . url('lottery_payment/jsskd_notify'),
            'spbill_create_ip' => get_client_ip(),
            'openid' => $wx->getOpenId(),
            // 外部订单号
            'out_trade_no' => $data['pay_order_no'],
            'timeStamp' => $timeStamp,
            'total_fee' => $totalFee,
            'attach' => isset($data['attach']) ? $data['attach'] : 'other',
            'trade_type' => 'JSAPI'
        );

        $pack['sign'] = $wx->paySign($pack);

        $xml = $wx->toXML($pack);

        $ret = Curl::post('https://api.mch.weixin.qq.com/pay/unifiedorder', $xml);

        $postObj = json_decode(json_encode(simplexml_load_string($ret, 'SimpleXMLElement', LIBXML_NOCDATA)));

        if (empty($postObj->prepay_id) || $postObj->return_code == "FAIL") {
            throw new Exception('创建支付失败');
        } else {
            $packJs = array(
                'appId' => $config['appid'],
                'timeStamp' => $timeStamp,
                'nonceStr' => $nonceStr,
                'package' => "prepay_id=" . $postObj->prepay_id,
                'signType' => 'MD5'
            );

            $JsSign = $wx->paySign($packJs);

            $p['timestamp'] = $timeStamp;
            $p['nonceStr'] = $nonceStr;
            $p['package'] = "prepay_id=" . $postObj->prepay_id;
            $p['signType'] = 'MD5';
            $p['paySign'] = $JsSign;

            return json(['ret_code' => 0, 'bizPackage' => $p, 'out_trade_no' => $data['pay_order_no']]);
        }
    }
}

?>