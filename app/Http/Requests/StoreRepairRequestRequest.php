<?php

namespace App\Http\Requests;

use App\Models\RepairRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRepairRequestRequest extends FormRequest
{
    /**
     * 修理依頼を登録できるユーザーか判定する。
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', RepairRequest::class) ?? false;
    }

    /**
     * 修理依頼の入力値を検証する。
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'asset_name' => ['required', 'string', 'max:255'],
            'priority' => [
                'required',
                'string',
                Rule::in(['low', 'normal', 'high']),
            ],
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
            'title.required' => '件名を入力してください。',
            'title.max' => '件名は255文字以内で入力してください。',
            'description.required' => '修理内容を入力してください。',
            'location.required' => '発生場所を入力してください。',
            'location.max' => '発生場所は255文字以内で入力してください。',
            'asset_name.required' => '対象設備を入力してください。',
            'asset_name.max' => '対象設備は255文字以内で入力してください。',
            'priority.required' => '優先度を選択してください。',
            'priority.in' => '優先度の選択が正しくありません。',
        ];
    }

    /**
     * バリデーション対象の属性名を定義する。
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => '件名',
            'description' => '修理内容',
            'location' => '発生場所',
            'asset_name' => '対象設備',
            'priority' => '優先度',
        ];
    }
}
