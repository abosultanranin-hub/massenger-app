<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class EmailService {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);

        // إعدادات SMTP (مثال: Gmail)
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com'; // غير إلى مزود البريد الخاص بك
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'your-email@gmail.com'; // غير إلى بريدك
        $this->mailer->Password = 'your-app-password'; // كلمة مرور التطبيق
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;

        $this->mailer->setFrom('your-email@gmail.com', 'Chat App');
        $this->mailer->isHTML(true);
    }

    public function sendOtp($email, $otp) {
        try {
            $this->mailer->addAddress($email);
            $this->mailer->Subject = 'رمز التحقق - Chat App';
            $this->mailer->Body = "
                <h2>مرحباً بك في تطبيق الدردشة</h2>
                <p>رمز التحقق الخاص بك هو: <strong>$otp</strong></p>
                <p>هذا الرمز صالح لمدة 5 دقائق.</p>
            ";

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
