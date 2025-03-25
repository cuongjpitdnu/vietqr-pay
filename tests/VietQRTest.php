<?php

namespace cuongnm\viet_qr_pay\Tests;

use PHPUnit\Framework\TestCase;
use cuongnm\viet_qr_pay\QRPay;

class VietQRTest extends TestCase
{
    public function testVietQRGeneration()
    {
        $bankBin = '970418';
        $bankNumber = '9631242004503530058'; // Số tài khoản
        $amount = '10000'; // Số tiền
        $purpose = 'Chuyen tien HSV'; // Nội dung chuyển tiền
        $qrPay = QRPay::initVietQR(
            $bankBin,
            $bankNumber,
            $amount,
            $purpose
        );
        $content = $qrPay->build();
        // Debug output
        echo "\nGenerated VietQR content: " . $content . "\n";

        return $content;
    }

    public function testVNPayQRGeneration()
    {
        $arr = ['merchantId' => '0102154778',
        'merchantName' => 'TUGIACOMPANY',
        'store' => 'TU GIA COMPUTER',
        'terminal' => 'TUGIACO1',];
        $qrPay = QRPay::initVNPayQR(
          $arr
        );
        $content = $qrPay->build();
        // Debug output
        echo "\nGenerated VNPAYQR content: " . $content . "\n";

        return $content;
    }

    public function testVietQRDecode()
    {
        $qrContent = $this->testVietQRGeneration();
        $qrPay = new QRPay($qrContent);
        $response = array(
            'error_code' => '00',
            'message' => 'Success',
            'data' => [
                'bankBin' => $qrPay->consumer->getBankBin(),
                'bankNumber' => $qrPay->consumer->getBankNumber(),
                'amount' => $qrPay->amount,
                'remark' => $qrPay->additionalData->getPurpose(),
            ]
        );
        echo json_encode($response);
    }

    public function testVNPayQRDecode()
    {
        $qrContent = $this->testVNPayQRGeneration();
        $qrPay = new QRPay($qrContent);

        $response = array(
            'error_code' => '00',
            'message' => 'Success',
            'data' => [
                'name' => $qrPay->provider->getName(),
                'guid' => $qrPay->provider->getGuid(),
                'store' => $qrPay->additionalData?->getStore(),
                'terminal' => $qrPay->additionalData?->getTerminal(),
            ]
        );
        echo json_encode($response);
    }

    public function testVietQRGenerationImage()
    {
        $bankBin = '970418';
        $bankNumber = '9631242004503530058'; // Số tài khoản
        $amount = '10000'; // Số tiền
        $purpose = 'Chuyen tien HSV'; // Nội dung chuyển tiền
        $qrPay = QRPay::initVietQR(
            $bankBin,
            $bankNumber,
            $amount,
            $purpose
        );
        $content = $qrPay->generate_image();
        
        // Debug output
        echo "\nGenerated VietQR Image: " . $content . "\n";

        return $content;
    }

}