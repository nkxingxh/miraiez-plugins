# miraiez-plugins

## 简介 / Introduction 

该仓库用于存放 MiraiEz 的公共插件。

被收入进此仓库的插件可使用**插件管理器**进行安装。

## 收录插件 / Income

### 收录要求 / Require

**如果你打算提交你的插件, 建议做到以下几点**

1. 在插件开头写一点注释, 在注释中 注明 插件开发者的联系方式 (例如 邮箱 [推荐]、QQ、微信等) 以及 Github 账号。这将有助于后续发布插件更新时的身份验证

1. 同一个开发者的插件, 包名开头应该一致 (例如: com.example.plugin1, com.example.plugin2, ...)

1. 插件版本请使用 「PHP 规范化」的版本数字字符串 (例如: 1.0.0, 1.0.1, 1.0.2, ...) (请参阅: https://www.php.net/manual/zh/function.version-compare.php)

1. 请务必仔细阅读这个示例插件的说明, 并对本框架的核心函数有一定的了解 (请查看 core.php、easyMirai.php、pluginsHelp.php 等文件,后续会有比较完备的 Wiki 供开发者参考)

### 收录方式

我们提供多种收录公共插件的方式

#### 1. Pull request

直接提交 Pull request, 在审核通过后会并入本仓库。

#### 2. Issue

可以在此仓库中提出 Issue 并提供你插件源码的仓库地址 （可以是 Github, Gitee 等)。

如果符合条件, 则我们会将你的插件收录。

#### 3. Contact us
 
NKXingXh : [Github](https://github.com/nkxingxh) </br>

Mr-XieXuan : [Github](https://github.com/MR-XieXuan) </br>

## 插件列表 / Plugins

以下插件都在 **MiraiEz v2** 版本中受支持

| 作者 | 名称 | 说明 | 包名 |
|------|-----|------|------|
| [NKXingXh](https://github.com/nkxingxh) | examplePlugin | 示例插件 | top.nkxingxh.examplePlugin |
| [NKXingXh](https://github.com/nkxingxh) | MiraiEzCommand | MiraiEz 命令支持前置插件 | top.nkxingxh.MiraiEzCommand |
| [NKXingXh](https://github.com/nkxingxh) | exampleCmdReg | 示例命令注册插件 | top.nkxingxh.exampleCmdReg |
| [NKXingXh](https://github.com/nkxingxh) | 自动处理事件请求 | 根据配置自动处理对应事件的请求 | top.nkxingxh.miraiez.autoRespRequestEvent |
| [MR-XieXuan](https://github.com/MR-XieXuan) | runoobC | 运行代码 | xyz.mrxie.runoobC |
| [Shiroi](https://github.com/hcr707305003) | baiduSearch | 百度搜索 | top.shiroi.baiduSearch |
| [Shiroi](https://github.com/hcr707305003) | moreImage | 获取图片~摩多摩多 | top.shiroi.moreImage |
| [Shiroi](https://github.com/hcr707305003) | poetry | 诗词 | top.shiroi.poetry |
| [Shiroi](https://github.com/hcr707305003) | replaceMasterBeat | 代替主人反击插件 | top.shiroi.replaceMasterBeat |
| [smikuy](https://github.com/Hatsune-Miku-001) | MainPlugin | 群管主插件 | com.smikuy.main |
| [smikuy](https://github.com/Hatsune-Miku-001) | SQLPlugin | 群管插件数据库前置 | com.smikuy.sql |
