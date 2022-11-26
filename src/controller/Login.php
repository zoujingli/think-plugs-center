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

namespace plugin\center\controller;

use plugin\center\service\CenterService;
use think\admin\Builder;
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
     * @return void
     * @throws \think\admin\Exception
     */
    public function in()
    {
        if ($this->request->isGet()) {
            $this->captcha = CenterService::call('login.captcha');
            Builder::mk()
                ->addTextInput('username', '登录邮箱', 'Email', true, '用户登录账号，请输入登录的邮箱账号！')
                ->addPassInput('password', '登录密码', 'Password', true, '用户登录密码，请输入登录账号的密码！')
                // ->addTextInput('wechat', '微信账号', 'WeChat', true, '请填写你的微信号')
                ->addSubmitButton('立即登录')
                ->addCancelButton('取消登录')
                ->fetch();
        } else {
            $data = $this->_vali([
                'email.email'      => '电子邮箱格式错误！',
                'email.require'    => '电子邮箱不能为空！',
                'verify.require'   => '图形验证不能为空！',
                'uniqid.require'   => '图形标识不能为空！',
                'password.require' => '登录密码不能为空！',
            ]);
            $data['password'] = md5($data['password'] . $data['uniqid']);
            $this->user = CenterService::call('login.in', $data['email'], $data['verify'], $data['password']);
            if (empty($this->user)) {
                $this->error('登录失败！');
            } else {
                $this->success('登录成功！');
            }
        }
    }

    /**
     * 用户注册管理
     * @auth true
     * @return void
     */
    public function register()
    {
        if ($this->request->isGet()) {
            Builder::mk()
                ->addTextInput('username', '登录邮箱', 'Email', true, '请输入常用邮箱，该邮箱账号用于登录授权！', 'email')
                ->addTextInput('password', '登录密码', "Password", true, '请输入登录密码，密码长度不得少于6位字符！', '.{6,}')
                ->addTextInput('wechat', '常用微信号', 'WeChat', false, '请输入常用微信号，前期已入会的会员将通过此微信号绑定！')
                ->addSubmitButton('立即注册')
                ->addCancelButton('取消注册')
                ->fetch();
        } else {
            $data = $this->_vali([
                'username.email'   => '邮箱账号格式错误！',
                'username.require' => '邮箱账号不能为空！',
                'password.require' => '登录密码不能为空！',
                'wechat.default'   => '',
            ]);
            $result = CenterService::call('login.register', $data);
            $this->success('请求成功！', $data, $result);
        }
    }

    /**
     * 找回登录密码
     * @auth true
     * @return void
     */
    public function forget()
    {
        $data = $this->_vali([
            'mail.email'   => '邮箱账号格式错误！',
            'mail.require' => '邮箱账号不能为空！',
        ]);
        $this->success('请求成功！', $data);
    }
}