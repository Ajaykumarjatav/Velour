<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Notifications\Messages\MailMessage;

/** Platform support contact shown on tenant-facing emails and invoices. */
final class SupportContact
{
    public static function phoneDisplay(): string
    {
        return (string) config('mail.support_phone', '+91 99501 05679');
    }

    public static function emailDisplay(): string
    {
        $email = trim((string) (
            config('mail.purposes.support.from.address')
            ?: config('mail.from.address')
            ?: 'support@easygrox.com'
        ));

        return $email !== '' ? $email : 'support@easygrox.com';
    }

    public static function emailMailtoHref(): string
    {
        return 'mailto:'.self::emailDisplay();
    }

    public static function phoneTelHref(): string
    {
        $digits = preg_replace('/\D+/', '', self::phoneDisplay()) ?: '919950105679';

        return 'tel:+'.$digits;
    }

    public static function phoneWhatsAppHref(): string
    {
        $digits = preg_replace('/\D+/', '', self::phoneDisplay()) ?: '919950105679';

        return 'https://wa.me/'.$digits;
    }

    public static function helpLineHtml(): string
    {
        $phone = e(self::phoneDisplay());
        $email = e(self::emailDisplay());
        $tel = e(self::phoneTelHref());
        $mail = e(self::emailMailtoHref());
        $wa = e(self::phoneWhatsAppHref());

        return 'Need help? <a href="'.$mail.'" style="color:inherit;text-decoration:underline;">'.$email.'</a>'
            .' · Call or WhatsApp <a href="'.$tel.'" style="color:inherit;text-decoration:underline;">'.$phone.'</a>'
            .' · <a href="'.$wa.'" style="color:inherit;text-decoration:underline;">Chat on WhatsApp</a>';
    }

    public static function appendToMailMessage(MailMessage $mail): MailMessage
    {
        return $mail
            ->line('Need help? Email '.self::emailDisplay().' or call/WhatsApp '.self::phoneDisplay().'.')
            ->salutation("Regards,\nEasyGrox");
    }
}
