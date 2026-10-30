<?php

namespace App\Http\Requests;

use App\Models\CancellationRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReviewCancellationRequestRequest extends FormRequest
{
    /**
     * 審査リクエストの実行権限を判定する。
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $cancellationRequest = $this->route('cancellationRequest');

        return $user !== null
            && $cancellationRequest instanceof CancellationRequest
            && $user->can('review', $cancellationRequest);
    }

    /**
     * 審査リクエストのバリデーションルールを定義する。
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', 'string', 'in:approved,rejected'],
            'review_comment' => ['nullable', 'string', 'max:2000'],
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
            'decision.required' => '審査結果を選択してください。',
            'decision.string' => '審査結果の指定が正しくありません。',
            'decision.in' => '審査結果は承認または却下を選択してください。',
            'review_comment.string' => '審査コメントは文字列で入力してください。',
            'review_comment.max' => '審査コメントは2000文字以内で入力してください。',
        ];
    }

    /**
     * バリデーション属性名を定義する。
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'decision' => '審査結果',
            'review_comment' => '審査コメント',
        ];
    }
}
