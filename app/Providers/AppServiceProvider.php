<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
// Email Notifications
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /**
         * Blade Directive
         *
         * @active('route.name')
         */
        Blade::directive('active', function ($expression) {
            return "<?php echo request()->routeIs({$expression}) ? 'active' : ''; ?>";
        });

        // Locale Indonesia
        Carbon::setLocale('id');

        /**
         * ==================================================
         * NOTIFIKASI EMAIL: RESET PASSWORD
         * ==================================================
         */
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            return (new MailMessage)
                ->subject('Permintaan Reset Password')
                ->greeting('Yth. Pengguna,')
                ->line('Kami menerima permintaan untuk melakukan reset password pada akun Anda.')
                ->line('Silakan klik tombol di bawah ini untuk melanjutkan proses reset password.')
                ->action(
                    'Reset Password',
                    url('/reset-password/'.$token)
                )
                ->line('Tautan ini hanya berlaku dalam waktu terbatas demi keamanan akun Anda.')
                ->line('Apabila Anda tidak merasa melakukan permintaan ini, silakan abaikan email ini.');
        });

        /**
         * ==================================================
         * NOTIFIKASI EMAIL: VERIFIKASI EMAIL
         * ==================================================
         */
        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Verifikasi Alamat Email')
                ->greeting('Yth. Pengguna,')
                ->line('Terima kasih telah melakukan pendaftaran akun.')
                ->line('Untuk mengaktifkan akun Anda, silakan lakukan verifikasi alamat email dengan mengklik tombol di bawah ini.')
                ->action('Verifikasi Email', $url)
                ->line('Apabila Anda tidak merasa melakukan pendaftaran akun, silakan abaikan email ini.');
        });
    }
}
