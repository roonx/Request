<?php

namespace roonx;


class Request
{
    //region Method
    const METHOD_DEF = 'GET';
    const GET = 'GET';
    const HEAD = 'HEAD';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const CONNECT = 'CONNECT';
    const OPTIONS = 'OPTIONS';
    const TRACE = 'TRACE';
    //endregion Method

    //region Proxy Protocol
    public static $httpProxy = CURLPROXY_HTTP;
    public static $httpsProxy = CURLPROXY_HTTPS;
    public static $Socks4Proxy = CURLPROXY_SOCKS4;
    public static $Socks4aProxy = CURLPROXY_SOCKS4A;
    public static $Socks5Proxy = CURLPROXY_SOCKS5;
    public static $Socks5HProxy = CURLPROXY_SOCKS5_HOSTNAME;
    //endregion Proxy Protocol

    //region Curl Config
    private static $curl = null;
    private static $curlOpts = [];
    private static $header = [];
    private static $headerbool = false;
    private static $cookie = false;
    private static $cookieFile = './cookie.txt';
    private static $userAgent = 'Roonx/Request project';
    private static $timeOut = null;
    private static $auth = [
        'user' => '',
        'pass' => '',
        'method' => CURLAUTH_BASIC
    ];
    private static $proxy = [
        'address' => false,
        'port' => false,
        'tunnel' => false,
        'type' => CURLPROXY_HTTP,
        'auth' => [
            'user' => '',
            'pass' => '',
            'method' => CURLAUTH_BASIC
        ]
    ];
    private static $verifyPeer = true;
    private static $verifyHost = true;

    //endregion Curl Config

    public static $http_code = null;
    public static $response = null;
    public static $body = null;
    public static $header_size = null;

    //region funcOther

    public static function clearOpt()
    {
        self::$curlOpts = [];
        return new static();
    }


    private static function mergeCurlOptions(&$existing_options, $new_options)
    {
        return $new_options + $existing_options;
    }

    public static function checkValidMethod($method): void
    {
        if (!in_array($method, [self::GET, self::CONNECT, self::POST, self::DELETE, self::HEAD, self::OPTIONS, self::PUT, self::TRACE])) {
            throw new \Exception('Not Valid Method');
        }
    }

    public static function buildHTTPCurlQuery($data, $parent = false)
    {
        $result = [];

        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        foreach ($data as $key => $value) {
            if ($parent) {
                $new_key = sprintf('%s[%s]', $parent, $key);
            } else {
                $new_key = $key;
            }

            if (!$value instanceof \CURLFile and (is_array($value) or is_object($value))) {
                $result = array_merge($result, self::buildHTTPCurlQuery($value, $new_key));
            } else {
                $result[$new_key] = $value;
            }
        }

        return $result;
    }


    public static function enbaleHeader()
    {
        return self::$headerbool = true;
    }
    //endregion funcOther

    //region funcSetter


    public static function setUserAgent($userAgent)
    {
        self::$userAgent = $userAgent;
        return new static();
    }

    public static function setProxy($address, $port = 1080, $type = CURLPROXY_HTTP, $tunnel = false)
    {
        self::$proxy['type'] = $type;
        self::$proxy['port'] = $port;
        self::$proxy['tunnel'] = $tunnel;
        self::$proxy['address'] = $address;

        return new static();
    }

    public static function setProxyAuth($username = '', $password = '', $method = CURLAUTH_BASIC)
    {
        self::$proxy['auth']['user'] = $username;
        self::$proxy['auth']['pass'] = $password;
        self::$proxy['auth']['method'] = $method;
        return new static();

    }

    public static function setAuth($username = '', $password = '', $method = CURLAUTH_BASIC)
    {

        self::$auth['user'] = $username;
        self::$auth['pass'] = $password;
        self::$auth['method'] = $method;
        return new static();

    }

    public static function enableCookie()
    {
        self::$cookie = true;
        return new static();
    }

    public static function setCookieFile($file)
    {
        self::$cookieFile = $file;
        return new static();
    }

    public static function setTimeOut($seconds)
    {
        self::$timeOut = $seconds;
        return new static();
    }


    //endregion funcSetter

    //region funcGetter
    public static function getUserAgent()
    {
        return self::$userAgent;
    }

    public static function getHeader()
    {
        return self::$header;
    }

    //endregion funcGetter

    //region funcAdd
    public static function addOpt($options, $value = null)
    {
        if (!is_array($options)) {
            self::$curlOpts[$options] = $value;
        } else {
            self::addOptArray($options);
        }
        return new static();
    }

