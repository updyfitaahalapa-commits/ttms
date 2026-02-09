<?php include '../layout/header.php'; ?>

<style>
  html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    background: url('../../assets/images/login-bg1.png') no-repeat center center fixed;
    background-size: cover; /* <-- change cover → contain */
    background-color: #003366; /* fallback color */
  }

  .login-wrapper {
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    /*backdrop-filter: blur(3px);*/
  }

  .card {
    width: 100%;
    max-width: 400px;
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    border-radius: 16px;
  }

  .card-header {
    border-top-left-radius: 16px;
    border-top-right-radius: 16px;
  }
</style>

<div class="login-wrapper">
  <div class="card">
    <div class="card-header bg-primary text-white text-center">
      <h5 class="mb-0"><i class="fas fa-user-lock"></i> Secure Login</h5>
    </div>
    <div class="card-body">
      <form method="POST" action="../../controllers/AuthController.php">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" placeholder="Enter username" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
      </form>
    </div>
  </div>
</div>

<?php include '../layout/footer.php'; ?>
