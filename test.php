<?php
require_once 'config.php';

// Initialize variables
$selectedDate = date('Y-m-d');
$selectedGrade = '';
$selectedSection = '';
$reportType = 'daily'; // daily, weekly, monthly, yearly
$attendanceRecords = [];
$summaryData = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedDate = $_POST['date'] ?? date('Y-m-d');
    $selectedGrade = $_POST['grade'] ?? '';
    $selectedSection = $_POST['section'] ?? '';
    $reportType = $_POST['report_type'] ?? 'daily';
    
    // Base query for all report types
    $baseSql = "SELECT a.student_id, a.student_name, a.status, a.date, 
                s.student_section, s.student_grade, s.student_roll 
                FROM attendance a
                JOIN student_data s ON a.student_id = s.id
                WHERE 1=1";
    
    $params = [];
    
    // Add filters based on report type
    switch ($reportType) {
        case 'weekly':
            $weekStart = date('Y-m-d', strtotime('monday this week', strtotime($selectedDate)));
            $weekEnd = date('Y-m-d', strtotime('sunday this week', strtotime($selectedDate)));
            $baseSql .= " AND a.date BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $weekStart;
            $params[':endDate'] = $weekEnd;
            break;
            
        case 'monthly':
            $monthStart = date('Y-m-01', strtotime($selectedDate));
            $monthEnd = date('Y-m-t', strtotime($selectedDate));
            $baseSql .= " AND a.date BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $monthStart;
            $params[':endDate'] = $monthEnd;
            break;
            
        case 'yearly':
            $yearStart = date('Y-01-01', strtotime($selectedDate));
            $yearEnd = date('Y-12-31', strtotime($selectedDate));
            $baseSql .= " AND a.date BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $yearStart;
            $params[':endDate'] = $yearEnd;
            break;
            
        default: // daily
            $baseSql .= " AND a.date = :date";
            $params[':date'] = $selectedDate;
    }
    
    // Add grade and section filters
    if (!empty($selectedGrade)) {
        $baseSql .= " AND s.student_grade = :grade";
        $params[':grade'] = $selectedGrade;
    }
    
    if (!empty($selectedSection)) {
        $baseSql .= " AND s.student_section = :section";
        $params[':section'] = $selectedSection;
    }
    
    $baseSql .= " ORDER BY a.date, s.student_roll ASC";
    
    // Execute query
    $stmt = $pdo->prepare($baseSql);
    $stmt->execute($params);
    $attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepare summary data for non-daily reports
    if ($reportType !== 'daily' && !empty($attendanceRecords)) {
        $summaryData = calculateSummary($attendanceRecords);
    }
}

// Get distinct grades and sections for dropdowns
$grades = $pdo->query("SELECT DISTINCT student_grade FROM student_data ORDER BY student_grade")->fetchAll(PDO::FETCH_COLUMN);
$sections = $pdo->query("SELECT DISTINCT student_section FROM student_data ORDER BY student_section")->fetchAll(PDO::FETCH_COLUMN);

