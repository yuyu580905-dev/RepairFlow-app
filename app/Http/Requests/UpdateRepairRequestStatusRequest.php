<?php

namespace App\Http\Requests;

use App\Models\RepairRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 修理依頼のステータス更新を検証する。
 */
class UpdateRepairRequestStatusRequest extends FormRequest
{
    /**
     * リクエストを実行できるユーザーか判定する。
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $repairRequest = $this->route('repairRequest');

        return $user !== null
            && $user->role === 'technician'
            && $user->is_active
            && $repairRequest instanceof RepairRequest
            && (int) $repairRequest->assigned_to === (int) $user->id;
    }

    /**
     * 入力値のバリデーションルールを返す。
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $repairRequest = $this->route('repairRequest');

        $allowedStatuses = $repairRequest instanceof RepairRequest
            ? match ($repairRequest->status) {
                'received' => ['in_progress', 'on_hold'],
                'in_progress' => ['on_hold', 'completed'],
                'on_hold' => ['in_progress'],
                default => [],
            }
        : [];

        return [
            'status' => [
                'required',
                'string',
                Rule::in($allowedStatuses),
            ],
            'message' => [
                'nullable',
                'string',
                'max:2000',
                'required_if:status,on_hold',
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
            'status.required' => '変更後のステータスを指定してください。',
            'status.string' => 'ステータスの指定が正しくありません。',
            'status.in' => '現在のステータスから変更できない状態が指定されました。',
            'message.required_if' => '保留にする理由を入力してください。',
            'message.string' => '変更理由は文字列で入力してください。',
            'message.max' => '変更理由は2000文字以内で入力してください。',
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
            'status' => 'ステータス',
            'message' => '変更理由',
        ];
    }
}
