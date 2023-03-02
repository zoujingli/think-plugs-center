<?php

// +----------------------------------------------------------------------
// | Center Plugin for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2023 Anyon <zoujingli@qq.com>
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/think-plugs-center
// | github 代码仓库：https://github.com/zoujingli/think-plugs-center
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace plugin\center\service;

use think\admin\Exception;
use think\admin\extend\JsonRpcClient;
use think\admin\install\Support;
use think\admin\Library;

/**
 * 应用插件服务
 * Class CenterService
 * @package plugin\center\service
 */
class CenterService
{
    private static $token = '';
    private static $cache = 'jwt-token';

    /**
     * 请用远程接口
     * @param string $uri 调用接口
     * @param array $args 调用参数
     * @return mixed
     * @throws \think\admin\Exception
     */
    public static function call(string $uri = '', ...$args)
    {
        try {
            $uris = explode('.', $uri);
            [$name, $path] = [array_pop($uris), join('.', $uris)];
            return static::_create($path)->$name(...$args);
        } catch (Exception $exception) {
            if ($exception->getCode() === 401) {
                if ('login.token' !== $uri) static::_token(true);
                return static::_create($path)->$name(...$args);
            }
            throw $exception;
        }
    }

    /**
     * 生成会话令牌
     * @param boolean $force
     * @return void
     * @throws \think\admin\Exception
     */
    private static function _token(bool $force = false): void
    {
        if ($force || empty(static::$token)) {
            static::$token = ' '; // 重置令牌并占位
            static::$token = static::call('login.token');
            Library::$sapp->cache->set(static::$cache, static::$token);
        }
    }

    /**
     * 创建请求对象
     * @param string $name 请求接口名称
     * @return \think\admin\extend\JsonRpcClient
     */
    private static function _create(string $name): JsonRpcClient
    {
        $rpc = Support::getServer() . 'plugin/api/jsonrpc';
        $token = static::$token ?: Library::$sapp->cache->get(static::$cache);
        return new JsonRpcClient($rpc, ["jwt-name:{$name}", "jwt-token:{$token}"]);
    }
}