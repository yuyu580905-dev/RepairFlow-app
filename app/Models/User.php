<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 一括代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * シリアライズ時に隠す属性。
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 属性の型変換。
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * 依頼者として作成した修理依頼を取得する。
     *
     * @return HasMany<RepairRequest, $this>
     */
    public function repairRequests(): HasMany
    {
        return $this->hasMany(RepairRequest::class, 'requester_id');
    }

    /**
     * 担当技術者として割り当てられた修理依頼を取得する。
     *
     * @return HasMany<RepairRequest, $this>
     */
    public function assignedRepairRequests(): HasMany
    {
        return $this->hasMany(RepairRequest::class, 'assigned_to');
    }

    /**
     * 自分が記録した修理履歴を取得する。
     *
     * @return HasMany<RepairLog, $this>
     */
    public function repairLogs(): HasMany
    {
        return $this->hasMany(RepairLog::class);
    }

    /**
     * 自分が申請したキャンセル申請を取得する。
     *
     * @return HasMany<CancellationRequest, $this>
     */
    public function cancellationRequests(): HasMany
    {
        return $this->hasMany(CancellationRequest::class, 'requester_id');
    }

    /**
     * 自分が審査したキャンセル申請を取得する。
     *
     * @return HasMany<CancellationRequest, $this>
     */
    public function reviewedCancellationRequests(): HasMany
    {
        return $this->hasMany(CancellationRequest::class, 'reviewed_by');
    }
}
