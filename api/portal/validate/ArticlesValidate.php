<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------
namespace api\portal\validate;

use think\Validate;

class ArticlesValidate extends Validate
{
    protected $rule = [
        'post_title'        =>  'require',
	    'post_content'      =>  'require',
	    'categories'        =>  'require'
    ];
    protected $message = [
        'post_title.require'    =>  '文章标题不能为空',
	    'post_content.require'  =>  '内容不能为空',
	    'categories.require'    =>  '文章分类不能为空'
    ];

    protected $scene = [
        'article'  => [ 'post_title' , 'post_content' , 'categories' ],
        'page' => ['post_title']
    ];
}