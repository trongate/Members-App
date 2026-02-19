<!DOCTYPE html>
<html lang="en">
<head>
    <base href="<?= BASE_URL ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/trongate.css">
    <title>Registration Temporarily Unavailable</title>
</head>
<body>
    <div class="container">
        <div class="text-center">
            <h1 class="mt-1">Registration Temporarily Unavailable</h1>
            
            <div class="container-xs">
                <div class="alert alert-warning">
                    <p><strong>We're unable to process your registration request at this time.</strong></p>
                    
                    <p>Our system has detected unusual activity from your network connection. For security reasons, registration has been temporarily restricted.</p>
                    
                    <p>This is an automated security measure designed to protect our platform and its users.</p>
                    
                    <h3 class="mt-2">What You Can Do:</h3>
                    <ol class="text-left">
                        <li><strong>Try again later</strong> - The restriction may be lifted automatically after some time</li>
                        <li>Ensure you're using a standard web browser</li>
                        <li>If you believe this is an error, please contact our support team</li>
                    </ol>
                    
                    <div class="mt-2">
                        <p><em>Thank you for your understanding as we work to maintain a secure environment for all users.</em></p>
                    </div>
                </div>
                
                <div class="text-center mt-2">
                    <a href="<?= BASE_URL ?>" class="button">Return to Homepage</a>
                    <a href="<?= BASE_URL ?>join" class="button alt">Try Registration Again</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>