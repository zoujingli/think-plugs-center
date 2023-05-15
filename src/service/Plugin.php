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

namespace plugin\center\service;

use think\admin\service\ModuleService;

/**
 * 插件数据服务
 * @class Plugin
 * @package plugin\center\service
 */
abstract class Plugin
{
    const TYPE_MODULE = 'module';
    const TYPE_PLUGIN = 'plugin';
    const TYPE_SERVICE = 'service';
    const TYPE_LIBRARY = 'library';

    const types = [
        self::TYPE_MODULE  => '应用模块',
        self::TYPE_PLUGIN  => '功能插件',
        self::TYPE_SERVICE => '基础服务',
        self::TYPE_LIBRARY => '开发组件',
    ];

    /**
     * 获取本地插件
     * @param ?string $type 插件类型
     * @param array $total 类型统计
     * @return array
     * @throws \think\admin\Exception
     */
    public static function getLocalPlugs(?string $type = null, array &$total = []): array
    {
        $data = [];
        $onlines = static::getOnlinePlugs();
        $librarys = ModuleService::getLibrarys();
        foreach (\think\admin\Plugin::all() as $pluginCode => $plugin) {
            if (!isset($librarys[$plugin['package']])) continue;
            $online = $onlines[$plugin['package']] ?? [];
            $library = $librarys[$plugin['package']];
            $pluginType = $library['type'] ?? '';
            $total[$pluginType] = ($total[$pluginType] ?? 0) + 1;
            if (is_string($type) && $pluginType !== $type) continue;
            $data[$plugin['package']] = [
                'type'      => $pluginType,
                'code'      => $pluginCode,
                'name'      => $online['name'] ?? ($library['name'] ?? ''),
                'cover'     => $online['cover'] ?? ($library['cover'] ?? ''),
                'amount'    => $online['amount'] ?? '0.00',
                'remark'    => $online['remark'] ?? ($library['description'] ?? ''),
                'version'   => $library['version'],
                'service'   => $plugin['service'],
                'package'   => $plugin['package'],
                'license'   => $online['license'] ?? (empty($library['license']) ? 'unknow' : $library['license'][0]),
                'licenses'  => $online['license_name'] ?? (empty($online['amount'] ?? '0.00') ? "免费使用" : "收费插件"),
                'platforms' => empty($plugin['platforms']) ? ($online['platforms'] ?? []) : $plugin['platforms'],
                'plugmenus' => $plugin['service']::menu(),
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
        foreach (Api::call('plugin.all') as $item) {
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
        return Api::call('plugin.get', $name);
    }
}