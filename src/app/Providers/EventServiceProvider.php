<?php

namespace App\Providers;

use App\Events\InquiryCreated;
use App\Listeners\SendConfirmInquiryNotifications;
use App\Listeners\SendInquiryToAdminNotifications;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        InquiryCreated::class => [
            SendConfirmInquiryNotifications::class,
            SendInquiryToAdminNotifications::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}