// Function to calculate summary statistics
function calculateSummary($records) {
    $summary = [];
    
    foreach ($records as $record) {
        $studentId = $record['student_id'];
        $date = $record['date'];
        
        if (!isset($summary[$studentId])) {
            $summary[$studentId] = [
                'student_name' => $record['student_name'],
                'student_roll' => $record['student_roll'],
                'student_grade' => $record['student_grade'],
                'student_section' => $record['student_section'],
                'total_days' => 0,
                'present_days' => 0,
                'absent_days' => 0,
                'attendance_percentage' => 0
            ];
        }
        
        $summary[$studentId]['total_days']++;
        
        if ($record['status'] === 'Present') {
            $summary[$studentId]['present_days']++;
        } else {
            $summary[$studentId]['absent_days']++;
        }
    }
    
    // Calculate percentages
    foreach ($summary as &$student) {
        if ($student['total_days'] > 0) {
            $student['attendance_percentage'] = round(($student['present_days'] / $student['total_days']) * 100, 2);
        }
    }
    
    return $summary;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .attendance-present { background-color: #d4edda; }
        .attendance-absent { background-color: #f8d7da; }
        .percentage-high { color: #28a745; }
        .percentage-medium { color: #ffc107; }
        .percentage-low { color: #dc3545; }
    </style>
</head>
<body>
<a href="attendence.php"><button class="btn  btn-info">Back to attendence</button></a>

    <div class="container mt-5">
        <h1 class="mb-4">Student Attendance</h1>
        
        <form method="POST" class="mb-4">
            <div class="row g-3">
                <div class="col-md-2">
                    <label for="report_type" class="form-label">Report Type</label>
                    <select class="form-select" id="report_type" name="report_type">
                        <option value="daily" <?= $reportType === 'daily' ? 'selected' : '' ?>>Daily Report</option>
                        <option value="weekly" <?= $reportType === 'weekly' ? 'selected' : '' ?>>Weekly Report</option>
                        <option value="monthly" <?= $reportType === 'monthly' ? 'selected' : '' ?>>Monthly Report</option>
                        <option value="yearly" <?= $reportType === 'yearly' ? 'selected' : '' ?>>Yearly Report</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="date" class="form-label">Select Date</label>
                    <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="grade" class="form-label">Grade</label>
                    <select class="form-select" id="grade" name="grade">
                        <option value="">All Grades</option>
                        <?php foreach ($grades as $grade): ?>
                            <option value="<?= htmlspecialchars($grade) ?>" <?= $grade === $selectedGrade ? 'selected' : '' ?>>
                                <?= htmlspecialchars($grade) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="section" class="form-label">Section</label>
                    <select class="form-select" id="section" name="section">
                        <option value="">All Sections</option>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?= htmlspecialchars($section) ?>" <?= $section === $selectedSection ? 'selected' : '' ?>>
                                <?= htmlspecialchars($section) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </div>
        </form>
        
        <?php if (!empty($attendanceRecords)): ?>
            <div class="card">
                <div class="card-header">
                    <h5>
                        <?= ucfirst($reportType) ?> Attendance Report 
                        <?php if ($reportType === 'weekly'): ?>
                            (Week of <?= date('M j, Y', strtotime($selectedDate)) ?>)
                        <?php elseif ($reportType === 'monthly'): ?>
                            (<?= date('F Y', strtotime($selectedDate)) ?>)
                        <?php elseif ($reportType === 'yearly'): ?>
                            (Year <?= date('Y', strtotime($selectedDate)) ?>)
                        <?php else: ?>
                            for <?= date('M j, Y', strtotime($selectedDate)) ?>
                        <?php endif; ?>
                    </h5>
                    <?php if (!empty($selectedGrade)): ?>
                        <span class="badge bg-secondary">Grade: <?= htmlspecialchars($selectedGrade) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($selectedSection)): ?>
                        <span class="badge bg-secondary ms-2">Section: <?= htmlspecialchars($selectedSection) ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="card-body">
                    <?php if ($reportType !== 'daily' && !empty($summaryData)): ?>
                        <div class="table-responsive mb-4">
                            <h6>Summary Report</h6>
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Roll No</th>
                                        <th>Student Name</th>
                                        <th>Grade</th>
                                        <th>Section</th>
                                        <th>Present Days</th>
                                        <th>Absent Days</th>
                                        <th>Total Days</th>
                                        <th>Attendance %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($summaryData as $student): ?>
                                        <?php
                                        $percentageClass = '';
                                        if ($student['attendance_percentage'] >= 75) {
                                            $percentageClass = 'percentage-high';
                                        } elseif ($student['attendance_percentage'] >= 50) {
                                            $percentageClass = 'percentage-medium';
                                        } else {
                                            $percentageClass = 'percentage-low';
                                        }
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($student['student_roll']) ?></td>
                                            <td><?= htmlspecialchars($student['student_name']) ?></td>
                                            <td><?= htmlspecialchars($student['student_grade']) ?></td>
                                            <td><?= htmlspecialchars($student['student_section']) ?></td>
                                            <td><?= $student['present_days'] ?></td>
                                            <td><?= $student['absent_days'] ?></td>
                                            <td><?= $student['total_days'] ?></td>
                                            <td class="<?= $percentageClass ?>">
                                                <?= $student['attendance_percentage'] ?>%
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <h6>Detailed Daily Records</h6>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <?php if ($reportType !== 'daily'): ?>
                                        <th>Date</th>
                                    <?php endif; ?>
                                    <th>Roll No</th>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Grade</th>
                                    <th>Section</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendanceRecords as $record): ?>
                                    <tr class="<?= $record['status'] === 'Present' ? 'attendance-present' : 'attendance-absent' ?>">
                                        <?php if ($reportType !== 'daily'): ?>
                                            <td><?= date('M j, Y', strtotime($record['date'])) ?></td>
                                        <?php endif; ?>
                                        <td><?= htmlspecialchars($record['student_roll']) ?></td>
                                        <td><?= htmlspecialchars($record['student_id']) ?></td>
                                        <td><?= htmlspecialchars($record['student_name']) ?></td>
                                        <td><?= htmlspecialchars($record['student_grade']) ?></td>
                                        <td><?= htmlspecialchars($record['student_section']) ?></td>
                                        <td>
                                            <span class="badge <?= $record['status'] === 'Present' ? 'bg-success' : 'bg-danger' ?>">
                                                <?= htmlspecialchars($record['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="alert alert-warning">No attendance records found for the selected criteria.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Initialize date picker
        flatpickr("#date", {
            dateFormat: "Y-m-d",
            maxDate: "today"
        });
        
        // Change date input label based on report type
        document.getElementById('report_type').addEventListener('change', function() {
            const dateLabel = document.querySelector('label[for="date"]');
            switch(this.value) {
                case 'weekly':
                    dateLabel.textContent = 'Select any date in week';
                    break;
                case 'monthly':
                    dateLabel.textContent = 'Select any date in month';
                    break;
                case 'yearly':
                    dateLabel.textContent = 'Select any date in year';
                    break;
                default:
                    dateLabel.textContent = 'Select date';
            }
        });
    </script>
</body>
</html>