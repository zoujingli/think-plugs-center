<?php

// +----------------------------------------------------------------------
// | Center Plugin for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2024 ThinkAdmin [ thinkadmin.top ]
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

namespace plugin\center\controller;

use plugin\center\Service;
use plugin\center\service\Login;
use plugin\center\service\Plugin;
use think\admin\Controller;
use think\admin\service\AdminService;

/**
 * 插件应用管理
 * Class Index
 * @package plugin\center\controller
 */
class Index extends Controller
{
    /**
     * 管理已安装插件
     * @menu true
     * @login true
     * @return void|\think\Response
     * @throws \ReflectionException
     * @throws \think\admin\Exception
     */
    public function index()
    {
        $this->default = sysdata('plugin.center.config')['default'] ?? '';
        $default = $this->request->get('from') === 'force' ? '' : $this->default;
        if (!empty($default) && Plugin::isInstall($default) && sysvar('CurrentPluginCode', $default)) {
            return json(['code' => 1, 'info' => '已设置默认插件', 'data' => strstr(plguri(), '#', true), 'wait' => 'false']);
        }
        $this->total = [];
        $this->title = '应用插件管理';
        $this->type = $this->request->get('type', Plugin::TYPE_MODULE);
        $this->items = Plugin::getLocalPlugs($this->type, $this->total, true);
        foreach ($this->items as &$vo) {
            $vo['encode'] = encode($vo['code']);
            $vo['center'] = sysuri("layout/{$vo['encode']}", [], false);
        }
        $this->login = Login::check();
        $this->types = Plugin::types;
        $this->fetch();
    }

    /**
     * 显示插件菜单
     * @login true
     * @param string $encode
     * @throws \ReflectionException
     * @throws \think\admin\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function layout(string $encode = '')
    {
        if (empty($code = decode($encode))) {
            $this->error('插件不能为空！');
        }

        sysvar('CurrentPluginCode', $code);
        $this->plugin = \think\admin\Plugin::get($code);
        if (empty($this->plugin)) $this->error('插件未安装！');

        // 读取插件菜单
        $menus = $this->plugin['service']::menu();
        if (empty($menus)) $this->fetchError('插件未配置菜单！');

        foreach ($menus as $k1 => &$one) {
            $one['id'] = $k1 + 1;
            $one['url'] = $one['url'] ?? (empty($one['node']) ? '#' : plguri($one['node']));
            $one['title'] = lang($one['title'] ?? $one['name']);
            if (!empty($one['subs'])) {
                foreach ($one['subs'] as $k2 => &$two) {
                    if (isset($two['node']) && !auth($two['node'])) {
                        unset($one['subs'][$k2]);
                        continue;
                    }
                    $two['id'] = intval($k2) + 1;
                    $two['pid'] = $one['id'];
                    $two['url'] = empty($two['node']) ? '#' : plguri($two['node']);
                    $two['title'] = lang($two['title'] ?? $two['name']);
                }
                $one['sub'] = $one['subs'];
                unset($one['subs']);
            }
            if ($one['url'] === '#' && empty($one['sub']) || (isset($one['node']) && !auth($one['node']))) {
                unset($menus[$k1]);
            }
        }

        /*! 读取当前用户权限菜单树 */
        $this->menus = [
            [
                'id'    => 9999998,
                'url'   => '#',
                'sub'   => $menus,
                'node'  => Service::getAppName(),
                'title' => $this->plugin['name']
            ],
            [
                'id'    => 9999999,
                'url'   => admuri('index/index') . '?from=force',
                'title' => '返回首页'
            ]
        ];

        $this->super = AdminService::isSuper();
        $this->theme = AdminService::getUserTheme();
        $this->title = $this->plugin['name'] ?? '';
        $this->fetch('layout/index');
    }

    /**
     * 设置默认插件
     * @auth true
     * @return void
     * @throws \think\admin\Exception
     */
    public function setDefault()
    {
        $data = $this->_vali(['default.require' => '默认插件不能为空！']);
        sysdata('plugin.center.config', $data);
        $this->success('设置默认插件成功！');
    }

    /**
     * 显示异常模板
     * @return void
     */
    protected function fetchError(string $content)
    {
        $this->content = $content;
        $this->fetch('layout/error');
    }
}