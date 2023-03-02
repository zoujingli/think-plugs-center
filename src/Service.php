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

namespace plugin\center;

use think\admin\Plugin;

include __DIR__ . DIRECTORY_SEPARATOR . 'helper.php';

/**
 * 组件注册服务
 * Class Service
 * @package plugin\center
 */
class Service extends Plugin
{
    /**
     * 定义安装包名
     * @var string
     */
    protected $package = 'zoujingli/think-plugs-center';

    /**
     * 定义返回菜单
     * @return array
     */
    public static function menu(): array
    {
        return [];
    }
}