    public static function addOptArray($array)
    {
        foreach ($array as $name => $value) {
            self::$curlOpts[$name] = $value;
        }
        return new static();

    }

    public static function addHeaderArray($array)
    {
        foreach ($array as $name => $value) {
            self::$header[$name] = $value;
        }
        return new static();

    }

    public static function addHeader($header)
    {

        self::$header = array_merge(self::$header, is_array($header) ? $header : [$header]);
        return new static();
    }
    //endregion funcAdd


    //region Curl method
    public static function getInfo($opt = false)
    {
        if ($opt) {
            return curl_getinfo(self::$curl, $opt);
        }

        return curl_getinfo(self::$curl);
    }

    public static function GetHttpCode()
    {
        return self::$http_code;
    }

    public static function verifyPeer($enabled)
    {
        return self::$verifyPeer = $enabled;
    }

    public static function verifyHost($enabled)
    {
        return self::$verifyHost = $enabled;
    }

    //endregion Curl method

    //region Send data by method
    public static function get($url, $body = null)
    {
        return self::send(self::GET, $url, $body);
    }

    public static function post($url, $body = null)
    {
        return self::send(self::POST, $url, $body);
    }

    public static function put($url, $body = null)
    {
        return self::send(self::PUT, $url, $body);
    }

    public static function head($url, $body = null)
    {
        return self::send(self::HEAD, $url, $body);
    }

    public static function delete($url, $body = null)
    {
        return self::send(self::DELETE, $url, $body);
    }

    public static function trace($url, $body = null)
    {
        return self::send(self::TRACE, $url, $body);
    }

    public static function options($url, $body = null)
    {
        return self::send(self::OPTIONS, $url, $body);
    }

    public static function connecy($url, $body = null)
    {
        return self::send(self::CONNECT, $url, $body);
    }

    //endregion Send data by method


    public function __call($methodName, $args)
    {
        if (method_exists($this, $methodName)) {
            try {
                self::$methodName($args);
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
        } else {
            throw new \Exception('Not Found ' . $methodName . ' Method');
        }
    }

    public static function send($method, string $url, $body = null)
    {
        self::checkValidMethod($method);
        self::$curl = curl_init();
        $curl = self::$curl;
        if ($method !== self::GET) {
            if ($method === self::POST) {
                curl_setopt($curl, CURLOPT_POST, true);
            } else {
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            }

            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        } elseif (is_array($body)) {
            if (strpos($url, '?') !== false) {
                $url .= '&';
            } else {
                $url .= '?';
            }

            $url .= urldecode(http_build_query(self::buildHTTPCurlQuery($body)));
        }

        $curl_options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTPHEADER => self::$header,
            CURLOPT_HEADER => self::$headerbool,
            CURLOPT_SSL_VERIFYPEER => self::$verifyPeer === false ? 0 : 2,
            CURLOPT_SSL_VERIFYHOST => self::$verifyHost === false ? 0 : 2,
            CURLOPT_ENCODING => ''
        ];
        curl_setopt_array($curl, self::mergeCurlOptions($curl_options, self::$curlOpts));

        if (self::$timeOut !== null) {
            curl_setopt($curl, CURLOPT_TIMEOUT, self::$timeOut);
        }

        if (self::$cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, self::$cookie);
            curl_setopt($curl, CURLOPT_COOKIEFILE, self::$cookieFile);
            curl_setopt($curl, CURLOPT_COOKIEJAR, self::$cookieFile);
        }

        if (!empty(self::$auth['user'])) {
            curl_setopt_array($curl, [
                CURLOPT_HTTPAUTH => self::$auth['method'],
                CURLOPT_USERPWD => self::$auth['user'] . ':' . self::$auth['pass']
            ]);
        }

        if (self::$proxy['address'] !== false) {
            curl_setopt_array($curl, array(
                CURLOPT_PROXYTYPE => self::$proxy['type'],
                CURLOPT_PROXY => self::$proxy['address'],
                CURLOPT_PROXYPORT => self::$proxy['port'],
                CURLOPT_HTTPPROXYTUNNEL => self::$proxy['tunnel'],
                CURLOPT_PROXYAUTH => self::$proxy['auth']['method'],
                CURLOPT_PROXYUSERPWD => self::$proxy['auth']['user'] . ':' . self::$proxy['auth']['pass']
            ));
        }
        $response = curl_exec($curl);
        $error = curl_error($curl);
        $info = self::getInfo();

        if ($error) {
            throw new \Exception($error);
        }
        self::$body = $response;
        self::$http_code = $info['http_code'];

        return $response;

    }
}
