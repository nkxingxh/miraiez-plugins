<?php

pluginRegister(new class extends pluginParent
{
    const _pluginName = "群图片过滤";
    const _pluginAuthor = "nkxingxh";
    const _pluginDescription = "使用 yolox-onnx-api-server 识别图片并根据配置过滤";
    const _pluginPackage = "top.nkxingxh.miraiez.yolox.ImageFilter";
    const _pluginVersion = "1.2.0";

    private static array $conf;

    public function __construct()
    {
        parent::__construct();
    }

    public function _init()
    {
        hookRegister('hook', 'GroupMessage');
        return true;
    }

    public function hook($_DATA)
    {
        global $_ImageUrl;

        // 如果没有图片直接返回
        if (empty($_ImageUrl)) {
            // writeLog('没有图片, 跳过。' . json_encode($_DATA, JSON_UNESCAPED_UNICODE), level: 1);
            return 0;
        }

        // 读取在当前对话中的配置
        if (empty(self::$conf)) {
            self::$conf = self::getConfig($_DATA);
        }

        // 检查插件是否全局启用
        if (!self::enableCheck(self::$conf, $_DATA)) {
            return 0;
        }

        // 遍历规则组
        foreach (self::$conf['ruleGroups'] ?? [] as $groupId => $ruleGroup) {
            // 遍历规则列表
            foreach ($ruleGroup['rules'] ?? [] as $ruleId => $rule) {
                // 判断当前规则是否启用
                /* if (!($rule['enable'] ?? true)) {
                    // 未启用则跳过
                    continue;
                } */

                // 检查该条规则是否在当前环境中启用
                if (!self::enableCheck($rule, $_DATA)) {
                    continue;
                }

                // 群聊判断
                if ($_DATA['type'] == 'GroupMessage' && (
                    // 判断是否跳过管理员
                    (($rule['skipStaff'] ?? ($ruleGroup['skipStaff'] ?? (self::$conf['skipStaff'] ?? true))) && $_DATA['sender']['permission'] != 'MEMBER')
                    // 判断是否需要@
                    // || (($rule['keywordsNeedAt'] ?? ($ruleGroup['keywordsNeedAt'] ?? false)) && !in_array(bot, $_At))
                )) {
                    continue;
                }

                // 可视化配置
                $visConf = ($rule['vis'] ?? $ruleGroup['vis']) ?? false;

                // 遍历所有图片
                foreach ($_ImageUrl as $imageId => $imageUrl) {
                    $imageResult = self::predictImage($ruleGroup, $imageId, $imageUrl, boolval($visConf));
                    if (empty($imageResult)) {
                        writeLog("predictImage 失败: $imageUrl");
                        continue;
                    }
                    // 遍历结果中的所有目标
                    foreach ($imageResult['data'] as $target) {
                        // 遍历所有配置的类别
                        foreach ($rule['classes'] as $classConf) {
                            if (
                                (
                                    in_array($target['class_id'], $classConf['classId'] ?? []) ||
                                    in_array($target['class_name'], $classConf['className'] ?? [])
                                ) &&
                                $target['score'] >= $classConf['score']
                            ) {
                                writeLog("图片命中! {$target['class_id']}, {$target['class_name']}, $imageUrl");
                                // 做出动作
                                self::hitAction(
                                    (($classConf['action'] ?? $rule['action']) ?? $ruleGroup['action']) ?? [],
                                    $imageResult['vis'] ?? null
                                );
                                // 跳出所有遍历循环
                                // break 5;
                                // 拦截消息
                                return 1;
                            }
                        }
                    }
                    // 执行到这里说明当前图片没有命中任何规则
                    // 如果 vis>1 回复可视化结果
                    if ($visConf > 1 && !empty($imageResult['vis'])) {
                        replyMessage([getMessageChain_Image(ImageBase64: $imageResult['vis'])]);
                    }
                }
            }
        }
        return 0;
    }

    private static function hitAction($action_conf, $vis_base64 = null)
    {
        $need_recall = $action_conf['recall'] ?? false;
        if ($need_recall) {
            recall(true);
        }

        $messageChain = array();

        $reply = $action_conf['reply'] ?? false;
        if (!empty($reply) && is_string($reply)) {
            $messageChain[] = getMessageChain_PlainText($reply);
        }

        // if(($rule['vis'] ?? $ruleGroup['vis']) ?? false) {
        if (!empty($vis_base64)) {
            $messageChain[] = getMessageChain_Image(ImageBase64: $vis_base64);
        }

        if (!empty($messageChain)) {
            replyMessage($messageChain, $need_recall ? 0 : true);
        }
    }

    private static function predictImage(&$ruleGroup, $imageId, $imageUrl, $vis = false)
    {
        // 检查是否有结果缓存
        if (!empty($ruleGroup['predictResultCache'][$imageId])) {
            writeLog("缓存命中! $imageUrl", 'predictImage', level: 1);
            return $ruleGroup['predictResultCache'][$imageId];
        }

        // query 参数
        $apiUrl = $ruleGroup['apiUrl'] ?? '';
        $apiKey = $ruleGroup['apiKey'] ?? '';
        $query = array(
            'vis' => intval($vis),
            'key' => $apiKey
        );
        $apiUrl .= '?' . http_build_query($query);

        $payload = CurlGET($imageUrl, UserAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36');
        $payload = self::convertImageToJpg($payload);
        if (empty($payload)) {
            writeLog("convertImageToJpg 失败");
            return false;
        }
        $headers = ['Content-Type' => 'image/jpeg'];
        $resp = CurlPOST($payload, $apiUrl, header: $headers);
        // writeLog("predictImage 响应: $resp", level: 1);
        
        $resp = json_decode($resp, true);
        if (empty($resp['data'])) {
            writeLog("yolox 服务器无数据返回: " . json_encode($resp, JSON_UNESCAPED_UNICODE), level: 3);
            return null;
        }

        // 记录缓存
        if (empty($ruleGroup['predictResultCache'])) {
            $ruleGroup['predictResultCache'] = array();
        }
        $ruleGroup['predictResultCache'][$imageId] = $resp;

        $et = $resp['et'] ?? '未知';
        writeLog("耗时: {$et}ms", 'predictImage', level: 1);
        return $resp;
    }

    private static function convertImageToJpg($image)
    {
        // 获取图像类型
        $imageInfo = getimagesizefromstring($image);
        $mime = $imageInfo['mime'];

        // writeLog("图片类型为 $mime", level: 1);

        // 判断图像格式
        if ($mime === 'image/jpeg' || $mime === 'image/png') {
            // 如果是jpg或png格式，直接返回
            return $image;
        } elseif ($mime === 'image/gif') {
            // 检查Imagick扩展是否可用
            if (class_exists('Imagick')) {
                try {
                    $imagick = new Imagick();
                    $imagick->readImageBlob($image);

                    /* // 随机选择一帧
                    $randomFrame = rand(0, $imagick->getNumberImages() - 1);
                    writeLog("截取第{$randomFrame}帧", level:1);
                    $imagick->setIteratorIndex($randomFrame);

                    // 强制转换颜色空间
                    $imagick->transformImageColorspace(Imagick::COLORSPACE_RGB); */

                    $imagick = $imagick->coalesceImages();
                    $frames_count = $imagick->count();
                    $randomFrame = mt_rand(0, $frames_count - 1);
                    writeLog("总共有 {$imagick->count()} 帧, 截取第{$randomFrame}帧", level: 1);
                    // $imagick->setIteratorIndex($randomFrame);
                    for ($i = 0; $i <= $randomFrame; $i++) {
                        $imagick->nextImage();
                    }

                    // 将当前帧转换为jpg格式
                    $imagick->setImageFormat('jpeg');
                    $jpgImage = $imagick->getImageBlob();

                    // 释放资源
                    $imagick->clear();
                    $imagick->destroy();

                    // file_put_contents(__DIR__ . '/../out1.jpg', $jpgImage);
                    return $jpgImage;
                } catch (Exception $e) {
                    // 处理异常
                    return false;
                }
            } else {
                // 如果Imagick不可用，使用GD库
                $gif = imagecreatefromstring($image);
                if ($gif === false) {
                    return false; // 无法创建GIF图像
                }

                // 获取GIF的宽高
                $width = imagesx($gif);
                $height = imagesy($gif);

                // 创建新的空白图像
                $newImage = imagecreatetruecolor($width, $height);
                imagecopy($newImage, $gif, 0, 0, 0, 0, $width, $height);

                // 保存为jpg格式
                ob_start();
                imagejpeg($newImage);
                $jpgImage = ob_get_clean();

                // 释放资源
                imagedestroy($gif);
                imagedestroy($newImage);

                // file_put_contents(__DIR__ . '/../out2.jpg', $jpgImage);
                return $jpgImage;
            }
        } else {
            // 处理其他格式
            writeLog("图片类型为 $mime, 不支持!", level: 1);
            return false; // 或者抛出异常
        }
    }

    private static function enableCheck(&$currentConf, &$_DATA)
    {
        // 配置启用检查
        if (!($currentConf['enable'] ?? true)) return false;

        // 消息类型检查
        $enableTargets = $currentConf['enableGroups'] ?? true;
        $target = $_DATA['sender']['group']['id'];

        if (!($enableTargets === true || (is_array($enableTargets) && in_array($target, $enableTargets)))) {
            return false;
        }

        // skipSpecial 检查
        if (in_array($_DATA['sender']['id'], $currentConf['skipSpecial'] ?? [])) {
            return false;
        }
        return true;
    }

    private static function getConfig(&$_DATA)
    {
        $conf = getConfig();
        if (empty($conf)) {
            $conf = array(
                'enable' => true,
                'enableDesc' => '插件是否启用',
                'enableGroups' => true,
                'enableGroupsDesc' => '本插件在哪些群聊启用 (数组), 使用 true 则在所有群聊启用, 使用 false 在群聊禁用 (默认 true)',
                'skipStaff' => false,
                'skipStaffDesc' => '是否跳过群主和管理员',
                'skipSpecial' => [114514],
                'skipSpecialDesc' => '全局跳过这些QQ号',
                // 'replyPrefix' => '',
                // 'replyPrefixDesc' => '回复前缀',
                'ruleGroups' => [
                    [
                        'enable' => false,
                        'enableDesc' => '是否启用当前规则组 (默认 true)',

                        'apiUrl' => 'http://127.0.0.1:9656/predict',
                        'apiUrlDesc' => 'yolox-onnx-api-server 服务地址',
                        'apiKey' => '',
                        'apiKeyDesc' => 'API 密钥',

                        'enableGroups' => true,
                        'enableGroupsDesc' => '在满足全局设置的情况下, 再额外判断。null 则不进行额外设置 (默认)',

                        'skipStaff' => null,
                        'skipStaffDesc' => '可覆盖上级设置, null 继承上级设置 (默认)',
                        'skipSpecial' => null,
                        'skipSpecialDesc' => '在满足全局设置的情况下, 再额外判断。null 则不进行额外设置 (默认)',
                        'vis' => false,
                        'visDesc' => '图片可视化。启用后将会发送可视化结果。等效false值: 禁用, 1: 在命中规则时发送, 2或其他值: API有检出结果就发送',

                        'action' => [
                            'recall' => true,
                            'recallDesc' => '是否撤回消息',
                            'reply' => false,
                            'replyDesc' => '回复的文本消息。设置为false不回复。'
                        ],
                        'actionDesc' => '触发规则后的动作',

                        'rules' => [
                            [
                                'enable' => false,
                                'enableDesc' => '是否启用当前规则',
                                'enableGroups' => null,
                                'enableGroupsDesc' => '在满足上级设置的情况下, 再额外判断。null 则不进行额外设置 (默认)',

                                'skipStaff' => null,
                                'skipStaffDesc' => '可覆盖上级设置, null 继承上级设置 (默认)',
                                'skipSpecial' => null,
                                'skipSpecialDesc' => '在满足全局设置的情况下, 再额外判断。null 则不进行额外设置 (默认)',
                                'vis' => null,
                                'visDesc' => '可覆盖上级设置, null 继承上级设置',

                                'action' => null,
                                'actionDesc' => '可覆盖上级设置, null 继承上级设置。注意如果设置了该选项, 将会完全覆盖上级 action 设置, 无法单独覆盖某一项动作',

                                'classes' => [
                                    [
                                        'classId' => [1],
                                        'classIdDesc' => '分类ID。支持设置多个, 找到一个就触发。',
                                        'className' => ['nailong'],
                                        'classNameDesc' => '分类名称。支持设置多个, ClassId与ClassName判断关系为或, 只要任意一个类存在都会触发规则。',
                                        'score' => 0.8,
                                        'scoreDesc' => '置信度阈值, 低于这个值的结果将不会触发规则',

                                        'action' => null,
                                        'actionDesc' => '可覆盖上级设置, null 继承上级设置。注意如果设置了该选项, 将会完全覆盖上级 action 设置, 无法单独覆盖某一项动作',
                                    ]
                                ],
                                'classesDesc' => '如果图片类别位于其中将会触发规则。支持设置多个类别, 只要有一个就触发。支持设置类别ID和类别名称。',
                            ]
                        ],
                        'rulesDesc' => '规则列表。任意一条规则命中将会停止继续判断。'
                    ]
                ],
                'ruleGroupsDesc' => '规则组列表。任意一组规则命中将会停止继续判断。'
            );
            saveConfig(config: $conf);
        }
        // 过滤规则组
        foreach ($conf['ruleGroups'] as $groupId => $ruleGroup) {
            // 检查是否在当前对话中启用
            if (!self::enableCheck($ruleGroup, $_DATA)) {
                // 如果未启用 则删除这个规则组
                unset($conf['ruleGroups'][$groupId]);
            }
        }
        // 重整规则组
        $conf['ruleGroups'] = array_values($conf['ruleGroups']);
        return $conf;
    }
});
