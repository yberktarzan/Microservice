<?php

declare(strict_types=1);

return [
    'required' => ':attribute alanı zorunludur.',
    'email' => ':attribute geçerli bir e-posta adresi olmalıdır.',
    'unique' => ':attribute zaten kullanılmaktadır.',
    'min' => [
        'string' => ':attribute en az :min karakter olmalıdır.',
    ],
    'confirmed' => ':attribute onayı eşleşmiyor.',
    'max' => [
        'string' => ':attribute en fazla :max karakter olabilir.',
    ],
    'string' => ':attribute bir metin olmalıdır.',
    'integer' => ':attribute bir sayı olmalıdır.',
    'exists' => 'Seçilen :attribute geçersiz.',

    'attributes' => [
        'name' => 'ad',
        'email' => 'e-posta adresi',
        'password' => 'şifre',
        'password_confirmation' => 'şifre onayı',
        'token' => 'token',
        'id' => 'ID',
        'hash' => 'hash',
    ],
];
