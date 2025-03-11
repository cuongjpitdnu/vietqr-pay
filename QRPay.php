<?php

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

        $guid = self::genFieldData(ProviderFieldID::GUID, $this->provider->guid);

        $providerDataContent = '';
        if ($this->provider->guid === QRProviderGUID::VIETQR) {
            $bankBin = self::genFieldData(VietQRConsumerFieldID::BANK_BIN, $this->consumer->bankBin);
            $bankNumber = self::genFieldData(VietQRConsumerFieldID::BANK_NUMBER, $this->consumer->bankNumber);
            $providerDataContent = $bankBin . $bankNumber;
        } elseif ($this->provider->guid === QRProviderGUID::VNPAY) {
            $providerDataContent = $this->merchant->id ?? '';
        }
        $provider = self::genFieldData(ProviderFieldID::DATA, $providerDataContent);
        $service = self::genFieldData(ProviderFieldID::SERVICE, $this->provider->service);
        $providerData = self::genFieldData($this->provider->fieldId, $guid . $provider . $service);

        $category = self::genFieldData(FieldID::CATEGORY, $this->category);
        $currency = self::genFieldData(FieldID::CURRENCY, $this->currency ?? '704');
        $amountStr = self::genFieldData(FieldID::AMOUNT, $this->amount);
        $tipAndFeeType = self::genFieldData(FieldID::TIP_AND_FEE_TYPE, $this->tipAndFeeType);
        $tipAndFeeAmount = self::genFieldData(FieldID::TIP_AND_FEE_AMOUNT, $this->tipAndFeeAmount);
        $tipAndFeePercent = self::genFieldData(FieldID::TIP_AND_FEE_PERCENT, $this->tipAndFeePercent);
        $nation = self::genFieldData(FieldID::NATION, $this->nation ?? 'VN');
        $merchantName = self::genFieldData(FieldID::MERCHANT_NAME, $this->merchant->name);
        $city = self::genFieldData(FieldID::CITY, $this->city);
        $zipCode = self::genFieldData(FieldID::ZIP_CODE, $this->zipCode);

        $buildNumber = self::genFieldData(AdditionalDataID::BILL_NUMBER, $this->additionalData->billNumber);
        $mobileNumber = self::genFieldData(AdditionalDataID::MOBILE_NUMBER, $this->additionalData->mobileNumber);
        $storeLabel = self::genFieldData(AdditionalDataID::STORE_LABEL, $this->additionalData->store);
        $loyaltyNumber = self::genFieldData(AdditionalDataID::LOYALTY_NUMBER, $this->additionalData->loyaltyNumber);
        $reference = self::genFieldData(AdditionalDataID::REFERENCE_LABEL, $this->additionalData->reference);
        $customerLabel = self::genFieldData(AdditionalDataID::CUSTOMER_LABEL, $this->additionalData->customerLabel);
        $terminal = self::genFieldData(AdditionalDataID::TERMINAL_LABEL, $this->additionalData->terminal);
        $purpose = self::genFieldData(AdditionalDataID::PURPOSE_OF_TRANSACTION, $this->additionalData->purpose);
        $dataRequest = self::genFieldData(AdditionalDataID::ADDITIONAL_CONSUMER_DATA_REQUEST, $this->additionalData->dataRequest);

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
        $qr->provider->fieldId = FieldID::VNPAYQR;
        $qr->provider->guid = QRProviderGUID::VNPAY;
        $qr->provider->name = QRProvider::VNPAY;
        $qr->merchant->id = $merchantId;
        $qr->amount = $amount;
        $qr->tipAndFeeType = $tipAndFeeType;
        $qr->tipAndFeeAmount = $tipAndFeeAmount;
        $qr->tipAndFeePercent = $tipAndFeePercent;
        return $qr;
    }

    public static function initVietQR(string $bankBin, string $bankNumber, string $amount = null, string $purpose = null , string $service = VietQRService::BY_ACCOUNT_NUMBER): QRPay {
        $qr = new QRPay();
        $qr->initMethod = $amount ? '12' : '11';
        $qr->provider->fieldId = FieldID::VIETQR;
        $qr->provider->guid = QRProviderGUID::VIETQR;
        $qr->provider->name = QRProvider::VIETQR;
        $qr->provider->service = $service;
        $qr->consumer->bankBin = $bankBin;
        $qr->consumer->bankNumber = $bankNumber;
        $qr->amount = $amount;
        $qr->additionalData->purpose = $purpose;
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
                    $this->provider->fieldId = $fieldId;
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
                    $this->consumer->bankBin = $fieldValue;
                    break;
                case VietQRConsumerFieldID::BANK_NUMBER:
                    $this->consumer->bankNumber = $fieldValue;
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
                $this->provider->guid = $info['value'];
                break;
            case ProviderFieldID::DATA:
                if ($this->provider->guid === QRProviderGUID::VNPAY) {
                    $this->provider->name = QRProvider::VNPAY;
                    $this->merchant->id = $info['value'];
                } elseif ($this->provider->guid === QRProviderGUID::VIETQR) {
                    $this->provider->name = QRProvider::VIETQR;
                    $this->parseVietQRConsumer($info['value']);
                }
                break;
            case ProviderFieldID::SERVICE:
                $this->provider->service = $info['value'];
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
                    $this->additionalData->billNumber = $fieldValue;
                    break;
                case AdditionalDataID::MOBILE_NUMBER:
                    $this->additionalData->mobileNumber = $fieldValue;
                    break;
                case AdditionalDataID::STORE_LABEL:
                    $this->additionalData->store = $fieldValue;
                    break;
                case AdditionalDataID::LOYALTY_NUMBER:
                    $this->additionalData->loyaltyNumber = $fieldValue;
                    break;
                case AdditionalDataID::REFERENCE_LABEL:
                    $this->additionalData->reference = $fieldValue;
                    break;
                case AdditionalDataID::CUSTOMER_LABEL:
                    $this->additionalData->customerLabel = $fieldValue;
                    break;
                case AdditionalDataID::TERMINAL_LABEL:
                    $this->additionalData->terminal = $fieldValue;
                    break;
                case AdditionalDataID::PURPOSE_OF_TRANSACTION:
                    $this->additionalData->purpose = $fieldValue;
                    break;
                case AdditionalDataID::ADDITIONAL_CONSUMER_DATA_REQUEST:
                    $this->additionalData->dataRequest = $fieldValue;
                    break;
                default:
                    $this->parseEVMCo($fieldId, $fieldValue);
                    break;
            }
        }
    }

    private static function verifyCRC(string $content): bool {
        $contentLen = strlen($content);
        $crc = substr($content, $contentLen - 6, 4);
        $content = substr($content, 0, $contentLen - 6);
        $expectedCRC = self::genCRCCode($content);
        return $crc === $expectedCRC;
    }

    private static function genCRCCode(string $content): string {
        $crc = 0xFFFF;
        $contentLen = strlen($content);
        for ($i = 0; $i < $contentLen; $i++) {
            $crc ^= ord($content[$i]);
            for ($j = 0; $j < 8; $j++) {
                if (($crc & 1) === 1) {
                    $crc = ($crc >> 1) ^ 0x8408;
                } else {
                    $crc >>= 1;
                }
            }
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
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

    private static function genFieldData(string $fieldId, string $fieldValue): string {
        $fieldLen = str_pad(strlen($fieldValue), 2, '0', STR_PAD_LEFT);
        return $fieldId . $fieldLen . $fieldValue;
    }
}