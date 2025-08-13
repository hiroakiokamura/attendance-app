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

    'accepted' => ':attributeを承認してください。',
    'accepted_if' => ':otherが:valueの場合、:attributeを承認してください。',
    'active_url' => ':attributeが有効なURLではありません。',
    'after' => ':attributeには、:date以降の日付を指定してください。',
    'after_or_equal' => ':attributeには、:date以降の日付を指定してください。',
    'alpha' => ':attributeはアルファベットのみがご利用できます。',
    'alpha_dash' => ':attributeはアルファベットとダッシュ(-)及び下線(_)がご利用できます。',
    'alpha_num' => ':attributeはアルファベット数字がご利用できます。',
    'array' => ':attributeは配列を指定してください。',
    'ascii' => ':attributeは半角英数字と記号のみ使用できます。',
    'before' => ':attributeには、:date以前の日付をご利用ください。',
    'before_or_equal' => ':attributeには、:date以前の日付をご利用ください。',
    'between' => [
        'array' => ':attributeは:min〜:max個を指定してください。',
        'file' => ':attributeのファイルは、:min〜:maxキロバイトの間で指定してください。',
        'numeric' => ':attributeは:min〜:maxの間で指定してください。',
        'string' => ':attributeは:min〜:max文字の間でご入力ください。',
    ],
    'boolean' => ':attributeはtrueかfalseを指定してください。',
    'can' => ':attributeフィールドには認可されていない値が含まれています。',
    'confirmed' => ':attributeと確認フィールドとが、一致していません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attributeには有効な日付を指定してください。',
    'date_equals' => ':attributeには、:dateと同じ日付けを指定してください。',
    'date_format' => ':attributeは:format形式で指定してください。',
    'decimal' => ':attributeは小数点以下:decimal桁で指定してください。',
    'declined' => ':attributeを拒否してください。',
    'declined_if' => ':otherが:valueの場合、:attributeを拒否してください。',
    'different' => ':attributeと:otherには、異なった内容を指定してください。',
    'digits' => ':attributeは:digits桁で指定してください。',
    'digits_between' => ':attributeは:min桁から:max桁の間で指定してください。',
    'dimensions' => ':attributeの図形サイズが正しくありません。',
    'distinct' => ':attributeフィールドに重複した値があります。',
    'doesnt_end_with' => ':attributeには、:valuesのどれかで終わらない値を指定してください。',
    'doesnt_start_with' => ':attributeには、:valuesのどれかで始まらない値を指定してください。',
    'email' => ':attributeには、有効なメールアドレスを指定してください。',
    'ends_with' => ':attributeには、:valuesのどれかで終わる値を指定してください。',
    'enum' => '選択された:attributeは正しくありません。',
    'exists' => '選択された:attributeは正しくありません。',
    'extensions' => ':attributeには:extensionsタイプのファイルを指定してください。',
    'file' => ':attributeにはファイルを指定してください。',
    'filled' => ':attributeに値を指定してください。',
    'gt' => [
        'array' => ':attributeには、:value個より多くのアイテムを指定してください。',
        'file' => ':attributeには、:valueキロバイトより大きなファイルを指定してください。',
        'numeric' => ':attributeには、:valueより大きな値を指定してください。',
        'string' => ':attributeは、:value文字より長く指定してください。',
    ],
    'gte' => [
        'array' => ':attributeには、:value個以上のアイテムを指定してください。',
        'file' => ':attributeには、:valueキロバイト以上のファイルを指定してください。',
        'numeric' => ':attributeには、:value以上の値を指定してください。',
        'string' => ':attributeは、:value文字以上で指定してください。',
    ],
    'hex_color' => ':attributeには、有効な16進数の色を指定してください。',
    'image' => ':attributeには画像ファイルを指定してください。',
    'in' => '選択された:attributeは正しくありません。',
    'in_array' => ':attributeフィールドには:otherの値が存在しません。',
    'integer' => ':attributeは整数で指定してください。',
    'ip' => ':attributeには、有効なIPアドレスを指定してください。',
    'ipv4' => ':attributeには、有効なIPv4アドレスを指定してください。',
    'ipv6' => ':attributeには、有効なIPv6アドレスを指定してください。',
    'json' => ':attributeには、有効なJSON文字列を指定してください。',
    'list' => ':attributeフィールドはリストである必要があります。',
    'lowercase' => ':attributeは小文字である必要があります。',
    'lt' => [
        'array' => ':attributeには、:value個より少ないアイテムを指定してください。',
        'file' => ':attributeには、:valueキロバイトより小さなファイルを指定してください。',
        'numeric' => ':attributeには、:valueより小さな値を指定してください。',
        'string' => ':attributeは、:value文字より短く指定してください。',
    ],
    'lte' => [
        'array' => ':attributeには、:value個以下のアイテムを指定してください。',
        'file' => ':attributeには、:valueキロバイト以下のファイルを指定してください。',
        'numeric' => ':attributeには、:value以下の値を指定してください。',
        'string' => ':attributeは、:value文字以下で指定してください。',
    ],
    'mac_address' => ':attributeは有効なMACアドレスである必要があります。',
    'max' => [
        'array' => ':attributeは:max個以下指定してください。',
        'file' => ':attributeのファイルは、:maxキロバイト以下にしてください。',
        'numeric' => ':attributeには、:max以下の数字を指定してください。',
        'string' => ':attributeは、:max文字以下で指定してください。',
    ],
    'max_digits' => ':attributeは:max桁以下で指定してください。',
    'mimes' => ':attributeには:valuesタイプのファイルを指定してください。',
    'mimetypes' => ':attributeには:valuesタイプのファイルを指定してください。',
    'min' => [
        'array' => ':attributeは:min個以上指定してください。',
        'file' => ':attributeのファイルは、:minキロバイト以上にしてください。',
        'numeric' => ':attributeには、:min以上の数字を指定してください。',
        'string' => ':attributeは、:min文字以上で指定してください。',
    ],
    'min_digits' => ':attributeは:min桁以上で指定してください。',
    'missing' => ':attributeフィールドが存在してはいけません。',
    'missing_if' => ':otherが:valueの場合、:attributeフィールドが存在してはいけません。',
    'missing_unless' => ':otherが:valueでない場合、:attributeフィールドが存在してはいけません。',
    'missing_with' => ':valuesが存在する場合、:attributeフィールドが存在してはいけません。',
    'missing_with_all' => ':valuesが存在する場合、:attributeフィールドが存在してはいけません。',
    'multiple_of' => ':attributeは:valueの倍数である必要があります。',
    'not_in' => '選択された:attributeは正しくありません。',
    'not_regex' => ':attributeの形式が正しくありません。',
    'numeric' => ':attributeには、数字を指定してください。',
    'password' => [
        'letters' => ':attributeは文字を含む必要があります。',
        'mixed' => ':attributeは大文字と小文字を含む必要があります。',
        'numbers' => ':attributeは数字を含む必要があります。',
        'symbols' => ':attributeは記号を含む必要があります。',
        'uncompromised' => '指定された:attributeはデータ漏洩に含まれています。別の:attributeを選択してください。',
    ],
    'present' => ':attributeフィールドが存在していません。',
    'present_if' => ':otherが:valueの場合、:attributeフィールドが存在している必要があります。',
    'present_unless' => ':otherが:valueでない場合、:attributeフィールドが存在している必要があります。',
    'present_with' => ':valuesが存在する場合、:attributeフィールドが存在している必要があります。',
    'present_with_all' => ':valuesが存在する場合、:attributeフィールドが存在している必要があります。',
    'prohibited' => ':attributeフィールドは禁止されています。',
    'prohibited_if' => ':otherが:valueの場合、:attributeフィールドは禁止されています。',
    'prohibited_unless' => ':otherが:valuesにない場合、:attributeフィールドは禁止されています。',
    'prohibits' => ':attributeフィールドは:otherの存在を禁じています。',
    'regex' => ':attributeに正しい形式を指定してください。',
    'required' => ':attributeは必ず指定してください。',
    'required_array_keys' => ':attributeフィールドには、:valuesのエントリが含まれている必要があります。',
    'required_if' => ':otherが:valueの場合、:attributeも指定してください。',
    'required_if_accepted' => ':otherが承認された場合、:attributeフィールドは必須です。',
    'required_unless' => ':otherが:valuesでない場合、:attributeを指定してください。',
    'required_with' => ':valuesを指定する場合は、:attributeも指定してください。',
    'required_with_all' => ':valuesを指定する場合は、:attributeも指定してください。',
    'required_without' => ':valuesを指定しない場合は、:attributeを指定してください。',
    'required_without_all' => ':valuesのどれも指定しない場合は、:attributeを指定してください。',
    'same' => ':attributeと:otherには同じ値を指定してください。',
    'size' => [
        'array' => ':attributeは:size個指定してください。',
        'file' => ':attributeのファイルは、:sizeキロバイトにしてください。',
        'numeric' => ':attributeには:sizeを指定してください。',
        'string' => ':attributeは:size文字で指定してください。',
    ],
    'starts_with' => ':attributeには、:valuesのどれかで始まる値を指定してください。',
    'string' => ':attributeは文字列を指定してください。',
    'timezone' => ':attributeには、有効なタイムゾーンを指定してください。',
    'unique' => ':attributeの値は既に存在しています。',
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'uppercase' => ':attributeは大文字である必要があります。',
    'url' => ':attributeに正しい形式を指定してください。',
    'ulid' => ':attributeは有効なULIDである必要があります。',
    'uuid' => ':attributeに有効なUUIDを指定してください。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "rule.attribute" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'お名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード（確認）',
        'current_password' => '現在のパスワード',
        'attendance_id' => '勤怠記録',
        'request_type' => '修正項目',
        'requested_time' => '修正希望時刻',
        'reason' => '修正理由',
        'admin_comment' => '管理者コメント',
        'clock_in' => '出勤時刻',
        'clock_out' => '退勤時刻',
        'break_start' => '休憩開始時刻',
        'break_end' => '休憩終了時刻',
        'notes' => '備考',
        'work_date' => '勤務日',
    ],
];
