<?php
// Add this section immediately below your Login button in auth/login.php
?>

<button type="submit" class="btn-login">
    Login
</button>

<div class="register-link">
    <p>
        Don't have an account?
        <a href="register.php">Create an account</a>
    </p>

    <p style="margin-top:8px;">
        <a href="forgot-password.php">Forgot your password?</a>
    </p>
</div>

<style>
.register-link{
    margin-top:20px;
    text-align:center;
}

.register-link p{
    margin:0;
    color:#666;
    font-size:15px;
}

.register-link a{
    color:#8B5E3C;
    font-weight:600;
    text-decoration:none;
    transition:.25s;
}

.register-link a:hover{
    color:#C89B3C;
}
</style>
