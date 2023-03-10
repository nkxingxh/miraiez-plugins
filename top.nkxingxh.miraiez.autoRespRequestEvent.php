<?php

/**
 * MiraiEz Copyright (c) 2021-2023 NKXingXh
 * License AGPLv3.0: GNU AGPL Version 3 <https://www.gnu.org/licenses/agpl-3.0.html>
 * This is free software: you are free to change and redistribute it.
 * There is NO WARRANTY, to the extent permitted by law.
 *
 * Github: https://github.com/nkxingxh/MiraiEz
 */

class autoRespRequestEventPlugin
{
    const _pluginName = "自动处理事件请求";
    const _pluginAuthor = "NKXingXh";
    const _pluginDescription = "根据配置自动处理对应事件的请求";
    const _pluginPackage = "top.nkxingxh.miraiez.autoRespRequestEvent";
    const _pluginVersion = "1.0.0";

    public function _init()
    {
        hookRegister('autoRespFriendRequestEvent', 'NewFriendRequestEvent');
        hookRegister('autoRespMemberJoinRequestEvent', 'MemberJoinRequestEvent');
        hookRegister('autoRespBotInvitedJoinGroupRequestEvent', 'BotInvitedJoinGroupRequestEvent');
        return true;
    }

    function autoRespFriendRequestEvent($_DATA)
    {
        $config = getConfig('autoRespRequestEvent');
        if ($config['Friend'] === null) {
            $config['Friend'] = array('operate' => false, 'message' => '');
            saveConfig('autoRespRequestEvent', $config);
        }
        if ($config['Friend']['operate'] !== false) {
            resp_newFriendRequestEvent($config['Friend']['operate'], $_DATA['eventId'], $_DATA['fromId'], $_DATA['groupId'], $config['Friend']['message']);
        }
    }

    function autoRespMemberJoinRequestEvent($_DATA)
    {
        $config = getConfig('autoRespRequestEvent');
        if ($config['MemberJoin'] === null) {
            $config['MemberJoin'] = array('operate' => false, 'message' => '');
            saveConfig('autoRespRequestEvent', $config);
        }
        if ($config['MemberJoin']['operate'] !== false) {
            resp_memberJoinRequestEvent($config['MemberJoin']['operate'], $_DATA['eventId'], $_DATA['fromId'], $_DATA['groupId'], $config['MemberJoin']['message']);
        }
    }

    function autoRespBotInvitedJoinGroupRequestEvent($_DATA)
    {
        $config = getConfig('autoRespRequestEvent');
        if ($config['BotInvitedJoinGroup'] === null) {
            $config['BotInvitedJoinGroup'] = array('operate' => false, 'message' => '');
            saveConfig('autoRespRequestEvent', $config);
        }
        if ($config['BotInvitedJoinGroup']['operate'] !== false) {
            resp_botInvitedJoinGroupRequestEvent($config['BotInvitedJoinGroup']['operate'], $_DATA['eventId'], $_DATA['fromId'], $_DATA['groupId'], $config['BotInvitedJoinGroup']['message']);
        }
    }
}

pluginRegister(new autoRespRequestEventPlugin);
