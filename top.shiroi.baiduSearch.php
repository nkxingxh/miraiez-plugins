<?php
/**
 * User: Shiroi
 * EMail: 707305003@qq.com
 * github: https://github.com/hcr707305003
 * example:
 *   私聊机器人|群聊 发送：百度查询一下 内容
 * 以下的关键词{$keywords}属性可以设置关键词回复
 */
pluginRegister(new class extends pluginParent {
    //以下五行插件信息必须定义
    const _pluginName = "baiduSearch";                  //插件名称
    const _pluginAuthor = "shiroi";                     //插件作者
    const _pluginDescription = "百度搜索";               //插件描述
    const _pluginPackage = "top.shiroi.baiduSearch";    //插件包名 必须是唯一的 (如已加载相同包名的插件，将跳过当前插件类，不予加载)
    const _pluginVersion = "0.0.1";                     //插件版本

    //关键词
    protected array $keywords = [
        '百度查询一下',
        '百度搜索一下',
        '搜索百度一下',
        '查询百度一下',
        '百度搜索',
        '百度一下',
        '搜索百度',
        '百度查询',
        '查询百度',
        '查百度',
        '搜百度',
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

        if ($_DATA['type'] == 'FriendMessage') {
            foreach ($this->keywords as $v) {
                $exists = $this->both_field_exists($_PlainText, $v, 1);
                if($exists['bool']) {
                    replyMessage(
                        '手机端：https://m.baidu.com/s?wd=' . urlencode($exists['cut_content']) .
                        "\r电脑端：https://www.baidu.com/s?wd=" . urlencode($exists['cut_content'])
                    );
                    break;
                }
            }
        }

        return 0;
    }

    /**
     * 判断文本是否在(头部|尾部|当前文本)存在
     * @param string $string (文本内容)
     * @param string $subString （是否存在该字段）
     * @param int $type (0=>不指定头部或者尾部, 1=>头部, 2=>尾部)
     * @return array
     */
    protected function both_field_exists(string $string, string $subString, int $type = 0): array
    {
        $bool = false;
        $cut_content = $string;
        if ($type == 0) {
            $bool = mb_strpos($string,$subString);
            if($bool) {
                $cut_content = str_replace($subString,'',$string);
            }
        } elseif ($type == 1) {
            $bool = mb_substr($string, 0, mb_strlen($subString)) === $subString;
            if($bool) {
                $cut_content = mb_substr($string,mb_strlen($subString),(mb_strlen($string)-mb_strlen($subString)));
            }
        } elseif ($type == 2) {
            $bool = mb_substr($string, mb_strpos($string, $subString)) === $subString;
            if($bool) {
                $cut_content = mb_substr($string,0,mb_strpos($string, $subString));
            }
        }
        return compact('bool','cut_content');
    }
});