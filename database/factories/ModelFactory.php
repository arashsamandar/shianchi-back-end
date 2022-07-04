<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => "باقر ابزار",
        'email' => $faker->email,
        'password' => bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\WorkTime::class, function (Faker\Generator $faker) {
    $days = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه شنبه', 'چهارشنبه', 'پنج شنبه', 'جمعه'];
    $opening = rand(-1, 5);
    return [
        'day' => $days[random_int(0, 6)],
        'opening_time' => ($opening == -1) ? -1 : $opening,
        'closing_time' => ($opening == -1) ? -1 : rand(16, 19),
        'is_closed' => ($opening == -1) ? 1 : 0,
    ];
});

$factory->define(App\Staff::class, function (Faker\Generator $faker) {
    return [
        'last_name' => 'Darabi',
        'mobile' => '09124247487'
    ];
});
$factory->define(App\Department::class, function (Faker\Generator $faker) {
    return [
        'department_name' => $faker->name
    ];
});

$factory->define(App\Category::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'unit' => 'تومان'
    ];
});

$factory->define(App\Specification::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
    ];
});

$factory->define(App\Value::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'specification_id' => 1
    ];
});

$factory->define(App\Store::class, function (Faker\Generator $faker) {
    $name = $faker->name;
    return [
        'english_name' => $name,
        'information' => \Wego\Helpers\PersianFaker::getSentence(),
        'wego_expiration' => $faker->numberBetween(10, 40),
        'shaba_number' => $faker->randomNumber(8),
        'bazaar' => 1,
        "lat" => $faker->latitude,
        "long" => $faker->longitude,
        'url' => strtolower(str_replace(' ', '-', $name)),
        'address' => $faker->address,
        'province_id' => 1698,
        'city' => 'تهران',
        'city_id' => 1,
        'business_license' => $faker->randomNumber(7),
        'fax_number' => $faker->randomNumber(8),
        'about_us' => \Wego\Helpers\PersianFaker::getSentence(),
        'manager_national_code' => $faker->randomNumber(7),
        'manager_first_name' => \Wego\Helpers\PersianFaker::getName(),
        'manager_last_name' => \Wego\Helpers\PersianFaker::getFamily(),
        'manager_picture' => '#',
        'account_number' => $faker->randomNumber(7),
        'card_number' => $faker->creditCardNumber(),
        'card_owner_name' => \Wego\Helpers\PersianFaker::getName() . ' ' . \Wego\Helpers\PersianFaker::getFamily(),
    ];
});

$factory->define(App\Product::class, function (Faker\Generator $faker) {
    return [
        'weight' => random_int(1000, 15000),
        'english_name' => $faker->name,
        'persian_name' => "کفش مخصوص اسکی آلپاین",
        'key_name' => $faker->name,
        'quantity' => $faker->randomNumber(2),
        'store_id' => random_int(1, 10),
        'category_id' => random_int(1, 10),
        'current_price' => (random_int(10000, 100000)),
        'wego_coin_need' => random_int(1, 50),
        'warranty_name' => "گارانتی دو ساله فن آوارهگان",
    ];
});
$factory->define(App\ProductPicture::class, function (Faker\Generator $faker) {
    return [
        'path' => "wego/product/{$faker->randomDigit}.png",
        'type' => random_int(0, 4)
    ];
});

$factory->define(App\ManagerMobile::class, function (Faker\Generator $faker) {
    return [
        'prefix_phone_number' => \Wego\Helpers\PersianFaker::getMobilePrefix(),
        'phone_number' => $faker->randomNumber(7),
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ];
});

$factory->define(App\Guarantee::class, function (Faker\Generator $faker) {

    $nameArray = ['ضمانت تعویض کالا در صورت عدم رضایت تا # روز', 'ضمانت برگشت پول در صورت عدم رضایت تا # روز', 'ضمانت تعویض در صورت نقص فنی و یا ظاهری کالا تا # روز', 'ضمانت برگشت پول در صورت نقص فنی و یا ظاهری کالا تا # روز'];
    return [
        'name' => $nameArray[random_int(0, 3)]
    ];
});

$factory->define(App\Payment::class, function (Faker\Generator $faker) {
    $paymentArray = ['C', 'P', 'M', 'A', 'O'];
    $nameArray = ['پرداخت آنلاین', 'پرداخت نقدی در محل تحویل کالا', 'کارت به کارت ', 'واریز به حساب', 'پرداخت با کارت های عضو شتاب در محل تحویل کالا'];
    return [
        'name' => $nameArray[random_int(0, 3)]

    ];
});

