<?php
    session_start();
    $event_id  = isset($_GET['event_id']) ? (int)$_GET['event_id'] : null;
    $signup_id = isset($_GET['signup_id']) ? (int)$_GET['signup_id'] : null;
    if (!$event_id || !$signup_id) { header("location: view_events.php"); exit; }
    include('header.php');
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
                        <form action="payment_process.php" method="post">
                            <input type="hidden" name="payment_type" value="signup">
                            <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                            <input type="hidden" name="signup_id" value="<?php echo $signup_id; ?>">                         
                            <input type="hidden" name="amount" value="500">
                            <button type="submit" class="btn btn-primary w-100">Order Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="view_events.php" style="color: #94a3b8;">Skip for now and go back to events</a>
        </div>
    </div>
</div>