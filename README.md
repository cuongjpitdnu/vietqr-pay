## Introduction

Library supporting QR code encoding/decoding of VietQR & VNPayQR

## Installation

Use [Composer](https://getcomposer.org/) to install the library. Also make sure you have enabled and configured the
[GD extension](https://www.php.net/manual/en/book.image.php) if you want to generate images.

``` bash
composer require cuongnm/viet_qr_pay
```

## Import lib

```json
{
  "require": {
    "cuongnm/viet_qr_pay": "^1.0"
  }
}
```

## Tạo mã VietQR

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

## Tạo mã VNPay

```php
use cuongnm\viet_qr_pay\QRPay;

      $merchantId = '0102154778';
      $merchantName = 'TUGIACOMPANY';
      $store = 'TU GIA COMPUTER';
      $terminal = 'TUGIACO1';
      $qrPay = QRPay::initVNPayQR(
        $merchantId,
        $merchantName,
        $terminal,
        $terminal
      );
      $content = $qrPay->build();

      echo $content;
      // 00020101021126280010A0000007750110010531314453037045408210900005802VN5910CELLPHONES62600312CPSHN ONLINE0517021908061613127850705ONLHN0810CellphoneS63047685
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

## Tạo mã VietQR Image (base64)

```php
use cuongnm\viet_qr_pay\QRPay;

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
```



## `QRPay` class

| Name             | Type             | Description                                           |
| ---------------- | ---------------- | ----------------------------------------------------- |
| `isValid`        | `boolean`        | Kiểm tra tính hợp lệ của mã QR                        |
| `initMethod`     | `string`         | Phương thức khởi tạo (`11` - QR Tĩnh, `12` - QR động) |
| `provider`       | `Provider`       | Thông tin nhà cung cấp                                |
| `merchant`       | `Merchant`       | Thông tin merchant                                    |
| `consumer`       | `Consumer`       | Thông tin người thanh toán                            |
| `amount`         | `string`         | Số tiền giao dịch                                     |
| `currency`       | `string`         | Mã tiền tệ (VNĐ: 704)                                 |
| `nation`         | `string`         | Mã quốc gia                                           |
| `additionalData` | `AdditionalData` | Thông tin bổ sung                                     |
| `crc`            | `string`         | Mã kiểm tra                                           |
| `build()`        | `method`         | Tạo lại mã QR mới                                     |

### `Provider` class

Thông tin đơn vị cung cấp mã QR (VietQR, VNPay)

| Name   | Type     | Description           |
| ------ | -------- | --------------------- |
| `guid` | `string` | Mã định danh toàn cầu |
| `name` | `string` | Tên nhà cung cấp      |

### `Merchant` class

Thông tin merchant (Đơn vị chấp nhận thanh toán)

| Name   | Type     | Description              |
| ------ | -------- | ------------------------ |
| `id`   | `string` | Mã định danh đơn vị CNTT |
| `name` | `string` | Tên đơn vị CNTT          |

### `Consumer` class

Thông tin người thanh toán

| Name         | Type     | Description  |
| ------------ | -------- | ------------ |
| `bankBin`    | `string` | Mã ngân hàng |
| `bankNumber` | `string` | Số tài khoản |

### `AdditionalData` class

Thông tin bổ sung

| Name            | Type     | Description              |
| --------------- | -------- | ------------------------ |
| `billNumber`    | `string` | Số hóa đơn               |
| `mobileNumber`  | `string` | Số điện thoại di động    |
| `store`         | `string` | Tên cửa hàng             |
| `loyaltyNumber` | `string` | Mã khách hàng thân thiết |
| `reference`     | `string` | Mã Tham chiếu            |
| `customerLabel` | `string` | Mã khách hàng            |
| `terminal`      | `string` | Tên điểm bản             |
| `purpose`       | `string` | Nội dung giao dịch       |

### `build()` method

Trả về nội dung mã QR mới
