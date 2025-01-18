# CommentPusher - Typecho评论微信推送插件

基于 WxPusher 实现的 Typecho 评论微信推送插件，当博客收到新评论时，将通过微信向您推送通知。

## 使用前准备

### 1. 获取 WxPusher 的配置信息

1. 访问 [WxPusher官网](https://wxpusher.zjiecode.com/docs/#/) 并使用微信扫码登录

2. 创建应用
   - 点击顶部导航栏的「应用管理」
   - 点击「新建应用」
   - 填写应用名称（如：我的博客评论通知）
   - 复制生成的 `appToken`，这就是插件需要的 AppToken

3. 获取 UID
   - 点击顶部导航栏的「用户管理」
   - 使用微信扫描二维码关注您的应用
   - 关注后，在用户列表中可以看到您的 UID
   - 复制该 UID，这就是插件需要的 UID

### 2. 安装插件

1. [下载插件](https://github.com/flyhunterl/CommentPusher/releases)
2. 将插件解压到 Typecho 的 `/usr/plugins` 目录
3. 确保插件目录名为 `CommentPusher`

### 3. 配置插件

1. 登录 Typecho 后台
2. 进入「控制台」->「插件」
3. 找到「CommentPusher」插件，点击「启用」
4. 点击「设置」按钮
5. 填入您之前获取的 AppToken 和 UID
6. 保存配置

## 功能特点

- 实时推送评论通知到微信
- 通知内容包含：
  - 评论者昵称
  - 评论内容
  - 评论时间
  - 评论的文章标题
- 支持 SSL 安全连接
- 异常处理和错误日志

## 常见问题

1. 没有收到推送？
   - 检查 AppToken 和 UID 是否正确填写
   - 确认是否已关注 WxPusher 的应用
   - 检查服务器是否能访问 WxPusher 的 API

2. 推送失败？
   - 查看网站的错误日志获取详细信息
   - 确认 PHP 的 curl 扩展已启用

## 技术支持

如有问题，请访问 [GitHub Issues](https://github.com/flyhunterl/CommentPusher/issues) 反馈。

## 开源协议

MIT License

## 致谢

感谢 [WxPusher](https://wxpusher.zjiecode.com) 提供的推送服务。 