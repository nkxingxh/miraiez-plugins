<?php
/**
 * User: Shiroi
 * EMail: 707305003@qq.com
 * github: https://github.com/hcr707305003
 * example:
 *   私聊机器人|群聊 发送：诗词
 * 以下的关键词{$keywords}属性可以设置关键词回复
 */
pluginRegister(new class extends pluginParent{
    //以下五行插件信息必须定义
    const _pluginName = "poetry";                     //插件名称
    const _pluginAuthor = "shiroi";                   //插件作者
    const _pluginDescription = "诗词";                 //插件描述
    const _pluginPackage = "top.shiroi.poetry";       //插件包名 必须是唯一的 (如已加载相同包名的插件，将跳过当前插件类，不予加载)
    const _pluginVersion = "0.0.1";                   //插件版本

    //关键词
    protected array $keywords = [
        '诗词',
        '今日诗词',
        '获取诗词',
        '来首诗词',
        '整首诗词',
        '诗词获取',
        '我要诗词',
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
        $init = false;
        foreach ($this->keywords as $v) if (trim($_PlainText) == $v) {
            $init = true;break;
        }
        if ($init) if ($body = $this->createBody()) {
            replyMessage($body);
        }

        return 0;
    }

    private function getPoetry() {
        try {
            return json_decode(CurlGET('https://v2.jinrishici.com/sentence','','', [
                "X-User-Token: " . json_decode(CurlGET('https://v2.jinrishici.com/token'), true)['data']
            ]), true);
        } catch (Exception $e) {
            return [];
        }
    }

    private function createBody(): string {
        $str = "";
        if (($result = $this->getPoetry()) && isset($result['data'])) {
            $data = $result['data']['origin'];
            $str .= "{$data['title']}\r{$data['dynasty']}\r{$data['author']}\r{$data['author']}\r";
            foreach ($data['content'] as $c) {
                $str .= "{$c}\n";
            }
        }
        return $str;
    }
});