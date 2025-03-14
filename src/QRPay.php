<?php

namespace cuongnm\viet_qr_pay;

use cuongnm\viet_qr_pay\constants\AdditionalDataID;
use cuongnm\viet_qr_pay\constants\FieldID;
use cuongnm\viet_qr_pay\constants\ProviderFieldID;
use cuongnm\viet_qr_pay\constants\QRProvider;
use cuongnm\viet_qr_pay\constants\QRProviderGUID;
use cuongnm\viet_qr_pay\constants\VietQRConsumerFieldID;
use cuongnm\viet_qr_pay\constants\VietQRService;

class QRPay {
    public bool $isValid = true;
    public ?string $version = null;
    public ?string $initMethod = null;
    public Provider $provider;
    public Merchant $merchant;
    public Consumer $consumer;
    public ?string $category = null;
    public ?string $currency = null;
    public ?string $amount = null;
    public ?string $tipAndFeeType = null;
    public ?string $tipAndFeeAmount = null;
    public ?string $tipAndFeePercent = null;
    public ?string $nation = null;
    public ?string $city = null;
    public ?string $zipCode = null;
    public AdditionalData $additionalData;
    public ?string $crc = null;

    /** @var array<string, string>|null */
    public ?array $EVMCo = null;

    /** @var array<string, string>|null */
    public ?array $unreserved = null;

    public function __construct(?string $content = '') {
        $this->provider = new Provider();
        $this->consumer = new Consumer();
        $this->merchant = new Merchant();
        $this->additionalData = new AdditionalData();
        $this->parse($content ?? '');
    }

    public function parse(string $content): void {
        if (strlen($content) < 4) {
            $this->invalid();
            return;
        }
        // Verify CRC
        $crcValid = self::verifyCRC($content);
        if (!$crcValid) {
            $this->invalid();
            return;
        }
        // Parse content
        $this->parseRootContent($content);
    }

