<?php
namespace Helper;

class Config
{
    public const MODE_DEFAULT = 0;
    public const MODE_UPDATE = 1;
    public const MODE_DEBUG = 2;

    public static array $modes = [
        'default' => self::MODE_DEFAULT,
        'update' => self::MODE_UPDATE,
        'debug' => self::MODE_DEBUG
    ];
}