<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$signupErrorActive = false;

if (isset($_SESSION['signup-error'])) {
    $signupErrorActive = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="assets/css/stylelogin.css" />
    
</head>

<body style="background-image: url('assets/images/orangepinkbackground.jpg'); background-repeat: no-repeat; background-size: cover;">

                
    <div class="container <?php echo $signupErrorActive ? 'right-panel-active' : ''; ?>" id="container">

        <div class="form-container sign-up-container">
            <form action="register_process.php" method="POST">
                <h1 style="font-size:30px" class="mt-3">Create Account</h1>
                <br />
                <input type="text" name="uFirst" placeholder="First name" required value="<?php echo isset($_SESSION['signup-data']['uFirst']) ? htmlspecialchars($_SESSION['signup-data']['uFirst']) : ''; ?>" />
                <input type="text" name="uLast" placeholder="Last Name" required value="<?php echo isset($_SESSION['signup-data']['uLast']) ? htmlspecialchars($_SESSION['signup-data']['uLast']) : ''; ?>" />
                <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_SESSION['signup-data']['email']) ? htmlspecialchars($_SESSION['signup-data']['email']) : ''; ?>" />
                <input type="password" name="password" placeholder="Password" required minlength="6" />
                <input type="password" name="confirm_password" placeholder="Confirm Password" required />
                <br />

                <?php
                if (isset($_SESSION['signup-error'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['signup-error'] . '</div>';
                    unset($_SESSION['signup-error']);
                    
                }
                if (isset($_SESSION['success'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                    unset($_SESSION['success']);
                }
                ?>
                
                <button type="submit" class="mb-3">Sign Up</button>
            </form>
        </div>
        <div class="form-container sign-in-container">
            <form action="login_process.php" method="POST">
                <h1>Log in</h1>
                <br />
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password" required />
                <br />
                <?php
                if (!$signupErrorActive && isset($_SESSION['login-error'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['login-error'] . '</div>';
                    unset($_SESSION['login-error']);
                }
                ?>
                <button type="submit">Log In</button>
            </form>
        </div>
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Already have an account?</h1>
                    <button class="ghost" id="signIn"  style="margin-top: 30px">Sign In</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>Hello, Friend!</h1>
                    <p>Need an account? Sign up for free!</p>
                    <button class="ghost" id="signUp">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Login -->
    <div class="mobile-login-wrapper" style="display: none;">
        <h2 class="fw-semibold">Login</h2>
        <form action="login_process.php" method="POST" style="width: 100%; padding-left: 0; padding-right: 0;">
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="password" placeholder="Password" required />
            <?php
            if (!$signupErrorActive && isset($_SESSION['login-error'])) {
                echo '<div class="alert alert-danger mt-2">' . $_SESSION['login-error'] . '</div>';
                unset($_SESSION['login-error']);
            }
            ?>
            <button type="submit" class="mt-2">Login</button>
        </form>
        <div style="margin-top: 20px; text-align:center;">
            <a href="#" onclick="showSignup()">Sign up</a>
        </div>
    </div>

    <!-- Mobile Sign Up -->
    <div class="mobile-signup-wrapper" style="display: none;">
        <h2 class="fw-semibold">Sign Up</h2>
        <form action="register_process.php" method="POST" style="width: 100%; padding-left: 0; padding-right: 0;">
            <input type="text" name="uFirst" placeholder="First name" required />
            <input type="text" name="uLast" placeholder="Last name" required />
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="password" placeholder="Password" required />
            <input type="password" name="confirm_password" placeholder="Confirm Password" required />
            <button type="submit" class="mt-2">Sign Up</button>
        </form>
        <div style="margin-top: 20px; text-align:center;">
            <a href="#" onclick="showLogin()">Already have an account? Log in</a>
        </div>
    </div>

    <!-- Switch Panels JS -->
    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('container');

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });
    </script>

    <!-- Mobile Toggle Script -->
    <script>

    function showSignup() {
        document.querySelector('.mobile-login-wrapper').style.display = 'none';
        document.querySelector('.mobile-signup-wrapper').style.display = 'flex';
    }

    function showLogin() {
        document.querySelector('.mobile-login-wrapper').style.display = 'flex';
        document.querySelector('.mobile-signup-wrapper').style.display = 'none';
    }

       function toggleMobileDesktopView() {
    const isMobile = window.innerWidth <= 768;
    const mobileLogin = document.querySelector('.mobile-login-wrapper');
    const mobileSignup = document.querySelector('.mobile-signup-wrapper');
    const container = document.getElementById('container');

    if (isMobile) {
        container.style.display = 'none';
        // Show mobile login by default
        mobileLogin.style.display = 'flex';
        mobileSignup.style.display = 'none';
    } else {
        container.style.display = 'block';
        mobileLogin.style.display = 'none';
        mobileSignup.style.display = 'none';
    }
}

document.addEventListener("DOMContentLoaded", toggleMobileDesktopView);
window.addEventListener('resize', toggleMobileDesktopView);  
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