    public function build(): string {
        $version = self::genFieldData(FieldID::VERSION, $this->version ?? '01');
        $initMethod = self::genFieldData(FieldID::INIT_METHOD, $this->initMethod ?? '11');

        $guid = self::genFieldData(ProviderFieldID::GUID, $this->provider->getGuid());

        $providerDataContent = '';
        if ($this->provider->getGuid() === QRProviderGUID::VIETQR) {
            $bankBin = self::genFieldData(VietQRConsumerFieldID::BANK_BIN, $this->consumer->getBankBin());
            $bankNumber = self::genFieldData(VietQRConsumerFieldID::BANK_NUMBER, $this->consumer->getBankNumber());
            $providerDataContent = $bankBin . $bankNumber;
        } elseif ($this->provider->getGuid() === QRProviderGUID::VNPAY) {
            $providerDataContent = $this->merchant->getId()?? '';
        }
        $provider = self::genFieldData(ProviderFieldID::DATA, $providerDataContent);
        $service = self::genFieldData(ProviderFieldID::SERVICE, $this->provider->getService());
        $providerData = self::genFieldData($this->provider->getFieldId(), $guid . $provider . $service);

        $category = self::genFieldData(FieldID::CATEGORY, $this->category);
        $currency = self::genFieldData(FieldID::CURRENCY, $this->currency ?? '704');
        $amountStr = self::genFieldData(FieldID::AMOUNT, $this->amount);
        $tipAndFeeType = self::genFieldData(FieldID::TIP_AND_FEE_TYPE, $this->tipAndFeeType);
        $tipAndFeeAmount = self::genFieldData(FieldID::TIP_AND_FEE_AMOUNT, $this->tipAndFeeAmount);
        $tipAndFeePercent = self::genFieldData(FieldID::TIP_AND_FEE_PERCENT, $this->tipAndFeePercent);
        $nation = self::genFieldData(FieldID::NATION, $this->nation ?? 'VN');
        $merchantName = self::genFieldData(FieldID::MERCHANT_NAME, $this->merchant->getName());
        $city = self::genFieldData(FieldID::CITY, $this->city);
        $zipCode = self::genFieldData(FieldID::ZIP_CODE, $this->zipCode);

        $buildNumber = self::genFieldData(AdditionalDataID::BILL_NUMBER, $this->additionalData->getBillNumber());
        $mobileNumber = self::genFieldData(AdditionalDataID::MOBILE_NUMBER, $this->additionalData->getMobileNumber());
        $storeLabel = self::genFieldData(AdditionalDataID::STORE_LABEL, $this->additionalData->getStore());
        $loyaltyNumber = self::genFieldData(AdditionalDataID::LOYALTY_NUMBER, $this->additionalData->getLoyaltyNumber());
        $reference = self::genFieldData(AdditionalDataID::REFERENCE_LABEL, $this->additionalData->getReference());
        $customerLabel = self::genFieldData(AdditionalDataID::CUSTOMER_LABEL, $this->additionalData->getCustomerLabel());
        $terminal = self::genFieldData(AdditionalDataID::TERMINAL_LABEL, $this->additionalData->getTerminal());
        $purpose = self::genFieldData(AdditionalDataID::PURPOSE_OF_TRANSACTION, $this->additionalData->getPurpose());
        $dataRequest = self::genFieldData(AdditionalDataID::ADDITIONAL_CONSUMER_DATA_REQUEST, $this->additionalData->getDataRequest());

        $additionalDataContent = $buildNumber . $mobileNumber . $storeLabel . $loyaltyNumber . $reference . $customerLabel . $terminal . $purpose . $dataRequest;
        $additionalData = self::genFieldData(FieldID::ADDITIONAL_DATA, $additionalDataContent);

        $EVMCoContent = '';
        if ($this->EVMCo !== null) {
            ksort($this->EVMCo);
            foreach ($this->EVMCo as $key => $value) {
                $EVMCoContent .= self::genFieldData($key, $value);
            }
        }

        $unreservedContent = '';
        if ($this->unreserved !== null) {
            ksort($this->unreserved);
            foreach ($this->unreserved as $key => $value) {
                $unreservedContent .= self::genFieldData($key, $value);
            }
        }

        $content = "{$version}{$initMethod}{$providerData}{$category}{$currency}{$amountStr}{$tipAndFeeType}{$tipAndFeeAmount}{$tipAndFeePercent}{$nation}{$merchantName}{$city}{$zipCode}{$additionalData}{$EVMCoContent}{$unreservedContent}" . FieldID::CRC . "04";
        $crc = self::genCRCCode($content);
        return $content . $crc;
    }

    public static function initVNPAYQR(string $merchantId, string $amount, string $tipAndFeeType, string $tipAndFeeAmount, string $tipAndFeePercent): QRPay {
        $qr = new QRPay();
        $qr->initMethod = '11';
        $qr->provider->setFieldId(FieldID::VNPAYQR);
        $qr->provider->setGuid(QRProviderGUID::VNPAY);
        $qr->provider->setName(QRProvider::VNPAY);
        $qr->merchant->setId($merchantId);
        $qr->amount = $amount;
        $qr->tipAndFeeType = $tipAndFeeType;
        $qr->tipAndFeeAmount = $tipAndFeeAmount;
        $qr->tipAndFeePercent = $tipAndFeePercent;
        return $qr;
    }

    public static function initVietQR(string $bankBin, string $bankNumber, string $amount = null, string $purpose = null , string $service = VietQRService::BY_ACCOUNT_NUMBER): QRPay {
        $qr = new QRPay();
        $qr->initMethod = $amount ? '12' : '11';
        $qr->provider->setFieldId(FieldID::VIETQR);
        $qr->provider->setGuid(QRProviderGUID::VIETQR);
        $qr->provider->setName(QRProvider::VIETQR);
        $qr->provider->setService($service);
        $qr->consumer->setBankBin($bankBin);
        $qr->consumer->setBankNumber($bankNumber);
        $qr->amount = $amount;
        $qr->additionalData->setPurpose($purpose);
        return $qr;
    }

    private function parseEVMCo(string $fieldId, string $fieldValue): void {
        if ($this->EVMCo === null) {
            $this->EVMCo = [];
        }
        $this->EVMCo[$fieldId] = $fieldValue;
    }

