<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 修理依頼の担当技術者割り当てを検証する。
 */
class AssignRepairTechnicianRequest extends FormRequest
{
    /**
     * リクエストを実行できるユーザーか判定する。
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * 入力値のバリデーションルールを返す。
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'assigned_to' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where('role', 'technician')
                    ->where('is_active', true),
            ],
        ];
    }

    /**
     * バリデーションエラーメッセージを返す。
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'assigned_to.required' => '担当技術者を選択してください。',
            'assigned_to.integer' => '担当技術者の指定が正しくありません。',
            'assigned_to.exists' => '有効な技術者を選択してください。',
        ];
    }

    /**
     * バリデーション属性名を返す。
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'assigned_to' => '担当技術者',
        ];
    }
}
