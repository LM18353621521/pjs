<?php
// +----------------------------------------------------------------------
// | bronet [ 以客户为中心 以奋斗者为本 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.bronet.cn All rights reserved.
// +----------------------------------------------------------------------
namespace app\admin\annotation;

use mindplay\annotations\AnnotationException;
use mindplay\annotations\Annotation;

/**
 * Specifies validation of a string, requiring a minimum and/or maximum length.
 *
 * @usage('class'=>true, 'inherited'=>true, 'multiple'=>true)
 */
class AdminMenuRootAnnotation extends Annotation
{
    /**
     * @var int|null Minimum string length (or null, if no minimum)
     */
    public $remark = '';

    /**
     * @var int|null Maximum string length (or null, if no maximum)
     */
    public $icon = '';

    /**
     * @var int|null Minimum string length (or null, if no minimum)
     */
    public $name = '';

    public $action = '';

    public $param = '';

    public $parent = '';

    public $display = false;

    public $order = 10000;

    /**
     * Initialize the annotation.
     * @param array $properties
     */
    public function initAnnotation(array $properties)
    {
        parent::initAnnotation($properties);
    }
}
