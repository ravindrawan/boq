<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="../../modules/dashboard/home.php">BOQ System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="../../modules/dashboard/home.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="../../modules/projects/index.php">Projects</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Master Data</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../../modules/master/offices.php">Offices</a></li>
                        <li><a class="dropdown-item" href="../../modules/master/project_types.php">Project Types</a></li>
                        <li><a class="dropdown-item" href="../../modules/master/funding_sources.php">Funding Sources</a></li>
                        <li><a class="dropdown-item" href="../../modules/master/cida_grades.php">CIDA Grades</a></li>
                    </ul>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if ($_SESSION['role'] === 'supervisor' || $_SESSION['role'] === 'admin'): ?>
                <li class="nav-item"><a class="nav-link text-warning" href="../../modules/projects/approvals.php">Approvals</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li class="nav-item"><a class="nav-link" href="../../modules/users/manage.php">Users</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="../../modules/users/profile.php">My Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="../../public/index.php" target="_blank">Public View</a></li>
                <li class="nav-item"><span class="nav-link text-white">Welcome, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></span></li>
                <li class="nav-item"><a class="nav-link text-danger" href="../../logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
