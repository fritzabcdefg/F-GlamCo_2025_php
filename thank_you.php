<?php
session_start();
include('./includes/auth_user.php');
include('./includes/header.php');
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Helvetica+Neue:wght@400;600&display=swap');

    body {
        background: #f5f5f5;
        font-family: 'Helvetica Neue', 'Helvetica World', Arial, sans-serif;
        margin: 0;
        padding: 0;
    }

    .thankyou-container {
        min-height: 60vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px;
    }

    .thankyou-box {
        background: #ffffff; /* ✅ clean white container */
        padding: 60px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        text-align: center;
        max-width: 600px;
        width: 100%;
    }

    .thankyou-box h1 {
        font-size: 2rem;
        color: #C71585;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .thankyou-box p {
        font-size: 1.1rem;
        color: #333;
        margin: 8px 0;
    }
</style>

<div class="thankyou-container">
    <div class="thankyou-box">
        <h1>Thank you for purchasing!</h1>
        <p>Your order has been placed successfully.</p>
        <p>Please check your email for confirmation details.</p>
    </div>
</div>

<?php include('./includes/footer.php'); ?>
