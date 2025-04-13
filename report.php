<?php

ob_start();


// Start output buffering
// Your existing code...
include_once "models/Database.php";
include_once "models/studentClass.php";

$student = new Students();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['h_action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['h_action']) {
            case 'update_attendance':
                $attendance_status = $_POST['status'];
                $student_id = $_POST['student_id'];
                $date = $_POST['date'] ?? date('Y-m-d');
                
                if (empty($student_id) || !in_array($attendance_status, ['Present', 'Absent'])) {
                    throw new Exception("Invalid input data");
                }
                
                $result = $student->updateAttendance($student_id, $attendance_status, $date);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Attendance updated successfully',
                        'status' => $attendance_status
                    ]);
                } else {
                    throw new Exception("Database update failed");
                }
                exit;
                
                // In your existing POST handler, modify the get_attendance case:
// In your existing POST handler, modify the get_attendance case:
    case 'get_attendance':
        $date = $_POST['date'] ?? date('Y-m-d');
        $section = $_POST['section'] ?? null;
        $class = $_POST['class'] ?? null;
        
        // Validate date format
        if (!DateTime::createFromFormat('Y-m-d', $date)) {
            echo json_encode(['success' => false, 'error' => 'Invalid date format']);
            exit;
        }
        
        $attendance_report = $student->getAttendanceReport($date, $section, $class);
        
        echo json_encode([
            'success' => !empty($attendance_report),
            'data' => $attendance_report ?: [],
            'date_queried' => $date,
            'section_queried' => $section,
            'class_queried' => $class
        ]);
        exit;
                                 
                
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
echo json_encode([
    'success' => !empty($attendance_report),
    'data' => $attendance_report ?: [],
    'error' => empty($attendance_report) ? 'No Data Found' : null
]);
exit;

    }
}

// Initial page load// In your controller code, ensure the date format matches your database:
$attendance_date = $_GET['date'] ?? date('Y-m-d');
$attendance_report = $student->getAttendanceReport($attendance_date);

// Debug output
error_log("Requested Date: ".$attendance_date);
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Attendance Management System</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="style.css">



    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@100..900&display=swap" rel="stylesheet">

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const body = document.body;
        const mainContent = document.getElementById('mainContent');

        // Check screen size
        function checkScreenSize() {
            return window.matchMedia('(min-width: 993px)').matches;
        }

        // Toggle sidebar
        function toggleSidebar() {
            if (checkScreenSize()) {
                // Desktop: toggle collapsed state
                body.classList.toggle('sidebar-collapsed');
                sidebar.classList.toggle('collapsed');

                // Save state in localStorage
                localStorage.setItem('sidebarCollapsed', body.classList.contains('sidebar-collapsed'));
            } else {
                // Mobile: toggle open/close
                sidebar.classList.toggle('open');
            }
        }

        // Initialize sidebar state
        function initSidebar() {
            if (checkScreenSize()) {
                // Desktop: check saved state
                const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                if (isCollapsed) {
                    body.classList.add('sidebar-collapsed');
                    sidebar.classList.add('collapsed');
                }
                toggleBtn.style.display = 'block';
            } else {
                // Mobile: always start closed
                sidebar.classList.remove('collapsed', 'open');
                body.classList.remove('sidebar-collapsed');
                toggleBtn.style.display = 'block';
            }
        }

        // Event listeners
        toggleBtn.addEventListener('click', toggleSidebar);

        // Handle window resize
        window.addEventListener('resize', function() {
            initSidebar();
        });

        // Initialize
        initSidebar();
    });
    </script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


    <style>
    .attendance-option {
        padding: 8px;
        border-radius: 4px;
        transition: background-color 0.3s;
    }

    .attendance-option:hover {
        background-color: #f8f9fa;
    }

    .present-checked {
        background-color: #d4edda;
    }

    .absent-checked {
        background-color: #f8d7da;
    }

    .loading {
        background-color: #fff3cd;
    }
    </style>
</head>

<body>

