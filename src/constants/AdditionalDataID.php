<?php

namespace cuongnm\viet_qr_pay\constants;

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
