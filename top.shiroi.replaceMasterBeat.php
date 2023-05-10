<?php
/**
 * User: Shiroi
 * EMail: 707305003@qq.com
 * github: https://github.com/hcr707305003
 */
pluginRegister(new class extends pluginParent{
    //以下五行插件信息必须定义
    const _pluginName = "replaceMasterBeat";                    //插件名称
    const _pluginAuthor = "shiroi";                             //插件作者
    const _pluginDescription = "代替主人反击插件";                 //插件描述
    const _pluginPackage = "top.shiroi.replaceMasterBeat";      //插件包名 必须是唯一的 (如已加载相同包名的插件，将跳过当前插件类，不予加载)
    const _pluginVersion = "0.0.2";                             //插件版本

    //主人
    protected int $master = 123456;
    //打击的用户 (不设置则默认全部)
    protected array $beatUser = [];
    //反击概率(单位：%)
    protected int $probability = 80;
    //设置反击的消息(暂支持类型：text,images,image)
    protected array $beatJson = [
        [
            //回复文本消息
            'type' => 'text',
            'content' => '你好',
        ],
        [
            //图片库(某个目录下的所有图片)->建议用目录的完整路径地址
            'type' => 'images',
            'content' => 'C:/Users/Administrator/Desktop/背景图/*'
        ],
        [
            //图片库(某个目录下的所有图片)->项目根目录下
            'type' => 'images',
            'content' => baseDir . '/image/*'
        ],
        [
            //单个图片(本地图片或远程图片)->项目根目录下
            'type' => 'image',
            'content' => baseDir . '/image/1.gif'
        ],
        [
            //单个图片(本地图片或远程图片)
            'type' => 'image',
            'content' => 'https://static-qn.51miz.com/images/show/ICON_Search-big.png'
        ]
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
        //将回复加入at数组中
        foreach ($_DATA['messageChain'] as $chain) if($chain['type'] == 'Quote') {
            $_At[] = $chain['senderId'];
        }

        if($this->probability($this->probability)) {
            if ($_DATA['type'] == 'FriendMessage') {
                if($_DATA['sender']['id'] != $this->master) {
                    if($msg = $this->getBitch()) replyMessage($msg,true, true);
                }
            }

            if ($_DATA['type'] == 'GroupMessage') {
                //at主人则返回消息
                if(in_array($this->master,$_At) && ($_DATA['sender']['id'] != $this->master) && (empty($this->beatUser) || in_array($_DATA['sender']['id'], $this->beatUser))) {
                    if($msg = $this->getBitch()) replyMessage($msg,true, true);
                }
            }
        }

        return 0;
    }

    protected function getBitch()
    {
        $allBitch = $this->beatJson;
        $rankNum = array_rand($allBitch);
        switch ($allBitch[$rankNum]['type'] ?? '') {
            case 'text':
                return $allBitch[$rankNum]['content'];
            case 'image':
                return getMessageChain('',base64_encode(file_get_contents($allBitch[$rankNum]['content'])));
            case 'images':
                $img = glob($allBitch[$rankNum]['content']);
                if($img) {
                    return getMessageChain('', base64_encode(file_get_contents($img[array_rand($img)])));
                }
                break;
        }
        return '';
    }

    /**
     * 中奖概率
     * @param int $winning_probability
     * @return bool
     */
    protected function probability(int $winning_probability = 50): bool
    {
        if (rand(1, 100) <= $winning_probability) {
            return true;
        } else {
            return false;
        }
    }
});