# Introduction 

Library supporting QR code encoding/decoding of VietQR & VNPayQR

# Import lib
```json
{
  "require":{
    "cuongnm/viet_qr_pay": "^1.0"
  }
}

```
# Examples
## Generate
```php
use cuongnm\viet_qr_pay\QRPay;

      $bankBin = '970418';
      $bankNumber = '257678859'; // Số tài khoản
      $amount = '10000'; // Số tiền
      $purpose = 'Chuyen tien'; // Nội dung chuyển tiền
      $qrPay = QRPay::initVietQR(
        $bankBin,
        $bankNumber,
        $amount,
        $purpose
      );
      $content = $qrPay->build();

      echo $content;
      // 00020101021138530010A0000007270123000697041601092576788590208QRIBFTTA53037045802VN6304AE9F
```

## Decode mã QR
```php
use cuongnm\viet_qr_pay\QRPay;

    $qrContent = '00020101021238530010A0000007270123000697041601092576788590208QRIBFTTA5303704540410005802VN62150811Chuyen tien6304BBB8'
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
```
