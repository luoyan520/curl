<?php
declare (strict_types=1);

namespace LuoYan;

class Curl
{
    private string $cookie = '';        //获取到的cookie
    private string $ip = '';            // 模拟IP
    private string $ua = '';            // 模拟UA
    private string $referer = '';       // 模拟referer
    private string $user = '';          // HTTP认证用户名
    private string $password = '';      // HTTP认证密码
    private string $needHeader = '';    // 是否需要header信息
    private string $noBody = '';        // 是否需要body信息

    /**
     * Curl开始执行
     *
     * @param string $url 要访问的链接
     * @param array $post 是否需要post，以及值
     * @return string 返回网页信息
     */
    public function exec(string $url, $post = []): string
    {
        $curl = curl_init();                                //初始化
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);            // 超时时间
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  // 避开ssl证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  // 避开ssl证书检查
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');       // 开启GZIP
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);      // 设置是否将响应结果存入变量

        $head_init = array();
        $head_init[] = 'Accept:*/*';
        $head_init[] = 'Accept-Encoding:gzip,deflate,sdch';
        $head_init[] = 'Accept-Language:zh-CN,zh;q=0.8';
        $head_init[] = "X-FORWARDED-FOR:{$this->ip}";           //模拟IP
        $head_init[] = "CLIENT-IP: {$this->ip}";
        curl_setopt($curl, CURLOPT_HTTPHEADER, $head_init);     // 初始化头部

        if ($this->user && $this->password) {
            curl_setopt($curl, CURLOPT_USERPWD, $this->user . ':' . $this->password); // HTTP认证
        }
        curl_setopt($curl, CURLOPT_COOKIE, $this->cookie);      // 模拟cookies
        curl_setopt($curl, CURLOPT_REFERER, $this->referer);    // 模拟referer

        if ($this->ua) {
            curl_setopt($curl, CURLOPT_USERAGENT, $this->ua);
        } else {
            curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36');
        }

        curl_setopt($curl, CURLOPT_URL, $url);   // 初始化URL

        if (!empty($post)) {                                // post请求
            curl_setopt($curl, CURLOPT_POST, 1);            // 表明是post请求
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);  // post内容
        }

        if ($this->noBody) {
            curl_setopt($curl, CURLOPT_NOBODY, 1);  // 设定是否输出页面内容，非0不输出
        }

        if ($this->needHeader) {
            curl_setopt($curl, CURLOPT_HEADER, TRUE);   // 设定是否显示头信息
        }
        $result = curl_exec($curl);
        curl_close($curl);

        $this->getCookie($result);

        return $result;
    }

    /**
     * 解析出网页是否要更新cookies
     *
     * @param string $result Curl的结果
     */
    private function getCookie(string $result): string
    {
        // 解析返回内容
        list($header, $body) = explode("\r\n\r\n", $result);

        // 解析cookie
        $matches = '';
        preg_match("/set\-cookie:([^\r\n]*)/i", $header, $matches);
        $cookie = $matches[1];

        if (!empty($cookie)) {
            $this->cookie .= $cookie;
        }

        return $cookie;
    }

    /**
     * 设置Curl参数
     *
     * @param string $name 要设置的参数名称
     * @param int|string $value 要设置的值
     */
    public function set(string $name, $value): void
    {
        $this->$name = $value;
    }
}
