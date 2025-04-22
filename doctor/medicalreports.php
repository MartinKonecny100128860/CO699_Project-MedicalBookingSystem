<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "medicalbookingsystem");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch Reports
$sql = "SELECT mr.report_id, mr.diagnosis, mr.report_summary, 
               mr.treatment, mr.notes, mr.referral_status, mr.referral_reason, 
               mr.report_date, u.first_name, u.last_name, u.date_of_birth
        FROM medical_reports mr
        JOIN users u ON mr.patient_id = u.user_id
        ORDER BY mr.report_date DESC";

$result = $conn->query($sql);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>All Medical Reports</title>
        <!-- External links -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
       
        <!-- stylesheet from styles folder -->
        <link rel="stylesheet" href="styles/doctordash.css">
        <link rel="stylesheet" href="../accessibility/accessibility.css">
        <link rel="stylesheet" href="../accessibility/highcontrast.css">
        <link rel="stylesheet" href="styles/bars.css">
        <script src="scripts/bars.js" defer></script>

        <script src="../accessibility/accessibility.js" defer></script>
        <link rel="stylesheet" href="styles/medicalreport.css">

</head>
<body>

<?php
        $pageTitle = "Dashboard";
        include 'php/bars.php'; // contains header and sidebar
        ?>
<div class="content">
<h2 class="text-center mb-4">All Medical Reports</h2>

    <input type="text" id="searchBar" class="form-control mb-3" placeholder="Search by Patient Name or DOB...">
    
    <table class="table table-bordered text-center">
        <thead>
            <tr>
                <th>Patient</th>
                <th>Diagnosis</th>
                <th>Treatment</th>
                <th>Notes</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="reportsTable">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr id="row-<?= $row['report_id'] ?>">
                        <td><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?> <?= date("d/m/Y", strtotime($row['date_of_birth'])) ?> </td>
                        <td><?= htmlspecialchars($row['diagnosis']) ?></td>
                        <td><?= htmlspecialchars($row['treatment']) ?></td>
                        <td><?= htmlspecialchars($row['notes']) ?></td>
                        <td><?= date("d/m/Y", strtotime($row['report_date'])) ?></td>
                        <td><?= htmlspecialchars($row['referral_status']) ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm refer-btn" data-id="<?= $row['report_id'] ?>">Refer</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9" class="text-center">No medical reports found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Referral Modal -->