    private function parseUnreserved(string $fieldId, string $fieldValue): void {
        if ($this->unreserved === null) {
            $this->unreserved = [];
        }
        $this->unreserved[$fieldId] = $fieldValue;
    }

    private function parseRootContent(string $content): void {
        $contentLen = strlen($content);
        $pos = 0;
        while ($pos < $contentLen) {
            $fieldId = substr($content, $pos, 2);
            $pos += 2;
            $fieldLen = (int)substr($content, $pos, 2);
            $pos += 2;
            $fieldValue = substr($content, $pos, $fieldLen);
            $pos += $fieldLen;
            switch ($fieldId) {
                case FieldID::VERSION:
                    $this->version = $fieldValue;
                    break;
                case FieldID::INIT_METHOD:
                    $this->initMethod = $fieldValue;
                    break;

                case FieldID::VIETQR:
                case FieldID::VNPAYQR:
                    $this->provider->setFieldId($fieldId);
                    $this->parseProviderInfo($fieldValue);
                    break;
                case FieldID::CATEGORY:
                    $this->category = $fieldValue;
                    break;
                case FieldID::CURRENCY:
                    $this->currency = $fieldValue;
                    break;
                case FieldID::AMOUNT:
                    $this->amount = $fieldValue;
                    break;
                case FieldID::TIP_AND_FEE_TYPE:
                    $this->tipAndFeeType = $fieldValue;
                    break;
                case FieldID::TIP_AND_FEE_AMOUNT:
                    $this->tipAndFeeAmount = $fieldValue;
                    break;
                case FieldID::TIP_AND_FEE_PERCENT:
                    $this->tipAndFeePercent = $fieldValue;
                    break;
                case FieldID::NATION:
                    $this->nation = $fieldValue;
                    break;
                case FieldID::CITY:
                    $this->city = $fieldValue;
                    break;
                case FieldID::ZIP_CODE:
                    $this->zipCode = $fieldValue;
                    break;
                case FieldID::ADDITIONAL_DATA:
                    $this->parseAdditionalData($fieldValue);
                    break;
                case FieldID::CRC:
                    $this->crc = $fieldValue;
                    break;
                default:
                    $this->parseUnreserved($fieldId, $fieldValue);
                    break;
            }
        }
    }

    private function parseVietQRConsumer(string $content): void {
        $pos = 0;
        $contentLen = strlen($content);
        while ($pos < $contentLen) {
            $fieldId = substr($content, $pos, 2);
            $pos += 2;
            $fieldLen = (int)substr($content, $pos, 2);
            $pos += 2;
            $fieldValue = substr($content, $pos, $fieldLen);
            $pos += $fieldLen;
            switch ($fieldId) {
                case VietQRConsumerFieldID::BANK_BIN:
                    $this->consumer->setBankBin( $fieldValue);
                    break;
                case VietQRConsumerFieldID::BANK_NUMBER:
                    $this->consumer->setBankNumber($fieldValue);
                    break;
                default:
                    break;
            }
        }
    }

    private function parseProviderInfo(string $content): void {
        $info = self::sliceContent($content);
        switch ($info['id']) {
            case ProviderFieldID::GUID:
                $this->provider->setGuid($info['value']);
                break;
            case ProviderFieldID::DATA:
                if ($this->provider->getGuid() === QRProviderGUID::VNPAY) {
                    $this->provider->setName(QRProvider::VNPAY);
                    $this->merchant->setId($info['value']);
                } elseif ($this->provider->getGuid() === QRProviderGUID::VIETQR) {
                    $this->provider->setName(QRProvider::VIETQR);
                    $this->parseVietQRConsumer($info['value']);
                }
                break;
            case ProviderFieldID::SERVICE:
                $this->provider->setService($info['value']);
                break;
            default:
                break;
        }
        if (strlen($info['nextValue']) > 4) {
            $this->parseProviderInfo($info['nextValue']);
        }
    }

