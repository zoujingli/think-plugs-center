<?php

// +----------------------------------------------------------------------
// | Center Plugin for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2023 ThinkAdmin [ thinkadmin.top ]
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
use think\exception\HttpResponseException;

/**
 * 应用插件服务
 * @class ApiService
 * @package plugin\center\service
 */
class ApiService
{
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
            return self::_create($path)->$name(...$args);
        } catch (Exception $exception) {
            if ($exception->getCode() === 401) {
                try {
                    self::clearToken();
                    return self::_create($path)->$name(...$args);
                } catch (\Exception$exception) {
                    throw new HttpResponseException(json([
                        'code' => $exception->getCode(),
                        'info' => $exception->getMessage()
                    ]));
                }
            }
            throw $exception;
        } catch (\Exception $exception) {
            throw new HttpResponseException(json([
                'code' => $exception->getCode(),
                'info' => $exception->getMessage()
            ]));
        }
    }

    /**
     * 创建请求对象
     * @param string $name 请求接口名称
     * @return \think\admin\extend\JsonRpcClient
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private static function _create(string $name): JsonRpcClient
    {
        if (request()->host() === 'plugin.local.cuci.cc') {
            $rpc = 'http:' . '//plugin.local.cuci.cc/plugin/api/jsonrpc';
        } else {
            $rpc = Support::getServer() . 'plugin/api/jsonrpc';
        }
        $token = Library::$sapp->cache->get('plugin-jwt-token');
        if (empty($token)) $token = sysdata('plugin.login.token')['token'] ?? '';
        return new JsonRpcClient($rpc, ["api-name:{$name}", "api-token:{$token}", "api-client:" . Support::getSysId()]);
    }

    /**
     * 保存请求令牌
     * @param string $token
     * @return boolean
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function saveToken(string $token): bool
    {
        Library::$sapp->cache->set('plugin-jwt-token', $token);
        return !!sysdata('plugin.login.token', [
            'token' => $token, 'utime' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 清除请求令牌
     * @return boolean
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function clearToken(): bool
    {
        Library::$sapp->cache->delete('plugin-jwt-token');
        return !!sysdata('plugin.login.token', []);
    }
}