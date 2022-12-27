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
    const _pluginVersion = "0.1.0";                         //插件版本

    //构造函数, 目前没有用到，写不写这个函数都可以
    public function __construct()
    {
        parent::__construct();
    }

    public function _init()
    {
        hookRegister('hook', 'FriendMessage', 'GroupMessage', 'StrangerMessage');
        return true;
    }

    public function hook($_DATA)
    {
        global $_PlainText;
        if ($this->get_command($_PlainText, ">c")) {
            $body = $this->cut_command($_PlainText, ">c");
            $data = http_build_query(array(
                "code" => $body,
                "token" => "b6365362a90ac2ac7098ba52c13e352b",
                "language" => "7",
                "fileext" => "c",
                "stdin" => ""
            ));
            $msg = CurlPOST($data, "https://tool.runoob.com/compile2.php");
            $msg = json_decode($msg);
            if (empty($msg)) {
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
            $data = http_build_query(array(
                "code" => $body,
                "token" => "b6365362a90ac2ac7098ba52c13e352b",
                "language" => "15",
                "fileext" => "py3",
                "stdin" => ""
            ));
            $msg = CurlPOST($data, "https://tool.runoob.com/compile2.php");
            $msg = json_decode($msg);
            if (empty($msg)) {
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
            if (empty($msg)) {
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
