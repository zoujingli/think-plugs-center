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

use plugin\center\service\Login as LoginService;
use think\admin\Controller;

/**
 * 用户登录管理
 * Class Login
 * @package plugin\center\controller
 */
class Login extends Controller
{

    /**
     * 插件用户登录
     * @auth true
     * @throws \think\admin\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function in()
    {
        if ($this->request->isGet()) {
            $info = LoginService::check();
            if ($info['code'] === 'null') $this->error($info['message']);
            if ($info['code'] === 'done') $this->success('已经完成登录！');
            if ($info['code'] === 'temp') $this->fetch('login');
            if ($info['code'] === 'wxqrc') $this->fetch('wxqrc', ['vo' => $info]);
            if ($info['code'] === 'login') $this->fetch('login');
            $this->error('接口服务异常，请稍候再试！');
        } else {
            $data = $this->_vali([
                'email.email'    => '邮箱格式错误！',
                'email.require'  => '邮箱不能为空！',
                'verify.require' => '验证码不能为空！'
            ]);
            if (LoginService::bind($data['email'], $data['verify'])) {
                $this->success('登录成功！');
            } else {
                $this->error('登录失败！');
            }
        }
    }

    /**
     * 检查用户状态
     * @auth true
     * @throws \think\admin\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function check()
    {
        $this->success('当前用户状态', LoginService::check());
    }

    /**
     * 发送邮箱验证码
     * @auth true
     * @throws \think\admin\Exception
     */
    public function sender()
    {
        $data = $this->_vali([
            'email.email'   => '邮箱格式错误！',
            'email.require' => '邮箱不能为空！',
        ]);
        if (LoginService::sender($data['email'])) {
            $this->success('发送验证码成功！');
        } else {
            $this->success('发送验证码失败！');
        }
    }

    /**
     * 退出登录
     * @auth true
     * @throws \think\admin\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function logout()
    {
        LoginService::logout();
        $this->success('退出登录成功！');
    }
}