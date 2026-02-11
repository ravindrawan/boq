<?php
include '../../includes/db_connect.php';
include '../../includes/functions.php';
include '../../includes/header.php';
include '../../includes/navbar.php';

$project_types = getProjectTypes($conn);
$funding_sources = getFundingSources($conn);

if (isset($_POST['submit'])) {
    // Collect Data
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

    // Office Logic
    $office_name = $_SESSION['office_name'] ?? 'Head Office';
    
    // Approval Logic
    $approval_status = ($_SESSION['role'] === 'office') ? 'pending' : 'approved';

    // Insert Initial Record to get ID
    $sql = "INSERT INTO projects (project_name, office_name, project_type_id, district, ds_division, gn_division, contractor_name, cida_reg_no, agreement_no, cida_grade_id, start_date, contract_period_months, completion_date, contract_amount, estimate_cost, funding_source_id, approval_status) 
            VALUES ('$pname', '$office_name', '$ptype', '$district', '$ds', '$gn', '$contractor', '$cida_reg', '$agreement', '$cida_grade_id', '$start_date', '$duration', '$end_date', '$amount', '$estimate', '$fund_source', '$approval_status')";
    
    if ($conn->query($sql) === TRUE) {
        $last_id = $conn->insert_id;
        $upload_dir = "../../uploads/projects/" . $last_id . "/";
        
        // Handle BOQ File
        $boq_path = "";
        if (!empty($_FILES["boq_file"]["name"])) {
             $boq_path = uploadFile($_FILES["boq_file"], $upload_dir);
             $conn->query("UPDATE projects SET boq_file='$boq_path' WHERE id=$last_id");
        }

        // Handle Project Photos
        if (!empty($_FILES["project_photos"]["name"][0])) {
            foreach ($_FILES["project_photos"]["name"] as $key => $name) {
                $tmp_name = $_FILES["project_photos"]["tmp_name"][$key];
                $file_name = basename($name);
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
                $target = $upload_dir . time() . "_" . $file_name;
                if (move_uploaded_file($tmp_name, $target)) {
                    $conn->query("INSERT INTO project_photos (project_id, photo_path) VALUES ($last_id, '$target')");
                }
            }
        }
        
        $msg = "Project Added Successfully!";
        if ($approval_status === 'pending') {
            $msg .= " It is now pending Supervisor approval.";
        }
        echo "<script>alert('$msg'); window.location='index.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<div class="container mb-5">
    <div class="card p-4">
        <h3 class="mb-4">Register New Project</h3>
        <form method="POST" enctype="multipart/form-data">
            
            <h5 class="text-primary mt-3">Basic Details</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Project Name</label>
                    <input type="text" name="project_name" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Project Type</label>
                    <div class="input-group">
                        <select name="project_type_id" class="form-select" required>
                            <option value="">Select Type</option>
                            <?php foreach ($project_types as $t) echo "<option value='{$t['id']}'>{$t['type_name']}</option>"; ?>
                        </select>
                        <a href="../master/project_types.php" class="btn btn-outline-secondary">+</a>
                    </div>
                </div>
            </div>

            <h5 class="text-primary mt-3">Location</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>District</label>
                    <select name="district" id="district" class="form-select" required>
                        <option value="">Select District</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>DS Division</label>
                    <select name="ds_division" id="ds_division" class="form-select" required>
                        <option value="">Select DS Division</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>GN Division</label>
                    <select name="gn_division" id="gn_division" class="form-select" required>
                        <option value="">Select GN Division</option>
                    </select>
                </div>
            </div>

            <h5 class="text-primary mt-3">Contractor Details</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Contractor Name</label>
                    <input type="text" name="contractor_name" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label>CIDA Reg No</label>
                    <input type="text" name="cida_reg_no" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label>CIDA Grade</label>
                    <div class="input-group">
                        <select name="cida_grade_id" class="form-select">
                            <option value="">Select Grade</option>
                            <?php 
                            $grades = getCidaGrades($conn);
                            foreach ($grades as $g) echo "<option value='{$g['id']}'>{$g['grade_name']}</option>"; 
                            ?>
                        </select>
                        <a href="../master/cida_grades.php" class="btn btn-outline-secondary">+</a>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Agreement Number</label>
                    <input type="text" name="agreement_no" class="form-control">
                </div>
            </div>

            <h5 class="text-primary mt-3">Timeline & Financials</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Contract Period (Months)</label>
                    <input type="number" name="contract_period_months" id="months" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Completion Date</label>
                    <input type="date" name="completion_date" id="completion_date" class="form-control" readonly>
                </div>
                 <div class="col-md-3 mb-3">
                    <label>Funding Source</label>
                     <div class="input-group">
                        <select name="funding_source_id" class="form-select" required>
                            <option value="">Select Source</option>
                            <?php foreach ($funding_sources as $s) echo "<option value='{$s['id']}'>{$s['source_name']}</option>"; ?>
                        </select>
                         <a href="../master/funding_sources.php" class="btn btn-outline-secondary">+</a>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Estimate Cost (Rs.)</label>
                    <input type="number" step="0.01" name="estimate_cost" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Contract Amount (Rs.)</label>
                    <input type="number" step="0.01" name="contract_amount" class="form-control">
                </div>
            </div>

            <h5 class="text-primary mt-3">Files</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>BOQ PDF</label>
                    <input type="file" name="boq_file" class="form-control" accept=".pdf">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Project Photos</label>
                    <input type="file" name="project_photos[]" class="form-control" multiple accept="image/*">
                </div>
            </div>

            <button type="submit" name="submit" class="btn btn-success w-100 btn-lg mt-3">Register Project</button>
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

    // --- Cascading Dropdowns ---
    const districtSelect = document.getElementById('district');
    const dsSelect = document.getElementById('ds_division');
    const gnSelect = document.getElementById('gn_division');

    // Load Districts
    fetch('../../api/get_locations.php?type=districts')
        .then(response => response.json())
        .then(data => {
            data.forEach(d => {
                const option = document.createElement('option');
                option.value = d;
                option.textContent = d;
                districtSelect.appendChild(option);
            });
        });

    // Load DS on District Change
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

    // Load GN on DS Change
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
