<style>
  .card {
    max-width: 1300px;
    margin: 20px auto;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    padding: 40px;
    background-color: #fff;
  }

  .card .lead {
    font-size: 1.1rem;
    margin-top: 10px;
    margin-bottom: 20px;
  }

  .features li {
    margin: 12px 0;
    font-size: 1rem;
  }

  @media (max-width: 768px) {
    .card {
      padding: 20px;
    }

    .card h2 {
      font-size: 2rem;
    }

    .features li {
      font-size: 0.80rem;
      text-align: center;
    }
  }

  .btn-get-started {
    color: #fff;
    padding: 12px 25px;
    font-size: 1.25rem;
    
  }

</style>

<div class="card text-center">
  <h2>Become a Buyer on TukoCart</h2>
  <p class="lead">Setting up your Buyer Profile allows us to securely store your delivery details for a smooth and seamless shopping experience.</p>
  
  <div class="row text-start text-md-center">
    <div class="col-12 col-md-6 mb-4 mb-md-0">
      <ul class="list-unstyled features mx-auto" style="max-width: 500px;">
        <li>🔍 Discover thousands of products</li>
        <li>🛒 Add items to your cart</li>
        <li>🧾 Track your orders in real-time</li>
      </ul>
    </div>
    <div class="col-12 col-md-6">
      <ul class="list-unstyled features mx-auto" style="max-width: 500px;">
        <li>💳 Secure checkout</li>
        <li>📦 Hassle-free delivery system</li>
        <li>⭐ Rate and review products</li>
      </ul>
    </div>
  </div>

  <form action="setup_buyer.php" method="POST" class="mt-4">
    <button type="button" class="btn-get-started" data-bs-toggle="modal" data-bs-target="#buyerSetupModal">
      Set Up Buyer Profile
    </button>
  </form>
</div>

<?php include '../buyer_setup_model.php' ?>
