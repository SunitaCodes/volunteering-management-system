<?php
session_start();
include('header.php'); // Keeps your dark blue VMS theme
?>

<div class="container mt-5 text-center">
    <div style="background: #1e293b; padding: 40px; border-radius: 15px; border: 1px solid #334155;">
        <h1 style="color: #4ade80;">✔ Registration Confirmed!</h1>
        <p class="text-white mt-3">You have successfully joined the initiative. What's next?</p>
        
        <hr style="border-color: #334155; margin: 30px 0;">

        <h3 class="text-white">Get Your Volunteer Gear</h3>
        <p style="color: #94a3b8;">For safety and identification at the site, all volunteers are encouraged to wear the official initiative T-shirt.</p>
        
        <div class="row justify-content-center mt-4">
            <div class="col-md-4">
                <div class="card bg-dark border-secondary text-white">
                    <img src="assets/tshirt_mockup.png" class="card-img-top" alt="T-shirt">
                    <div class="card-body">
                        <h5>Official Volunteer Tee</h5>
                        <p class="small text-secondary">Available in S, M, L, XL</p>
                        <h4 class="text-primary">Rs. 500</h4>
                        <a href="shop.php" class="btn btn-primary w-100">Order Now</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="view_events.php" style="color: #94a3b8;">Skip for now and go back to events</a>
        </div>
    </div>
</div>