<main class="main-content" id="mainContent" style="margin:auto;">

    <div class="main-container d-flex">
        <!-- Add a toggle button near your sidebar -->
        <!-- Toggle Button - Will be positioned properly with CSS -->
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fa-solid fa-bars"></i>
        </button>
        <!-- Sidebar Container -->
        <div class="sidebar_container" id="sidebar">
            <ul class="side_menu">
                <li><a href="index.php"><i class="fa-solid fa-chart-line"></i> <span>Dashboard</span></a></li>
                <li><a href="student_managment.php"><i class="fa-solid fa-user-graduate"></i> <span>Manage
                            Students</span></a></li>
                <li><a href="attendence.php" class="active"><i class="fa-solid fa-clipboard-check"></i>
                        <span>Attendance</span></a></li>
                <li><a href="class_managment.php"><i class="fa-solid fa-chalkboard-teacher"></i> <span>Class
                            Management</span></a></li>
                <li><a href="report.php"><i class="fa-solid fa-user-tie"></i> <span>Teachers</span></a></li>
                <li><a href="test.php"><i class="fa-solid fa-user-tie"></i> <span>Test</span></a></li>

            </ul>
        </div>

        <!-- Main Content - Add this wrapper -->
        <main class="main-content" id="mainContent" style="margin-right:150px;">
            <!-- Your page content goes here -->



            <div class="content w-100">
                <div class="section_student_div text-center py-3">
                    <h1>Attendance Management System</h1>
                </div>

                <section class="class_managment ml-5">
                    <div class="container-fluid">
                        <!-- Date Selector Card -->
                        <div class="row justify-content-center mb-4">
                            <div class="col-12 col-md-8 col-lg-6">
                                <div class="card shadow-sm w-100">
                                    <div class="card-body w-100">
                                        <label for="attendance_date" class="form-label fw-bold">Select Date:</label>
                                        <input type="date" id="attendance_date" class="form-control"
                                            value="<?= $attendance_date ?>" max="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Add this below your date selector card -->
                        <div class="row justify-content-center mb-4">
                            <div class="col-12 col-md-8 col-lg-6">
                                <div class="card shadow-sm w-100">
                                    <div class="card-body w-100">
                                        <label for="section_filter" class="form-label fw-bold">Filter by
                                            Section:</label>
                                        <select id="section_filter" class="form-control">
                                            <option value="">All Sections</option>
                                            <option value="A">Section A</option>
                                            <option value="B">Section B</option>
                                            <option value="C">Section C</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add this below your section filter card -->
<div class="row justify-content-center mb-4">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm w-100">
            <div class="card-body w-100">
                <label for="class_filter" class="form-label fw-bold">Filter by Class:</label>
                <select id="class_filter" class="form-control">
                    <option value="">All Classes</option>
                  
                    <option value="8">Class 8</option>
                    <option value="9">Class 9</option>
                  
                </select>
            </div>
        </div>
    </div>
