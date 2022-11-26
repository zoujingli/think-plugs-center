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

namespace plugin\center\controller;

use plugin\center\service\PluginService;
use think\admin\Controller;

/**
 * 插件市场管理
 * Class Store
 * @package plugin\center\controller
 */
class Store extends Controller
{
    /**
     * 插件市场
     * @auth true
     * @menu true
     * @return void
     * @throws \think\admin\Exception
     */
    public function index()
    {
        $this->title = '插件市场';
        $this->items = PluginService::getLocalPlugs();
        $this->fetch();
    }

    /**
     * 显示插件详情
     * @auth true
     * @menu true
     * @return void
     * @throws \think\admin\Exception
     */
    public function show()
    {
        $this->title = '插件详情';
        $this->name = $this->get['code'] ?? '';
        $this->data = PluginService::get($this->name);
        $this->data['version'] = PluginService::getLocalLibrarys($this->data['package']);
        if (empty($this->data)) $this->error('无效的插件标识！');
        $this->fetch();
    }
}