// here form id selector is "wcfm_membership_registration_form"
// form button id selector is "wcfm_membership_register_button"
/* note use your own domain sitekey and replace  
'data-sitekey' => "6Lf9N############C2I14xq" 
and 
$recaptcha_secret  "6Lf9NzgqA####################w9BJ-Ma"

put this code to your wordpress functions.php but make sure you backup your website before use it.
*/


// Enqueue Google reCAPTCHA Script
function enqueue_recaptcha_script() {
    wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_recaptcha_script');

// Inject reCAPTCHA into Form and Handle Safari Issues
function add_recaptcha_to_form() {
    ?>
    <style>
        #wcfm_membership_register_button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #ccc; /* Optional: You can change the button color here */
        }
    </style>
    
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('wcfm_membership_registration_form');
            var submitButton = document.getElementById('wcfm_membership_register_button');
            var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

            // Handle cross-site cookie issues in Safari
            if (isSafari) {
                document.cookie = "cross-site-cookie=foo; SameSite=None; Secure";
            }

            if (form && submitButton) {
                // Create and inject the reCAPTCHA div
                var recaptchaDiv = document.createElement('div');
                recaptchaDiv.className = 'g-recaptcha';
                recaptchaDiv.setAttribute('data-sitekey', '6Lf9N############C2I14xq');
                recaptchaDiv.setAttribute('data-callback', 'recaptchaCallback');
                recaptchaDiv.style.textAlign = 'right';
                recaptchaDiv.style.marginBottom = '20px';

                // Insert the reCAPTCHA before the submit button
                submitButton.parentNode.insertBefore(recaptchaDiv, submitButton);

                // Initially disable the submit button
                submitButton.disabled = true;

                // Function to enable/disable submit button based on CAPTCHA state
                window.recaptchaCallback = function() {
                    var recaptchaResponse = grecaptcha.getResponse();
                    if (recaptchaResponse.length > 0) {
                        submitButton.disabled = false; // Enable the submit button
                    } else {
                        submitButton.disabled = true; // Disable if CAPTCHA is unchecked
                    }
                }

                // Handle form submit event to check CAPTCHA
                form.addEventListener('submit', function(event) {
                    if (grecaptcha.getResponse().length === 0) {
                        event.preventDefault(); // Prevent form submission
                        alert('Please complete the reCAPTCHA before submitting the form.');
                    }
                });

                // If on Safari, reload reCAPTCHA after a short delay
                if (isSafari) {
                    setTimeout(function() {
                        grecaptcha.reset(); // Force reload of reCAPTCHA
                    }, 1000); // Delay to ensure the script has loaded
                }
            }
        });
    </script>
    <?php
}
add_action('wp_footer', 'add_recaptcha_to_form');

// Validate reCAPTCHA on Form Submission (Server-Side)
function validate_recaptcha() {
    if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
        $recaptcha_secret = '6Lf9NzgqA####################w9BJ-Ma';
        $response = wp_remote_get("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$_POST['g-recaptcha-response']}");
        $response = json_decode($response['body'], true);

        if (true === $response['success']) {
            // CAPTCHA is successfully validated, continue with form submission
        } else {
            // CAPTCHA validation failed
            wp_die('reCAPTCHA validation failed. Please try again.');
        }
    } else {
        // CAPTCHA not checked
        wp_die('Please complete the reCAPTCHA before submitting.');
    }
}
add_action('wcfm_membership_registration_before', 'validate_recaptcha');
