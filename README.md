# Typecho Manager

> 你的私人Tyepcho助理

---

>作者:[cxbsoft](https://blog.bsot.cn)
>
>使用语言: PHP72
>
>安装: [Gihub](https://github.com/cxb-soft/Typecho_Manager)
>
>主要功能: 提供Typecho管理服务

> 联系方式
>
> QQ : 3319066174
>
> 邮箱 : cxbsoft@bsot.cn
>
> Telegram : [@cxbsoft](https://t.me/cxbsoft)

## 使用方法

### 安装 

将```typecho_manager_setup.zip```放入博客目录下的```usr/plugins```目录并解压，得到TypechoManager文件夹,将```typecho_manager_setup.zip```删除，安装完成

### 配置

1. 登陆Typecho后台，进入插件设置页面，启用插件

   ![启用插件](https://tva1.sinaimg.cn/large/008i3skNgy1gshffj17amj32060jk46e.jpg)

2. 进入配置页面后，点击```设置```对插件进行设置

   ![进入配置](https://tva1.sinaimg.cn/large/008i3skNgy1gshfhsjgdlj31u608c3ze.jpg)

3. 配置说明

   ![设置页面](https://tva1.sinaimg.cn/large/008i3skNgy1gshfixgd3aj31ci0u0as8.jpg)

   >中文情感检测API:本API是作者自己做的神经网络(感谢大家的支持)，可能会有些不准，但是大部分还是可以的。如果要更换其他API，请保证数据为以下格式：``

```json
{
  "code" : 200,
  "emotion" : 1 //1代表正面情绪,0代表负面情绪
}
```

>TG BOT token : Telegram推送机器人的token，默认即可，当然也可以用自己的

>Chat ID : 你Telegram的id,可以从@typechomgbot或@getidbot获取

> URL检查网址:用于评论url安全检测,默认即可,如要更换,请保证数据为以下格式:

```JSON
{
  "wordtit" : "", // 空则为安全,不空就是不安全
  "word" : "" // 用于推送telegram时显示的问题url的问题所在
}
```

4. 配置完成后,保存配置即可。

## 功能说明

### Telegram推送

​	这是相当于是一个总开关，打开它，当有人在你的博客评论时，评论的信息（包括评论的内容，情感检测结果，url安全检测结果）就是通过telegram bot推送到指定的chatid(也就是接受信息的用户)

### 情感检测

​		通过大量数据训练的神经网络，对评论的主观情感做辅助预判,辅助判断评论是正面的还是负面的。

### URL安全检测

​	当评论中包含url时,会将url提取并判断url是否安全。



## 以上为使用说明



