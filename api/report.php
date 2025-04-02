<?php
session_start();
require '../config/connection.php';
require '../vendor/autoload.php';
require '../fpdf/fpdf.php';
require '../vendor/tecnickcom/tcpdf/tcpdf.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
            WHERE (origin.origin_id = :branch OR :branch IS NULL)
            AND (unloading.unloading_date BETWEEN :dateFrom AND :dateTo)
            ORDER BY transaction.transaction_id DESC";

                $stmt = $this->conn->prepare($query);
                $stmt->execute(['branch' => $branch, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Professional Color Scheme
                $primaryColor = '2C3E50';  // Dark Blue-Gray
                $headerColor = 'ECF0F1';   // Light Gray
                $textColor = '34495E';     // Muted Dark Blue

                // Title Styling
                $sheet->setCellValue('A1', 'TALLY REPORT');
                $sheet->mergeCells('A1:R1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ]
                ]);

                // Add Subtitle with As Of
                $sheet->setCellValue('A2', "As Of: {$dateFrom} to {$dateTo}");
                $sheet->mergeCells('A2:R2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10,
                        'color' => ['rgb' => $textColor]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Column Headers
                $headers = [
                    'Tally In No.',
                    'Tally Out No.',
                    'Date Received',
                    'Unload Start',
                    'Unload End',
                    'Project ID',
                    'Received Bales',
                    'Bales from Transfer Out',
                    'Received Net Weight',
                    'Transfer Out Net Weight',
                    'Scrap/LL Kilos',
                    'GUIA',
                    'Truck Type',
                    'Trucker',
                    'Plate No.',
                    'Driver',
                    'Destination',
                    'Remarks'
                ];

                $sheet->fromArray($headers, NULL, 'A3');
                $sheet->getStyle('A3:R3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ]
                ]);

                // Populate Data Rows
                $rowIndex = 4;
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

                // Advanced Column Formatting
                foreach (range('A', $sheet->getHighestColumn()) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $sheet->getStyle($col . '3:' . $col . $rowIndex)->applyFromArray([
                        'font' => ['color' => ['rgb' => $textColor]],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ]
                    ]);
                }

                // Number Formatting with Comma Separators
                $sheet->getStyle("G4:J{$rowIndex}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // Table Styling
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
                $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ],
                    'alignment' => ['wrapText' => true]
                ]);

                // Footer with Timestamp
                $sheet->setCellValue("A{$rowIndex}", "Signed by: " . $data['signature'] . " | Date: " . date('Y-m-d H:i:s'));
                $sheet->mergeCells("A{$rowIndex}:R{$rowIndex}");
                $sheet->getStyle("A{$rowIndex}")->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 9,
                        'color' => ['rgb' => $textColor]
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
                ]);

                // Output File
                $writer = new Xlsx($spreadsheet);
                $fileName = 'Tally_Report_' . date('Y-m-d_His') . '.xlsx';
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
                $writer->save('php://output');
                exit;
            } else {

                $pdf = new TCPDF('L', 'mm', [210, 380], true, 'UTF-8', false);

                // Professional Color Scheme
                $headerColor = array(44, 62, 80);    // Dark Blue-Gray
                $headerTextColor = array(236, 240, 241); // Light Gray
                $bodyTextColor = array(52, 73, 94);   // Muted Dark Blue

                // Document properties
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('Your Company Name');
                $pdf->SetTitle('Tally Report');
                $pdf->SetSubject('Detailed Transaction Report');
                $pdf->SetKeywords('tally, report, transactions');

                // Remove default header/footer
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(true);

                // Set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // Set margins
                $pdf->SetMargins(10, 20, 10);
                $pdf->SetHeaderMargin(10);
                $pdf->SetFooterMargin(10);

                // Set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                // Add a page
                $pdf->AddPage();

                // Logo
                $logoPath = '../assets/img/ulpi agoo.png';
                if (file_exists($logoPath)) {
                    $pdf->Image($logoPath, 80, 10, 50, '', '', '', 'T', false, 300, 'C');
                }

                // Title
                $pdf->SetFont('helvetica', 'B', 16);
                $pdf->SetTextColor($headerColor[0], $headerColor[1], $headerColor[2]);
                $pdf->Ln(25);
                $pdf->Cell(0, 10, 'Tally Report', 0, 1, 'C');

                // As Of Subtitle
                $pdf->SetFont('helvetica', 'I', 10);
                $pdf->SetTextColor($bodyTextColor[0], $bodyTextColor[1], $bodyTextColor[2]);
                $pdf->Cell(0, 10, "As Of: {$dateFrom} to {$dateTo}", 0, 1, 'C');

                // Fetch transactions
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
    WHERE (origin.origin_id = :branch OR :branch IS NULL)
    AND (unloading.unloading_date BETWEEN :dateFrom AND :dateTo)
    ORDER BY transaction.transaction_id DESC";

                $stmt = $this->conn->prepare($query);
                $stmt->execute(['branch' => $branch, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Table headers
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

                // Create table
                $pdf->SetFont('helvetica', 'B', 6);
                $pdf->SetFillColor($headerColor[0], $headerColor[1], $headerColor[2]);
                $pdf->SetTextColor($headerTextColor[0], $headerTextColor[1], $headerTextColor[2]);

                // Header row
                foreach ($headers as $header) {
                    $pdf->Cell(20, 7, $header, 1, 0, 'C', 1);
                }
                $pdf->Ln();

                // Data rows
                $pdf->SetFont('helvetica', '', 6);
                $pdf->SetTextColor($bodyTextColor[0], $bodyTextColor[1], $bodyTextColor[2]);

                foreach ($transactions as $row) {
                    $data = [
                        str_pad($row['transaction_id'], 6, '0', STR_PAD_LEFT) . '-AG',
                        $row['to_reference'],
                        date('m/d', strtotime($row['unloading_date'])),
                        date('m/d, h:i', strtotime($row['unloading_time_start'] ?? '1970-01-01 00:00:00')),
                        date('m/d, h:i', strtotime($row['unloading_time_end'] ?? '1970-01-01 00:00:00')),

                        $row['project_name'],
                        $row['no_of_bales'],
                        $row['no_of_bales'],
                        number_format($row['kilos'] ?? 0, 2),
                        number_format($row['transfer_out_kilos'] ?? 0, 2),
                        number_format($row['scrap'] ?? 0, 2),

                        $row['guia'],
                        $row['truck_type'],
                        $row['hauler_name'],
                        $row['plate_number'],
                        $row['driver_lname'] . ', ' . $row['driver_fname'] . ' ' . $row['driver_mname'],
                        'Agoo',
                        $row['remarks']
                    ];

                    // Check for page break
                    if ($pdf->GetY() > 250) {
                        $pdf->AddPage();
                        // Reprint headers
                        $pdf->SetFont('helvetica', 'B', 8);
                        $pdf->SetFillColor($headerColor[0], $headerColor[1], $headerColor[2]);
                        $pdf->SetTextColor($headerTextColor[0], $headerTextColor[1], $headerTextColor[2]);
                        foreach ($headers as $header) {
                            $pdf->Cell(12, 7, $header, 1, 0, 'C', 1);
                        }
                        $pdf->Ln();
                        $pdf->SetFont('helvetica', '', 6);
                        $pdf->SetTextColor($bodyTextColor[0], $bodyTextColor[1], $bodyTextColor[2]);
                    }

                    // Print row
                    foreach ($data as $field) {
                        $pdf->Cell(20, 7, $field, 1, 0, 'C');
                    }
                    $pdf->Ln();
                }

                // Signature and Date
                $pdf->SetFont('helvetica', '', 10);
                $pdf->SetTextColor($bodyTextColor[0], $bodyTextColor[1], $bodyTextColor[2]);
                $pdf->Cell(0, 10, 'Signed by: ' . $signature . ' | Date: ' . date('Y-m-d'), 0, 1, 'R');

                // Output PDF
                $fileName = 'Tally_Report_' . date('Y-m-d_His') . '.pdf';
                $pdf->Output($fileName, 'I');
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


            $branch = $data['branch'] === 'all' ? null : $data['branch'];
            $status = null;
            $dateFrom = $data['dateFrom'];
            $dateTo = $data['dateTo'];
            $signature = $data['signature'];
            $reportFormat = $data['reportFormat'];

            if ($reportFormat === 'excel') {
                // Professional Color Scheme
                $primaryColor = '2C3E50';  // Dark Blue-Gray
                $headerColor = 'ECF0F1';   // Light Gray
                $textColor = '34495E';     // Muted Dark Blue

                function outputShiftTotal($sheet, &$rowIndex, $date, $shift, $dayDoneCount, $dayOngoingCount, $nightDoneCount, $nightOngoingCount)
                {
                    global $primaryColor, $headerColor, $textColor;

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

                    // Apply styling for shift total
                    $sheet->getStyle("A$rowIndex:I$rowIndex")->applyFromArray([
                        'font' => [
                            'italic' => true,
                            'color' => ['rgb' => $textColor]
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'color' => ['rgb' => $headerColor]
                        ]
                    ]);

                    $rowIndex++;
                }

                function outputDayTotal($sheet, &$rowIndex, $date, $totalDone, $totalOngoing)
                {
                    global $primaryColor, $headerColor, $textColor;

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

                    // Apply styling for day total
                    $sheet->getStyle("A$rowIndex:I$rowIndex")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => $primaryColor]
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'color' => ['rgb' => $headerColor]
                        ]
                    ]);

                    $rowIndex++;
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

                // Header Styling
                $sheet->getStyle('A1:I1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ]
                ]);

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

                // Functions
                function outputShiftTotalPDF($pdf, $date, $shift, $dayDoneCount, $dayOngoingCount, $nightDoneCount, $nightOngoingCount)
                {
                    $pdf->SetFont('helvetica', 'I', 10);
                    $totalDone = $shift == 'day' ? $dayDoneCount : $nightDoneCount;
                    $totalOngoing = $shift == 'day' ? $dayOngoingCount : $nightOngoingCount;
                    $shiftTotal = $totalDone + $totalOngoing;

                    $pdf->Cell(30, 10, "Shift Total - " . ucfirst($shift), 1);
                    $pdf->Cell(150, 10, '', 1);
                    $pdf->Cell(30, 10, $totalDone, 1);
                    $pdf->Cell(30, 10, $totalOngoing, 1);
                    $pdf->Cell(30, 10, $shiftTotal, 1);
                    $pdf->Ln();
                }

                function outputDayTotalPDF($pdf, $date, $totalDone, $totalOngoing)
                {
                    $pdf->SetFont('helvetica', 'B', 10);
                    $dayTotal = $totalDone + $totalOngoing;

                    $pdf->Cell(30, 10, "Day Total", 1);
                    $pdf->Cell(150, 10, '', 1);
                    $pdf->Cell(30, 10, $totalDone, 1);
                    $pdf->Cell(30, 10, $totalOngoing, 1);
                    $pdf->Cell(30, 10, $dayTotal, 1);
                    $pdf->Ln();
                }

                // Create new PDF document
                $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

                // Set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('Your Company');
                $pdf->SetTitle('Unloading Report');
                $pdf->SetSubject('Unloading Report');
                $pdf->SetKeywords('Unloading, Report, PDF');

                // Professional Color Scheme
                $primaryColor = [44, 62, 80]; // Dark Blue-Gray
                $headerColor = [236, 240, 241]; // Light Gray
                $textColor = [52, 73, 94]; // Muted Dark Blue

                // Set margins
                $pdf->SetMargins(10, 10, 10);
                $pdf->SetHeaderMargin(5);
                $pdf->SetFooterMargin(10);
                $pdf->SetAutoPageBreak(TRUE, 10);

                // Set font
                $pdf->SetFont('helvetica', '', 10);
                $pdf->setPrintHeader(false);

                // Add a page
                $pdf->AddPage();

                // Add a header image
                $imagePath = '../assets/img/ulpi agoo.png';
                if (file_exists($imagePath)) {
                    $pdf->Image($imagePath, ($pdf->getPageWidth() - 100) / 2, 10, 100);
                }

                $pdf->Ln(45);

                // Add title
                $pdf->SetFont('helvetica', 'B', 14);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->Cell(0, 10, 'Unloading Report', 0, 1, 'C');
                $pdf->Ln(2);
                // As Of Subtitle
                $pdf->SetFont('helvetica', 'I', 10);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->Cell(0, 10, "As Of: {$dateFrom} to {$dateTo}", 0, 1, 'C');

                // Function to add table headers
                function addTableHeader($pdf, $headerColor, $textColor)
                {
                    $pdf->SetFillColor($headerColor[0], $headerColor[1], $headerColor[2]);
                    $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                    $pdf->SetFont('helvetica', 'B', 10);

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
                        $pdf->Cell(30, 10, $col, 1, 0, 'C', true);
                    }
                    $pdf->Ln();
                }

                // Add the table header
                addTableHeader($pdf, $headerColor, $textColor);
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
                // Fetch transactions
                $stmt = $this->conn->prepare($query);
                $stmt->execute([
                    ':status' => $status,
                    ':branch' => $branch,
                    ':dateFrom' => $dateFrom,
                    ':dateTo' => $dateTo,
                ]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Initialize variables
                $currentDate = null;
                $currentShift = null;
                $dayDoneCount = $dayOngoingCount = $nightDoneCount = $nightOngoingCount = 0;
                $totalDayDoneCount = $totalDayOngoingCount = 0;

                foreach ($transactions as $row) {
                    if ($currentDate != $row['arrival_date']) {
                        // Output totals for the previous date
                        if ($currentDate !== null) {
                            outputShiftTotalPDF($pdf, $currentDate, $currentShift, $dayDoneCount, $dayOngoingCount, $nightDoneCount, $nightOngoingCount);
                            outputDayTotalPDF($pdf, $currentDate, $totalDayDoneCount, $totalDayOngoingCount);
                            $totalDayDoneCount = $totalDayOngoingCount = 0;
                        }

                        // Add date header
                        $currentDate = $row['arrival_date'];
                        $pdf->SetFont('helvetica', 'B', 10);
                        $pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                        $pdf->SetTextColor(255, 255, 255);
                        $pdf->Cell(270, 10, "Date: " . date('F j, Y', strtotime($currentDate)), 1, 1, 'C', true);

                        // Reset shift data
                        $currentShift = null;
                        $dayDoneCount = $dayOngoingCount = $nightDoneCount = $nightOngoingCount = 0;
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

                    // Add data row
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                    $pdf->Cell(30, 10, $row['arrival_date'], 1);
                    $pdf->Cell(30, 10, $row['unloading_date'], 1);
                    $pdf->Cell(30, 10, ucfirst($row['shift']), 1);
                    $pdf->Cell(30, 10, $row['transfer_in_line'], 1);
                    $pdf->Cell(30, 10, $row['plate_number'], 1);
                    $pdf->Cell(30, 10, $row['project_name'], 1);
                    $pdf->Cell(30, 10, ($row['status'] == 'done' || $row['status'] == 'diverted') ? 1 : '', 1);
                    $pdf->Cell(30, 10, ($row['status'] == 'ongoing') ? 1 : '', 1);
                    $pdf->Cell(30, 10, 1, 1);
                    $pdf->Ln();
                }

                // Output totals for the last date
                outputShiftTotalPDF($pdf, $currentDate, $currentShift, $dayDoneCount, $dayOngoingCount, $nightDoneCount, $nightOngoingCount);
                outputDayTotalPDF($pdf, $currentDate, $totalDayDoneCount, $totalDayOngoingCount);

                // Footer
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Signed by: ' . $signature, 0, false, 'R', 0, '', 0, false, 'T', 'M');

                // Output the PDF
                $pdf->Output('Unloading_Report_' . date('Y-m-d') . '.pdf', 'I');
            }
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error');
        }
    }
    public function summary($data)
    {
        try {
            $startDate = $data['dateFrom'];
            $endDate = $data['dateTo'];
            $signature = $data['signature'];
            $reportFormat = $data['reportFormat'];

            // Query remains the same...
            $query = "SELECT 
                        MAX(t.status) as status,
                        a.arrival_date,
                        SUM(t.kilos) as kilos,
                        SUM(u.transfer_out_kilos) as unloaded,
                        COUNT(DISTINCT t.transaction_id) as arrived,
                        SUM(CASE WHEN t.status = 'done' THEN 1 ELSE 0 END) as unloaded_new_entry,
                        SUM(CASE WHEN t.status = 'ongoing' THEN 1 ELSE 0 END) as ongoing_new_entry,
                        SUM(CASE WHEN t.status = 'diverted' THEN 1 ELSE 0 END) as diverted_new_entry,
                        SUM(CASE WHEN t.kilos != u.transfer_out_kilos AND (t.status = 'done' OR t.status = 'diverted') THEN 1 ELSE 0 END) as total_backlog,
                        SUM(CASE WHEN q.transfer_in_line = 'GLAD WHSE' THEN 1 ELSE 0 END) as glad_receiving,
                        SUM(CASE WHEN q.transfer_in_line = 'Line 3' THEN 1 ELSE 0 END) as of_line_3,
                        SUM(CASE WHEN q.transfer_in_line = 'Line 4' THEN 1 ELSE 0 END) as of_line_4,
                        SUM(CASE WHEN q.transfer_in_line = 'Line 5' THEN 1 ELSE 0 END) as of_line_5,
                        SUM(CASE WHEN q.transfer_in_line = 'Line 6' THEN 1 ELSE 0 END) as of_line_6,
                        SUM(CASE WHEN q.transfer_in_line = 'Line 7' THEN 1 ELSE 0 END) as of_line_7,
                        SUM(CASE WHEN q.transfer_in_line = 'WHSE 2-BAY 3' THEN 1 ELSE 0 END) as whse_2_bay_3,
                        SUM(CASE WHEN q.transfer_in_line = 'WHSE 2-BAY 2' THEN 1 ELSE 0 END) as whse_2_bay_2,
                        COUNT(q.queue_id) as total,
                        SUM(CASE WHEN q.shift = 'day' THEN 1 ELSE 0 END) as day_shift,
                        SUM(CASE WHEN q.shift = 'night' THEN 1 ELSE 0 END) as night_shift,
                        SUM(CASE WHEN q.shift = 'day/night' THEN 1 ELSE 0 END) as day_night
                    FROM arrival a
                    INNER JOIN transaction t ON a.transaction_id = t.transaction_id
                    INNER JOIN unloading u ON t.transaction_id = u.transaction_id
                    LEFT JOIN queue q ON t.transaction_id = q.transaction_id
                    WHERE a.arrival_date BETWEEN :dateFrom AND :dateTo
                    GROUP BY a.arrival_date
                    ORDER BY a.arrival_date DESC;
                    ";

            $stmt = $this->conn->prepare($query);
            $stmt->execute(['dateFrom' => $startDate, 'dateTo' => $endDate]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($reportFormat === 'excel') {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Set page setup
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL);

                // Add company logo
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Company Logo');
                $drawing->setPath('../assets/img/ulpi agoo.png');
                $drawing->setHeight(200);
                $drawing->setCoordinates('A1');
                $drawing->setWorksheet($sheet);

                // Professional Color Scheme
                $primaryColor = '2C3E50';  // Dark Blue-Gray
                $headerColor = 'ECF0F1';   // Light Gray
                $textColor = '34495E';     // Muted Dark Blue

                // Modify DAILY SUMMARY REPORT title styling
                $sheet->setCellValue('A7', 'DAILY SUMMARY REPORT');
                $sheet->mergeCells('A7:W7');
                $sheet->getStyle('A7')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ]
                ]);

                // Add signature
                $sheet->setCellValue('U5', 'Signed By: ' . $signature);
                $sheet->mergeCells('U5:W5');
                $sheet->getStyle('U5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Section headers with improved styling
                $sheet->setCellValue('A10', 'Number of Trucks');
                $sheet->mergeCells('A10:I10');
                $sheet->setCellValue('K10', 'Unloaded Trucks per Line');
                $sheet->mergeCells('K10:S10');
                $sheet->setCellValue('U10', '# of Trucks UNLOADED per Shift');
                $sheet->mergeCells('U10:W10');
                // Update As Of styling
                $sheet->setCellValue('A8', 'As Of: ' . date('F d, Y', strtotime($startDate)) . ' - ' . date('F d, Y', strtotime($endDate)));
                $sheet->mergeCells('A8:W8');
                $sheet->getStyle('A8')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10,
                        'color' => ['rgb' => $textColor]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Modify section headers styling
                $sectionStyle = [
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $headerColor]
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ];
                $sheet->getStyle('A10:I10')->applyFromArray($sectionStyle);
                $sheet->getStyle('K10:S10')->applyFromArray($sectionStyle);
                $sheet->getStyle('U10:W10')->applyFromArray($sectionStyle);

                // Modify other existing code as needed to maintain the professional color scheme
                // Column headers
                $headers = [
                    'Transaction Date',
                    'Arrived',
                    'Unloaded (New Entry)',
                    'Unloaded (Backlog)',
                    'On-going (New Entry)',
                    'On-going (Backlog)',
                    'Diverted (New Entry)',
                    'Diverted (Backlog)',
                    'Backlog',
                    '',
                    'GLAD Receiving',
                    'OF - Line 3',
                    'OF - Line 4',
                    'OF - Line 5',
                    'OF - Line 6',
                    'OF - Line 7',
                    'Whse 2 - Bay 3',
                    'Whse 2 - Bay 2',
                    'TOTAL',
                    '',
                    'Day Shift',
                    'Night Shift',
                    'Day/Night'
                ];

                // Apply column headers
                $sheet->fromArray($headers, NULL, 'A11');

                // Style column headers
                $headerStyle = [
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E7E6E6']
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ];
                $sheet->getStyle('A11:W11')->applyFromArray($headerStyle);

                // Add data with improved styling
                $row = 12;
                foreach ($transactions as $data) {
                    $unloaded_backlog = ($data['kilos'] != $data['unloaded'] && $data['status'] == 'done') ? 1 : 0;
                    $diverted_backlog = ($data['kilos'] != $data['unloaded'] && $data['status'] == 'diverted') ? 1 : 0;

                    $rowData = [
                        date('M d, Y', strtotime($data['arrival_date'])),
                        $data['arrived'],
                        $data['unloaded_new_entry'],
                        $unloaded_backlog,
                        $data['ongoing_new_entry'],
                        0,
                        $data['diverted_new_entry'],
                        $diverted_backlog,
                        $data['total_backlog'],
                        '',
                        $data['glad_receiving'],
                        $data['of_line_3'],
                        $data['of_line_4'],
                        $data['of_line_5'],
                        $data['of_line_6'],
                        $data['of_line_7'],
                        $data['whse_2_bay_3'],
                        $data['whse_2_bay_2'],
                        $data['total'],
                        '',
                        $data['day_shift'],
                        $data['night_shift'],
                        $data['day_night']
                    ];

                    $sheet->fromArray($rowData, NULL, 'A' . $row);

                    // Style data rows
                    $dataStyle = [
                        'borders' => [
                            'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ]
                    ];
                    $sheet->getStyle('A' . $row . ':W' . $row)->applyFromArray($dataStyle);

                    // Alternate row colors
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':W' . $row)->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F9F9F9');
                    }

                    $row++;
                }

                // Auto-size columns
                foreach (range('A', 'W') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Add totals row
                $totalRow = $row;
                $sheet->setCellValue('A' . $totalRow, 'TOTAL');
                $sheet->getStyle('A' . $totalRow . ':W' . $totalRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $totalRow . ':W' . $totalRow)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E7E6E6');

                // Add sum formulas for numeric columns
                foreach (range('B', 'W') as $col) {
                    $sheet->setCellValue(
                        $col . $totalRow,
                        '=SUM(' . $col . '12:' . $col . ($row - 1) . ')'
                    );
                }

                // Output file
                $fileName = 'Summary_Report_' . date('Y-m-d_His') . '.xlsx';
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
                exit;
            } else {

                // Color Scheme
                $primaryColor = [44, 62, 80];     // Dark Blue-Gray
                $headerColor = [236, 240, 241];   // Light Gray
                $textColor = [52, 73, 94];        // Muted Dark Blue

                // Create PDF
                $pdf = new TCPDF('L', 'mm', array(216, 385), true, 'UTF-8', false);
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor($signature);
                $pdf->SetTitle('Daily Summary Report');
                $pdf->SetAutoPageBreak(TRUE, 10);
                $pdf->SetPrintHeader(false);
                $pdf->SetPrintFooter(false);
                $pdf->SetMargins(10, 10, 10);
                $pdf->SetAutoPageBreak(true, 15);
                $pdf->AddPage();
                // Logo
                $imagePath = '../assets/img/ulpi agoo.png';
                if (file_exists($imagePath)) {
                    $pdf->Image($imagePath, ($pdf->getPageWidth() - 100) / 2, 10, 100);
                }
                $pdf->Ln(40);

                // Custom Header
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetFont('helvetica', 'B', 16);

                $pdf->Cell(0, 15, 'DAILY SUMMARY REPORT', 0, 1, 'C');

                // As Of
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                $pdf->SetFont('helvetica', 'I', 10);
                $pdf->Cell(0, 10, 'As Of: ' . date('F d, Y', strtotime($startDate)) . ' - ' . date('F d, Y', strtotime($endDate)), 0, 1, 'C');
                // Section headers
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->Cell(160, 7, 'Number of Trucks', 0, 0, 'C', false);
                $pdf->Cell(160, 7, 'Unloaded Trucks per Line', 0, 0, 'C', false);
                $pdf->Cell(50, 7, 'Trucks UNLOADED per Shift', 0, 1, 'C', false);
                $pdf->Ln(2);
                // Headers
                $headers = [
                    'Date',
                    'Arrived',
                    'Unloaded',
                    'Backlog',
                    'Ongoing',
                    'Backlog',
                    'Diverted',
                    'Backlog',
                    'Backlog(All)',
                    '',
                    'GLAD',
                    'Line 3',
                    'Line 4',
                    'Line 5',
                    'Line 6',
                    'Line 7',
                    'WH2-B3',
                    'WH2-B2',
                    'TOTAL',
                    '',
                    'Day',
                    'Night',
                    'D/N'
                ];

                // Table header styling
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetFillColor($headerColor[0], $headerColor[1], $headerColor[2]);
                $colWidth = 16;

                // Print headers
                $pdf->SetLineStyle(['width' => 0.5, 'color' => $primaryColor]);
                foreach ($headers as $header) {
                    $pdf->Cell($colWidth, 7, $header, 1, 0, 'C', true);
                }
                $pdf->Ln();

                // Data rows
                $pdf->SetFont('helvetica', '', 7);
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

                foreach ($transactions as $data) {
                    $fill = ($pdf->GetY() % 2 == 0);

                    $unloaded_backlog = ($data['kilos'] != $data['unloaded'] && $data['status'] == 'done') ? 1 : 0;
                    $diverted_backlog = ($data['kilos'] != $data['unloaded'] && $data['status'] == 'diverted') ? 1 : 0;

                    $rowData = [
                        date('d, M', strtotime($data['arrival_date'])),
                        $data['arrived'],
                        $data['unloaded_new_entry'],
                        $unloaded_backlog,
                        $data['ongoing_new_entry'],
                        0,
                        $data['diverted_new_entry'],
                        $diverted_backlog,
                        $data['total_backlog'],
                        '',
                        $data['glad_receiving'],
                        $data['of_line_3'],
                        $data['of_line_4'],
                        $data['of_line_5'],
                        $data['of_line_6'],
                        $data['of_line_7'],
                        $data['whse_2_bay_3'],
                        $data['whse_2_bay_2'],
                        $data['total'],
                        '',
                        $data['day_shift'],
                        $data['night_shift'],
                        $data['day_night']
                    ];

                    foreach ($rowData as $field) {
                        $pdf->Cell($colWidth, 6, $field, 1, 0, 'C', $fill);
                    }
                    $pdf->Ln();
                }


                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Signed By: ' . $signature . ' | Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages(), 0, 0, 'R');

                // Output PDF
                $fileName = 'Summary_Report_' . date('Y-m-d_His') . '.pdf';
                $pdf->Output($fileName, 'I');
            }
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error', $e->getMessage());
        }
    }
    public function allReports($data)
    {
        try {
            $dateFrom = $data['dateFrom'];
            $dateTo = $data['dateTo'];
            $branch = $data['branch'] === 'all' ? null : $data['branch'];
            $status = $data['status'] === 'all' ? null : $data['status'];
            $signature = $data['signature'];
            $reportFormat = $data['reportFormat'];

            if ($reportFormat === 'excel') {
                // Updated query with headers and processing
                $query = "SELECT transaction.*, 
                            hauler.hauler_name,
                            vehicle.plate_number,
                            vehicle.truck_type,
                            driver.driver_fname,
                            driver.driver_lname,
                            helper.helper_fname,
                            helper.helper_lname,
                            project.project_name,
                            origin.origin_name
                            FROM transaction
                            INNER JOIN hauler ON transaction.hauler_id = hauler.hauler_id
                            INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id
                            INNER JOIN driver ON transaction.driver_id = driver.driver_id
                            INNER JOIN helper ON transaction.helper_id = helper.helper_id
                            INNER JOIN project ON transaction.project_id = project.project_id
                            INNER JOIN origin ON transaction.origin_id = origin.origin_id
                            LEFT JOIN queue ON transaction.transaction_id = queue.transaction_id
                            LEFT JOIN arrival ON transaction.transaction_id = arrival.transaction_id
                            LEFT JOIN unloading ON transaction.transaction_id = unloading.transaction_id
                            WHERE (transaction.status = :status OR :status IS NULL)
                            AND (origin.origin_id = :branch OR :branch IS NULL)
                            AND (transaction.created_at BETWEEN :dateFrom AND :dateTo)
                            ORDER BY transaction.transaction_id DESC";

                $stmt = $this->conn->prepare($query);
                $stmt->execute(['status' => $status, 'branch' => $branch, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Clear any previous output or errors
                ob_clean();

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Professional Color Scheme
                $primaryColor = '2C3E50';
                $headerColor = 'ECF0F1';
                $textColor = '34495E';

                // Title Styling
                $title = $status ? strtoupper($status) . ' REPORT' : 'ALL REPORT';
                $sheet->setCellValue('A1', $title);
                $sheet->mergeCells('A1:K1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ]
                ]);

                // Add Subtitle with As Of
                $sheet->setCellValue('A2', "As Of: {$dateFrom} to {$dateTo}");
                $sheet->mergeCells('A2:K2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10,
                        'color' => ['rgb' => $textColor]
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Column Headers
                $headers = [
                    'TO Reference',
                    'GUIA',
                    'Hauler',
                    'Vehicle',
                    'Driver Name',
                    'Helper Name',
                    'Project',
                    'Origin',
                    'No of Bales',
                    'Kilos',
                    'Demurrage'
                ];

                $sheet->fromArray($headers, NULL, 'A3');
                $sheet->getStyle('A3:K3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ]
                ]);

                // Populate Data Rows
                $rowIndex = 4;
                foreach ($transactions as $row) {
                    $sheet->fromArray([
                        $row['to_reference'],
                        $row['guia'],
                        $row['hauler_name'],
                        $row['plate_number'] . ' (' . $row['truck_type'] . ')',
                        $row['driver_lname'] . ', ' . $row['driver_fname'],
                        $row['helper_lname']  . ', ' . $row['helper_fname'],
                        $row['project_name'],
                        $row['origin_name'],
                        $row['no_of_bales'],
                        $row['kilos'],
                        $row['demurrage']
                    ], NULL, 'A' . $rowIndex++);
                }

                // Advanced Column Formatting
                foreach (range('A', 'K') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $sheet->getStyle($col . '3:' . $col . $rowIndex)->applyFromArray([
                        'font' => ['color' => ['rgb' => $textColor]],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                        ]
                    ]);
                }

                // Number Formatting with Comma Separators
                $sheet->getStyle("I4:J{$rowIndex}")->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // Table Styling
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
                $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ],
                    'alignment' => ['wrapText' => true]
                ]);

                // Footer with Timestamp
                $sheet->setCellValue("A{$rowIndex}", "Generated on: " . date('Y-m-d H:i:s'));
                $sheet->mergeCells("A{$rowIndex}:K{$rowIndex}");
                $sheet->getStyle("A{$rowIndex}")->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 9,
                        'color' => ['rgb' => $textColor]
                    ],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]
                ]);

                // Output File
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

                // Set headers
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . rawurlencode('Status_Report_' . date('Y-m-d_His') . '.xlsx') . '"');
                header('Cache-Control: max-age=0');

                // Disable any buffering to prevent memory issues
                if (ob_get_length()) ob_end_clean();

                // Save directly to output
                $writer->save('php://output');
                exit;
            } else {
                // Query remains the same as your original code
                $query = "SELECT transaction.*, 
                            hauler.hauler_name,
                            vehicle.plate_number,
                            vehicle.truck_type,
                            driver.driver_fname,
                            driver.driver_lname,
                            helper.helper_fname,
                            helper.helper_lname,
                            project.project_name,
                            origin.origin_name
                            FROM transaction
                            INNER JOIN hauler ON transaction.hauler_id = hauler.hauler_id
                            INNER JOIN vehicle ON transaction.vehicle_id = vehicle.vehicle_id
                            INNER JOIN driver ON transaction.driver_id = driver.driver_id
                            INNER JOIN helper ON transaction.helper_id = helper.helper_id
                            INNER JOIN project ON transaction.project_id = project.project_id
                            INNER JOIN origin ON transaction.origin_id = origin.origin_id
                            LEFT JOIN queue ON transaction.transaction_id = queue.transaction_id
                            LEFT JOIN arrival ON transaction.transaction_id = arrival.transaction_id
                            LEFT JOIN unloading ON transaction.transaction_id = unloading.transaction_id
                            WHERE (transaction.status = :status OR :status IS NULL)
                            AND (origin.origin_id = :branch OR :branch IS NULL)
                            AND (transaction.created_at BETWEEN :dateFrom AND :dateTo)
                            ORDER BY transaction.transaction_id DESC";

                $stmt = $this->conn->prepare($query);
                $stmt->execute(['status' => $status, 'branch' => $branch, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Color Scheme
                $primaryColor = [44, 62, 80];     // Dark Blue-Gray
                $headerColor = [236, 240, 241];   // Light Gray
                $textColor = [52, 73, 94];        // Muted Dark Blue

                // Create PDF
                $pdf = new TCPDF('L', 'mm', [310, 210], true, 'UTF-8', false);
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor($signature);
                $pdf->SetTitle('STATUS Report');

                // Set up page
                $pdf->SetAutoPageBreak(TRUE, 15);
                $pdf->SetPrintHeader(false);
                $pdf->SetPrintFooter(false);
                $pdf->SetMargins(10, 10, 10);
                $pdf->AddPage();

                // Logo
                $imagePath = '../assets/img/ulpi agoo.png';
                if (file_exists($imagePath)) {
                    $pdf->Image($imagePath, ($pdf->getPageWidth() - 100) / 2, 10, 100);
                    $pdf->Ln(40);
                }

                // Title
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetFont('helvetica', 'B', 16);
                $title = $status ? strtoupper($status) . ' REPORT' : 'ALL REPORT';
                $pdf->Cell(0, 15, $title, 0, 1, 'C');

                // As Of
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                $pdf->SetFont('helvetica', 'I', 10);
                $pdf->Cell(0, 10, 'As Of: ' . date('F d, Y', strtotime($dateFrom)) . ' - ' . date('F d, Y', strtotime($dateTo)), 0, 1, 'C');
                $pdf->Ln(5);

                // Headers
                $headers = [
                    'TO Reference',
                    'GUIA',
                    'Hauler',
                    'Vehicle',
                    'Driver Name',
                    'Helper Name',
                    'Project',
                    'Origin',
                    'No of Bales',
                    'Kilos',
                    'Demurrage'
                ];

                // Calculate column widths (total width = 277mm for A4 Landscape)
                $colWidths = [
                    25,  // TO Reference
                    25,  // GUIA
                    35,  // Hauler
                    30,  // Vehicle
                    35,  // Driver Name
                    30,  // Helper Name
                    25,  // Project
                    25,  // Origin
                    20,  // No of Bales
                    17,  // Kilos
                    20   // Demurrage
                ];

                // Table header styling
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetFillColor($headerColor[0], $headerColor[1], $headerColor[2]);
                $pdf->SetLineStyle(['width' => 0.5, 'color' => $primaryColor]);

                // Print headers
                foreach ($headers as $index => $header) {
                    $pdf->Cell($colWidths[$index], 7, $header, 1, 0, 'C', true);
                }
                $pdf->Ln();

                // Data rows
                $pdf->SetFont('helvetica', '', 7);
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

                foreach ($transactions as $row) {
                    $fill = ($pdf->GetY() % 2 == 0);

                    // Format data
                    $rowData = [
                        $row['to_reference'],
                        $row['guia'],
                        $row['hauler_name'],
                        $row['plate_number'] . ' (' . $row['truck_type'] . ')',
                        $row['driver_lname'] . ', ' . $row['driver_fname'],
                        $row['helper_lname'] . ', ' . $row['helper_fname'],
                        $row['project_name'],
                        $row['origin_name'],
                        number_format($row['no_of_bales']),
                        number_format($row['kilos'], 2),
                        $row['demurrage']
                    ];

                    // Check if row will exceed page height
                    if ($pdf->GetY() + 6 > $pdf->getPageHeight() - 20) {
                        $pdf->AddPage();

                        // Reprint headers on new page
                        $pdf->SetFont('helvetica', 'B', 8);
                        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                        foreach ($headers as $index => $header) {
                            $pdf->Cell($colWidths[$index], 7, $header, 1, 0, 'C', true);
                        }
                        $pdf->Ln();
                        $pdf->SetFont('helvetica', '', 7);
                        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                    }

                    // Print row data
                    foreach ($rowData as $index => $field) {
                        $pdf->Cell($colWidths[$index], 6, $field, 1, 0, 'C', $fill);
                    }
                    $pdf->Ln();
                }

                // Footer
                $pdf->Ln(10);
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Signed By: ' . $signature . ' | Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages(), 0, 0, 'R');

                // Output PDF
                $fileName = 'Tally_Report_' . date('Y-m-d_His') . '.pdf';
                $pdf->Output($fileName, 'I');
            }

            $this->sendResponse(true, 'All reports generated successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error', $e->getMessage());
        }
    }

    public function demurrage($data)
    {
        try {
            $dateFrom = $data['dateFrom'];
            $dateTo = $data['dateTo'];
            $branch = $data['branch'] === 'all' ? null : $data['branch'];
            $signature = $data['signature'];
            $reportFormat = $data['reportFormat'];

            if ($reportFormat === 'excel') {
                // Updated query with headers and processing
                $query = "SELECT t.to_reference, 
                            t.time_spent_waiting_area, 
                            v.plate_number, 
                            v.truck_type, 
                            d.*, 
                            h.*, 
                            t.status, 
                            u.time_of_entry, 
                            t.demurrage
                        FROM transaction t
                        INNER JOIN hauler ha ON t.hauler_id = ha.hauler_id
                        INNER JOIN vehicle v ON t.vehicle_id = v.vehicle_id
                        INNER JOIN driver d ON t.driver_id = d.driver_id
                        INNER JOIN helper h ON t.helper_id = h.helper_id
                        INNER JOIN project p ON t.project_id = p.project_id
                        INNER JOIN origin o ON t.origin_id = o.origin_id
                        LEFT JOIN queue q ON t.transaction_id = q.transaction_id
                        LEFT JOIN arrival a ON t.transaction_id = a.transaction_id
                        LEFT JOIN unloading u ON t.transaction_id = u.transaction_id
                        WHERE (o.origin_id = :branch OR :branch IS NULL)
                        AND (t.created_at BETWEEN :dateFrom AND :dateTo)
                        AND t.demurrage <> 0
                        ORDER BY t.transaction_id DESC;
                        ";

                $stmt = $this->conn->prepare($query);
                $stmt->execute(['branch' => $branch, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Clear any previous output or errors
                ob_clean();

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Professional Color Scheme
                $primaryColor = '2C3E50';
                $headerColor = 'ECF0F1';
                $textColor = '34495E';

                // Title Styling
                $sheet->setCellValue('A1', 'DEMURRAGE REPORT');
                $sheet->mergeCells('A1:I1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ]
                ]);

                // Add Subtitle with As Of
                $sheet->setCellValue('A2', "As Of: {$dateFrom} to {$dateTo}");
                $sheet->mergeCells('A2:I2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10,
                        'color' => ['rgb' => $textColor]
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Column Headers
                $headers = [
                    'TO Reference',
                    'Plate Number',
                    'Truck Type',
                    'Driver Name',
                    'Helper Name',
                    'Time',
                    'Status',
                    'Time of Entry',
                    'Demurrage'
                ];

                $sheet->fromArray($headers, NULL, 'A3');
                $sheet->getStyle('A3:I3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ]
                ]);

                // Populate Data Rows
                $rowIndex = 4;
                foreach ($transactions as $row) {
                    $sheet->fromArray([
                        $row['to_reference'],
                        $row['plate_number'],
                        $row['truck_type'],
                        $row['driver_lname'] . ', ' . $row['driver_fname'],
                        $row['helper_lname']  . ', ' . $row['helper_fname'],
                        $row['time_spent_waiting_area'],
                        $row['status'],
                        $row['time_of_entry'],
                        $row['demurrage']
                    ], NULL, 'A' . $rowIndex++);
                }

                // Advanced Column Formatting
                foreach (range('A', 'I') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $sheet->getStyle($col . '3:' . $col . $rowIndex)->applyFromArray([
                        'font' => ['color' => ['rgb' => $textColor]],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                        ]
                    ]);
                }

                // Number Formatting with Comma Separators
                $sheet->getStyle("I4:I{$rowIndex}")->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // Table Styling
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
                $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ],
                    'alignment' => ['wrapText' => true]
                ]);

                // Footer with Timestamp
                $sheet->setCellValue("A{$rowIndex}", "Generated on: " . date('Y-m-d H:i:s'));
                $sheet->mergeCells("A{$rowIndex}:I{$rowIndex}");
                $sheet->getStyle("A{$rowIndex}")->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 9,
                        'color' => ['rgb' => $textColor]
                    ],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]
                ]);

                // Output File
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

                // Set headers
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . rawurlencode('Demurrage_Report_' . date('Y-m-d_His') . '.xlsx') . '"');
                header('Cache-Control: max-age=0');

                // Disable any buffering to prevent memory issues
                if (ob_get_length()) ob_end_clean();

                // Save directly to output
                $writer->save('php://output');
                exit;
            } else {
                // Query remains the same as your original code
                // Updated query with headers and processing
                $query = "SELECT t.to_reference, 
               t.time_spent_waiting_area, 
               v.plate_number, 
               v.truck_type, 
               d.*, 
               h.*, 
               t.status, 
               u.time_of_entry, 
               t.demurrage
           FROM transaction t
           INNER JOIN hauler ha ON t.hauler_id = ha.hauler_id
           INNER JOIN vehicle v ON t.vehicle_id = v.vehicle_id
           INNER JOIN driver d ON t.driver_id = d.driver_id
           INNER JOIN helper h ON t.helper_id = h.helper_id
           INNER JOIN project p ON t.project_id = p.project_id
           INNER JOIN origin o ON t.origin_id = o.origin_id
           LEFT JOIN queue q ON t.transaction_id = q.transaction_id
           LEFT JOIN arrival a ON t.transaction_id = a.transaction_id
           LEFT JOIN unloading u ON t.transaction_id = u.transaction_id
           WHERE (o.origin_id = :branch OR :branch IS NULL)
           AND (t.created_at BETWEEN :dateFrom AND :dateTo)
           AND t.demurrage <> 0
           ORDER BY t.transaction_id DESC;
           ";

                $stmt = $this->conn->prepare($query);
                $stmt->execute(['branch' => $branch, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $stmt = $this->conn->prepare($query);
                $stmt->execute(['branch' => $branch, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Color Scheme
                $primaryColor = [44, 62, 80];     // Dark Blue-Gray
                $headerColor = [236, 240, 241];   // Light Gray
                $textColor = [52, 73, 94];        // Muted Dark Blue

                // Create PDF
                $pdf = new TCPDF('L', 'mm', [280, 210], true, 'UTF-8', false);
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor($signature);
                $pdf->SetTitle('Demurrage Report');

                // Set up page
                $pdf->SetAutoPageBreak(TRUE, 15);
                $pdf->SetPrintHeader(false);
                $pdf->SetPrintFooter(false);
                $pdf->SetMargins(10, 10, 10);
                $pdf->AddPage();

                // Logo
                $imagePath = '../assets/img/ulpi agoo.png';
                if (file_exists($imagePath)) {
                    $pdf->Image($imagePath, ($pdf->getPageWidth() - 100) / 2, 10, 100);
                    $pdf->Ln(40);
                }

                // Title
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetFont('helvetica', 'B', 16);
                $pdf->Cell(0, 15, 'DEMURRAGE REPORT', 0, 1, 'C');

                // As Of
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                $pdf->SetFont('helvetica', 'I', 10);
                $pdf->Cell(0, 10, 'As Of: ' . date('F d, Y', strtotime($dateFrom)) . ' - ' . date('F d, Y', strtotime($dateTo)), 0, 1, 'C');
                $pdf->Ln(5);

                // Headers
                $headers = [
                    'TO Reference',
                    'Plate Number',
                    'Truck Type',
                    'Driver Name',
                    'Helper Name',
                    'Time',
                    'Status',
                    'Time of Entry',
                    'Demurrage'
                ];

                // Calculate column widths (total width = 277mm for A4 Landscape)
                $colWidths = [
                    25,  // TO Reference
                    25,  // Plate Number
                    35,  // Truck Type
                    30,  // Driver Name
                    35,  // Helper Name
                    25,  // Time
                    20,  // Status
                    30,  // Time of Entry
                    30   // Demurrage
                ];

                // Table header styling
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetFillColor($headerColor[0], $headerColor[1], $headerColor[2]);
                $pdf->SetLineStyle(['width' => 0.5, 'color' => $primaryColor]);

                // Print headers
                foreach ($headers as $index => $header) {
                    $pdf->Cell($colWidths[$index], 7, $header, 1, 0, 'C', true);
                }
                $pdf->Ln();

                // Data rows
                $pdf->SetFont('helvetica', '', 7);
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

                foreach ($transactions as $row) {
                    $fill = ($pdf->GetY() % 2 == 0);

                    // Format data
                    $rowData = [
                        $row['to_reference'],
                        $row['plate_number'] . ' (' . $row['truck_type'] . ')',
                        $row['truck_type'],
                        $row['driver_lname'] . ', ' . $row['driver_fname'],
                        $row['helper_lname'] . ', ' . $row['helper_fname'],
                        $row['time_spent_waiting_area'],
                        $row['status'],
                        $row['time_of_entry'],
                        $row['demurrage']
                    ];

                    // Check if row will exceed page height
                    if ($pdf->GetY() + 6 > $pdf->getPageHeight() - 20) {
                        $pdf->AddPage();

                        // Reprint headers on new page
                        $pdf->SetFont('helvetica', 'B', 8);
                        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                        foreach ($headers as $index => $header) {
                            $pdf->Cell($colWidths[$index], 7, $header, 1, 0, 'C', true);
                        }
                        $pdf->Ln();
                        $pdf->SetFont('helvetica', '', 7);
                        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                    }

                    // Print row data
                    foreach ($rowData as $index => $field) {
                        $pdf->Cell($colWidths[$index], 6, $field, 1, 0, 'C', $fill);
                    }
                    $pdf->Ln();
                }

                // Footer
                $pdf->Ln(10);
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Signed By: ' . $signature . ' | Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages(), 0, 0, 'R');

                // Output PDF
                $fileName = 'Tally_Report_' . date('Y-m-d_His') . '.pdf';
                $pdf->Output($fileName, 'I');
            }

            $this->sendResponse(true, 'All reports generated successfully');
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error', $e->getMessage());
        }
    }

    public function diverted($data)
    {
        try {
            $branch = $data['branch'] === 'all' ? null : $data['branch'];
            $dateFrom = $data['dateFrom'];
            $dateTo = $data['dateTo'];
            $reportFormat = $data['reportFormat'];
            $signature = $data['signature'];

            if ($reportFormat === 'excel') {
                // Updated query with headers and processing
                $query = "SELECT transaction.to_reference, origin.origin_name, diverted.remarks FROM diverted INNER JOIN transaction ON diverted.transaction_id = transaction.transaction_id INNER JOIN origin ON diverted.new_destination = origin.origin_id WHERE diverted.new_destination = :branch OR :branch IS NULL AND transaction.created_at BETWEEN :dateFrom AND :dateTo ORDER BY transaction.transaction_id DESC";

                $stmt = $this->conn->prepare($query);
                $stmt->execute(['branch' => $branch, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Clear any previous output or errors
                ob_clean();

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Professional Color Scheme
                $primaryColor = '2C3E50';
                $headerColor = 'ECF0F1';
                $textColor = '34495E';

                // Title Styling
                $sheet->setCellValue('A1', 'DIVERTED VEHICLES REPORT');
                $sheet->mergeCells('A1:C1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ]
                ]);

                // Add Subtitle with As Of
                $sheet->setCellValue('A2', "As Of: {$dateFrom} to {$dateTo}");
                $sheet->mergeCells('A2:C2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10,
                        'color' => ['rgb' => $textColor]
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Column Headers
                $headers = [
                    'TO Reference',
                    'New Destination',
                    'Remarks',
                ];

                $sheet->fromArray($headers, NULL, 'A3');
                $sheet->getStyle('A3:C3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ]
                ]);

                // Populate Data Rows
                $rowIndex = 4;
                foreach ($transactions as $row) {
                    $sheet->fromArray([
                        $row['to_reference'],
                        $row['origin_name'],
                        $row['remarks'],
                    ], NULL, 'A' . $rowIndex++);
                }

                // Advanced Column Formatting
                foreach (range('A', 'C') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $sheet->getStyle($col . '3:' . $col . $rowIndex)->applyFromArray([
                        'font' => ['color' => ['rgb' => $textColor]],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                        ]
                    ]);
                }

                // Number Formatting with Comma Separators
                $sheet->getStyle("C4:C{$rowIndex}")->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // Table Styling
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
                $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ],
                    'alignment' => ['wrapText' => true]
                ]);

                // Footer with Timestamp
                $sheet->setCellValue("A{$rowIndex}", "Generated on: " . date('Y-m-d H:i:s'));
                $sheet->mergeCells("A{$rowIndex}:C{$rowIndex}");
                $sheet->getStyle("A{$rowIndex}")->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 9,
                        'color' => ['rgb' => $textColor]
                    ],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]
                ]);

                // Output File
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

                // Set headers
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . rawurlencode('Diverted_Report_' . date('Y-m-d_His') . '.xlsx') . '"');
                header('Cache-Control: max-age=0');

                // Disable any buffering to prevent memory issues
                if (ob_get_length()) ob_end_clean();

                // Save directly to output
                $writer->save('php://output');
                exit;
            } else {
                // Query remains the same as your original code
                // Updated query with headers and processing
                $query = "SELECT transaction.to_reference, origin.origin_name, diverted.remarks FROM diverted INNER JOIN transaction ON diverted.transaction_id = transaction.transaction_id INNER JOIN origin ON diverted.new_destination = origin.origin_id WHERE diverted.new_destination = :branch OR :branch IS NULL AND transaction.created_at BETWEEN :dateFrom AND :dateTo ORDER BY transaction.transaction_id DESC";

                $stmt = $this->conn->prepare($query);
                $stmt->execute(['branch' => $branch, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]);
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Color Scheme
                $primaryColor = [44, 62, 80];     // Dark Blue-Gray
                $headerColor = [236, 240, 241];   // Light Gray
                $textColor = [52, 73, 94];        // Muted Dark Blue

                // Create PDF
                $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor($signature);
                $pdf->SetTitle('Diverted Vehicles Report');

                // Set up page
                $pdf->SetAutoPageBreak(TRUE, 15);
                $pdf->SetPrintHeader(false);
                $pdf->SetPrintFooter(false);
                $pdf->SetMargins(10, 10, 10);
                $pdf->AddPage();

                // Logo
                $imagePath = '../assets/img/ulpi agoo.png';
                if (file_exists($imagePath)) {
                    $pdf->Image($imagePath, ($pdf->getPageWidth() - 100) / 2, 10, 100);
                    $pdf->Ln(40);
                }

                // Title
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetFont('helvetica', 'B', 16);
                $pdf->Cell(0, 15, 'Diverted Vehicles Report', 0, 1, 'C');

                // As Of
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                $pdf->SetFont('helvetica', 'I', 10);
                $pdf->Cell(0, 10, 'As Of: ' . date('F d, Y', strtotime($dateFrom)) . ' - ' . date('F d, Y', strtotime($dateTo)), 0, 1, 'C');
                $pdf->Ln(5);

                // Calculate column widths (total width = 277mm for A4 Landscape)
                $colWidths = [
                    25,  // TO Reference
                    30,  // Plate Number
                    35,  // Truck Type
                ];

                // Center table horizontally
                $tableWidth = array_sum($colWidths);
                $pdf->SetX(($pdf->getPageWidth() - $tableWidth) / 2);

                // Headers
                $headers = [
                    'TO Reference',
                    'New Destination',
                    'Remarks',
                ];

                // Table header styling
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetFillColor($headerColor[0], $headerColor[1], $headerColor[2]);
                $pdf->SetLineStyle(['width' => 0.5, 'color' => $primaryColor]);

                // Print headers
                foreach ($headers as $index => $header) {
                    $pdf->Cell($colWidths[$index], 7, $header, 1, 0, 'C', true);
                }
                $pdf->Ln();

                // Center table horizontally
                $tableWidth = array_sum($colWidths);
                $pdf->SetX(($pdf->getPageWidth() - $tableWidth) / 2);

                // Data rows
                $pdf->SetFont('helvetica', '', 7);
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

                foreach ($transactions as $row) {
                    // Center table horizontally for each row
                    $pdf->SetX(($pdf->getPageWidth() - $tableWidth) / 2);

                    $fill = ($pdf->GetY() % 2 == 0);

                    // Format data
                    $rowData = [
                        $row['to_reference'],
                        $row['origin_name'],
                        $row['remarks'],
                    ];

                    // Check if row will exceed page height
                    if ($pdf->GetY() + 6 > $pdf->getPageHeight() - 20) {
                        $pdf->AddPage();

                        // Reprint headers on new page
                        $pdf->SetFont('helvetica', 'B', 8);
                        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                        $pdf->SetX(($pdf->getPageWidth() - $tableWidth) / 2); // Center alignment for header on new page
                        foreach ($headers as $index => $header) {
                            $pdf->Cell($colWidths[$index], 7, $header, 1, 0, 'C', true);
                        }
                        $pdf->Ln();
                        $pdf->SetFont('helvetica', '', 7);
                        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                    }

                    // Print row data with center alignment
                    foreach ($rowData as $index => $field) {
                        $pdf->Cell($colWidths[$index], 6, $field, 1, 0, 'C', $fill); // Changed alignment to 'C'
                    }
                    $pdf->Ln();
                }

                // Footer
                $pdf->Ln(10);
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Signed By: ' . $signature . ' | Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages(), 0, 0, 'R');

                // Output PDF
                $fileName = 'Diverted_Report_' . date('Y-m-d_His') . '.pdf';
                $pdf->Output($fileName, 'I');
            }
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error', $e->getMessage());
        }
    }


    public function settings($data)
    {
        try {
            $reportFormat = $data['reportFormat'];
            $signature = $data['signature'];
            $dateTo = $data['dateTo'];
            $dateFrom = $data['dateFrom'];

            if ($reportFormat === 'excel') {
                // Updated query with headers and processing
                $query = "SELECT * FROM settings_logs WHERE created_at BETWEEN '$dateFrom' AND '$dateTo' ORDER BY created_at DESC";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // Clear any previous output or errors
                ob_clean();

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Professional Color Scheme
                $primaryColor = '2C3E50';
                $headerColor = 'ECF0F1';
                $textColor = '34495E';

                // Title Styling
                $sheet->setCellValue('A1', 'SETTINGS LOGS');
                $sheet->mergeCells('A1:D1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ]
                ]);



                // Column Headers
                $headers = [
                    'Settings Name',
                    'Details',
                    'Created By',
                    'Created At',
                ];

                $sheet->fromArray($headers, NULL, 'A3');
                $sheet->getStyle('A3:D3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ]
                ]);

                // Populate Data Rows
                $rowIndex = 4;
                foreach ($settings as $row) {
                    $sheet->fromArray([
                        $row['settings_name'],
                        $row['details'],
                        $row['created_by'],
                        $row['created_at'],
                    ], NULL, 'A' . $rowIndex++);
                }

                // Advanced Column Formatting
                foreach (range('A', 'D') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $sheet->getStyle($col . '3:' . $col . $rowIndex)->applyFromArray([
                        'font' => ['color' => ['rgb' => $textColor]],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                        ]
                    ]);
                }

                // Number Formatting with Comma Separators
                $sheet->getStyle("C4:D{$rowIndex}")->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // Table Styling
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
                $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ],
                    'alignment' => ['wrapText' => true]
                ]);

                // Footer with Timestamp
                $sheet->setCellValue("A{$rowIndex}", "Generated on: " . date('Y-m-d H:i:s'));
                $sheet->mergeCells("A{$rowIndex}:D{$rowIndex}");
                $sheet->getStyle("A{$rowIndex}")->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 9,
                        'color' => ['rgb' => $textColor]
                    ],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]
                ]);

                // Output File
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

                // Set headers
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . rawurlencode('Settings_Report_' . date('Y-m-d_His') . '.xlsx') . '"');
                header('Cache-Control: max-age=0');

                // Disable any buffering to prevent memory issues
                if (ob_get_length()) ob_end_clean();

                // Save directly to output
                $writer->save('php://output');
                exit;
            } else {
                // Updated query with headers and processing
                $query = "SELECT * FROM settings_logs WHERE created_at BETWEEN '$dateFrom' AND '$dateTo' ORDER BY created_at DESC";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Color Scheme
                $primaryColor = [44, 62, 80];     // Dark Blue-Gray
                $headerColor = [236, 240, 241];   // Light Gray
                $textColor = [52, 73, 94];        // Muted Dark Blue

                // Make sure no output has been sent before this point
                ob_clean();

                // Create PDF
                $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor($signature);
                $pdf->SetTitle('Settings Logs');

                // Set up page
                $pdf->SetAutoPageBreak(TRUE, 15);
                $pdf->SetPrintHeader(false);
                $pdf->SetPrintFooter(false);
                $pdf->SetMargins(10, 10, 10);
                $pdf->AddPage();

                // Logo
                $imagePath = '../assets/img/ulpi agoo.png';
                if (file_exists($imagePath)) {
                    $pdf->Image($imagePath, ($pdf->getPageWidth() - 100) / 2, 10, 100);
                    $pdf->Ln(40);
                }

                // Title
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetFont('helvetica', 'B', 16);
                $pdf->Cell(0, 15, 'Settings Logs Report as of ' . date('F j, Y', strtotime($dateTo)), 0, 1, 'C');

                // Calculate column widths (matched with headers)
                $colWidths = [
                    40,  // Settings Name
                    100, // Details
                    40,  // Created By
                    40   // Created At
                ];

                // Center table horizontally
                $tableWidth = array_sum($colWidths);
                $pdf->SetX(($pdf->getPageWidth() - $tableWidth) / 2);

                // Headers
                $headers = [
                    'Settings Name',
                    'Details',
                    'Created By',
                    'Created At'
                ];

                // Table header styling
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetFillColor($headerColor[0], $headerColor[1], $headerColor[2]);
                $pdf->SetLineStyle(['width' => 0.5, 'color' => $primaryColor]);

                // Print headers (only on first page)
                foreach ($headers as $index => $header) {
                    $pdf->Cell($colWidths[$index], 7, $header, 1, 0, 'C', true);
                }
                $pdf->Ln();

                // Data rows
                $pdf->SetFont('helvetica', '', 7);
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

                foreach ($settings as $row) {
                    // Check if row will exceed page height
                    if ($pdf->GetY() + 6 > $pdf->getPageHeight() - 20) {
                        $pdf->AddPage();

                        // Center table horizontally on new page
                        $pdf->SetX(($pdf->getPageWidth() - $tableWidth) / 2);
                    }

                    // Center table horizontally on each row
                    $pdf->SetX(($pdf->getPageWidth() - $tableWidth) / 2);

                    $fill = ($pdf->GetY() % 2 == 0);

                    // Print row data
                    $pdf->Cell($colWidths[0], 6, $row['settings_name'], 1, 0, 'C', $fill);
                    $pdf->Cell($colWidths[1], 6, $row['details'], 1, 0, 'C', $fill);
                    $pdf->Cell($colWidths[2], 6, $row['created_by'], 1, 0, 'C', $fill);
                    $pdf->Cell($colWidths[3], 6, $row['created_at'], 1, 0, 'C', $fill);
                    $pdf->Ln();
                }

                // Footer
                $pdf->Ln(10);
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Signed By: ' . $signature . ' | Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages(), 0, 0, 'R');

                // Output PDF
                $fileName = 'Settings_Report_' . date('Y-m-d_His') . '.pdf';
                $pdf->Output($fileName, 'I');
                exit;
            }
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error', $e->getMessage());
        }
    }
    public function sms($data)
    {
        try {
            $reportFormat = $data['reportFormat'];
            $signature = $data['signature'];
            $dateFrom = $data['dateFrom'];
            $dateTo = $data['dateTo'];

            if ($reportFormat === 'excel') {
                // Updated query with headers and processing
                $query = "SELECT * FROM sms_log WHERE created_at BETWEEN '$dateFrom' AND '$dateTo' ORDER BY created_at DESC";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // Clear any previous output or errors
                ob_clean();

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Professional Color Scheme
                $primaryColor = '2C3E50';
                $headerColor = 'ECF0F1';
                $textColor = '34495E';

                // Title Styling
                $sheet->setCellValue('A1', 'SMS LOGS Report as of ' . date('F j, Y', strtotime($dateTo)));
                $sheet->mergeCells('A1:D1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ]
                ]);



                // Column Headers
                $headers = [
                    'Recipient Number',
                    'Details',
                    'Send Attempt',
                    'Created At',
                ];

                $sheet->fromArray($headers, NULL, 'A3');
                $sheet->getStyle('A3:D3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ]
                ]);

                // Populate Data Rows
                $rowIndex = 4;
                foreach ($settings as $row) {
                    $sheet->fromArray([
                        $row['recipient_number'],
                        $row['message_content'],
                        $row['send_attempt'],
                        $row['created_at'],
                    ], NULL, 'A' . $rowIndex++);
                }

                // Advanced Column Formatting
                foreach (range('A', 'D') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $sheet->getStyle($col . '3:' . $col . $rowIndex)->applyFromArray([
                        'font' => ['color' => ['rgb' => $textColor]],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                        ]
                    ]);
                }

                // Number Formatting with Comma Separators
                $sheet->getStyle("C4:D{$rowIndex}")->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // Table Styling
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
                $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ],
                    'alignment' => ['wrapText' => true]
                ]);

                // Footer with Timestamp
                $sheet->setCellValue("A{$rowIndex}", "Generated on: " . date('Y-m-d H:i:s'));
                $sheet->mergeCells("A{$rowIndex}:D{$rowIndex}");
                $sheet->getStyle("A{$rowIndex}")->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 9,
                        'color' => ['rgb' => $textColor]
                    ],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]
                ]);

                // Output File
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

                // Set headers
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . rawurlencode('SMS_Report_' . date('Y-m-d_His') . '.xlsx') . '"');
                header('Cache-Control: max-age=0');

                // Disable any buffering to prevent memory issues
                if (ob_get_length()) ob_end_clean();

                // Save directly to output
                $writer->save('php://output');
                exit;
            } else {
                // Updated query with headers and processing
                $query = "SELECT * FROM sms_log WHERE created_at BETWEEN '$dateFrom' AND '$dateTo' ORDER BY created_at DESC";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Color Scheme
                $primaryColor = [44, 62, 80];     // Dark Blue-Gray
                $headerColor = [236, 240, 241];   // Light Gray
                $textColor = [52, 73, 94];        // Muted Dark Blue

                // Make sure no output has been sent before this point
                ob_clean();

                // Create PDF
                $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor($signature);
                $pdf->SetTitle('SMS Logs');

                // Set up page
                $pdf->SetAutoPageBreak(TRUE, 15);
                $pdf->SetPrintHeader(false);
                $pdf->SetPrintFooter(false);
                $pdf->SetMargins(10, 10, 10);
                $pdf->AddPage();

                // Logo
                $imagePath = '../assets/img/ulpi agoo.png';
                if (file_exists($imagePath)) {
                    $pdf->Image($imagePath, ($pdf->getPageWidth() - 100) / 2, 10, 100);
                    $pdf->Ln(40);
                }

                // Title
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetFont('helvetica', 'B', 16);
                $pdf->Cell(0, 15, 'SMS Logs Report as of ' . date('F j, Y', strtotime($dateTo)), 0, 1, 'C');

                // Calculate column widths (matched with headers)
                $colWidths = [
                    40,  // Settings Name
                    100, // Details
                    40,  // Created By
                    40   // Created At
                ];

                // Center table horizontally
                $tableWidth = array_sum($colWidths);
                $pdf->SetX(($pdf->getPageWidth() - $tableWidth) / 2);

                // Headers
                $headers = [
                    'Settings Name',
                    'Details',
                    'Attempts',
                    'Created At'
                ];

                // Table header styling
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetFillColor($headerColor[0], $headerColor[1], $headerColor[2]);
                $pdf->SetLineStyle(['width' => 0.5, 'color' => $primaryColor]);

                // Print headers (only on first page)
                foreach ($headers as $index => $header) {
                    $pdf->Cell($colWidths[$index], 7, $header, 1, 0, 'C', true);
                }
                $pdf->Ln();

                // Data rows
                $pdf->SetFont('helvetica', '', 7);
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

                foreach ($settings as $row) {
                    // Check if row will exceed page height
                    if ($pdf->GetY() + 6 > $pdf->getPageHeight() - 20) {
                        $pdf->AddPage();

                        // Center table horizontally on new page
                        $pdf->SetX(($pdf->getPageWidth() - $tableWidth) / 2);
                    }

                    // Center table horizontally on each row
                    $pdf->SetX(($pdf->getPageWidth() - $tableWidth) / 2);

                    $fill = ($pdf->GetY() % 2 == 0);

                    // Print row data
                    $pdf->Cell($colWidths[0], 6, $row['recipient_number'], 1, 0, 'C', $fill);
                    $pdf->Cell($colWidths[1], 6, $row['message_content'], 1, 0, 'C', $fill);
                    $pdf->Cell($colWidths[2], 6, $row['send_attempt'], 1, 0, 'C', $fill);
                    $pdf->Cell($colWidths[3], 6, $row['created_at'], 1, 0, 'C', $fill);
                    $pdf->Ln();
                }

                // Footer
                $pdf->Ln(10);
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Signed By: ' . $signature . ' | Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages(), 0, 0, 'R');

                // Output PDF
                $fileName = 'SMS_Report_' . date('Y-m-d_His') . '.pdf';
                $pdf->Output($fileName, 'I');
                exit;
            }
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error', $e->getMessage());
        }
    }
    public function event($data)
    {
        try {
            $reportFormat = $data['reportFormat'];
            $signature = $data['signature'];
            $dateFrom = $data['dateFrom'];
            $dateTo = $data['dateTo'];

            if ($reportFormat === 'excel') {
                // Updated query with headers and processing
                $query = "SELECT * FROM transaction_log INNER JOIN transaction on transaction_log.transaction_id = transaction.transaction_id WHERE transaction_log.created_at BETWEEN '$dateFrom' AND '$dateTo' ORDER BY transaction_log.created_at DESC";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // Clear any previous output or errors
                ob_clean();

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Professional Color Scheme
                $primaryColor = '2C3E50';
                $headerColor = 'ECF0F1';
                $textColor = '34495E';

                // Title Styling
                $sheet->setCellValue('A1', 'EVENT LOGS - As Of: ' . date('Y-m-d'));
                $sheet->mergeCells('A1:D1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ]
                ]);



                // Column Headers
                $headers = [
                    'TO Reference',
                    'Details',
                    'Created By',
                    'Created At',
                ];

                $sheet->fromArray($headers, NULL, 'A3');
                $sheet->getStyle('A3:D3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ]
                ]);

                // Populate Data Rows
                $rowIndex = 4;
                foreach ($settings as $row) {
                    $sheet->fromArray([
                        $row['to_reference'],
                        $row['details'],
                        $row['created_by'],
                        $row['created_at'],
                    ], NULL, 'A' . $rowIndex++);
                }

                // Advanced Column Formatting
                foreach (range('A', 'D') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $sheet->getStyle($col . '3:' . $col . $rowIndex)->applyFromArray([
                        'font' => ['color' => ['rgb' => $textColor]],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                        ]
                    ]);
                }

                // Number Formatting with Comma Separators
                $sheet->getStyle("C4:D{$rowIndex}")->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // Table Styling
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
                $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ],
                    'alignment' => ['wrapText' => true]
                ]);

                // Footer with Timestamp
                $sheet->setCellValue("A{$rowIndex}", "Generated on: " . date('Y-m-d H:i:s'));
                $sheet->mergeCells("A{$rowIndex}:D{$rowIndex}");
                $sheet->getStyle("A{$rowIndex}")->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 9,
                        'color' => ['rgb' => $textColor]
                    ],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]
                ]);

                // Output File
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

                // Set headers
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . rawurlencode('Event_Report_' . date('Y-m-d_His') . '.xlsx') . '"');
                header('Cache-Control: max-age=0');

                // Disable any buffering to prevent memory issues
                if (ob_get_length()) ob_end_clean();

                // Save directly to output
                $writer->save('php://output');
                exit;
            } else {
                // Updated query with headers and processing
                $query = "SELECT * FROM transaction_log INNER JOIN transaction on transaction_log.transaction_id = transaction.transaction_id WHERE transaction_log.created_at BETWEEN '$dateFrom' AND '$dateTo' ORDER BY transaction_log.created_at DESC";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Color Scheme
                $primaryColor = [44, 62, 80];     // Dark Blue-Gray
                $headerColor = [236, 240, 241];   // Light Gray
                $textColor = [52, 73, 94];        // Muted Dark Blue

                // Make sure no output has been sent before this point
                ob_clean();

                // Create PDF
                $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor($signature);
                $pdf->SetTitle('Settings Logs');

                // Set up page
                $pdf->SetAutoPageBreak(TRUE, 15);
                $pdf->SetPrintHeader(false);
                $pdf->SetPrintFooter(false);
                $pdf->SetMargins(10, 10, 10);
                $pdf->AddPage();

                // Logo
                $imagePath = '../assets/img/ulpi agoo.png';
                if (file_exists($imagePath)) {
                    $pdf->Image($imagePath, ($pdf->getPageWidth() - 100) / 2, 10, 100);
                    $pdf->Ln(40);
                }

                // Title
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetFont('helvetica', 'B', 16);
                $pdf->Cell(0, 15, 'Settings Logs Report as of ' . date('F j, Y', strtotime($dateFrom)) . ' - ' . date('F j, Y', strtotime($dateTo)), 0, 1, 'C');

                // Calculate column widths (matched with headers)
                $colWidths = [
                    40,  // Settings Name
                    150, // Details
                    40,  // Created By
                    40   // Created At
                ];

                // Center table horizontally
                $tableWidth = array_sum($colWidths);
                $pdf->SetX(($pdf->getPageWidth() - $tableWidth) / 2);

                // Headers
                $headers = [
                    'TO Reference',
                    'Details',
                    'Created By',
                    'Created At'
                ];

                // Table header styling
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetFillColor($headerColor[0], $headerColor[1], $headerColor[2]);
                $pdf->SetLineStyle(['width' => 0.5, 'color' => $primaryColor]);

                // Print headers (only on first page)
                foreach ($headers as $index => $header) {
                    $pdf->Cell($colWidths[$index], 7, $header, 1, 0, 'C', true);
                }
                $pdf->Ln();

                // Data rows
                $pdf->SetFont('helvetica', '', 7);
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

                foreach ($settings as $row) {
                    // Check if row will exceed page height
                    if ($pdf->GetY() + 6 > $pdf->getPageHeight() - 20) {
                        $pdf->AddPage();

                        // Center table horizontally on new page
                        $pdf->SetX(($pdf->getPageWidth() - $tableWidth) / 2);
                    }

                    // Center table horizontally on each row
                    $pdf->SetX(($pdf->getPageWidth() - $tableWidth) / 2);

                    $fill = ($pdf->GetY() % 2 == 0);

                    // Print row data
                    $pdf->Cell($colWidths[0], 6, $row['to_reference'], 1, 0, 'C', $fill);
                    $pdf->Cell($colWidths[1], 6, $row['details'], 1, 0, 'C', $fill);
                    $pdf->Cell($colWidths[2], 6, $row['created_by'], 1, 0, 'C', $fill);
                    $pdf->Cell($colWidths[3], 6, $row['created_at'], 1, 0, 'C', $fill);
                    $pdf->Ln();
                }

                // Footer
                $pdf->Ln(10);
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Signed By: ' . $signature . ' | Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages(), 0, 0, 'R');

                // Output PDF
                $fileName = 'Event_Report_' . date('Y-m-d_His') . '.pdf';
                $pdf->Output($fileName, 'I');
                exit;
            }
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error', $e->getMessage());
        }
    }
    public function user($data)
    {
        try {
            $reportFormat = $data['reportFormat'];
            $signature = $data['signature'];
            $dateFrom = $data['dateFrom'];
            $dateTo = $data['dateTo'];
            $user = $data['user'] === 'all' ? null : $data['user'];

            if ($reportFormat === 'excel') {
                // Updated query with headers and processing
                // Determine if the user filter should be applied
                $user = $data['user'] === 'all' ? null : $data['user'];

                // Build the query dynamically based on the user filter
                $query = "SELECT * 
          FROM user_logs 
          WHERE 1=1"; // Start with a true condition for easier dynamic filtering

                if ($user !== null) {
                    $query .= " AND username = '$user'"; // Add username filter only if $user is not null
                }

                $query .= " AND timestamp BETWEEN '$dateFrom' AND '$dateTo' ORDER by timestamp DESC"; // Add date range filter
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // Clear any previous output or errors
                ob_clean();

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Professional Color Scheme
                $primaryColor = '2C3E50';
                $headerColor = 'ECF0F1';
                $textColor = '34495E';

                // Title Styling
                $sheet->setCellValue('A1', 'USER LOGS AS OF ' . date('F j, Y', strtotime($dateFrom)) . ' - ' . date('F j, Y', strtotime($dateTo)));
                $sheet->mergeCells('A1:D1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ]
                ]);



                // Column Headers
                $headers = [
                    'Username',
                    'Action',
                    'Created At',
                ];

                $sheet->fromArray($headers, NULL, 'A3');
                $sheet->getStyle('A3:C3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => $primaryColor]
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerColor]
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ]
                ]);

                // Populate Data Rows
                $rowIndex = 4;
                foreach ($settings as $row) {
                    $sheet->fromArray([
                        $row['username'],
                        $row['action'],
                        $row['timestamp'],
                    ], NULL, 'A' . $rowIndex++);
                }

                // Advanced Column Formatting
                foreach (range('A', 'C') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $sheet->getStyle($col . '3:' . $col . $rowIndex)->applyFromArray([
                        'font' => ['color' => ['rgb' => $textColor]],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                        ]
                    ]);
                }

                // Number Formatting with Comma Separators
                $sheet->getStyle("C4:C{$rowIndex}")->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // Table Styling
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
                $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => $primaryColor]
                        ]
                    ],
                    'alignment' => ['wrapText' => true]
                ]);

                // Footer with Timestamp
                $sheet->setCellValue("A{$rowIndex}", "Generated on: " . date('Y-m-d H:i:s'));
                $sheet->mergeCells("A{$rowIndex}:C{$rowIndex}");
                $sheet->getStyle("A{$rowIndex}")->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 9,
                        'color' => ['rgb' => $textColor]
                    ],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]
                ]);

                // Output File
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

                // Set headers
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . rawurlencode('User_Report_' . date('Y-m-d_His') . '.xlsx') . '"');
                header('Cache-Control: max-age=0');

                // Disable any buffering to prevent memory issues
                if (ob_get_length()) ob_end_clean();

                // Save directly to output
                $writer->save('php://output');
                exit;
            } else {
                // Updated query with headers and processing
                // Determine if the user filter should be applied
                $user = $data['user'] === 'all' ? null : $data['user'];

                // Build the query dynamically based on the user filter
                $query = "SELECT * 
          FROM user_logs 
          WHERE 1=1"; // Start with a true condition for easier dynamic filtering

                if ($user !== null) {
                    $query .= " AND username = '$user'"; // Add username filter only if $user is not null
                }

                $query .= " AND timestamp BETWEEN '$dateFrom' AND '$dateTo' ORDER BY timestamp DESC"; // Add date range filter
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Color Scheme
                $primaryColor = [44, 62, 80];     // Dark Blue-Gray
                $headerColor = [236, 240, 241];   // Light Gray
                $textColor = [52, 73, 94];        // Muted Dark Blue

                // Make sure no output has been sent before this point
                ob_clean();

                // Create PDF
                $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor($signature);
                $pdf->SetTitle('User Logs');

                // Set up page
                $pdf->SetAutoPageBreak(TRUE, 15);
                $pdf->SetPrintHeader(false);
                $pdf->SetPrintFooter(false);
                $pdf->SetMargins(10, 10, 10);
                $pdf->AddPage();

                // Logo
                $imagePath = '../assets/img/ulpi agoo.png';
                if (file_exists($imagePath)) {
                    $pdf->Image($imagePath, ($pdf->getPageWidth() - 100) / 2, 10, 100);
                    $pdf->Ln(40);
                }

                // Title
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetFont('helvetica', 'B', 16);
                $pdf->Cell(0, 15, 'User Logs Report as of ' . date('Y-m-d H:i:s'), 0, 1, 'C');

                // Calculate column widths (matched with headers)
                $colWidths = [
                    40,  // Username
                    80,  // Action (adjusted for longer text)
                    40   // Timestamp
                ];

                // Calculate total table width
                $tableWidth = array_sum($colWidths);

                // Center table horizontally
                $pdf->SetX(($pdf->getPageWidth() - $tableWidth) / 2);

                // Headers
                $headers = [
                    'Username',
                    'Action',
                    'Created At'
                ];

                // Table header styling
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetFillColor($headerColor[0], $headerColor[1], $headerColor[2]);
                $pdf->SetLineStyle(['width' => 0.5, 'color' => $primaryColor]);

                // Print headers (only on first page)
                foreach ($headers as $index => $header) {
                    $pdf->Cell($colWidths[$index], 7, $header, 1, 0, 'C', true);
                }
                $pdf->Ln();

                // Data rows
                $pdf->SetFont('helvetica', '', 7);
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

                foreach ($settings as $row) {
                    // Check if row will exceed page height
                    if ($pdf->GetY() + 6 > $pdf->getPageHeight() - 20) {
                        $pdf->AddPage();

                        // Center table horizontally on new page
                        $pdf->SetX(($pdf->getPageWidth() - $tableWidth) / 2);
                    }

                    // Center table horizontally on each row
                    $pdf->SetX(($pdf->getPageWidth() - $tableWidth) / 2);

                    $fill = ($pdf->GetY() % 2 == 0);

                    // Print row data
                    $pdf->Cell($colWidths[0], 6, $row['username'], 1, 0, 'C', $fill);
                    $pdf->Cell($colWidths[1], 6, $row['action'], 1, 0, 'C', $fill);
                    $pdf->Cell($colWidths[2], 6, $row['timestamp'], 1, 0, 'C', $fill);
                    $pdf->Ln();
                }

                // Footer
                $pdf->Ln(10);
                $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Cell(0, 10, 'Signed By: ' . $signature . ' | Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages(), 0, 0, 'R');

                // Output PDF
                $fileName = 'User_Report_' . date('Y-m-d_His') . '.pdf';
                $pdf->Output($fileName, 'I');
                exit;
            }
        } catch (Exception $e) {
            error_log('Unhandled error: ' . $e->getMessage());
            $this->sendResponse(false, 'Internal server error', $e->getMessage());
        }
    }
}


// Main API Handler
try {
    if (!isset($conn) || $conn === null) {
        throw new Exception('Database connection not established');
    }

    $reportManager = new reportManager($conn);
    $stmt = $conn->prepare('INSERT INTO user_logs (user_id, username, action) VALUES (:user_id, :username, :action)');
    $stmt->execute([
        'user_id' => $_SESSION['id'],
        'username' => $_SESSION['username'],
        'action' => 'Generated Report: ' . $_POST['action']
    ]);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        switch ($action) {
            case 'tally-in':
                $reportManager->tallyIn($_POST);
                break;
            case 'daily-unloading':
                $reportManager->dailyUnloading($_POST);
                break;
            case 'summary':
                $reportManager->summary($_POST);
                break;
            case 'all-reports':
                $reportManager->allReports($_POST);
                break;
            case 'demurrage':
                $reportManager->demurrage($_POST);
                break;
            case 'diverted':
                $reportManager->diverted($_POST);
                break;
            case 'settings':
                $reportManager->settings($_POST);
                break;
            case 'sms':
                $reportManager->sms($_POST);
                break;
            case 'event':
                $reportManager->event($_POST);
                break;
            case 'user':
                $reportManager->user($_POST);
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
