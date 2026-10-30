<?php

namespace App\Providers;

use App\Models\CancellationRequest;
use App\Models\RepairRequest;
use App\Policies\CancellationRequestPolicy;
use App\Policies\RepairRequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * 認証・認可サービスを登録する。
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * モデルとPolicyの対応関係。
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        RepairRequest::class => RepairRequestPolicy::class,
        CancellationRequest::class => CancellationRequestPolicy::class,
    ];

    /**
     * 認可サービスを登録する。
     */
    public function boot(): void
    {
        //
    }
}
