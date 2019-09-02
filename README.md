# SSL Expire Scan
SSL 证书到期时间检测，使用 PHP 开发

## 使用方法
将项目 clone 到本地，然后在命令行输入
```bash
php main.php /path/to/ssl/directory/
```
示例（扫描 Nginx 的 SSL 目录下所有证书到期时间）
```bash
php main.php /usr/local/nginx/conf/ssl/
```
也可以对指定证书的到期时间进行扫描
```bash
php main.php /usr/local/nginx/conf/ssl/natfrp.org.crt
```
可以将以下内容加入到 /etc/bashrc 以方便随时使用：
```bash
alias sslscan='php /path/to/main.php'
```
## 未来计划
- [ ] 支持定期自动扫描
- [ ] 支持到期邮件通知
- [ ] 还在想

## 开源协议
本项目使用 GPL v3 协议开源
