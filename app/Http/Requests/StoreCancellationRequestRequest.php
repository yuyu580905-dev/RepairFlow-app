<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCancellationRequestRequest extends FormRequest
{
    /**
     * リクエストを実行できるユーザーか判定する。
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $repairRequest = $this->route('repairRequest');

        return $user !== null
            && $user->is_active
            && $user->role === 'requester'
            && $repairRequest !== null
            && (int) $repairRequest->requester_id === (int) $user->id
            && $repairRequest->status === 'received';
    }

    /**
     * 入力値のバリデーションルールを返す。
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:2000'],
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
            'reason.required' => 'キャンセル理由を入力してください。',
            'reason.string' => 'キャンセル理由は文字列で入力してください。',
            'reason.max' => 'キャンセル理由は2000文字以内で入力してください。',
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
            'reason' => 'キャンセル理由',
        ];
    }
}
