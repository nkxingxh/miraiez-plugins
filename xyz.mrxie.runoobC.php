<?php

/**
 * 
 * 作者: Mr-XieXuan
 * 邮箱: Mr_Xie_@outlook.com
 * Github: https://github.com/Mr-XieXuan
 * 
 * 通过该插件可以运行以 c ，python ，js 的程序代码。
 * 是通过调取 Runoob 接口实现的。
 * 
 * 安装后对机器人发送 >js console.log("Hello world !"); 即可开始你的第一次使用 runoobC 插件。
 * 以 >js 开头将运行 javascript 程序
 * 以 >python 开头将运行 python 程序
 * 以 >c 开头将运行 c 程序
 */
pluginRegister(new class extends pluginParent   //建议继承 pluginParent 插件类,当框架更新导致插件类定义发生变化时, pluginParent 将能提供一定的容错能力
{
    //以下五行插件信息必须定义
    const _pluginName = "runoobC";                    //插件名称
    const _pluginAuthor = "mrxie";                       //插件作者
    const _pluginDescription = "运行代码";                  //插件描述
    const _pluginPackage = "xyz.mrxie.runoobC";    //插件包名 必须是唯一的 (如已加载相同包名的插件，将跳过当前插件类，不予加载)
    const _pluginVersion = "0.0.1";                         //插件版本

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
    public function _init()
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
        hookRegister('hook', 'FriendMessage', 'GroupMessage', 'StrangerMessage');
        return true;
    }

    /**
     * hook 处理函数
     * 这个函数被注册了, 所以必须设置为 公共 (public) 函数
     * 否则调用时会出错
     */
    public function hook($_DATA)
    {
        /**
         * $_PlainText 全局变量, 类型为 字符串 (String), 存储消息的纯文本内容，使用前需要先通过 global 声明或者通过 $GLOBALS['_PlainText'] 调用
         * $_ImageUrl 全局变量，类型为 数组 (Array), 成员类型为 字符串 (String), 存储消息中图片的链接，使用前需要先通过 global 声明或者通过 $GLOBALS['_ImageUrl'] 调用
         * $_At 全局变量，类型为 数组 (Array), 成员类型为 整型 (int), 存储消息中被 @ 用户的 QQ 号，使用前需要先通过 global 声明或者通过 $GLOBALS['_At'] 调用
         */
        global $_PlainText, $_At, $_ImageUrl;
        if ($this->get_command($_PlainText, ">c")) {
            $body = $this->cut_command($_PlainText, ">c");
            $data = array(
                "code" => $body,
                "token" => "b6365362a90ac2ac7098ba52c13e352b",
                "language" => "7",
                "fileext" => "c",
                "stdin" => ""
            );
            $msg = CurlPOST($data, "https://tool.runoob.com/compile2.php");
            $msg = json_decode($msg);
            if (is_null($msg)) {
                replyMessage("维护中......");
                return 1;
            }
            if ($msg->output != "") {
                replyMessage($msg->output, true);   //使用 replyMessage 快速回复消息
            } else {
                replyMessage($msg->errors);   //使用 replyMessage 快速回复消息
            }
            return;
        }

        if ($this->get_command($_PlainText, ">python")) {
            $body = $this->cut_command($_PlainText, ">python");
            $data = array(
                "code" => $body,
                "token" => "b6365362a90ac2ac7098ba52c13e352b",
                "language" => "15",
                "fileext" => "py3",
                "stdin" => ""
            );
            $msg = CurlPOST($data, "https://tool.runoob.com/compile2.php");
            $msg = json_decode($msg);
            if (is_null($msg)) {
                replyMessage("维护中......");
                return 1;
            }
            if ($msg->output != "") {
                replyMessage($msg->output, true);   //使用 replyMessage 快速回复消息
            } else {
                replyMessage($msg->errors);   //使用 replyMessage 快速回复消息
            }
            return;
        }

        if ($this->get_command($_PlainText, ">js")) {
            $body = $this->cut_command($_PlainText, ">js");
            $data = array(
                "code" => $body,
                "token" => "b6365362a90ac2ac7098ba52c13e352b",
                "language" => "4",
                "fileext" => "node.js",
                "stdin" => ""
            );
            $msg = CurlPOST($data, "https://tool.runoob.com/compile2.php");
            $msg = json_decode($msg);
            if (is_null($msg)) {
                replyMessage("维护中......");
                return 1;
            }
            if ($msg->output != "") {
                replyMessage($msg->output, true);   //使用 replyMessage 快速回复消息
            } else {
                replyMessage($msg->errors);   //使用 replyMessage 快速回复消息
            }
            return;
        }
    }

    function get_command($command, $expect)
    {
        if (substr($command, 0, strlen($expect)) == $expect) {
            return true;
        } else {
            return false;
        }
    }

    function cut_command($command, $expect)
    {
        if ($this->get_command($command, $expect)) {
            return substr($command, strlen($expect));
        } else {
            return $command;
        }
    }
});
