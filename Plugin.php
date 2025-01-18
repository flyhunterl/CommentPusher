<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 使用 WxPusher 推送评论通知到微信
 * 
 * @package CommentPusher 
 * @author flyhunterl
 * @version 1.0.0
 * @link https://github.com/flyhunterl/CommentPusher
 */
class CommentPusher_Plugin implements Typecho_Plugin_Interface
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
        Typecho_Plugin::factory('Widget_Feedback')->comment = array('CommentPusher_Plugin', 'pushComment');
        Typecho_Plugin::factory('Widget_Comments_Edit')->finishComment = array('CommentPusher_Plugin', 'pushComment');
        return _t('插件已经激活，请设置 appToken 和 UID');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
        return _t('插件已被禁用');
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $appToken = new Typecho_Widget_Helper_Form_Element_Text(
            'appToken', 
            NULL,
            '',
            _t('WxPusher AppToken'),
            _t('请输入你的 WxPusher AppToken')
        );
        $form->addInput($appToken->addRule('required', _t('AppToken 不能为空')));

        $uid = new Typecho_Widget_Helper_Form_Element_Text(
            'uid',
            NULL,
            '',
            _t('WxPusher UID'),
            _t('请输入你的 WxPusher UID')
        );
        $form->addInput($uid->addRule('required', _t('UID 不能为空')));
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * 发送推送请求
     * 
     * @access private
     * @param array $data 推送数据
     * @return array|false
     */
    private static function sendRequest($data)
    {
        try {
            $ch = curl_init('https://wxpusher.zjiecode.com/api/send/message');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($response === false) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new Exception('请求失败: ' . $error);
            }
            
            curl_close($ch);
            
            $result = json_decode($response, true);
            if ($httpCode != 200 || empty($result) || $result['code'] !== 1000) {
                throw new Exception('推送失败: ' . ($result['msg'] ?? '未知错误'));
            }
            
            // 显示成功消息
            Typecho_Widget_Notice::success(_t('推送成功'));
            return $result;
            
        } catch (Exception $e) {
            Typecho_Widget_Notice::error(_t('推送失败: ' . $e->getMessage()));
            error_log('WxPusher 推送异常: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 推送评论通知
     * 
     * @access public
     * @param array $comment 评论结构
     * @param Widget_Comments_Edit|Widget_Feedback $widget 评论组件
     * @return array
     */
    public static function pushComment($comment, $widget)
    {
        // 获取文章标题
        $db = Typecho_Db::get();
        $post = $db->fetchRow($db->select('title')
            ->from('table.contents')
            ->where('cid = ?', $comment['cid']));
        
        // 构建推送内容
        $content = sprintf(
            "评论通知：\n 评论者：%s\n 评论内容：%s\n 评论时间：%s\n 文章标题：《%s》",
            $comment['author'],
            $comment['text'],
            date('Y-m-d H:i:s', $comment['created']),
            $post['title']
        );
        
        // 获取配置
        $options = Helper::options();
        $appToken = $options->plugin('CommentPusher')->appToken;
        $uid = $options->plugin('CommentPusher')->uid;
        
        // 构建请求数据
        $data = array(
            "appToken" => $appToken,
            "content" => $content,
            "contentType" => 1,
            "uids" => [
                $uid
            ],
            "summary" => sprintf("收到新的评论")
        );
        
        // 发送请求
        self::sendRequest($data);
        
        return $comment;
    }
} 