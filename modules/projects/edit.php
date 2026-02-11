<?php
include '../../includes/db_connect.php';
include '../../includes/functions.php';
include '../../includes/header.php';
include '../../includes/navbar.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$sql = "SELECT * FROM projects WHERE id = $id";
$result = $conn->query($sql);
$project = $result->fetch_assoc();

$project_types = getProjectTypes($conn);
$funding_sources = getFundingSources($conn);

// Handle BOQ Deletion
if (isset($_GET['delete_boq']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT boq_file FROM projects WHERE id = $id";
    $res = $conn->query($sql);
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (!empty($row['boq_file']) && file_exists($row['boq_file'])) {
            unlink($row['boq_file']);
        }
        $conn->query("UPDATE projects SET boq_file = NULL WHERE id = $id");
        echo "<script>alert('BOQ File Deleted!'); window.location='edit.php?id=$id';</script>";
        exit();
    }
}

// Handle Photo Deletion
if (isset($_GET['delete_photo']) && isset($_GET['id'])) {
    $photo_id = $_GET['delete_photo'];
    $project_id = $_GET['id'];
    $sql = "SELECT photo_path FROM project_photos WHERE id = $photo_id";
    $res = $conn->query($sql);
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (file_exists($row['photo_path'])) {
            unlink($row['photo_path']);
        }
        $conn->query("DELETE FROM project_photos WHERE id = $photo_id");
        echo "<script>alert('Photo Deleted!'); window.location='edit.php?id=$project_id';</script>";
        exit();
    }
}

