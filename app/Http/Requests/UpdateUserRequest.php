<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ユーザー情報更新のリクエストを処理する。
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * リクエストを実行できるか判定する。
     */
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            && ($this->user()?->can('update', $user) ?? false);
    }

    /**
     * バリデーションルールを定義する。
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => [
                'required',
                'string',
                Rule::in(['requester', 'technician', 'admin']),
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * バリデーションエラーメッセージを定義する。
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => '名前を入力してください。',
            'name.string' => '名前は文字列で入力してください。',
            'name.max' => '名前は255文字以内で入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.string' => 'メールアドレスは文字列で入力してください。',
            'email.email' => 'メールアドレスはメール形式で入力してください。',
            'email.max' => 'メールアドレスは255文字以内で入力してください。',
            'email.unique' => 'このメールアドレスはすでに使用されています。',
            'role.required' => 'ユーザー種別を選択してください。',
            'role.string' => 'ユーザー種別の指定が正しくありません。',
            'role.in' => 'ユーザー種別の選択が正しくありません。',
            'is_active.required' => 'アカウント状態を指定してください。',
            'is_active.boolean' => 'アカウント状態の指定が正しくありません。',
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
            'role' => 'ユーザー種別',
            'is_active' => 'アカウント状態',
        ];
    }
}
