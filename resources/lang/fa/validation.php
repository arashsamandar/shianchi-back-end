<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'alpha' => ':attribute باید شامل حروف باشد.',
    'alpha_dash' => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num' => 'The :attribute may only contain letters and numbers.',
    'alpha_spaces' => ':attribute باید شامل حروف باشد.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'between' => [
        'numeric' => ':attribute باید بین :min تا :max رقم باشد',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => ':attribute باید بین :min تا :max کاراکتر باشد',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'date' => 'The :attribute is not a valid date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => ':attribute باید بین :min تا :max رقم باشد',
    'email' => 'The :attribute must be a valid email address.',
    'exists' => ' :attribute قابل تغییر نیست',
    'filled' => 'The :attribute field is required.',
    'image' => ':attribute باید عکس باشد',
    'in' => 'The selected :attribute is invalid.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'max' => [
        'numeric' => ':attribute نمی تواند بیشتر از  :max باشد',
        'file' => ':attribute نمی تواند بیشتر از :max کیلو بایت باشد.',
        'string' => 'The :attribute may not be greater than :max characters.',
        'array' => 'The :attribute may not have more than :max items.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'numeric' => ':attribute نمی تواند کمتر از  :min باشد',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => ':attribute باید حداقل شامل :min کاراکتر باشد',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'not_in' => 'The selected :attribute is invalid.',
    'numeric' => ':attribute باید فقط شامل عدد باشد',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'لطفا قسمت :attribute را وارد نمایید',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values is present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file' => ':attribute باید :size کیلوبایت باشد',
        'string' => 'The :attribute must be :size characters.',
        'array' => 'The :attribute must contain :size items.',
    ],
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid zone.',
    'unique' => ':attribute وارد شده قبلا ثبت شده است.',
    'url' => 'The :attribute format is invalid.',
    'card' => 'شماره کارت وارد شده صحیح نمی باشد.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */


    'custom' => [
        'authentication_failure' => 'کلمه عبور یا نام کاربری به درستی وارد نشده است',
        "g-recaptcha-response" => [
            "required" => "لطفا از کادر سفید روی قسمت من ربات نیستم کلیک نمایید",
        ],
        "pic" => [
            "required" => "لطفا عکس محصول خود را وارد نمایید",
            "max" => 'حداکثر اندازه عکس :max کیلوبایت می باشد',
            "min" => "عکس به درستی وارد نشده است",
            "dimensions" => "حداقل ابعاد عکس 150*150 میباشد",
            'image' => 'فرمت تصویر ارسالی صحیح نیست',

        ],
        "inside_store_pic" => [
            "required" => "لطفا عکس مربوط به فروشگاه را وارد نمایید",
            "max" => 'حداکثر اندازه عکس :max کیلوبایت می باشد',
            "min" => "عکس به درستی وارد نشده است",
            "dimensions" => "حداقل ابعاد عکس 150*150 میباشد",
            'image' => 'فرمت تصویر ارسالی صحیح نیست',

        ],
        "thumbnail_store_pic" => [
            "required" => "لطفا عکس مربوط به فروشگاه را وارد نمایید",
            "max" => 'حداکثر اندازه عکس :max کیلوبایت می باشد',
            "min" => "عکس به درستی وارد نشده است",
            "dimensions" => "حداقل ابعاد عکس 130*130 میباشد",
            'image' => 'فرمت تصویر ارسالی صحیح نیست',

        ],
        "cover_store_pic" => [
            "required" => "لطفا عکس مربوط به فروشگاه را وارد نمایید",
            "max" => 'حداکثر اندازه عکس :max کیلوبایت می باشد',
            "min" => "عکس به درستی وارد نشده است",
            "dimensions" => "حداقل ابعاد عکس 200*800 میباشد",
            'image' => 'فرمت تصویر ارسالی صحیح نیست',

        ],
        "email" => [
            "required" => "لطفا ایمیل خود را وارد نمایید",
            "max" => "ایمیل به درستی وارد نشده است",
            "min" => "ایمیل به درستی وارد نشده است",
            "digits" => "ایمیل به درستی وارد نشده است",
            "alpha" => "ایمیل به درستی وارد نشده است",
            "email" => "ایمیل به درستی وارد نشده است",
        ],
        "name" => [
            "required" => "لطفا نام خود را وارد نمایید",
            "max" => "نام به درستی وارد نشده است",
            "min" => "نام به درستی وارد نشده است",
            "digits" => "نام به درستی وارد نشده است",
            "alpha" => "نام به درستی وارد نشده است",
            "email" => "نام به درستی وارد نشده است",
        ],
        "last_name" => [
            "required" => "لطفا نام خانوادگی خود را وارد نمایید",
            "max" => "نام خانوادگی به درستی وارد نشده است",
            "min" => "نام خانوادگی به درستی وارد نشده است",
            "digits" => "نام خانوادگی به درستی وارد نشده است",
            "alpha" => "نام خانوادگی به درستی وارد نشده است",
            "email" => "نام خانوادگی به درستی وارد نشده است",
        ],
        "mobile_number" => [
            "required" => "لطفا شماره همراه خود را وارد نمایید",
            "max" => "شماره همراه به درستی وارد نشده است",
            "min" => "شماره همراه به درستی وارد نشده است",
            "digits" => "شماره همراه به درستی وارد نشده است",
            "alpha" => "شماره همراه به درستی وارد نشده است",
            "email" => "شماره همراه به درستی وارد نشده است",
        ],
        "national_code" => [
            "required" => "لطفا کد ملی خود را وارد نمایید",
            "max" => "کد ملی به درستی وارد نشده است",
            "min" => "کد ملی به درستی وارد نشده است",
            "digits" => "کد ملی به درستی وارد نشده است",
            "alpha" => "کد ملی به درستی وارد نشده است",
            "email" => "کد ملی به درستی وارد نشده است",
        ],
        "password" => [
            "required" => "لطفا کلمه عبور خود را وارد نمایید",
            "max" => "کلمه عبور به درستی وارد نشده است",
            "min" => "کلمه عبور به درستی وارد نشده است",
            "digits" => "کلمه عبور به درستی وارد نشده است",
            "alpha" => "کلمه عبور به درستی وارد نشده است",
            "email" => "کلمه عبور به درستی وارد نشده است",
            "confirmed" => "رمز عبور با تکرار رمز عبور تطابق ندارد",
        ],
        "departments.*.department_manager_first_name" => [
            'required' => 'نام کوچک مسئول دپارتمان را وارد نمایید',
            'alpha_spaces' => 'نام کوچک مسئول دپارتمان باید فقط شامل حروف باشد'
        ],
        "departments.*.department_prefix_phone_number" => [
            'required' => 'پیش شماره را وارد نمایید',
            'numeric' => 'پیش شماره باید فقط شامل عدد باشد'
        ],
        "departments.*.department_phone_number" => [
            'required' => 'شماره تلفن دپارتمان را وارد نمایید',
            'numeric' => 'شماره تلفن دپارتمان باید شامل عدد باشد'
        ],
        "departments.*.department_email" => [
            'required' => 'ایمیل دپارتمان را وارد نمایید',
            'email' => "ایمیل به درستی وارد نشده است"
        ],
        "departments.*.department_manager_picture" => [
            'required' => 'تصویر مسئول دپارتمان را وارد نمایید',
        ],
        'phones.*.prefix_phone_number' => [
            'digits_between' => 'پیش شماره باید بین :min تا :max رقم باشد'
        ],
        'phones.*.phone_number' => [
            'digits_between' => 'شماره باید بین :min تا :max رقم باشد'
        ],
        'participant.email' => [
            "required" => "لطفا ایمیل خود را وارد نمایید",
            "max" => "ایمیل به درستی وارد نشده است",
            "min" => "ایمیل به درستی وارد نشده است",
            "digits" => "ایمیل به درستی وارد نشده است",
            "alpha" => "ایمیل به درستی وارد نشده است",
            "email" => "ایمیل به درستی وارد نشده است",
        ],
        'modify.*.english_name' => [
            'exists' => 'نام انگلیسی قابل تغییر نیست',
            'unique' => 'نام انگلیسی ثبت شده قبلا وارد شده است',
            'alpha' => 'نام انگلیسی باید فقط شامل حروف باشد',
            'required' => 'لطفا نام انگلیسی را وراد نمایید'
        ],
        'modify.*.is_leaf' => [
            'required' => 'لطفا قسمت برگ را وارد نمایید',
            'in' => 'مقدار وارد شدی برای برگ مجاز نیست'
        ],
        'modify.*.persian_name' => [
            'required' => 'لطفا نام فارسی را وارد نمایید'
        ],
        'modify.*.category_id' => [
            'required' => 'لطفا شناسه دسته را وارد نمایید'
        ],
        'modify.*.id' => [
            'required' => 'لطفا شناسه را وارد نمایید'
        ],
        'modify.*.english_path' => [
            'required' => 'لطفا مسیر انگلیسی را وارد نمایید'
        ],
        'modify.*.unit' => [
            'required' => 'لطفا واحد را وارد نمایید'
        ],
        'delete.*' => [
            'numeric' => 'حذف دسته بندی فقط باید شامل عدد باشد'
        ],
        'special.*.amount' => [
            'max' => 'تخفیف نمی تواند بیشتر از :max تومان باشد'
        ],
        'product_id' => [
            'unique'=>'مشخصات فروش درج شده قبلا ثبت شده است'
        ],
        'color_id' => [
            'alpha'=> 'این کالا دارای مشخصات فروش بدون رنگ است'
        ],
        'sliders.*.priority'=>[
            'distinct' => 'لطفا اولویت ها را درست وارد نمایید'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'inside_store_pic' => 'عکس فضای داخل فروشگاه',
        'english_name' => 'نام انگلیسی',
        "description" => 'توضیحات',
        'persian_name' => "نام فارسی",
        'current_price' => 'قیمت',
        'weight' => 'وزن',
        'warranty_name' => 'نام گارنتی',
        'warranty_text' => 'توضیحات گارانتی',
        'wego_coin_need' => 'ویگو سکه های دریافتی از این محصول',
        'quantity' => 'تعداد',
        'picture' => 'عکس',
        'value' => 'ویژگی های تخصصی',
        'name' => 'نام',
        'last_name' => 'نام خانوادگی',
        'email' => 'ایمیل',
        'mobile_number' => 'شماره همراه',
        'national_code' => 'کد ملی',
        'password' => 'کلمه عبور',
        'type' => 'نوع',
        'current_password' => 'کلمه عبور فعلی',
        'fax_number' => 'شماره فکس',
        '*.*.prefix_phone_number' => 'پیش شماره',
        'key_name' => 'نام کلیدی',
        'category_id' => 'گروه کالا',
        'values' => 'ویژگی های تخصصی',
        'message' => 'پیام',
        'participant.name' => 'نام',
        'participant.phone_number' => 'شماره تماس',
        'participant.email' => 'ایمیل',
        "phone_number"=>"شماره تماس"
    ],

];
