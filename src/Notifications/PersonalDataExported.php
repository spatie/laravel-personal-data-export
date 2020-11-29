<?php

namespace Spatie\PersonalDataExport\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class PersonalDataExported extends Notification
{
    /**
     * The callback that should be used to build the mail message.
     *
     * @var \Closure|null
     */
    public static $toMailCallback;

    /** @var string */
    public $zipFilename;

    /** @var \Illuminate\Support\Carbon */
    public $deletionDatetime;

    public function __construct(string $zipFilename)
    {
        $this->zipFilename = $zipFilename;

        $this->deletionDatetime = now()->addDays(config('personal-data-export.delete_after_days'));
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $downloadUrl = route('personal-data-exports', $this->zipFilename);

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $downloadUrl);
        }

        return (new MailMessage)
            ->subject(Lang::get('Personal Data Download'))
            ->line(Lang::get('Please click the button below to download a zip file containg all data we got for your account.'))
            ->action(Lang::get('Download Zip File'), $downloadUrl)
            ->line(Lang::get('This file will be deleted at ' . $this->deletionDatetime->format('Y-m-d H:i:s') . '.'));
    }

    public static function toMailUsing($callback)
    {
        static::$toMailCallback = $callback;
    }
}
