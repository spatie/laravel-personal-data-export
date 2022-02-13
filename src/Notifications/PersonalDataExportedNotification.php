<?php

namespace Spatie\PersonalDataExport\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PersonalDataExportedNotification extends Notification
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

        return (new MailMessage())
            ->subject(trans('personal-data-export::notifications.subject'))
            ->line(trans('personal-data-export::notifications.instructions'))
            ->action(trans('personal-data-export::notifications.action'), $downloadUrl)
            ->line(trans(
                'personal-data-export::notifications.deletion_message',
                ['date' => $this->deletionDatetime->format('Y-m-d H:i:s')]
            ));
    }

    public static function toMailUsing($callback)
    {
        static::$toMailCallback = $callback;
    }
}
