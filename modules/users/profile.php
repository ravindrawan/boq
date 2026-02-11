<?php
include '../../includes/db_connect.php';
include '../../includes/header.php';
include '../../includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Handle Theme Change
if (isset($_POST['save_theme'])) {
    $theme = $_POST['theme'];
    $conn->query("UPDATE users SET theme_color='$theme' WHERE id=$user_id");
    $_SESSION['theme_color'] = $theme; // Update session
    echo "<script>window.location='profile.php';</script>";
}



$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4">
                <h3 class="mb-4">User Profile</h3>
                
                <form method="POST" class="mb-4">
                    <h5 class="text-primary">Select Theme</h5>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php
                        $themes = [
                            'blue' => 'Default Blue',
                            'green' => 'Emerald Green',
                            'red' => 'Rose Red',
                            'purple' => 'Violet Purple',
                            'orange' => 'Amber Orange',
                            'dark' => 'Dark Mode'
                        ];
                        foreach ($themes as $key => $name) {
                            $checked = ($user['theme_color'] == $key) ? 'checked' : '';
                            echo "
                            <div class='form-check'>
                                <input class='form-check-input' type='radio' name='theme' value='$key' id='t_$key' $checked>
                                <label class='form-check-label' for='t_$key'>$name</label>
                            </div>";
                        }
                        ?>
                    </div>
                    <button type="submit" name="save_theme" class="btn btn-primary">Apply Theme</button>
                </form>
                

            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
