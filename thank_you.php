<?php
session_start();
include('./includes/auth_user.php');
include('./includes/header.php');
?>

<style>
    .thankyou-container {
        min-height: 60vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #000000ff;
    }
    .thankyou-box {
        background: #F8BBD0;
        padding: 60px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        text-align: center;
    }
    .thankyou-box h1 {
        font-size: 2.0rem;
        color: #C71585;
        margin-bottom: 10px;
    }
    .thankyou-box p {
        font-size: 1.3rem;
        color: #000000ff;
    }
</style>

<div class="thankyou-container">
    <div class="thankyou-box">
        <h1> Thank you for purchasing!</h1>
        <p>Your order has been placed successfully. </p>
        <p>Please check your email for confirmation details.</p>
    </div>
</div>

<?php include('./includes/footer.php'); ?>
