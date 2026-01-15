<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header text-center">
                    <h5>Login</h5>
                </div>
                <div class="card-body">
                    
                    <div id="msg"></div>

                    <form id="loginForm" novalidate>
                      <?= csrfField() ?>
                      
                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                            <div class="invalid-feedback">Email is required</div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                            <div class="invalid-feedback">Password is required</div>
                        </div>

                        <button class="btn btn-primary w-100" type="submit">
                            Login
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
$page_scripts = [
  'https://code.jquery.com/jquery-3.7.1.min.js',
  baseUrl('assets/js/login.js'),
];
?>
<?php include "includes/footer.php"; ?>