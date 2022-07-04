<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>سفارش شما به موفقیت در ویگوبازار ثبت شد.</title>
</head>
<body>
<div style="width:900px;height:auto; font-family:tahoma;direction:rtl;padding:20px;margin:0 auto;background: #f5f5f5; color: #5f5f5f">
    <figure style="margin: 20px 0; text-align: center;">
        <img src="https://api.wegobazaar.com/logo.png" alt="wegobazaar.com" style="width: 150px;" />
    </figure>
    <h2 style="margin:50px 0 0 0;text-align: center;border-bottom: 1px solid #ccc;padding-bottom: 10px;font-size: 20px;font-weight: normal;">
        سفارش شما با موفقیت در ویگوبازار ثبت شد
    </h2>
    <div style="font-size:13px;line-height:25px;text-align:center;">
        <p>با تشکر از خرید شما</p>
        <p style="background: #ffa21c;padding: 10px;color: white;font-weight: bold;font-size: 15px;border-radius: 5px;line-height: 1.5">
            سفارش شما به کد پیگیری {{$order->id}} با موفقیت ثبت شد. در صورت نیاز با شما در تماس خواهیم بود.
        </p>
    </div>

    <h2 style="margin:50px 0 0 0;text-align: center;border-bottom: 1px solid #ccc;padding-bottom: 10px;font-size: 20px;font-weight: normal;">
        فاکتور سفارش
    </h2>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
        <tr style="line-height: 3;background: #f0f0f0">
            <th style="font-weight: normal;text-align: center; ">نام کالا</th>
            <th style="font-weight: normal;text-align: center;">تعداد</th>
        </tr>
        </thead>
        <tbody>
        @foreach($order->products as $productDetail)
        <tr style="text-align: center; line-height: 3;background: #fff;border-bottom: 1px solid #ccc;">
            <td style="width: 60%;max-width: 100px;"><p style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;margin:0">{{$productDetail->product->persian_name}}</p></td>
            <td>{{$productDetail->pivot->quantity}}</td>
        </tr>
            @endforeach
        </tbody>
    </table>
    <div style="background: #f0f0f0;padding: 12px 0;overflow: hidden;">
        <div style="width: 60%; text-align: center;display: inline-block;float:right">مبلغ نهایی سفارش : </div>
        <div style="width: 40%; text-align: center;display: inline-block;float:right">{{$order->final_order_price}} تومان</div>
    </div>
    <h2 style="margin:50px 0 0 0;text-align: center;border-bottom: 1px solid #ccc;padding-bottom: 10px;font-size: 20px;font-weight: normal;">
        مشخصات تحویل گیرنده
    </h2>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
        <tr style="line-height: 3;background: #f0f0f0">
            <th style="font-weight: normal;text-align: center; ">نام و نام خانوادگی</th>
            <th style="font-weight: normal;text-align: center;">شماره تماس</th>
            <th style="font-weight: normal;text-align: center;">آدرس</th>
        </tr>
        </thead>
        <tbody>
        <tr style="text-align: center; line-height: 3;background: #fff;border-bottom: 1px solid #ccc;">
            <td style="max-width: 100px;width: 20%">{{$order->address->receiver_first_name . ' ' . $order->address->receiver_last_name}}</td>
            <td style="max-width: 100px;width: 20%">{{$order->address->prefix_mobile_number . $order->address->mobile_number}}</td>
            <td style="max-width: 100px;width: 60%;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                {{$location['name'].'-'.$location['cities']['Title'].'-'.$order->address->address}}
            </td>
        </tr>
        </tbody>
    </table>

    @if($order->payment_id != 1 || $order->progressable == 'false')
    <p style="margin-top: 50px;text-align: center;">در صورتی که سفارش خود را پرداخت نکرده اید می توانید از طریق دکمه زیر به صورت آنلاین پرداخت نمایید.</p>
    <div style="text-align: center;margin-top: 35px;">
        <a href="{{$url}}">
            <button style="border:none;outline:none;padding:0;background: #ffa21c; font-size: 1.2em; color: #fff;  border-radius: 3px;  text-align: center;  min-width: 310px;  position: relative;  height: 36px;  overflow: hidden;  padding: 0 4px;  box-sizing: border-box; cursor:pointer;">پرداخت آنلاین سفارش</button>
        </a>
    </div>
    @endif
    <p style="width:100%;text-align:center;padding-top:10px;font-weight:normal;margin-top:100px;border-top:2px solid #e0e0e0;">
    <div style="text-align:center;">
        <a href="https://www.instagram.com/wegobazaar"><img style="width:35px ;margin-left: 8px;" src="https://ci5.googleusercontent.com/proxy/ENRakxZ8-VM94ig14RgwPdEAXq5yO4fdpBZhTUn3zcjp_rjHQ1y1DbzHW6-xtfv0OZJYhSwRInmxKcC3PGKjOlmgKqMPEUp_XpUR40Rjrox2tRy4qeYbkLXVf8AclCw=s0-d-e1-ft#https://www.reyhoon.com/assets/img/emails/welcomeemail/instagram_icon.png" alt=""></a>
        <a href="https://www.linkedin.com/company/wegobazaar.com"><img style="width:35px ;margin-left: 8px;" src="https://ci4.googleusercontent.com/proxy/V9jQSff6Jf-eY8LlxC0_HKD37jutYNRtpZTBiYJgfNBHsQGm860j9urCtQsMAa5mDGc1ZiSz4ELJpSDrkOTHRAWprMkAMwPACDhEqIz1jqXb2Zhk9vxQinLc2EaMUg=s0-d-e1-ft#https://www.reyhoon.com/assets/img/emails/welcomeemail/linkedin_icon.png" alt=""></a>
        <a href="https://www.telegram.me/wegobazaar">	<img style="width:35px ;margin-left: 8px;" src="https://ci5.googleusercontent.com/proxy/s-eH6ibvAlzMQ-MqGjnHFlZGPqAgiVBEGs00HNugWncIbVa_8rplDyjpmkCSRPDIuTaKc0Ng8Qhc-RhfVW7EF0ZhurWqKsvBDAvzwF5491EcwRu8inH9Ypmr5SXImA=s0-d-e1-ft#https://www.reyhoon.com/assets/img/emails/welcomeemail/telegram_icon.png" alt=""></a>
        <a href="https://www.facebook.com/wegobazaar"><img style="width:35px" src="https://ci4.googleusercontent.com/proxy/aMj1_HKKpYEepztVYLyDcZrhPqDnN6MHxOgvDha4M9jaEZQUN85JsxKCIamKtWpLI_BZDhc97Ii9zuCv7rpDd46DUIj6gUM0VpoSz-LtsKV5WAxU-oAfB_1WRYluNQ=s0-d-e1-ft#https://www.reyhoon.com/assets/img/emails/welcomeemail/facebook_icon.png" alt=""></a>
    </div>
    <div style="text-align: center; margin-top: 20px;color:#a0a0a0;font-size: 12px">
        ویگوبازار بازار بزرگ خرید  و فروش اینترنتی ایران  <br><br>
        021-77343349 - info@wegobazaar.com
    </div>
    </p>
</div>
</body>
</html>