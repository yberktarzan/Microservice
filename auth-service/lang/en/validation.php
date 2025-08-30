<?php

declare(strict_types=1);

return [
    'required' => 'The :attribute field is required.',
    'email' => 'The :attribute must be a valid email address.',
    'unique' => 'The :attribute has already been taken.',
    'min' => [
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'confirmed' => 'The :attribute confirmation does not match.',
    'max' => [
        'string' => 'The :attribute may not be greater than :max characters.',
    ],
    'string' => 'The :attribute must be a string.',
    'integer' => 'The :attribute must be an integer.',
    'exists' => 'The selected :attribute is invalid.',

    'attributes' => [
        'name' => 'name',
        'email' => 'email address',
        'password' => 'password',
        'password_confirmation' => 'password confirmation',
        'token' => 'token',
        'id' => 'ID',
        'hash' => 'hash',
    ],
];
