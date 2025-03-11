<?php

namespace begao\viet_qr_pay\constants;

use ReflectionClass;

class QRProvider {
    const VIETQR = 'VIETQR';
    const VNPAY = 'VNPAY';
}

class QRProviderGUID {
    const VNPAY = 'A000000775';
    const VIETQR = 'A000000727';
}

class FieldID {
    const VERSION = '00';
    const INIT_METHOD = '01';
    const VNPAYQR = '26';
    const VIETQR = '38';
    const CATEGORY = '52';
    const CURRENCY = '53';
    const AMOUNT = '54';
    const TIP_AND_FEE_TYPE = '55';
    const TIP_AND_FEE_AMOUNT = '56';
    const TIP_AND_FEE_PERCENT = '57';
    const NATION = '58';
    const MERCHANT_NAME = '59';
    const CITY = '60';
    const ZIP_CODE = '61';
    const ADDITIONAL_DATA = '62';
    const CRC = '63';
}

class ProviderFieldID {
    const GUID = '00';
    const DATA = '01';
    const SERVICE = '02';
}

class VietQRService {
    const BY_ACCOUNT_NUMBER = 'QRIBFTTA'; // Dịch vụ chuyển nhanh NAPAS247 đến Tài khoản
    const BY_CARD_NUMBER = 'QRIBFTTC'; // Dịch vụ chuyển nhanh NAPAS247 đến Thẻ
}

class VietQRConsumerFieldID {
    const BANK_BIN = '00';
    const BANK_NUMBER = '01';
}

class AdditionalDataID {
    const BILL_NUMBER = '01'; // Số hóa đơn
    const MOBILE_NUMBER = '02'; // Số ĐT
    const STORE_LABEL = '03'; // Mã cửa hàng
    const LOYALTY_NUMBER = '04'; // Mã khách hàng thân thiết
    const REFERENCE_LABEL = '05'; // Mã tham chiếu
    const CUSTOMER_LABEL = '06'; // Mã khách hàng
    const TERMINAL_LABEL = '07'; // Mã số điểm bán
    const PURPOSE_OF_TRANSACTION = '08'; // Mục đích giao dịch
    const ADDITIONAL_CONSUMER_DATA_REQUEST = '09'; // Yêu cầu dữ liệu KH bổ sung
}



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