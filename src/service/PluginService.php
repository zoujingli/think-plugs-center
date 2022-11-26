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

namespace plugin\center\service;

use think\admin\Plugin;
use think\admin\service\ModuleService;

/**
 * 插件数据服务
 * Class PluginService
 * @package plugin\center\service
 */
class PluginService
{
    /**
     * 当前插件标识
     * @var string
     */
    private static $code;

    /**
     * 获取本地插件
     * @return array
     * @throws \think\admin\Exception
     */
    public static function getLocalPlugs(): array
    {
        $data = [];
        $onlines = static::getOnlinePlugs();
        $librarys = ModuleService::getLibrarys();
        foreach (Plugin::all() as $code => $plugin) {
            $online = $onlines[$plugin['package']] ?? [];
            $library = $librarys[$plugin['package']] ?? [];
            $data[$plugin['package']] = [
                'type'      => $online['type'] ?? 'local',
                'code'      => $code,
                'name'      => empty($online['name']) ? ($library['name'] ?? '') : $online['name'],
                'cover'     => empty($online['cover']) ? ($library['cover'] ?? '') : $online['cover'],
                'amount'    => $online['amount'] ?? '0.00',
                'remark'    => empty($online['remark']) ? ($library['description'] ?? '') : $online['remark'],
                'version'   => $library['version'],
                'service'   => $plugin['service'],
                'package'   => $plugin['package'],
                'license'   => empty($library['license']) ? [] : $library['license'],
                'platforms' => empty($plugin['platforms']) ? ($online['platforms'] ?? []) : $plugin['platforms'],
                'mymenus'   => $plugin['service']::menu(),
            ];
        }
        return $data;
    }

    /**
     * 获取市场插件
     * @return array
     * @throws \think\admin\Exception
     */
    public static function getOnlinePlugs(): array
    {
        $data = [];
        foreach (CenterService::call('plugin.all') as $item) {
            $data[$item['package']] = $item;
        }
        return $data;
    }

    /**
     * 获取插件信息
     * @param string $name
     * @return mixed
     * @throws \think\admin\Exception
     */
    public static function get(string $name = '')
    {
        return CenterService::call('plugin.get', $name);
    }

    /**
     * 获取插件标识
     * @return string
     */
    public static function getCode(): string
    {
        return static::$code;
    }

    /**
     * 设置插件标识
     * @param string $code
     * @return string
     */
    public static function setCode(string $code = ''): string
    {
        return static::$code = $code;
    }
}