<?php

namespace cuongnm\viet_qr_pay\constants;

use ReflectionClass;

class EVMCoFieldID
{
    public const FIELD_65 = '65';
    public const FIELD_66 = '66';
    public const FIELD_67 = '67';
    public const FIELD_68 = '68';
    public const FIELD_69 = '69';
    public const FIELD_70 = '70';
    public const FIELD_71 = '71';
    public const FIELD_72 = '72';
    public const FIELD_73 = '73';
    public const FIELD_74 = '74';
    public const FIELD_75 = '75';
    public const FIELD_76 = '76';
    public const FIELD_77 = '77';
    public const FIELD_78 = '78';
    public const FIELD_79 = '79';

    public static function isValid(string $value): bool
    {
        $constants = (new ReflectionClass(__CLASS__))->getConstants();
        return in_array($value, $constants, true);
    }
}
