<?php
/**
 * User: Shiroi
 * EMail: 707305003@qq.com
 * github: https://github.com/hcr707305003
 * example:
 *   私聊机器人|群聊 发送：来张美女|来张动漫|来张风景
 * 以下的关键词{$meinv|$dongman|$fengjing}属性可以设置关键词回复
 */
pluginRegister(new class extends pluginParent{
    //以下五行插件信息必须定义
    const _pluginName = "moreImage";                  //插件名称
    const _pluginAuthor = "shiroi";                   //插件作者
    const _pluginDescription = "获取图片~摩多摩多";      //插件描述
    const _pluginPackage = "top.shiroi.moreImage";    //插件包名 必须是唯一的 (如已加载相同包名的插件，将跳过当前插件类，不予加载)
    const _pluginVersion = "0.0.1";                   //插件版本


    protected array $meinv = [
        '来张美女',
        '整张美女',
        '来张美女图',
        '整张美女图',
    ];

    protected array $dongman = [
        '来张动漫',
        '整张动漫',
        '来张动漫图',
        '整张动漫图',
    ];

    protected array $fengjing = [
        '来张风景',
        '整张风景',
        '整张风景图',
        '整张景色图',
        '来张风景图',
        '来张景色图',
    ];

    //构造函数, 目前没有用到，写不写这个函数都可以
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 插件初始化函数
     * 请不要在该函数中做除 hookRegister 外的任何操作
     * 返回 false 则表示插件初始化失败, 该插件将不会在后续被调用 (即使已经使用 hookRegister 注册 消息、事件或请求等 的处理函数)
     */
    public function _init():bool
    {
        /**
         * hookRegister
         * 注册消息、事件或请求等的处理函数
         * 第一个参数 (func) 被注册的函数名称
         * 从第二个参数开始到最后一个参数 (...$types) 为消息/事件类型,
         *
         * 具体消息类型、事件类型请参阅 mirai-api-http 文档:
         * https://github.com/project-mirai/mirai-api-http/blob/master/docs/api/MessageType.md
         * https://github.com/project-mirai/mirai-api-http/blob/master/docs/api/EventType.md
         */
        hookRegister('hook', 'FriendMessage', 'GroupMessage');
        return true;
    }

    public function hook($_DATA): int
    {
        global $_PlainText, $_At, $_ImageUrl;
        //消息切割
        $message = explode(' ', trim($_PlainText));
        //参数
        $param = ['type' => 'url'];

        if (in_array($message[0], $this->meinv)) {
            $param['category'] = 'meinv';
        } elseif (in_array($message[0], $this->dongman)) {
            $param['category'] = 'dongman';
        } elseif (in_array($message[0], $this->fengjing)) {
            $param['category'] = '{fengjing,biying}';
        }

        if ($message[1] ?? '') {
            if (in_array($message[1], ['手机', 'm', '手机端', '移动', '移动端'])) {
                $param['px'] = 'm';
            }
            if (in_array($message[1], ['电脑', 'pc', 'pc端', '电脑端'])) {
                $param['px'] = 'pc';
            }
        }

        if(isset($param['category'])) {
            replyMessage(getMessageChain('', base64_encode(file_get_contents(CurlGET('https://tuapi.eees.cc/api.php?'.http_build_query($param))))));
        }

        return 0;
    }
});