if (isset($_POST['update'])) {
    $pname = $_POST['project_name'];
    $ptype = $_POST['project_type_id'];
    $district = $_POST['district'];
    $ds = $_POST['ds_division'];
    $gn = $_POST['gn_division'];
    $contractor = $_POST['contractor_name'];
    $cida_reg = $_POST['cida_reg_no'];
    $agreement = $_POST['agreement_no'];
    $cida_grade_id = $_POST['cida_grade_id'];
    $start_date = $_POST['start_date'];
    $duration = $_POST['contract_period_months'];
    $end_date = $_POST['completion_date'];
    $amount = $_POST['contract_amount'];
    $estimate = $_POST['estimate_cost'];
    $fund_source = $_POST['funding_source_id'];

    // If edited by Office User, set back to Pending
    $approval_sql = "";
    if ($_SESSION['role'] === 'office') {
        $approval_sql = ", approval_status='pending'";
    }

    $sql = "UPDATE projects SET 
            project_name='$pname', project_type_id='$ptype', district='$district', 
            ds_division='$ds', gn_division='$gn', contractor_name='$contractor', 
            cida_reg_no='$cida_reg', agreement_no='$agreement', cida_grade_id='$cida_grade_id', 
            start_date='$start_date', contract_period_months='$duration', completion_date='$end_date', 
            contract_amount='$amount', estimate_cost='$estimate', funding_source_id='$fund_source' 
            $approval_sql
            WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
         // BOQ File Update
        if (!empty($_FILES["boq_file"]["name"])) {
            $upload_dir = "../../uploads/projects/" . $id . "/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true); // Ensure dir exists
            $boq_path = uploadFile($_FILES["boq_file"], $upload_dir);
            $conn->query("UPDATE projects SET boq_file='$boq_path' WHERE id=$id");
        }

        // Project Photos Update
        if (!empty($_FILES['project_photos']['name'][0])) {
             $upload_dir = "../../uploads/projects/" . $id . "/photos/";
             if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

             $count = count($_FILES['project_photos']['name']);
             for ($i = 0; $i < $count; $i++) {
                if ($_FILES['project_photos']['error'][$i] === 0) {
                    $tmp_name = $_FILES['project_photos']['tmp_name'][$i];
                    $name = time() . "_" . basename($_FILES['project_photos']['name'][$i]);
                    $target_file = $upload_dir . $name;
                    
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $conn->query("INSERT INTO project_photos (project_id, photo_path) VALUES ($id, '$target_file')");
                    }
                }
             }
        }
        
        $msg = "Project Updated Successfully!";
        if (isset($approval_sql) && $approval_sql !== "") {
            $msg .= " Changes are pending Supervisor approval.";
        }
        echo "<script>alert('$msg'); window.location='index.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<div class="container mb-5">
    <div class="card p-4">
        <h3 class="mb-4">Edit Project</h3>
        <form method="POST" enctype="multipart/form-data">
            
            <h5 class="text-primary mt-3">Basic Details</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Project Name</label>
                    <input type="text" name="project_name" class="form-control" value="<?php echo $project['project_name']; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Project Type</label>
                    <select name="project_type_id" class="form-select" required>
                        <?php foreach ($project_types as $t) {
                            $selected = ($t['id'] == $project['project_type_id']) ? 'selected' : '';
                            echo "<option value='{$t['id']}' $selected>{$t['type_name']}</option>";
                        } ?>
                    </select>
                </div>
            </div>

            <h5 class="text-primary mt-3">Location</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>District</label>
                    <select name="district" id="district" class="form-select" required>
                        <option value="<?php echo $project['district']; ?>"><?php echo $project['district']; ?></option>
                        <!-- Loaded via JS dynamically too -->
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>DS Division</label>
                    <select name="ds_division" id="ds_division" class="form-select" required>
                        <option value="<?php echo $project['ds_division']; ?>"><?php echo $project['ds_division']; ?></option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>GN Division</label>
                    <select name="gn_division" id="gn_division" class="form-select" required>
                        <option value="<?php echo $project['gn_division']; ?>"><?php echo $project['gn_division']; ?></option>
                    </select>
                </div>
            </div>

            <!-- Pre-load data for JS -->
            <input type="hidden" id="saved_district" value="<?php echo $project['district']; ?>">
            <input type="hidden" id="saved_ds" value="<?php echo $project['ds_division']; ?>">
            <input type="hidden" id="saved_gn" value="<?php echo $project['gn_division']; ?>">

            <h5 class="text-primary mt-3">Contractor & Funds</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Contractor Name</label>
                    <input type="text" name="contractor_name" class="form-control" value="<?php echo $project['contractor_name']; ?>" required>
                </div>
                 <div class="col-md-3 mb-3">
                    <label>CIDA Reg No</label>
                    <input type="text" name="cida_reg_no" class="form-control" value="<?php echo $project['cida_reg_no']; ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label>CIDA Grade</label>
                    <div class="input-group">
                        <select name="cida_grade_id" class="form-select">
                            <option value="">Select Grade</option>
                            <?php 
                            $grades = getCidaGrades($conn);
                            foreach ($grades as $g) {
                                $selected = ($g['id'] == $project['cida_grade_id']) ? 'selected' : '';
                                echo "<option value='{$g['id']}' $selected>{$g['grade_name']}</option>";
                            } 
                            ?>
                        </select>
                        <a href="../master/cida_grades.php" class="btn btn-outline-secondary">+</a>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Agreement No</label>
                    <input type="text" name="agreement_no" class="form-control" value="<?php echo $project['agreement_no']; ?>">
                </div>
                  <div class="col-md-6 mb-3">
                    <label>Funding Source</label>
                    <select name="funding_source_id" class="form-select" required>
                        <?php foreach ($funding_sources as $s) {
                            $selected = ($s['id'] == $project['funding_source_id']) ? 'selected' : '';
                            echo "<option value='{$s['id']}' $selected>{$s['source_name']}</option>";
                        } ?>
                    </select>
                </div>
            </div>

            <h5 class="text-primary mt-3">Timeline & Cost</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo $project['start_date']; ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Months</label>
                    <input type="number" name="contract_period_months" id="months" class="form-control" value="<?php echo $project['contract_period_months']; ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Completion Date</label>
                    <input type="date" name="completion_date" id="completion_date" class="form-control" value="<?php echo $project['completion_date']; ?>" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Contract Amount (Rs.)</label>
                    <input type="number" step="0.01" name="contract_amount" class="form-control" value="<?php echo $project['contract_amount']; ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Estimate Cost (Rs.)</label>
                    <input type="number" step="0.01" name="estimate_cost" class="form-control" value="<?php echo $project['estimate_cost']; ?>">
                </div>
            </div>

            <h5 class="text-primary mt-3">Attachments</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>BOQ PDF</label>
                    <?php if (!empty($project['boq_file'])): ?>
                        <div class="mb-2">
                             <a href="<?php echo $project['boq_file']; ?>" target="_blank" class="btn btn-sm btn-info text-white">View BOQ</a>
                             <a href="edit.php?id=<?php echo $id; ?>&delete_boq=1" class="btn btn-sm btn-danger" onclick="return confirm('Delete this BOQ file?')">Delete</a>
                        </div>
                        <p class="text-muted small">Upload new to replace existing.</p>
                    <?php endif; ?>
                    <input type="file" name="boq_file" class="form-control" accept=".pdf">
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-3">
                    <label>Project Photos (Preview, Delete, Insert)</label>
                    <input type="file" name="project_photos[]" class="form-control mb-3" multiple accept="image/*">
                    
                    <div class="row g-2">
                        <?php
                        $photos = $conn->query("SELECT * FROM project_photos WHERE project_id = $id");
                        while($photo = $photos->fetch_assoc()):
                        ?>
                        <div class="col-md-3 col-sm-4 position-relative">
                            <div class="card">
                                <img src="<?php echo $photo['photo_path']; ?>" class="card-img-top" style="height: 150px; object-fit: cover;">
                                <div class="card-body p-1 text-center">
                                    <a href="<?php echo $photo['photo_path']; ?>" target="_blank" class="btn btn-sm btn-light">View</a>
                                    <a href="edit.php?id=<?php echo $id; ?>&delete_photo=<?php echo $photo['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this photo?')">Delete</a>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <button type="submit" name="update" class="btn btn-warning w-100 btn-lg mt-3">Update Project</button>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
    // --- Date Calculation ---
    const startDateInput = document.getElementById('start_date');
    const monthsInput = document.getElementById('months');
    const completionDateInput = document.getElementById('completion_date');

    function calculateCompletionDate() {
        const startDate = new Date(startDateInput.value);
        const months = parseInt(monthsInput.value);
        
        if (startDate && !isNaN(months)) {
            const endDate = new Date(startDate);
            endDate.setMonth(endDate.getMonth() + months);
            completionDateInput.value = endDate.toISOString().split('T')[0];
        }
    }

    startDateInput.addEventListener('change', calculateCompletionDate);
    monthsInput.addEventListener('input', calculateCompletionDate);

    // --- Cascading Dropdowns Logic ---
    // If user changes district, we reload DS.
    const districtSelect = document.getElementById('district');
    const dsSelect = document.getElementById('ds_division');
    const gnSelect = document.getElementById('gn_division');

    // Initial Load of Districts (to populate the list fully, in case they want to change)
    fetch('../../api/get_locations.php?type=districts')
        .then(response => response.json())
        .then(data => {
            // Keep the selected one, add others
            const saved = document.getElementById('saved_district').value;
             districtSelect.innerHTML = ''; // Clear to rebuild with full list
             
             // Add default or placeholder if needed, but we have a value
             data.forEach(d => {
                const option = document.createElement('option');
                option.value = d;
                option.textContent = d;
                if(d === saved) option.selected = true;
                districtSelect.appendChild(option);
            });
        });

    districtSelect.addEventListener('change', function() {
        dsSelect.innerHTML = '<option value="">Select DS Division</option>';
        gnSelect.innerHTML = '<option value="">Select GN Division</option>';
        fetch('../../api/get_locations.php?type=ds&district=' + this.value)
            .then(response => response.json())
            .then(data => {
                data.forEach(d => {
                    const option = document.createElement('option');
                    option.value = d;
                    option.textContent = d;
                    dsSelect.appendChild(option);
                });
            });
    });

    dsSelect.addEventListener('change', function() {
        gnSelect.innerHTML = '<option value="">Select GN Division</option>';
        fetch('../../api/get_locations.php?type=gn&ds=' + this.value)
            .then(response => response.json())
            .then(data => {
                data.forEach(d => {
                    const option = document.createElement('option');
                    option.value = d;
                    option.textContent = d;
                    gnSelect.appendChild(option);
                });
            });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
