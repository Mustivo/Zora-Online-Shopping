<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Only require autoload if it exists (Composer finished)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . $host . '/commerce/');
}

if (!function_exists('build_zora_email_template')) {
    function build_zora_email_template($bodyContent, $subject = 'Zora Online Shopping Rwanda', $preheader = '') {
        $baseUrl = rtrim(defined('BASE_URL') ? BASE_URL : 'http://localhost/commerce/', '/') . '/';
        $currentYear = date('Y');
        
        // Clean preheader for email client inbox snippet preview
        $preheaderHtml = !empty($preheader) ? '<div style="display: none; max-height: 0px; overflow: hidden; font-size: 1px; line-height: 1px; color: #ffffff; opacity: 0;">' . htmlspecialchars($preheader) . '</div>' : '';

        return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . htmlspecialchars($subject) . '</title>
<style>
    body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
    body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #f1f5f9; font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
</style>
</head>
<body style="background-color: #f1f5f9; margin: 0; padding: 25px 10px; font-family: \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    ' . $preheaderHtml . '
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed;">
        <tr>
            <td align="center">
                <!-- Main Email Card (Max width 620px) -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 620px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
                    
                    <!-- Top Gradient Accent Bar -->
                    <tr>
                        <td style="background: linear-gradient(90deg, #012a5e 0%, #fb7c00 100%); height: 6px; font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>
                    
                    <!-- Header with Zora Logo Image -->
                    <tr>
                        <td align="center" style="padding: 28px 24px 22px; background-color: #ffffff; border-bottom: 1px solid #f1f5f9;">
                            <a href="' . $baseUrl . '" target="_blank" style="text-decoration: none; display: inline-block;">
                                <img src="cid:zora_logo" alt="ZORA Online Shopping Rwanda" style="max-height: 48px; max-width: 190px; width: auto; height: auto; display: block; margin: 0 auto; object-fit: contain;">
                            </a>
                        </td>
                    </tr>
                    
                    <!-- Main Body Content -->
                    <tr>
                        <td style="padding: 30px 28px; font-size: 15px; line-height: 1.6; color: #334155;">
                            ' . $bodyContent . '
                        </td>
                    </tr>
                    
                    <!-- Footer Section -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 32px 24px; color: #94a3b8; text-align: center; font-size: 13px; line-height: 1.6;">
                            
                            <!-- Brand Title & Tagline -->
                            <div style="margin-bottom: 16px;">
                                <div style="font-weight: 800; color: #ffffff; font-size: 15px; letter-spacing: 0.5px;">ZORA ONLINE SHOPPING RWANDA</div>
                                <div style="color: #94a3b8; font-size: 12px; margin-top: 3px;">Premium Quality &bull; Fast Delivery &bull; Best Prices in Kigali & Across Rwanda</div>
                            </div>
                            
                            <!-- Website Quick Links in Footer -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 18px 0 20px;">
                                <tr>
                                    <td align="center" style="font-size: 12px; font-weight: 600; line-height: 2;">
                                        <a href="' . $baseUrl . 'index.php" target="_blank" style="color: #cbd5e1; text-decoration: none; margin: 0 8px;">Home</a>
                                        <span style="color: #475569;">&bull;</span>
                                        <a href="' . $baseUrl . 'index.php#products" target="_blank" style="color: #cbd5e1; text-decoration: none; margin: 0 8px;">Shop Products</a>
                                        <span style="color: #475569;">&bull;</span>
                                        <a href="' . $baseUrl . 'user_panel.php?tab=orders" target="_blank" style="color: #cbd5e1; text-decoration: none; margin: 0 8px;">Track Orders</a>
                                        <span style="color: #475569;">&bull;</span>
                                        <a href="' . $baseUrl . 'about.php" target="_blank" style="color: #cbd5e1; text-decoration: none; margin: 0 8px;">About Us</a>
                                        <span style="color: #475569;">&bull;</span>
                                        <a href="' . $baseUrl . 'how_to_order.php" target="_blank" style="color: #cbd5e1; text-decoration: none; margin: 0 8px;">How to Order</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="font-size: 12px; font-weight: 600; line-height: 2; padding-top: 4px;">
                                        <a href="' . $baseUrl . 'returns.php" target="_blank" style="color: #94a3b8; text-decoration: none; margin: 0 8px;">Returns & Refunds</a>
                                        <span style="color: #475569;">&bull;</span>
                                        <a href="' . $baseUrl . 'privacy.php" target="_blank" style="color: #94a3b8; text-decoration: none; margin: 0 8px;">Privacy Policy</a>
                                        <span style="color: #475569;">&bull;</span>
                                        <a href="' . $baseUrl . 'terms.php" target="_blank" style="color: #94a3b8; text-decoration: none; margin: 0 8px;">Terms of Service</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <hr style="border: 0; border-top: 1px solid #1e293b; margin: 18px 0;">
                            
                            <!-- Help & Copyright Notice -->
                            <div style="font-size: 11px; color: #64748b; line-height: 1.5;">
                                Need assistance? Reply directly to this email or visit <a href="' . $baseUrl . '" target="_blank" style="color: #fb7c00; text-decoration: underline;">zora.rw</a>.<br>
                                &copy; ' . $currentYear . ' Zora Online Shopping Rwanda. All rights reserved. Kigali, Rwanda.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }
}

function sendMail($to, $toName, $subject, $htmlBody, $embeddedImages = []) {
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer is not installed. Run composer require phpmailer/phpmailer");
        return false;
    }

    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'zorarwanda@gmail.com';
        $mail->Password   = 'haby gymy gprh zfky';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->Timeout    = 10;

        // Recipients
        $mail->setFrom('zorarwanda@gmail.com', 'Zora Online Shopping Rwanda');
        $mail->addAddress($to, $toName);

        // Attach Zora Logo if available and not already specified
        if (!isset($embeddedImages['zora_logo'])) {
            $logoCandidatePaths = [
                __DIR__ . '/../uploads/logo.png',
                __DIR__ . '/../assets/images/logo.png',
                __DIR__ . '/../assets/logo.png'
            ];
            foreach ($logoCandidatePaths as $lp) {
                if (file_exists($lp)) {
                    $embeddedImages['zora_logo'] = $lp;
                    break;
                }
            }
        }

        // If body does not already contain full HTML layout, wrap in standard Zora branded template
        if (stripos($htmlBody, '<!DOCTYPE') === false && stripos($htmlBody, '<html') === false) {
            $finalHtml = build_zora_email_template($htmlBody, $subject);
        } else {
            $finalHtml = $htmlBody;
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $finalHtml;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $finalHtml));

        // Embedded Images
        foreach ($embeddedImages as $cid => $path) {
            if (file_exists($path)) {
                $mail->addEmbeddedImage($path, $cid);
            }
        }

        return $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
