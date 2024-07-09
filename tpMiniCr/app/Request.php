<?php

namespace app;


class Request extends \think\Request
{

    /**
     * 不过滤变量名
     * @var array
     */
    protected array $except = [];

    /**
     * 获取请求的数据
     * @param array $params 请求参数
     * @param bool $suffix 是否按索引排序
     * @param bool $filter 是否过滤参数
     * @return array
     */
    public function more(array $params, bool $suffix = true, bool $filter = true): array
    {
        $p = [];
        $i = 0;
        foreach ($params as $param) {
            if (!is_array($param)) {
                $p[$suffix ? $i++ : $param] = $this->filterWord(is_string($this->param($param)) ? trim($this->param($param)) : $this->param($param), $filter && !in_array($param, $this->except));
            } else {
                $param[1] = $param[1] ?? null;
                $param[2] = $param[2] ?? '';
                if (is_array($param[0])) {
                    $name = is_array($param[1]) ? $param[0][0] . '/a' : $param[0][0] . '/' . $param[0][1];
                    $keyName = $param[0][0];
                } else {
                    $name = is_array($param[1]) ? $param[0] . '/a' : $param[0];
                    $keyName = $param[0];
                }

                $p[$suffix ? $i++ : ($param[3] ?? $keyName)] = $this->filterWord(
                    is_string($this->param($name, $param[1], $param[2])) ?
                        trim($this->param($name, $param[1], $param[2])) :
                        $this->param($name, $param[1], $param[2]),
                    $filter && !in_array($keyName, $this->except));
            }
        }
        return $p;
    }

    /**
     * 过滤接受的参数
     * @param $str
     * @param bool $filter
     * @return array|mixed|string|string[]
     */
    public function filterWord($str, bool $filter = true)
    {
        if (!$str || !$filter) return $str;
        // 把数据过滤
        $farr = [
            "/<(\\/?)(script|i?frame|style|html|body|title|link|meta|object|\\?|\\%)([^>]*?)>/isU",
            "/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU",
            '/phar/is',
            "/select|join|where|drop|like|modify|rename|insert|update|table|database|alter|truncate|\'|\/\*|\.\.\/|\.\/|union|into|load_file|outfile/is"
        ];
        if (is_array($str)) {
            foreach ($str as &$v) {
                if (is_array($v)) {
                    foreach ($v as &$vv) {
                        if (!is_array($vv)) {
                            $vv = $this->replaceWord($farr, $vv);
                        }
                    }
                } else {
                    $v = $this->replaceWord($farr, $v);
                }
            }
        } else {
            $str = $this->replaceWord($farr, $str);
        }
        return $str;
    }

    public function replaceWord($farr, $str): array|string|null
    {
        if (filter_var($str, FILTER_VALIDATE_URL)) {
            $url = parse_url($str);
            if (!isset($url['scheme'])) return $str;
            $host = $url['scheme'] . '://' . $url['host'];
            $str = $host . preg_replace($farr, '', str_replace($host, '', $str));
        } else {
            $str = preg_replace($farr, '', $str);
        }
        return $str;
    }

    /**
     * 获取get参数
     * @param array $params 请求参数
     * @param bool $suffix 是否按索引排序
     * @param bool $filter 是否过滤参数
     * @return array
     */
    public function getMore(array $params, bool $suffix = true, bool $filter = true): array
    {
        return $this->more($params, $suffix, $filter);
    }

    /**
     * 获取用户访问端
     * @return array|string|null
     */
    public function getFromType(): array|string|null
    {
        return $this->header('Form-type', '');
    }

    /**
     * 当前访问端
     * @param string $terminal
     * @return bool
     */
    public function isTerminal(string $terminal): bool
    {
        return strtolower($this->getFromType()) === $terminal;
    }

    /**
     * 是否是H5端
     * @return bool
     */
    public function isH5(): bool
    {
        return $this->isTerminal('h5');
    }

    /**
     * 是否是微信小程序端
     * @return bool
     */
    public function isWechat(): bool
    {
        return $this->isTerminal('wechat');
    }


    /**
     * 是否是APP端
     * @return bool
     */
    public function isApp(): bool
    {
        return $this->isTerminal('app');
    }

    /**
     * 是否是PC端
     * @return bool
     */
    public function isPc(): bool
    {
        return $this->isTerminal('pc');
    }
}