</div>

                        <!-- Student Cards Grid -->
                       
                        <!-- After the Student Cards Grid section, add this table -->
                        <!-- After the Student Cards Grid section, add this simplified table -->
                        <div class="row mt-5">
                            <div class="col-12">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">Attendance Report for
                                            <?= date('d F Y', strtotime($attendance_date)) ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" id="attendanceTable">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>SL No</th>
                                                        <th>Photo</th>
                                                        <th>Name</th>
                                                        <th>Roll</th>
                                                        <th>Class</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($attendance_report)): ?>
                                                    <?php foreach($attendance_report as $index => $data): ?>
                                                    <tr>
                                                        <td data-label="SL No"><?= $index + 1 ?></td>
                                                        <td data-label="Photo">
                                                            <img src="uploads/<?= $data['image'] ?? 'default.png' ?>"
                                                                class="rounded-circle border"
                                                                style="width: 40px; height: 40px; object-fit: cover;"
                                                                onerror="this.src='uploads/default.png'">
                                                        </td>
                                                        <td data-label="Name">
                                                            <?= htmlspecialchars($data['student_name']) ?></td>
                                                        <td data-label="Roll"><?= $data['student_roll'] ?></td>
                                                        <td data-label="Class">
                                                            <?= $data['student_grade'] ?>-<?= $data['student_section'] ?>
                                                        </td>
                                                        <td data-label="Status">
                                                            <span
                                                                class="badge badge-<?= ($data['status'] ?? '') == 'Present' ? 'success' : 'danger' ?>">
                                                                <?= $data['status'] ?? 'Not Marked' ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No attendance data found for
                                                            this date</td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Student Selector and Month Selector Card -->


                        <!-- Monthly Report Section -->

                    </div>
                </section>

            </div>
    </div>
    </main>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Update the JavaScript to handle card layout
    $(document).ready(function() {
    // Initialize with today's date and current filters
    const initialSection = $('#section_filter').val();
    const initialClass = $('#class_filter').val();
    loadAttendanceData($('#attendance_date').val(), initialSection, initialClass);

    // Handle date change
    $('#attendance_date').change(function() {
        const date = $(this).val();
        const section = $('#section_filter').val();
        const classFilter = $('#class_filter').val();
        loadAttendanceData(date, section, classFilter);
    });

    // Handle section filter change
    $('#section_filter').change(function() {
        const date = $('#attendance_date').val();
        const section = $(this).val();
        const classFilter = $('#class_filter').val();
        loadAttendanceData(date, section, classFilter);
    });

    // Handle class filter change
    $('#class_filter').change(function() {
        const date = $('#attendance_date').val();
        const section = $('#section_filter').val();
        const classFilter = $(this).val();
        loadAttendanceData(date, section, classFilter);
    });

    // Handle present/absent clicks
    $(document).on('click', '.mark-present, .mark-absent', function() {
        const button = $(this);
        const element = button.closest('.student-card, tr');
        const studentId = element.data('student-id');
        const status = button.hasClass('mark-present') ? 'Present' : 'Absent';
        const section = $('#section_filter').val();
        const classFilter = $('#class_filter').val();

        // INSTANTLY UPDATE UI FIRST
        element.find('.badge')
            .removeClass('badge-success badge-danger bg-success bg-danger')
            .addClass(status === 'Present' ? 'badge-success bg-success' : 'badge-danger bg-danger')
            .text(status);

        // Then send to server
        updateAttendance(studentId, status, element, section, classFilter);
    });

    function loadAttendanceData(date, section = null, classFilter = null) {
        console.log("Loading data for:", date, "Section:", section, "Class:", classFilter);

        // Show loading state
        showLoadingState();

        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                h_action: 'get_attendance',
                date: date,
                section: section,
                class: classFilter
            },
            dataType: 'json',
            success: function(response) {
                console.log("Response:", response);
                if (response && response.success) {
                    renderAttendanceCards(response.data);
                    renderAttendanceTable(response.data, date, section, classFilter);
                } else {
                    showEmptyState(date, response?.error, section, classFilter);
                }
            },
            error: function(xhr) {
                console.error("Error:", xhr.responseText);
                showErrorState(xhr);
            }
        });
    }

    function showLoadingState() {
        $('#attendanceCardsContainer').html(`
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p>Loading attendance data...</p>
            </div>
        `);

        $('#attendanceTable tbody').html(`
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </td>
            </tr>
        `);
    }

    function renderAttendanceTable(data, date, section = null, classFilter = null) {
        let html = '';
        const displayDate = formatDisplayDate(date);
        
        let headerText = `Attendance Report for ${displayDate}`;
        if (classFilter) {
            headerText += ` (Class ${classFilter})`;
        }
        if (section) {
            headerText += ` (Section ${section})`;
        }

        if (data.length > 0) {
            data.forEach((student, index) => {
                const statusClass = (student.status || '').toLowerCase() === 'present' ? 'success' : 'danger';
                const statusText = student.status || 'Not Marked';

                html += `
                <tr data-student-id="${student.id}">
                    <td>${index + 1}</td>
                    <td>
                        <img src="uploads/${student.image || 'default.png'}" 
                             class="rounded-circle border"
                             style="width: 40px; height: 40px; object-fit: cover;"
                             onerror="this.src='uploads/default.png'">
                    </td>
                    <td>${escapeHtml(student.student_name)}</td>
                    <td>${student.student_roll}</td>
                    <td>${student.student_grade}-${student.student_section}</td>
                    <td>
                        <span class="badge badge-${statusClass}">
                            ${statusText}
                        </span>
                    </td>
                </tr>`;
            });
        } else {
            let noDataMessage = `No attendance records found for ${displayDate}`;
            if (classFilter) {
                noDataMessage += ` in Class ${classFilter}`;
            }
            if (section) {
                noDataMessage += `, Section ${section}`;
            }
            
            html = `
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                    ${noDataMessage}
                </td>
            </tr>`;
        }

        $('#attendanceTable tbody').html(html);
        $('.card-header h5').text(headerText);
    }

    function renderAttendanceCards(data) {
        let html = '';

        if (data.length === 0) {
            html = `
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        No attendance data found
                    </div>
                </div>
            `;
        } else {
            data.forEach((student) => {
                const statusClass = (student.status || '').toLowerCase() === 'present' ? 'success' : 'danger';
                const statusText = student.status || 'Not Marked';

                html += `
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card h-100 shadow-sm student-card" data-student-id="${student.id}">
                        <div class="card-body text-center p-3">
                            <div class="mb-3">
                                <img src="uploads/${student.image || 'default.png'}" 
                                     class="rounded-circle border" 
                                     style="width: 100px; height: 100px; object-fit: cover;"
                                     onerror="this.src='uploads/default.png'">
                            </div>
                            <h5 class="card-title mb-1">${escapeHtml(student.student_name)}</h5>
                            <div class="text-muted small mb-2">
                                Roll: ${student.student_roll} | ${student.student_grade}-${student.student_section}
                            </div>
                            <div class="mb-3">
                                <span class="badge bg-${statusClass} p-2">
                                    ${statusText}
                                </span>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-center">
                                <button class="btn btn-sm btn-success mark-present flex-grow-1">
                                    <i class="fas fa-check me-1"></i> Present
                                </button>
                                <button class="btn btn-sm btn-danger mark-absent flex-grow-1">
                                    <i class="fas fa-times me-1"></i> Absent
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
        }

        $('#attendanceCardsContainer').html(html);
    }

    function showEmptyState(date, error, section = null, classFilter = null) {
        const displayDate = formatDisplayDate(date);
        let message = error || 'No attendance data available for ' + displayDate;
        if (classFilter) {
            message += ` in Class ${classFilter}`;
        }
        if (section) {
            message += `, Section ${section}`;
        }
        
        $('#attendanceCardsContainer').html(`
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    ${message}
                </div>
            </div>
        `);
        
        $('#attendanceTable tbody').html(`
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                    ${message}
                </td>
            </tr>
        `);
    }

    function showErrorState(xhr) {
        let errorMsg = 'Failed to load data';
        try {
            const jsonResponse = JSON.parse(xhr.responseText);
            errorMsg = jsonResponse.error || errorMsg;
        } catch (e) {
            errorMsg = xhr.statusText || errorMsg;
        }

        $('#attendanceCardsContainer').html(`
            <div class="col-12">
                <div class="alert alert-danger text-center">
                    ${errorMsg}
                </div>
            </div>
        `);
        $('#attendanceTable tbody').html(`
            <tr>
                <td colspan="6" class="text-center py-4 text-danger">
                    ${errorMsg}
                </td>
            </tr>
        `);
    }

    function updateAttendance(studentId, status, element, section = null, classFilter = null) {
        const date = $('#attendance_date').val();
        const buttons = element.find('.btn');

        // Show loading state
        buttons.prop('disabled', true);
        element.addClass('loading');

        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                h_action: 'update_attendance',
                student_id: studentId,
                status: status,
                date: date,
                section: section,
                class: classFilter
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update UI
                    element.find('.badge')
                        .removeClass('badge-success badge-danger bg-success bg-danger')
                        .addClass(status === 'Present' ? 'badge-success bg-success' : 'badge-danger bg-danger')
                        .text(status);
                    
                    // Also update the table row if exists
                    $(`#attendanceTable tr[data-student-id="${studentId}"] .badge`)
                        .removeClass('badge-success badge-danger')
                        .addClass(status === 'Present' ? 'badge-success' : 'badge-danger')
                        .text(status);
                } else {
                    alert(response.error || 'Failed to update attendance');
                    // Revert UI
                    const currentStatus = element.find('.badge').text().trim();
                    element.find('.badge')
                        .removeClass('badge-success badge-danger bg-success bg-danger')
                        .addClass(currentStatus === 'Present' ? 'badge-success bg-success' : 'badge-danger bg-danger');
                }
            },
            error: function(xhr) {
                alert('Network error occurred');
                console.error(xhr.responseText);
            },
            complete: function() {
                buttons.prop('disabled', false);
                element.removeClass('loading');
            }
        });
    }

    // Helper functions
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatDisplayDate(dateString) {
        const options = {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        return new Date(dateString).toLocaleDateString(undefined, options);
    }

    function showToast(message, type = 'info') {
        const toast = $(`
            <div class="toast align-items-center text-white bg-${type === 'danger' ? 'danger' : 'success'} border-0 position-fixed bottom-0 end-0 m-3" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();

        toast.on('hidden.bs.toast', function() {
            toast.remove();
        });
    }
});
    </script>


</body>

</html>