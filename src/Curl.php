<?php
/**
 * Curl万能类
 * @Author LuoYan<51085726@qq.com>
 * @Date 2020.06.13
 */

declare (strict_types=1);

namespace LuoYan;

class Curl
{
    private array $header = [];         // 模拟header
    private string $cookie = '';        // 模拟cookie
    private string $ip = '';            // 模拟IP
    private string $userAgent = '';     // 模拟UA
    private string $referer = '';       // 模拟referer
    private string $username = '';      // HTTP认证用户名
    private string $password = '';      // HTTP认证密码
    private string $needHeader = '';    // 是否需要header信息
    private string $noBody = '';        // 是否需要body信息
    private string $returnCookie = '';  // 远程返回的cookie

    /**
     * Curl开始执行
     * @param string $url 要访问的链接
     * @param array $post 是否需要post，以及值
     * @return string 返回网页信息
     */
    public function run(string $url, $post = []): string
    {
        // 初始化
        $curl = curl_init();
        // 超时时间
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        // 避开ssl证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        // 开启GZIP
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        // 设置是否将响应结果存入变量
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // 初始化Url
        curl_setopt($curl, CURLOPT_URL, $url);

        $header = [];
        $header[] = 'Accept: */*';
        $header[] = 'Accept-Language: zh-CN,zh;q=0.8';
        // 模拟来源Ip
        $header[] = 'X-Forwarded-For: ' . $this->ip;
        $header[] = 'Client-Ip: ' . $this->ip;

        // 模拟自定义header
        if ($this->header) {
            foreach ($this->header as $k => $v) {
                $header[] = $k . ': ' . $v;
            }
        }

        // 初始化头部
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        if ($this->username && $this->password) {
            // Http认证
            curl_setopt($curl, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        }

        // 模拟cookies
        curl_setopt($curl, CURLOPT_COOKIE, $this->cookie);
        // 模拟referer
        curl_setopt($curl, CURLOPT_REFERER, $this->referer);

        if ($this->userAgent) {
            curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        } else {
            curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36');
        }

        if ($post) {
            // 表明是post请求
            curl_setopt($curl, CURLOPT_POST, true);
            // post内容
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }

        if ($this->noBody) {
            // 设定是否输出页面内容
            curl_setopt($curl, CURLOPT_NOBODY, true);
        }

        if ($this->needHeader) {
            // 设定是否显示头信息
            curl_setopt($curl, CURLOPT_HEADER, true);
        }

        // 执行请求
        $result = curl_exec($curl);
        // 关闭curl
        curl_close($curl);

        // 提取页面返回的cookies
        $this->returnCookie = $this->getCookie($result);

        return $result;
    }

    /**
     * 解析出网页是否要更新cookies
     * @param string $result Curl的结果
     * @return string cookies
     */
    private function getCookie(string $result): string
    {
        // 解析返回内容
        list($header, $body) = explode('\r\n\r\n', $result);

        // 解析cookie
        $matches = '';
        preg_match('/set\-cookie:([^\r\n]*)/i', $header, $matches);
        return $matches[1];
    }

    /**
     * 设置Curl参数
     * @param string $name 要设置的参数名称
     * @param int|string $value 要设置的值
     */
    public function set(string $name, $value): void
    {
        $this->$name = $value;
    }
}
