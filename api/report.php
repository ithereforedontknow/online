<?php
session_start();
require '../config/connection.php';
require '../vendor/autoload.php';
require '../fpdf/fpdf.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Improved error handling and security
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

class reportManager
{
    public $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    public function sendResponse($success, $message, $data = null)
    {
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ];
        echo json_encode($response);
        exit;
    }
    public function tallyIn($data)
    {
        try {
            $branch = $data['branch'] === 'all' ? null : $data['branch'];
            $status = $data['status'] === 'all' ? null : $data['status'];
            $dateFrom = $data['dateFrom'];
            $dateTo = $data['dateTo'];
            $signature = $data['signature'];
            $reportFormat = $data['reportFormat'];

            if ($reportFormat === 'excel') {
                $query = "SELECT * FROM transaction
            INNER JOIN hauler ON transaction.hauler_id = hauler.hauler_id
            INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id
            INNER JOIN driver ON transaction.driver_id = driver.driver_id
            INNER JOIN project ON transaction.project_id = project.project_id
            INNER JOIN origin ON transaction.origin_id = origin.origin_id
            INNER JOIN queue ON transaction.transaction_id = queue.transaction_id
            LEFT JOIN arrival ON transaction.transaction_id = arrival.transaction_id
            LEFT JOIN unloading ON transaction.transaction_id = unloading.transaction_id
            LEFT JOIN demurrage ON transaction.transaction_id = demurrage.demurrage_id
            WHERE (transaction.status = :status OR :status IS NULL)
            AND (origin.origin_id = :branch OR :branch IS NULL)
            AND (unloading.unloading_date BETWEEN :dateFrom AND :dateTo)
            ORDER BY transaction.transaction_id DESC";

                $stmt = $this->conn->prepare($query);
                $stmt->execute(['status' => $status, 'branch' => $branch, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Add header
                $sheet->setCellValue('A1', 'Tally Report');
                $sheet->mergeCells('A1:R1');
                $sheet->getStyle('A1')->getFont()->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Add headers
                $headers = ['Tally In No.', 'Tally Out No.', 'Date Received', 'Unload Start', 'Unload End', 'Project ID', 'Received Bales', 'Bales from Transfer Out', 'Received Net Weight', 'Transfer Out Net Weight', 'Scrap/LL Kilos', 'GUIA', 'Truck Type', 'Trucker', 'Plate No.', 'Driver', 'Destination', 'Remarks'];
                $sheet->fromArray($headers, NULL, 'A2');
                $sheet->getStyle('A2:R2')->getFont()->setBold(true);

                // Populate rows
                $rowIndex = 3;
                foreach ($transactions as $row) {
                    $sheet->fromArray([
                        str_pad($row['transaction_id'], 6, '0', STR_PAD_LEFT) . '-AG',
                        $row['to_reference'],
                        $row['unloading_date'],
                        $row['unloading_time_start'],
                        $row['unloading_time_end'],
                        $row['project_name'],
                        $row['no_of_bales'],
                        $row['no_of_bales'],
                        $row['kilos'],
                        $row['transfer_out_kilos'],
                        $row['scrap'],
                        $row['guia'],
                        $row['truck_type'],
                        $row['hauler_name'],
                        $row['plate_number'],
                        $row['driver_lname'] . ', ' . $row['driver_fname'] . ' ' . $row['driver_mname'],
                        'Agoo',
                        $row['remarks']
                    ], NULL, 'A' . $rowIndex++);
                }
                // Auto-size columns and center cells
                foreach (range('A', $sheet->getHighestColumn()) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $sheet->getStyle($col . '1:' . $col . $rowIndex)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                $sheet->getStyle($col . '1:' . $col . $rowIndex)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // Create table
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
                $sheet->getStyle($sheet->calculateWorksheetDimension())->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setWrapText(true);
                // Output file
                $writer = new Xlsx($spreadsheet);
                $fileName = 'Tally_Report_' . date('Y-m-d_His') . '.xlsx';
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
                // Assuming $spreadsheet is an instance of PhpSpreadsheet
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
                exit;
            } else {

                // Execute the same query to get data
                $query = "SELECT * FROM transaction
    INNER JOIN hauler ON transaction.hauler_id = hauler.hauler_id
    INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id
    INNER JOIN driver ON transaction.driver_id = driver.driver_id
    INNER JOIN project ON transaction.project_id = project.project_id
    INNER JOIN origin ON transaction.origin_id = origin.origin_id
    INNER JOIN queue ON transaction.transaction_id = queue.transaction_id
    LEFT JOIN arrival ON transaction.transaction_id = arrival.transaction_id
    LEFT JOIN unloading ON transaction.transaction_id = unloading.transaction_id
    LEFT JOIN demurrage ON transaction.transaction_id = demurrage.demurrage_id
    WHERE (transaction.status = :status OR :status IS NULL)
    AND (origin.origin_id = :branch OR :branch IS NULL)
    AND (unloading.unloading_date BETWEEN :dateFrom AND :dateTo)
    ORDER BY transaction.transaction_id DESC";

                $stmt = $this->conn->prepare($query);
                $stmt->execute(['status' => $status, 'branch' => $branch, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Create PDF object
                $pdf = new FPDF('L', 'mm', 'Legal'); // Landscape orientation
                $pdf->AddPage();

                // Add signature
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetXY(10, 10);
                $pdf->Cell(0, 6, 'Signed by: ' . $signature, 0, 1, 'R');
                $pdf->Ln(10);

                $imagePath = '../assets/img/ulpi agoo.png';
                if (file_exists($imagePath)) {
                    $pdf->Image($imagePath, ($pdf->GetPageWidth() - 100) / 2, 2, 100); // Center the image
                } else {
                    error_log("Header image not found at: " . $imagePath);
                }
                $pdf->Ln(30);

                // Set font
                $pdf->SetFont('Arial', 'B', 16);

                // Title
                $pdf->Cell(0, 10, 'Tally Report', 0, 1, 'C');
                $pdf->Ln(10);

                // Column headers
                $pdf->SetFont('Arial', 'B', 6);
                $headers = [
                    'Tally In No.',
                    'Tally Out No.',
                    'Date Received',
                    'Unload Start',
                    'Unload End',
                    'Project ID',
                    'Received Bales',
                    'Bales from TO',
                    'Received Net Weight',
                    'TO Net Weight',
                    'Scrap/LL Kilos',
                    'GUIA',
                    'Truck Type',
                    'Trucker',
                    'Plate No.',
                    'Driver',
                    'Destination',
                    'Remarks'
                ];

                // Calculate column width (assuming page width of 290 for landscape)
                $colWidth = 335 / count($headers);

                foreach ($headers as $header) {
                    $pdf->Cell($colWidth, 7, $header, 1, 0, 'C');
                }
                $pdf->Ln();

                // Add data rows
                $pdf->SetFont('Arial', '', 6);
                foreach ($transactions as $row) {
                    // Check if we need a new page
                    if ($pdf->GetY() > 180) {
                        $pdf->AddPage();
                        // Reprint headers
                        $pdf->SetFont('Arial', 'B', 8);
                        foreach ($headers as $header) {
                            $pdf->Cell($colWidth, 7, $header, 1, 0, 'C');
                        }
                        $pdf->Ln();
                        $pdf->SetFont('Arial', '', 8);
                    }

                    $data = [
                        str_pad($row['transaction_id'], 6, '0', STR_PAD_LEFT) . '-AG',
                        $row['to_reference'],
                        $row['unloading_date'],
                        $row['unloading_time_start'],
                        $row['unloading_time_end'],
                        $row['project_name'],
                        $row['no_of_bales'],
                        $row['no_of_bales'],
                        number_format($row['kilos'], 2),
                        number_format($row['transfer_out_kilos'], 2),
                        number_format($row['scrap'], 2),
                        $row['guia'],
                        $row['truck_type'],
                        $row['hauler_name'],
                        $row['plate_number'],
                        $row['driver_lname'] . ', ' . $row['driver_fname'] . ' ' . $row['driver_mname'],
                        'Agoo',
                        $row['remarks']
                    ];

                    foreach ($data as $field) {
                        $pdf->Cell($colWidth, 6, $field, 1, 0, 'C');
                    }
                    $pdf->Ln();
                }

                // Output PDF
                $fileName = 'Tally_Report_' . date('Y-m-d_His') . '.pdf';
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
                $pdf->Output('I', $fileName);
                exit;
            }
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function dailyUnloading($data)
    {
        try {
            // Function to output shift total
            function outputShiftTotal($sheet, &$rowIndex, $date, $shift, $dayDoneCount, $dayOngoingCount, $nightDoneCount, $nightOngoingCount)
            {
                $totalDone = $shift == 'day' ? $dayDoneCount : $nightDoneCount;
                $totalOngoing = $shift == 'day' ? $dayOngoingCount : $nightOngoingCount;
                $shiftTotal = $totalDone + $totalOngoing;

                $sheet->fromArray([
                    "Shift Total - " . ucfirst($shift),
                    '',
                    ucfirst($shift),
                    '',
                    '',
                    '',
                    $totalDone,
                    $totalOngoing,
                    $shiftTotal
                ], null, "A$rowIndex");

                $sheet->getStyle("A$rowIndex:I$rowIndex")->getFont()->setItalic(true);
                $rowIndex++;
            }

            // Function to output day total
            function outputDayTotal($sheet, &$rowIndex, $date, $totalDone, $totalOngoing)
            {
                $dayTotal = $totalDone + $totalOngoing;
                $sheet->fromArray([
                    "Day Total - " . date('F j, Y', strtotime($date)),
                    '',
                    '',
                    '',
                    '',
                    '',
                    $totalDone,
                    $totalOngoing,
                    $dayTotal
                ], null, "A$rowIndex");

                $sheet->getStyle("A$rowIndex:I$rowIndex")->getFont()->setBold(true);
                $rowIndex++;
            }

            $branch = $data['branch'] === 'all' ? null : $data['branch'];
            $status = $data['status'] === 'all' ? null : $data['status'];
            $dateFrom = $data['dateFrom'];
            $dateTo = $data['dateTo'];
            $signature = $data['signature'];
            $reportFormat = $data['reportFormat'];

            if ($reportFormat === 'excel') {
                $query = "SELECT 
                    arrival.arrival_date,
                    unloading.unloading_date,
                    queue.shift,
                    queue.transfer_in_line,
                    vehicle.plate_number,
                    project.project_name,
                    transaction.status
                FROM transaction
                INNER JOIN hauler ON transaction.hauler_id = hauler.hauler_id
                INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id
                INNER JOIN driver ON transaction.driver_id = driver.driver_id
                INNER JOIN project ON transaction.project_id = project.project_id
                INNER JOIN origin ON transaction.origin_id = origin.origin_id
                INNER JOIN queue ON transaction.transaction_id = queue.transaction_id
                LEFT JOIN arrival ON transaction.transaction_id = arrival.transaction_id
                LEFT JOIN unloading ON transaction.transaction_id = unloading.transaction_id
                LEFT JOIN demurrage ON transaction.transaction_id = demurrage.demurrage_id
                WHERE (transaction.status = :status OR :status IS NULL)
                AND (origin.origin_id = :branch OR :branch IS NULL)
                AND (unloading.unloading_date BETWEEN :dateFrom AND :dateTo)
                ORDER BY transaction.transaction_id DESC";

                $stmt = $this->conn->prepare($query);
                $stmt->execute([
                    ':status' => $status,
                    ':branch' => $branch,
                    ':dateFrom' => $dateFrom,
                    ':dateTo' => $dateTo,
                ]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Initialize PhpSpreadsheet
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Add headers
                $headers = [
                    'Arrival Date',
                    'Unloading Date',
                    'Shift',
                    'Transfer In Line',
                    'Plate Number',
                    'Project',
                    'Unloading Status - Done',
                    'Unloading Status - Ongoing',
                    'Total'
                ];
                $sheet->fromArray($headers, null, 'A1');
                $sheet->getStyle('A1:I1')->getFont()->setBold(true);

                // Set column auto-size and alignment
                foreach (range('A', 'I') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $sheet->getStyle($col)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }

                $rowIndex = 2;
                $currentDate = null;
                $currentShift = null;
                $dayDoneCount = 0;
                $dayOngoingCount = 0;
                $nightDoneCount = 0;
                $nightOngoingCount = 0;
                $totalDayDoneCount = 0;
                $totalDayOngoingCount = 0;

                foreach ($transactions as $row) {
                    if ($currentDate != $row['arrival_date']) {
                        // Output Shift Total
                        if ($currentShift !== null) {
                            outputShiftTotal($sheet, $rowIndex, $currentDate, $currentShift, $dayDoneCount, $dayOngoingCount, $nightDoneCount, $nightOngoingCount);
                        }
                        // Output Day Total
                        if ($currentDate !== null) {
                            outputDayTotal($sheet, $rowIndex, $currentDate, $totalDayDoneCount, $totalDayOngoingCount);
                            $totalDayDoneCount = 0;
                            $totalDayOngoingCount = 0;
                        }

                        $currentDate = $row['arrival_date'];
                        $currentShift = null;
                        $dayDoneCount = 0;
                        $dayOngoingCount = 0;
                        $nightDoneCount = 0;
                        $nightOngoingCount = 0;

                        // Add Date Header
                        $sheet->setCellValue("A$rowIndex", date('F j, Y', strtotime($currentDate)));
                        $sheet->mergeCells("A$rowIndex:I$rowIndex");
                        $sheet->getStyle("A$rowIndex")->getFont()->setBold(true);
                        $rowIndex++;
                    }

                    if ($currentShift != $row['shift']) {
                        if ($currentShift !== null) {
                            outputShiftTotal($sheet, $rowIndex, $currentDate, $currentShift, $dayDoneCount, $dayOngoingCount, $nightDoneCount, $nightOngoingCount);
                        }
                        $currentShift = $row['shift'];
                    }

                    if ($row['status'] == 'done' || $row['status'] == 'diverted') {
                        $row['shift'] == 'day' ? $dayDoneCount++ : $nightDoneCount++;
                        $totalDayDoneCount++;
                    } elseif ($row['status'] == 'ongoing') {
                        $row['shift'] == 'day' ? $dayOngoingCount++ : $nightOngoingCount++;
                        $totalDayOngoingCount++;
                    }

                    // Add Data Row
                    $sheet->fromArray([
                        $row['arrival_date'],
                        $row['unloading_date'],
                        ucfirst($row['shift']),
                        $row['transfer_in_line'],
                        $row['plate_number'],
                        $row['project_name'],
                        ($row['status'] == 'done' || $row['status'] == 'diverted') ? 1 : '',
                        ($row['status'] == 'ongoing') ? 1 : '',
                        1
                    ], null, "A$rowIndex");

                    $rowIndex++;
                }

                // Output the last Shift and Day Totals
                outputShiftTotal($sheet, $rowIndex, $currentDate, $currentShift, $dayDoneCount, $dayOngoingCount, $nightDoneCount, $nightOngoingCount);
                outputDayTotal($sheet, $rowIndex, $currentDate, $totalDayDoneCount, $totalDayOngoingCount);

                // Save the Excel file
                $writer = new Xlsx($spreadsheet);
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="Unloading_Report ' . date('Y-m-d') . '.xlsx"');
                header('Cache-Control: max-age=0');
                $writer->save('php://output');
                exit;
            } else {

                // Function to output shift total
                function outputShiftTotalPDF($pdf, $date, $shift, $dayDoneCount, $dayOngoingCount, $nightDoneCount, $nightOngoingCount)
                {
                    $pdf->SetFont('Arial', 'I', 8);
                    $totalDone = $shift == 'day' ? $dayDoneCount : $nightDoneCount;
                    $totalOngoing = $shift == 'day' ? $dayOngoingCount : $nightOngoingCount;
                    $shiftTotal = $totalDone + $totalOngoing;

                    $pdf->Cell(25, 10, "Shift Total - " . ucfirst($shift), 1);
                    $pdf->Cell(125, 10, '', 1);
                    $pdf->Cell(25, 10, $totalDone, 1);
                    $pdf->Cell(25, 10, $totalOngoing, 1);
                    $pdf->Cell(25, 10, $shiftTotal, 1);
                    $pdf->Ln();
                }

                // Function to output day total
                function outputDayTotalPDF($pdf, $date, $totalDone, $totalOngoing)
                {
                    $pdf->SetFont('Arial', 'B', 8);
                    $dayTotal = $totalDone + $totalOngoing;

                    $pdf->Cell(25, 10, "Day Total", 1);
                    $pdf->Cell(125, 10, '', 1);
                    $pdf->Cell(25, 10, $totalDone, 1);
                    $pdf->Cell(25, 10, $totalOngoing, 1);
                    $pdf->Cell(25, 10, $dayTotal, 1);
                    $pdf->Ln();
                }
                $query = "SELECT 
                arrival.arrival_date,
                unloading.unloading_date,
                queue.shift,
                queue.transfer_in_line,
                vehicle.plate_number,
                project.project_name,
                transaction.status
            FROM transaction
            INNER JOIN hauler ON transaction.hauler_id = hauler.hauler_id
            INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id
            INNER JOIN driver ON transaction.driver_id = driver.driver_id
            INNER JOIN project ON transaction.project_id = project.project_id
            INNER JOIN origin ON transaction.origin_id = origin.origin_id
            INNER JOIN queue ON transaction.transaction_id = queue.transaction_id
            LEFT JOIN arrival ON transaction.transaction_id = arrival.transaction_id
            LEFT JOIN unloading ON transaction.transaction_id = unloading.transaction_id
            LEFT JOIN demurrage ON transaction.transaction_id = demurrage.demurrage_id
            WHERE (transaction.status = :status OR :status IS NULL)
            AND (origin.origin_id = :branch OR :branch IS NULL)
            AND (unloading.unloading_date BETWEEN :dateFrom AND :dateTo)
            ORDER BY transaction.transaction_id DESC";

                $stmt = $this->conn->prepare($query);
                $stmt->execute([
                    ':status' => $status,
                    ':branch' => $branch,
                    ':dateFrom' => $dateFrom,
                    ':dateTo' => $dateTo,
                ]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // Create the FPDF object
                $pdf = new FPDF('L', 'mm', 'A4'); // Landscape orientation

                $pdf->AliasNbPages();

                $pdf->AddPage();

                // Add signature
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetXY(10, 10);
                $pdf->Cell(0, 6, 'Signed by: ' . $signature, 0, 1, 'R');
                $pdf->Ln(10);

                $imagePath = '../assets/img/ulpi agoo.png';
                if (file_exists($imagePath)) {
                    $pdf->Image($imagePath, ($pdf->GetPageWidth() - 100) / 2, 2, 100); // Center the image
                } else {
                    error_log("Header image not found at: " . $imagePath);
                }
                $pdf->Ln(20);
                // Add header
                $pdf->SetFont('Arial', 'B', 14);
                $pdf->Cell(0, 10, 'Unloading Report', 0, 1, 'C');
                $pdf->Ln(5);

                // Function to add table headers
                function addTableHeader($pdf)
                {
                    $pdf->SetFont('Arial', 'B', 8);
                    $headers = [
                        'Arrival Date',
                        'Unloading Date',
                        'Shift',
                        'Transfer In Line',
                        'Plate Number',
                        'Project',
                        'Done',
                        'Ongoing',
                        'Total'
                    ];
                    foreach ($headers as $col) {
                        $pdf->Cell(25, 10, $col, 1, 0, 'C');
                    }
                    $pdf->Ln();
                }

                // Add the table header
                addTableHeader($pdf);

                // Initialize variables
                $currentDate = null;
                $currentShift = null;
                $dayDoneCount = 0;
                $dayOngoingCount = 0;
                $nightDoneCount = 0;
                $nightOngoingCount = 0;
                $totalDayDoneCount = 0;
                $totalDayOngoingCount = 0;

                foreach ($transactions as $row) {
                    if ($currentDate != $row['arrival_date']) {
                        // Output Shift Total
                        if ($currentShift !== null) {
                            outputShiftTotalPDF($pdf, $currentDate, $currentShift, $dayDoneCount, $dayOngoingCount, $nightDoneCount, $nightOngoingCount);
                        }
                        // Output Day Total
                        if ($currentDate !== null) {
                            outputDayTotalPDF($pdf, $currentDate, $totalDayDoneCount, $totalDayOngoingCount);
                            $totalDayDoneCount = 0;
                            $totalDayOngoingCount = 0;
                        }

                        $currentDate = $row['arrival_date'];
                        $currentShift = null;
                        $dayDoneCount = 0;
                        $dayOngoingCount = 0;
                        $nightDoneCount = 0;
                        $nightOngoingCount = 0;

                        // Add Date Header
                        $pdf->SetFont('Arial', 'B', 8);
                        $pdf->Cell(225, 10, "Date: " . date('F j, Y', strtotime($currentDate)), 1, 1, 'C');
                    }

                    if ($currentShift != $row['shift']) {
                        if ($currentShift !== null) {
                            outputShiftTotalPDF($pdf, $currentDate, $currentShift, $dayDoneCount, $dayOngoingCount, $nightDoneCount, $nightOngoingCount);
                        }
                        $currentShift = $row['shift'];
                    }

                    if ($row['status'] == 'done' || $row['status'] == 'diverted') {
                        $row['shift'] == 'day' ? $dayDoneCount++ : $nightDoneCount++;
                        $totalDayDoneCount++;
                    } elseif ($row['status'] == 'ongoing') {
                        $row['shift'] == 'day' ? $dayOngoingCount++ : $nightOngoingCount++;
                        $totalDayOngoingCount++;
                    }

                    // Add Data Row
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->Cell(25, 10, $row['arrival_date'], 1);
                    $pdf->Cell(25, 10, $row['unloading_date'], 1);
                    $pdf->Cell(25, 10, ucfirst($row['shift']), 1);
                    $pdf->Cell(25, 10, $row['transfer_in_line'], 1);
                    $pdf->Cell(25, 10, $row['plate_number'], 1);
                    $pdf->Cell(25, 10, $row['project_name'], 1);
                    $pdf->Cell(25, 10, ($row['status'] == 'done' || $row['status'] == 'diverted') ? 1 : '', 1);
                    $pdf->Cell(25, 10, ($row['status'] == 'ongoing') ? 1 : '', 1);
                    $pdf->Cell(25, 10, 1, 1);
                    $pdf->Ln();
                }

                // Output the last Shift and Day Totals
                outputShiftTotalPDF($pdf, $currentDate, $currentShift, $dayDoneCount, $dayOngoingCount, $nightDoneCount, $nightOngoingCount);
                outputDayTotalPDF($pdf, $currentDate, $totalDayDoneCount, $totalDayOngoingCount);

                // Output Footer
                $pdf->SetY(-15);
                $pdf->SetFont('Arial', 'I', 8);
                $pdf->Cell(0, 10, 'Page ' . $pdf->PageNo() . '/{nb}', 0, 0, 'C');

                // Output the PDF
                $pdf->Output('I', 'Unloading_Report_' . date('Y-m-d') . '.pdf');
                exit;
            }
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }


    // Helper functions (outputShiftTotal and outputDayTotal) remain unchanged.

}


// Main API Handler
try {
    if (!isset($conn) || $conn === null) {
        throw new Exception('Database connection not established');
    }

    $reportManager = new reportManager($conn);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $action = $_POST['action'] ?? '';
        switch ($action) {
            case 'tally-in':
                $reportManager->tallyIn($_POST);
                break;
            case 'daily-unloading':
                $reportManager->dailyUnloading($_POST);
                break;
            default:
                $reportManager->sendResponse(false, 'Invalid action');
        }
    } else {
        $reportManager->sendResponse(false, 'Method not allowed');
    }
} catch (Exception $e) {
    error_log('Unhandled error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
    exit;
}
