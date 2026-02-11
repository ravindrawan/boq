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
                        $users = $conn->query("SELECT * FROM users");
                        while ($u = $users->fetch_assoc()) {
                            $badge = 'bg-primary';
                            if ($u['role'] == 'admin') $badge = 'bg-danger';
                            if ($u['role'] == 'supervisor') $badge = 'bg-warning text-dark';
                            
                            echo "<tr>
                                <td>{$u['id']}</td>
                                <td>{$u['username']}</td>
                                <td><span class='badge $badge'>{$u['role']}</span></td>
                                <td>{$u['office_name']}</td>
                                <td>";
                                if ($u['id'] != $_SESSION['user_id']) {
                                    echo "<a href='?delete={$u['id']}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Delete user?\")'>Delete</a>";
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
