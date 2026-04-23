<?php
include '../../includes/db_connect.php';
include '../../includes/header.php';
include '../../includes/navbar.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Access Denied. Only Admins can view this page.</div></div>";
    include '../../includes/footer.php';
    exit();
}

// Handle Add User
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $office = $_POST['office_name']; // New Field
    
    $check = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($check->num_rows > 0) {
        $error = "Username already exists!";
    } else {
        $sql = "INSERT INTO users (username, password, role, office_name) VALUES ('$username', '$password', '$role', '$office')";
        if ($conn->query($sql)) {
            $success = "User created successfully!";
        } else {
            $error = "Error creating user: " . $conn->error;
        }
    }
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id=$id");
        echo "<script>window.location='manage.php';</script>";
    } else {
        echo "<script>alert('You cannot delete your own account!');</script>";
    }
}

// Handle Toggle Status
if (isset($_GET['toggle_status'])) {
    $id = $_GET['toggle_status'];
    if ($id != $_SESSION['user_id']) {
        // Find current status
        $q = $conn->query("SELECT is_active FROM users WHERE id=$id");
        if ($q->num_rows > 0) {
            $r = $q->fetch_assoc();
            $new_status = ($r['is_active'] == 1) ? 0 : 1;
            $conn->query("UPDATE users SET is_active = $new_status WHERE id=$id");
        }
        echo "<script>window.location='manage.php';</script>";
    } else {
        echo "<script>alert('You cannot disable your own account!'); window.location='manage.php';</script>";
    }
}

// Handle Password Reset
if (isset($_POST['reset_password'])) {
    $id = $_POST['reset_user_id'];
    $new_pass_plain = $_POST['new_password'];
    $new_pass = password_hash($new_pass_plain, PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password='$new_pass' WHERE id=$id");
    $success = "Password resetted successfully!";
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card p-4">
                <h4>Create New User</h4>
                <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Role</label>
                        <select name="role" class="form-select">
                            <option value="office">Office User</option>
                            <option value="supervisor">Office Supervisor</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Office Name</label>
                        <select name="office_name" class="form-select" required>
                            <option value="">Select Office</option>
                            <?php
                            $off_res = $conn->query("SELECT * FROM offices");
                            while ($o = $off_res->fetch_assoc()) {
                                echo "<option value='{$o['office_name']}'>{$o['office_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="add_user" class="btn btn-primary w-100">Create User</button>
                </form>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card p-4">
                <h4>User List</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Office</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $users = $conn->query("SELECT * FROM users ORDER BY office_name ASC, id DESC");
                        $current_office = null;
                        
                        while ($u = $users->fetch_assoc()) {
                            if ($u['office_name'] !== $current_office) {
                                $current_office = $u['office_name'];
                                $display_office = !empty($current_office) ? $current_office : "No Office Assigned (Admins)";
                                echo "<tr class='table-light'><td colspan='5' class='fw-bold text-primary py-3'><i class='bi bi-building'></i> {$display_office}</td></tr>";
                            }
                        
                            $badge = 'bg-primary';
                            if ($u['role'] == 'admin') $badge = 'bg-danger';
                            if ($u['role'] == 'supervisor') $badge = 'bg-warning text-dark';
                            
                            $status_badge = ($u['is_active'] == 1) ? "<span class='badge bg-success'>Active</span>" : "<span class='badge bg-secondary'>Disabled</span>";

                            echo "<tr>
                                <td>{$u['id']}</td>
                                <td>
                                    {$u['username']}<br>
                                    $status_badge
                                </td>
                                <td><span class='badge $badge'>{$u['role']}</span></td>
                                <td>{$u['office_name']}</td>
                                <td>";
                                if ($u['id'] != $_SESSION['user_id']) {
                                    $toggle_btn = ($u['is_active'] == 1) ? "<a href='?toggle_status={$u['id']}' class='btn btn-sm btn-outline-warning'>Disable</a>" : "<a href='?toggle_status={$u['id']}' class='btn btn-sm btn-outline-success'>Enable</a>";
                                    echo "
                                        <button type='button' class='btn btn-sm btn-outline-info' data-bs-toggle='modal' data-bs-target='#resetModal' onclick='setResetUserId({$u['id']}, \"{$u['username']}\")'>Reset Password</button>
                                        $toggle_btn
                                        <a href='?delete={$u['id']}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Are you sure you want to delete this user completely?\")'>Delete</a>
                                    ";
                                } else {
                                    echo "<span class='text-muted'>Current User</span>";
                                }
                            echo "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Reset Password - <span id="resetUsernameDisplay" class="text-primary"></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="reset_user_id" id="reset_user_id">
            <div class="mb-3">
                <label>New Password</label>
                <input type="password" name="new_password" class="form-control" required minlength="4">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="reset_password" class="btn btn-primary">Save New Password</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
    function setResetUserId(id, username) {
        document.getElementById('reset_user_id').value = id;
        document.getElementById('resetUsernameDisplay').innerText = username;
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