    private function parseAdditionalData(string $content): void {
        $pos = 0;
        $contentLen = strlen($content);
        while ($pos < $contentLen) {
            $fieldId = substr($content, $pos, 2);
            $pos += 2;
            $fieldLen = (int)substr($content, $pos, 2);
            $pos += 2;
            $fieldValue = substr($content, $pos, $fieldLen);
            $pos += $fieldLen;
            switch ($fieldId) {
                case AdditionalDataID::BILL_NUMBER:
                    $this->additionalData->setBillNumber($fieldValue);
                    break;
                case AdditionalDataID::MOBILE_NUMBER:
                    $this->additionalData->setMobileNumber($fieldValue);
                    break;
                case AdditionalDataID::STORE_LABEL:
                    $this->additionalData->setStore($fieldValue);
                    break;
                case AdditionalDataID::LOYALTY_NUMBER:
                    $this->additionalData->setLoyaltyNumber($fieldValue);
                    break;
                case AdditionalDataID::REFERENCE_LABEL:
                    $this->additionalData->setReference($fieldValue);
                    break;
                case AdditionalDataID::CUSTOMER_LABEL:
                    $this->additionalData->setCustomerLabel($fieldValue);
                    break;
                case AdditionalDataID::TERMINAL_LABEL:
                    $this->additionalData->setTerminal($fieldValue);
                    break;
                case AdditionalDataID::PURPOSE_OF_TRANSACTION:
                    $this->additionalData->setPurpose($fieldValue);
                    break;
                case AdditionalDataID::ADDITIONAL_CONSUMER_DATA_REQUEST:
                    $this->additionalData->setDataRequest($fieldValue);
                    break;
                default:
                    $this->parseEVMCo($fieldId, $fieldValue);
                    break;
            }
        }
    }

    private static function verifyCRC(string $content): bool {
         // Extract the main content without the last 4 characters
         $checkContent = substr($content, 0, -4);
         // Extract the last 4 characters and convert to uppercase
         $crcCode = strtoupper(substr($content, -4));

         // Generate the CRC code for the main content
         $genCrcCode = self::genCRCCode($checkContent);
         // Compare the extracted CRC code with the generated one
         return $crcCode === $genCrcCode;
    }

