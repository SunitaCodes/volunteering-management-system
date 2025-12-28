<?php 
include('config.php'); 
include('header.php'); 
?>

<div class="container mt-5">
    <h2 class="text-white">Volunteer Store</h2>
    <p class="text-secondary">Support our initiatives by wearing the official gear!</p>
    
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card bg-dark text-white border-secondary">
                <img src="assets/tshirt.png" class="card-img-top" alt="Volunteer T-shirt">
                <div class="card-body">
                    <h5>Official VMS T-Shirt</h5>
                    <p>High-quality cotton for field work.</p>
                    <h4 class="text-success">Rs. 500</h4>
                    <form action="payment_process.php" method="POST">
                        <input type="hidden" name="product_id" value="1">
                        <input type="hidden" name="amount" value="500">
                        <button type="submit" class="btn btn-primary w-100">Buy Now</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>