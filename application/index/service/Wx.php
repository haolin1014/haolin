<?php
namespace app\index\service;

/**
 * 微信服务类
 */
class Wx
{
	// 微信授权部分
	 //用户检测是否已登录
	public static function userCheck(){
	  if( !session('uid') ) {
	        //授权后回调地址;
	       	$url = request()->url(true); 
	        session('url_return',$url);
	        $res = redirect('index/index/des');
	        var_dump($res);
	        // redirect(config('WX_RETURN_URL'));
	    } 
	}
		// 进行微信授权
    public static function oAuth(){
        if(!isset($_GET['code'])){
            $this->redirect(config('WX_RETURN_URL'));
        }
        else{
            $wx_code = $_GET['code'];                 //微信授权返回的code
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.config('WX_APP_ID').'&secret='.config('WX_APP_SECRET').'&code='.$wx_code.'&grant_type=authorization_code';
            $wxjk_res = httpGet($url);
            $wxsq_arr = json_decode($wxjk_res);
            $wxsq_openid = $wxsq_arr->openid;            //微信授权，openid
            $wxsq_access_token = $wxsq_arr->access_token;   //微信授权，access_token
            //根据 access_token 和 openid，获得用户信息
            $url2 = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$wxsq_access_token.'&openid='.$wxsq_openid.'&lang=zh_CN';
            $wxjk_userinfo = httpGet($url2);
            $wxsq_userinfo_arr = json_decode($wxjk_userinfo);
            $u_nickname = $wxsq_userinfo_arr->nickname;
            //utf-8 编码，替换一部分emoji表情
            $u_nickname = preg_replace('~\xEE[\x80-\xBF][\x80-\xBF]|\xEF[\x81-\x83][\x80-\xBF]~', '', $u_nickname);
            $u_openid   = $wxsq_userinfo_arr->openid;
            $u_headimg  = $wxsq_userinfo_arr->headimgurl;
            $u_sex      = $wxsq_userinfo_arr->sex;
            //保存用户session----------
            if(!empty($u_openid)){
                $_SESSION['openid']        = $u_openid;     //用户的 openid 加前缀，做帐号，openid  28位 + 前缀，一共32位
                $_SESSION['nickname']    = trim($u_nickname);    //把用户的微信昵称设置到  真实姓名中，剔除空格
                $_SESSION['headimgurl']  = $u_headimg;
                //查看用户是否存在，如果不存在，则记录用户
                $res = Db::table('user')->where(array('openid'=>$u_openid))->find();
                if(!$res){
                  $data = array(
                    'openid'=> session('openid'),
                    'nickname'=> session('nickname'),
                    'headimgurl'=> session('headimgurl'),
                    'reg_time'=>time(),
                  );
                  Db::table('user')->data($data)->insert();
                }else{
                  session('uid',$res['uid']);
                }
            }
            else{
                $this->redirect(config('WX_RETURN_CARE_URL'));
            }
        }
	   //返回相对页面
	   $url_return = session('url_return');
	   session('url_return',null);
	   redirect($url_return); 
           
    }     
 
//微信jsapi，分享
    protected static function getJsApiInfo(){
        $noncestr = substr(md5('lvgocn'.time()),8,16);  
        $jsapiTicket = $this->getJsApiTicket();
        $timestamp = time();
        $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $param_str = 'jsapi_ticket='.$jsapiTicket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$url;
        $signature = sha1($param_str);
        
        $shareScript = '
        <script type="text/javascript">
            wx.config({
            debug: false,
            appId: \''.config('WX_APP_ID').'\',
            timestamp: \''.$timestamp.'\',
            nonceStr: \''.$noncestr.'\',
            signature:\''.$signature.'\',
            jsApiList: [
                \'checkJsApi\',
                \'onMenuShareTimeline\',
                \'onMenuShareAppMessage\',
                \'onMenuShareQQ\',
                \'onMenuShareWeibo\',
                \'openLocation\',
                \'getLocation\'
            ]
            });
        </script>';
        return $shareScript;
    }

    //微信jsapi，获得 jsapi ticket
    protected  function getJsApiTicket() {
        $data = json_decode(file_get_contents("jsapi_ticket.json"));
        if ($data->expire_time < time()) {
            $accessToken = $this->getAccessToken();
            $url = "http://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=".$accessToken;
            $res = json_decode(httpGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $fp = fopen("jsapi_ticket.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }   
        return $ticket;
    }
    
    //微信jsapi，获得 access token
    public function getAccessToken() {
        $data = json_decode(file_get_contents("access_token.json"));
        if ($data->expire_time < time()) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".config('WX_APP_ID')."&secret=".config('WX_APP_SECRET');
            $res = file_get_contents($url);
            $arr = json_decode($res, true);
            $access_token = $arr['access_token'];
            if ($access_token) {
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $fp = fopen("access_token.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
            $access_token = $data->access_token;
        }
        return $access_token;
    }
        public function https_post($url,$data=null){
          $curl = curl_init();
          curl_setopt($curl,CURLOPT_URL,$url);
          curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
          curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
          if(!empty($data)){
            curl_setopt($curl,CURLOPT_POST,1);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
          }
          curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
          $output = curl_execonfig($curl);
          curl_close($curl);
          return $output;
        } 
        
        public function http_send($url,$data=null) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查 
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true); // 从证书中检查SSL加密算法是否存在
        if($data!=null){
          curl_setopt($curl, CURLOPT_POST, 1);
          curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        $res = curl_execonfig($curl);
        curl_close($curl);
        return $res;
      }
        //发送模版消息 
        public function send_template_message($order_id,$username,$order_day,$order_time,$add_time,$shopid){
        $shopname = M('shop')->where(array('id'=>$shopid))->getField('name');
        $access_token = $this->getAccessToken();
        // $touser  = 'o58PiwEiEj075vltFhNRlNv72kVQ';
        // 计算总订金
        $times = explode(',',$order_time);
        $count = count($times);
        $account = $count*10;

        $touser = 'o58PiwL21XKFRx9eyNawxqB5tcZ8';
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$access_token;
        $template = array(
          "touser"=>$touser,
          "template_id"=>"e0ANXvrdufIyUq8uoMnEm7XWFQ6F0mKoUxtUl8O7pUs",
          "url"=>"http://shwyzxg.com/",
          "data"=>array(
            'first'=>array('value'=>$username." 预定成功 \n\t",'color'=>'#44B549'),
            'keyword1'=>array('value'=>$shopname,'color'=>'#7B68EE'),
            'keyword2'=>array('value'=>$order_day.' '.$order_time,'color'=>'#7B68EE'),
            'keyword3'=>array('value'=>$account.'元','color'=>'#7B68EE'),
            'keyword4'=>array('value'=>date("Y-m-d H:i",$add_time),'color'=>'#7B68EE'),
            'keyword5'=>array('value'=>$order_id,'color'=>'#7B68EE'),
            'remark'=>array('value'=>'欢迎下次预约！','color'=>'#000')
            )
          );
        $data = json_encode($template);
        $res = $this->http_send($url,$data);
      }
}