    private static function genCRCCode(string $content): string {
        static $CRC16_Lookup = array(
            0x0000, 0x1021, 0x2042, 0x3063, 0x4084, 0x50A5, 0x60C6, 0x70E7,
            0x8108, 0x9129, 0xA14A, 0xB16B, 0xC18C, 0xD1AD, 0xE1CE, 0xF1EF,
            0x1231, 0x0210, 0x3273, 0x2252, 0x52B5, 0x4294, 0x72F7, 0x62D6,
            0x9339, 0x8318, 0xB37B, 0xA35A, 0xD3BD, 0xC39C, 0xF3FF, 0xE3DE,
            0x2462, 0x3443, 0x0420, 0x1401, 0x64E6, 0x74C7, 0x44A4, 0x5485,
            0xA56A, 0xB54B, 0x8528, 0x9509, 0xE5EE, 0xF5CF, 0xC5AC, 0xD58D,
            0x3653, 0x2672, 0x1611, 0x0630, 0x76D7, 0x66F6, 0x5695, 0x46B4,
            0xB75B, 0xA77A, 0x9719, 0x8738, 0xF7DF, 0xE7FE, 0xD79D, 0xC7BC,
            0x48C4, 0x58E5, 0x6886, 0x78A7, 0x0840, 0x1861, 0x2802, 0x3823,
            0xC9CC, 0xD9ED, 0xE98E, 0xF9AF, 0x8948, 0x9969, 0xA90A, 0xB92B,
            0x5AF5, 0x4AD4, 0x7AB7, 0x6A96, 0x1A71, 0x0A50, 0x3A33, 0x2A12,
            0xDBFD, 0xCBDC, 0xFBBF, 0xEB9E, 0x9B79, 0x8B58, 0xBB3B, 0xAB1A,
            0x6CA6, 0x7C87, 0x4CE4, 0x5CC5, 0x2C22, 0x3C03, 0x0C60, 0x1C41,
            0xEDAE, 0xFD8F, 0xCDEC, 0xDDCD, 0xAD2A, 0xBD0B, 0x8D68, 0x9D49,
            0x7E97, 0x6EB6, 0x5ED5, 0x4EF4, 0x3E13, 0x2E32, 0x1E51, 0x0E70,
            0xFF9F, 0xEFBE, 0xDFDD, 0xCFFC, 0xBF1B, 0xAF3A, 0x9F59, 0x8F78,
            0x9188, 0x81A9, 0xB1CA, 0xA1EB, 0xD10C, 0xC12D, 0xF14E, 0xE16F,
            0x1080, 0x00A1, 0x30C2, 0x20E3, 0x5004, 0x4025, 0x7046, 0x6067,
            0x83B9, 0x9398, 0xA3FB, 0xB3DA, 0xC33D, 0xD31C, 0xE37F, 0xF35E,
            0x02B1, 0x1290, 0x22F3, 0x32D2, 0x4235, 0x5214, 0x6277, 0x7256,
            0xB5EA, 0xA5CB, 0x95A8, 0x8589, 0xF56E, 0xE54F, 0xD52C, 0xC50D,
            0x34E2, 0x24C3, 0x14A0, 0x0481, 0x7466, 0x6447, 0x5424, 0x4405,
            0xA7DB, 0xB7FA, 0x8799, 0x97B8, 0xE75F, 0xF77E, 0xC71D, 0xD73C,
            0x26D3, 0x36F2, 0x0691, 0x16B0, 0x6657, 0x7676, 0x4615, 0x5634,
            0xD94C, 0xC96D, 0xF90E, 0xE92F, 0x99C8, 0x89E9, 0xB98A, 0xA9AB,
            0x5844, 0x4865, 0x7806, 0x6827, 0x18C0, 0x08E1, 0x3882, 0x28A3,
            0xCB7D, 0xDB5C, 0xEB3F, 0xFB1E, 0x8BF9, 0x9BD8, 0xABBB, 0xBB9A,
            0x4A75, 0x5A54, 0x6A37, 0x7A16, 0x0AF1, 0x1AD0, 0x2AB3, 0x3A92,
            0xFD2E, 0xED0F, 0xDD6C, 0xCD4D, 0xBDAA, 0xAD8B, 0x9DE8, 0x8DC9,
            0x7C26, 0x6C07, 0x5C64, 0x4C45, 0x3CA2, 0x2C83, 0x1CE0, 0x0CC1,
            0xEF1F, 0xFF3E, 0xCF5D, 0xDF7C, 0xAF9B, 0xBFBA, 0x8FD9, 0x9FF8,
            0x6E17, 0x7E36, 0x4E55, 0x5E74, 0x2E93, 0x3EB2, 0x0ED1, 0x1EF0
        );

        $crc16 = 0xFFFF; // the CRC
        $len = strlen($content);

        for($i = 0; $i < $len; $i++ )
        {
            $t = ($crc16 >> 8) ^ ord($content[$i]); // High byte Xor Message Byte to get index
            $crc16 = (($crc16 << 8) & 0xffff) ^ $CRC16_Lookup[$t]; // Update the CRC from table
        }
        $crc16 = dechex($crc16);
        $crc16 = strtoupper($crc16);
        return $crc16;
    }

    private static function sliceContent(string $content): array {
        $fieldId = substr($content, 0, 2);
        $fieldLen = (int)substr($content, 2, 2);
        $fieldValue = substr($content, 4, $fieldLen);
        $nextValue = substr($content, 4 + $fieldLen);
        return ['id' => $fieldId, 'value' => $fieldValue, 'nextValue' => $nextValue];
    }

    private function invalid(): void {
        $this->isValid = false;
    }

    private static function genFieldData($fieldId = '', $fieldValue = ''): string {
        $fieldId = $id ?? '';
        $fieldValue = $value ?? '';
        $idLen = strlen($fieldId);
        if ($idLen !== 2 || strlen($fieldValue) <= 0) {
            return '';
        }
        $length = str_pad(strlen($fieldValue), 2, '0', STR_PAD_LEFT);
        return $fieldId . $length . $fieldValue;
    }
}
