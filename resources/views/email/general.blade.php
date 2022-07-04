<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div style="width:80%;height:auto; max-width: 500px;font-family:arial;direction:rtl;padding:20px;margin:0 auto;background: #f5f5f5; box-shadow: 0 3px 10px 0 rgba(0,0,0,.18);">
    <figure style="margin: 20px 0; text-align: center;">
        <img src="https://api.wegobazaar.com/logo.png" alt="wegobazaar.com" style="width: 150px;" />
    </figure>
    <h1 style="width:100%;text-align:center;padding-bottom:10px;font-weight:normal;margin-bottom:20px;border-bottom:2px solid #e0e0e0;">
        <span style="font-size: 20px">{{$subject}}</span>
    </h1>
    <p class="email-description" style="font-size:13px;line-height:25px;text-align:justify;">
        <?php echo nl2br($description); ?>
    </p>
    @foreach($images as $image)
    <figure style="text-align: center;margin-top: 50px;">
        <a href="{{$image['href']}}">
            <img src="{{$image['src']}}" style="width: 80%;max-width: 310px;text-indent: 10000000px; min-width: 200px" alt="">
        </a>
    </figure>
    @endforeach
    <div style="text-align: center;margin-top: 50px;">
        <a href="{{$buttonHref}}">
            <button style="border:none;outline:none;padding:0;background: #ffa21c; font-size: 1.2em; color: #fff;  border-radius: 3px;  box-shadow: 0 3px 3px 0 rgba(0,0,0,.18);  text-align: center;  width: 260px;  position: relative;  height: 36px;  overflow: hidden;  padding: 0 4px;  box-sizing: border-box;">{{$buttonText}}</button>
        </a>
    </div>
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