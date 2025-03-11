<?php

namespace cuongnm\viet_qr_pay\constants;

use ReflectionClass;

class UnreservedFieldID
{
    public const FIELD_80 = '80';
    public const FIELD_81 = '81';
    public const FIELD_82 = '82';
    public const FIELD_83 = '83';
    public const FIELD_84 = '84';
    public const FIELD_85 = '85';
    public const FIELD_86 = '86';
    public const FIELD_87 = '87';
    public const FIELD_88 = '88';
    public const FIELD_89 = '89';
    public const FIELD_90 = '90';
    public const FIELD_91 = '91';
    public const FIELD_92 = '92';
    public const FIELD_93 = '93';
    public const FIELD_94 = '94';
    public const FIELD_95 = '95';
    public const FIELD_96 = '96';
    public const FIELD_97 = '97';
    public const FIELD_98 = '98';
    public const FIELD_99 = '99';

    public static function isValid(string $value): bool
    {
        $constants = (new ReflectionClass(__CLASS__))->getConstants();
        return in_array($value, $constants, true);
    }
}
