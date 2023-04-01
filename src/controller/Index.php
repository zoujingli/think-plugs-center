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

namespace plugin\center\controller;

use plugin\center\service\Login;
use plugin\center\service\Plugin;
use think\admin\Controller;
use think\admin\service\AdminService;
use think\admin\service\MenuService;
use think\admin\service\RuntimeService;

/**
 * 插件应用管理
 * Class Index
 * @package plugin\center\controller
 */
class Index extends Controller
{
    /**
     * 管理已安装插件
     * @auth true
     * @menu true
     * @throws \think\admin\Exception
     */
    public function index()
    {
        $this->title = '管理已安装插件';
        $this->total = [];
        $this->login = Login::check();
        $this->type = $this->request->get('type', 'module');
        $this->items = Plugin::getLocalPlugs($this->type, $this->total);
        $this->types = Plugin::types;
        $this->fetch();
    }

    /**
     * 显示插件菜单
     * @param string $code
     * @throws \ReflectionException
     * @throws \think\admin\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function layout(string $code = '')
    {
        $code = decode($code);
        if (empty($code)) {
            $this->error('操作标识不能为空！');
        }

        RuntimeService::swap('plugin-code', $code);
        $this->plugin = \think\admin\Plugin::all($code);

        if (empty($this->plugin)) $this->error('插件未安装！');

        // 读取插件菜单
        $menus = $this->plugin['service']::menu();
        if (empty($menus)) $this->fetchError('插件未配置菜单！');

        foreach ($menus as $k1 => &$one) {
            $one['id'] = $k1 + 1;
            $one['url'] = $one['url'] ?? (empty($one['node']) ? '#' : plguri($one['node']));
            $one['title'] = $one['title'] ?? $one['name'];
            if (!empty($one['subs'])) {
                foreach ($one['subs'] as $k2 => &$two) {
                    if (isset($two['node']) && !auth($two['node'])) {
                        unset($one['subs'][$k2]);
                        continue;
                    }
                    $two['id'] = intval($k2) + 1;
                    $two['pid'] = $one['id'];
                    $two['url'] = empty($two['node']) ? '#' : plguri($two['node']);
                    $two['title'] = $two['title'] ?? $two['name'];
                }
                $one['sub'] = $one['subs'];
                unset($one['subs']);
            }
            if ($one['url'] === '#' && empty($one['sub']) || (isset($one['node']) && !auth($one['node']))) {
                unset($menus[$k1]);
            }
        }

        array_unshift($menus, [
            'id' => 0, 'url' => admuri('index/index'), 'icon' => 'layui-icon layui-icon-prev', 'title' => '返回插件中心',
        ]);

        /*! 读取当前用户权限菜单树 */
        $this->menus = MenuService::getTree();
        foreach ($this->menus as &$menu) {
            if ($menu['node'] === 'plugin-center/index/index') {
                $menu['url'] = '#';
                $menu['sub'] = $menus;
            }
        }

        $this->super = AdminService::isSuper();
        $this->theme = AdminService::getUserTheme();
        $this->fetch('layout/index');
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