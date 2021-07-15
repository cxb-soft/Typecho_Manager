<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Typecho manager
 * 
 * @package Typecho manager
 * @author cxbsoft
 * @version 1.0.0
 * @link https://blog.bsot.cn
 */
class TypechoManager_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory("Widget_Feedback")->comment = array("TypechoManager_Plugin",'comment_process');
        return _t("Typecho Manager已启动");
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        /** 分类名称 */
        $emotion_api = new Typecho_Widget_Helper_Form_Element_Text('emotion_api', NULL, 'http://emotion.api.bsot.cn/predict?text=', _t('中文情感检测API'),"留空代表关闭");
        $form->addInput($emotion_api);
        $switch_telegram = new Typecho_Widget_Helper_Form_Element_Radio('switch_telegram', array("on" => "打开推送", "off" => "关闭推送"), "on",_t('Telegram推送开关'), "开/关");
        $form->addInput($switch_telegram);
        $switch_emotion = new Typecho_Widget_Helper_Form_Element_Radio('switch_emotion', array("on" => "打开情感检测", "off" => "关闭情感检测"), "on",_t('情感检测开关'), "开/关");
        $form->addInput($switch_emotion);
        $switch_urlsafe = new Typecho_Widget_Helper_Form_Element_Radio('switch_urlsafe', array("on" => "打开网址安全检测", "off" => "关闭网址安全检测"), "on",_t('网址安全检测开关'), "开/关");
        $form->addInput($switch_urlsafe);
        $telegram_token = new Typecho_Widget_Helper_Form_Element_Text('telegram_token', NULL, '1869948410:AAGEluZrbbRQR843eUMUYrlycvtRn2LS8jo', _t('TG BOT token'),"不修改则使用默认bot");
        $form->addInput($telegram_token);
        $chatid = new Typecho_Widget_Helper_Form_Element_Text('chatid', NULL, '你的chatid', _t('Chat ID'),"使用@typechomgbot获取");
        $form -> addInput($chatid);
        $safeurl = new Typecho_Widget_Helper_Form_Element_Text('safeurl', NULL, 'https://api.bsot.cn/urlsafe/urlsafe.php?url=', _t('URL检查网址'),"输入安全检测网址");
        $form -> addInput($safeurl);
        
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    /**
     * 从字符串中提取url
     */
    public static function getStringUrl($Text)
    {
        $parttern = "/http[s]?:\/\/(?:[a-zA-Z]|[0-9]|[$-_@.&+]|[!*\(\),]|(?:%[0-9a-fA-F][0-9a-fA-F]))+/";
        preg_match($parttern, $Text, $match);
        return count($match) > 0 ? $match[0] : '';
    }
    /**
     * Telegram send function
     */
    public static function send($data,$token) {
        $data_string = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL,"https://api.telegram.org/bot$token/sendMessage");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data_string))
        );
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $return_content = json_decode($return_content,true);
        return $return_content;
    }
    /**
     * 评论测试
     * 
     * @access public
     * @return array
     */

    public static function comment_process($comment,$post)
    {
        $opt = Typecho_Widget::widget('Widget_Options') -> plugin('TypechoManager');
        $text = $comment['text'];

        $message = "您收到了一个新评论:[$text]\n";
        if(($opt -> switch_emotion == "on") || ($opt -> switch_urlsafe == "on")){
            $message .= "以下为初步分析结果:\n";
        }
        if( ($opt -> switch_emotion) == "on" ){
            $url = ($opt -> emotion_api) . $text;
            $result = file_get_contents($url);
            $result = json_decode($result,true);
            $emotion = $result['emotion'];
            $emotion = ($emotion==1)?"积极":"消极";
            $message .= "情绪:$emotion\n";
            $comment['status'] = "waiting";
        }
        if($opt -> switch_urlsafe == "on"){
            $parttern = "/http[s]?:\/\/(?:[a-zA-Z]|[0-9]|[$-_@.&+]|[!*\(\),]|(?:%[0-9a-fA-F][0-9a-fA-F]))+/";
            preg_match($parttern, $text, $match);
            $url = count($match) > 0 ? $match[0] : '';
            if($url == ""){
                $message .= "是否包含URL:否\n";
                $comment['status'] = "waiting";

            }
            else{
                $message .= "是否包含URL:是 - [$url]\n";
                $safeurl = $opt -> safeurl;
                $url_request = file_get_contents($safeurl . $url);
                $result = json_decode($url_request,true);
                if($result['wordtit'] == ""){
                    $message .= "URL:安全";
                    $comment['status'] = "waiting";
                }
                else{
                    $word = $result['word'];
                    $message .= "URL:$word\n处理办法:已加入垃圾评论";
                    $comment['status'] = "spam";
                }
            }
            
        }
        else{
            $comment['status'] = "waiting";
        }
        $switch_telegram = $opt -> switch_telegram;
        if($switch_telegram == "on"){
            $telegram_token = $opt -> telegram_token;
            $chatid = $opt -> chatid;
            $data = array(
                "chat_id" => $chatid,
                "text" => $message,
                "disable_web_page_preview" => true
            );
            TypechoManager_Plugin::send($data,$telegram_token);
            //file_get_contents("https://api.telegram.org/bot$telegram_token/sendMessage?chat_id=$chatid&text=$message&disable_web_page_preview=false");
        }
        return $comment;
    }
}
