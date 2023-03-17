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

class LoginService
{
    /**
     * 检查登录状态
     * @return array
     * @throws \think\admin\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function check(): array
    {
        $result = ApiService::call('login.check');
        if (in_array($result['code'], ['temp', 'done'])) {
            ApiService::saveToken($result['user']['token']);
            unset($result['user']['token']);
        }
        return $result;
    }

    /**
     * 用户绑定主账号
     * @param string $email 用户邮箱
     * @param string $verify 验证编码
     * @return bool
     * @throws \think\admin\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function bind(string $email, string $verify): bool
    {
        $auth = ApiService::call('login.bind', $email, $verify);
        if (empty($auth['user']['token'])) return false;
        return ApiService::saveToken($auth['user']['token']);
    }

    /**
     * 退出登录状态
     * @return bool
     * @throws \think\admin\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function logout(): bool
    {
        ApiService::call('login.logout');
        return ApiService::clearToken();
    }

    /**
     * 发送邮箱验证码
     * @param string $email 用户邮箱
     * @return bool
     * @throws \think\admin\Exception
     */
    public static function sender(string $email): bool
    {
        return ApiService::call('login.sender', $email);
    }
}