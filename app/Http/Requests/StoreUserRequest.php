<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ユーザー登録のリクエストを処理する。
 */
class StoreUserRequest extends FormRequest
{
    /**
     * リクエストを実行できるか判定する。
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /**
     * バリデーションルールを定義する。
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => [
                'required',
                'string',
                Rule::in(['requester', 'technician', 'admin']),
            ],
        ];
    }

    /**
     * エラーメッセージの属性名を定義する。
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => '名前',
            'email' => 'メールアドレス',
            'password' => 'パスワード',
            'role' => 'ユーザー種別',
        ];
    }
}
