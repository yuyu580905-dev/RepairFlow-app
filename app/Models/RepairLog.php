<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairLog extends Model
{
    use HasFactory;

    /**
     * 自動タイムスタンプ更新を無効にする。
     *
     * created_at はデータベースのデフォルト値で設定する。
     */
    public $timestamps = false;

    /**
     * 一括代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'repair_request_id',
        'user_id',
        'action',
        'message',
        'from_status',
        'to_status',
    ];

    /**
     * 修理履歴が属する修理依頼を取得する。
     *
     * @return BelongsTo<RepairRequest, $this>
     */
    public function repairRequest(): BelongsTo
    {
        return $this->belongsTo(RepairRequest::class);
    }

    /**
     * 修理履歴を記録したユーザーを取得する。
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