<div class="modal fade" id="referModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Refer Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="referralReportId">

                <label for="referralType" class="form-label">Referral Type</label>
                <select class="form-control" id="referralType">
                    <option value="">Select Referral Type</option>
                    <option value="Hospital Admission">Hospital Admission</option>
                    <option value="Specialist Consultation">Specialist Consultation</option>
                    <option value="Emergency Surgery">Emergency Surgery</option>
                    <option value="Physical Therapy">Physical Therapy</option>
                    <option value="Psychiatric Evaluation">Psychiatric Evaluation</option>
                    <option value="Other">Other</option>
                </select>

                <div id="referralSubTypeContainer" class="mt-3">
                    <label for="referralSubType" class="form-label">Select Specific Department</label>
                    <select class="form-control" id="referralSubType">
                        <option value="">Select a Department</option>
                        <option value="Cardiology">Cardiology</option>
                        <option value="Orthopedics">Orthopedics</option>
                        <option value="Neurology">Neurology</option>
                        <option value="General Surgery">General Surgery</option>
                        <option value="Dermatology">Dermatology</option>
                        <option value="Endocrinology">Endocrinology</option>
                        <option value="Oncology">Oncology</option>
                        <option value="Psychiatry">Psychiatry</option>
                        <option value="Trauma">Trauma</option>
                        <option value="Neurosurgery">Neurosurgery</option>
                        <option value="Cardiac Surgery">Cardiac Surgery</option>
                        <option value="Rehabilitation">Rehabilitation</option>
                        <option value="Sports Medicine">Sports Medicine</option>
                        <option value="Neurological Therapy">Neurological Therapy</option>
                        <option value="Mental Health Ward">Mental Health Ward</option>
                        <option value="Addiction Treatment">Addiction Treatment</option>
                        <option value="Behavioral Therapy">Behavioral Therapy</option>
                    </select>
                </div>

                <!-- Custom Department Input (Only if 'Other' is selected) -->
                <div id="customDepartmentContainer" class="mt-3" style="display:none;">
                    <label for="customReferralDepartment" class="form-label">Enter Department</label>
                    <input type="text" class="form-control" id="customReferralDepartment" placeholder="Enter department">
                </div>

                <div id="referralReasonContainer" class="mt-3">
                    <label for="referralReason" class="form-label">Additional Notes</label>
                    <textarea class="form-control" id="referralReason" placeholder="Enter additional details..." rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submitReferral">Submit Referral</button>
            </div>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    // Live Search Filter
    $("#searchBar").on("input", function() {
        let searchValue = $(this).val().toLowerCase();
        $("#reportsTable tr").each(function() {
            let patient = $(this).find("td:first").text().toLowerCase();
            let dob = $(this).find("td:nth-child(2)").text().toLowerCase();
            $(this).toggle(patient.includes(searchValue) || dob.includes(searchValue));
        });
    });

    // Predefined departments for each referral type
    const departments = {
        "Hospital Admission": ["Cardiology", "Orthopedics", "Neurology", "General Surgery"],
        "Specialist Consultation": ["Dermatology", "Endocrinology", "Oncology", "Psychiatry"],
        "Emergency Surgery": ["Trauma", "Neurosurgery", "Cardiac Surgery", "General Surgery"],
        "Physical Therapy": ["Rehabilitation", "Sports Medicine", "Neurological Therapy"],
        "Psychiatric Evaluation": ["Mental Health Ward", "Addiction Treatment", "Behavioral Therapy"],
        "Other": []
    };

    // Referral Type Change Logic
    $("#referralType").on("change", function() {
        let type = $(this).val();
        let subTypeContainer = $("#referralSubTypeContainer");
        let reasonContainer = $("#referralReasonContainer");
        let subTypeDropdown = $("#referralSubType");

        // Show dropdown and text area
        subTypeContainer.show();
        reasonContainer.show();

        subTypeDropdown.empty(); // Clear existing options
        subTypeDropdown.append("<option value=''>Select a Department</option>");

        // Always display default departments
        Object.values(departments).flat().forEach(dept => {
            subTypeDropdown.append(`<option value="${dept}">${dept}</option>`);
        });

        if (type === "Other") {
            // Show custom input field when "Other" is selected
            $("#customDepartmentContainer").show();
        } else {
            $("#customDepartmentContainer").hide();
        }
    });

    // Open Referral Modal
    $(".refer-btn").on("click", function() {
        let reportId = $(this).data("id");
        $("#referralReportId").val(reportId);
        $("#referModal").modal("show");
    });

    // Submit Referral
    $("#submitReferral").on("click", function() {
        let reportId = $("#referralReportId").val();
        let referralType = $("#referralType").val();
        let referralSubType = $("#referralSubType").val();
        let reason = $("#referralReason").val();

        if (referralType === "Other") {
            referralSubType = $("#customReferralDepartment").val().trim();
        }

        if (!referralType || !referralSubType) {
            alert("Please select a referral type and department.");
            return;
        }

        $.ajax({
            url: "php/refer_patient.php",
            type: "POST",
            data: {
                report_id: reportId,
                referral_type: referralType,
                referral_department: referralSubType,
                reason: reason
            },
            dataType: "json",
            success: function(data) {
                alert(data.success || data.error);
                $("#referModal").modal("hide");
                location.reload();
            },
            error: function(error) {
                console.log("Error submitting referral:", error);
            }
        });
    });
});

</script>

<?php include '../accessibility/accessibility.php'; ?>
</body>
</html>