$factory->define(App\BuyerAddress::class, function (Faker\Generator $faker) {

    $array = [
        0 => ["province_id" => 6, "city_id" => 1623],
        1 => ["province_id" => 24, "city_id" => 2347],
        2 => ["province_id" => 17, "city_id" => 2094],
        3 => ["province_id" => 1, "city_id" => 1698]
    ];
    $randomNumber = 3;
    $randomProvinceId = $array[$randomNumber]['province_id'];
    $randomCityId = $array[$randomNumber]['city_id'];
    return [
        'address' => 'حکیمیه بلوار باباییان خیابان پاسگاه',
        'province_id' => $randomProvinceId,
        'city_id' => $randomCityId,
        'postal_code' => $faker->randomNumber(7),
        'phone_number' => $faker->randomNumber(7),
        'prefix_phone_number' => \Wego\Helpers\PersianFaker::getProvincePrefix(),
        'mobile_number' => $faker->randomNumber(7),
        'prefix_mobile_number' => \Wego\Helpers\PersianFaker::getMobilePrefix(),
        'receiver_first_name' => \Wego\Helpers\PersianFaker::getName(),
        'receiver_last_name' => \Wego\Helpers\PersianFaker::getFamily(),

    ];
});

$factory->define(App\Buyer::class, function (Faker\Generator $faker) {

    return [
        'mobile_number' => $faker->randomNumber(7),
        'company_name' => $faker->name
    ];
});

$factory->define(App\StorePhone::class, function (Faker\Generator $faker) {
    return [
        'prefix_phone_number' => \Wego\Helpers\PersianFaker::getProvincePrefix(),
        'phone_number' => $faker->randomNumber(7),
        'type' => 0

    ];
});

$factory->define(App\Color::class, function (Faker\Generator $faker) {
    return [
    ];
});

$factory->define(App\StorePicture::class, function (Faker\Generator $faker) {
    $arrays = ['logo'];
    return [
        'path' => "/wego/store/{$faker->randomDigit}.png",
        'type' => $arrays[0]
    ];
});

$factory->define(App\WegoCoin::class, function (Faker\Generator $faker) {
    return [
        'store_id' => random_int(1, 8),
        'status' => 'a',
        'amount' => $faker->randomNumber(2),
        'expiration' => $faker->dateTime
    ];
});

$factory->define(App\ShippingCompany::class, function (Faker\Generator $faker) {

    return [
        'name' => $faker->name
    ];
});

$factory->define(App\FreeShippingConditions::class, function (Faker\Generator $faker) {
    $nameArray = ['ارسال رایگان به کل کشور به ازای هر میزان خرید.', 'ارسال رایگان به کل کشور به ازای خرید بالای # تومان ', 'ارسال رایگان به شهر $ به ازای هر میزان خرید.', 'ارسال رایگان به شهر $ در ازای خرید بالای # تومان'];

    return [
        'name' => $nameArray[random_int(0, 3)]
    ];
});

$factory->define(App\StoreMenu::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->sentence(3),
        'body' => $faker->sentence(10),
    ];
});
$factory->define(App\Message::class, function (Faker\Generator $faker) {
    return [
        'type' => $faker->name,
        'body' => $faker->sentence(10),
        'phone' => $faker->phoneNumber,
        'sender_id' => $faker->numberBetween(1, 20),
        'receiver_id' => $faker->numberBetween(1, 20)
    ];
});
$factory->define(App\Brand::class, function (Faker\Generator $faker) {
    return [
        'persian_name' => $faker->company,
        'english_name' => $faker->company
    ];
});

$factory->define(App\Title::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->name
    ];
});
$factory->define(App\SpecialCondition::class, function (Faker\Generator $faker) {
    $type = ['تومان', 'عدد'];
    $specType = ['gift', 'discount', 'wego_coin'];

    $whichType = random_int(0, 2);
    $text = null;
    if ($whichType === 0) {
        $text = $faker->sentence();
    }
    return [
        'type' => $specType[random_int(0, 2)],
        'upper_value' => $faker->numberBetween(6000, 56000),
        'upper_value_type' => $type[random_int(0, 1)],
        'amount' => $faker->numberBetween(10, 50),
        'text' => $text
    ];
});

$factory->define(App\ScoreTitle::class, function (Faker\Generator $faker) {
    return [

    ];
});
