<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Doctrine\Orm\Enum;

enum DriverMappingEnum: string
{
    case ATTRIBUTE_MAPPING = 'attribute';
    case PHP_MAPPING = 'php';
    case STATIC_PHP_MAPPING = 'static_php';
    case XML_MAPPING = 'xml';
}
