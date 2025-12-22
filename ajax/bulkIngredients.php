<?php
@session_start();

include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

require $_SERVER['DOCUMENT_ROOT'] . '/vendor1/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

trait validatesIntegers
{
    public static function validateInteger(string $value): bool
    {
        return ((int)$value && 1 == floatval($value) / intval($value));
    }
}

class Request
{
    protected array $data = [];

    protected array $fields = [
        'client_id',
        'document_type',
        'document_file',
        'spreadsheet_file',
        'producerName',
        'certificationBodyName',
        'expiryDate',
        'supplierName',
        'statementSupplierName',
    ];

    public function __construct()
    {
        foreach ($this->fields as $field) {
            $this->data[$field] = isset($_POST[$field]) ? trim($_POST[$field]) : '';
        }

        $this->data['document_file'] = !empty($this->data['document_file'])
            ? json_decode($this->data['document_file'], true)
            : [];

        $this->data['spreadsheet_file'] = !empty($this->data['spreadsheet_file'])
            ? json_decode($this->data['spreadsheet_file'], true)
            : [];
    }

    public function get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function getSpreadsheetFilePath(): ?string
    {
        if (empty($this->data['spreadsheet_file'])) {
            return null;
        }

        if (!isset($this->data['spreadsheet_file']['hostpath'])) {
            return null;
        }

        $path = realpath($this->data['spreadsheet_file']['hostpath']);

        if (!file_exists($path)) {
            throw new Exception('Spreadsheet file is not found.');
        }

        return $path;
    }

    public function getDocumentFileData(): ?array
    {
        if (empty($this->data['document_file']) || !is_array($this->data['document_file'])) {
            return null;
        }

        return $this->data['document_file'];
    }
}

class Response
{
    public static function success(array $data)
    {
        header('Content-type: application/json');
        echo json_encode(array_merge($data, ['status' => 'success']));
    }

    public static function error(string $message)
    {
        header('Content-type: application/json');
        echo json_encode(['status' => 'error', 'message' => $message]);
    }
}

class Reader
{
    public static function read(string $spreadsheetFile): array
    {
        $fileType = IOFactory::identify($spreadsheetFile);
        $reader = IOFactory::createReader($fileType);
        $spreadsheet = $reader->load($spreadsheetFile);
        $worksheet = $spreadsheet->getActiveSheet();

        $header = [];
        $data = [];

        foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            if ($rowIndex === 1) {
                foreach ($cellIterator as $cell) {
                    $header[] = $cell->getValue();
                }
            } else {
                $rowData = [];
                $cellIndex = 0;
                foreach ($cellIterator as $cell) {
                    $value = $cell->getValue();
                    if (!empty($value)) {
                        $rowData[$header[$cellIndex]] = $value;
                    }
                    $cellIndex++;
                }
                if (!empty($rowData)) {
                    $data[] = $rowData;
                }
            }
        }

        return $data;
    }
}

class RequestValidator
{
    use validatesIntegers;

    /**
     * @param Request $request
     *
     * @return void
     * @throws Exception
     */
    public static function validateRequest(Request $request): void
    {
        static::validateUploadedSpreadsheet($request);
        static::validateUploadedDocument($request);
        static::validateClientId($request);

        switch ($request->get('document_type')) {
            case 'certificate':
                static::validateCertificateFields($request);
                static::validateExpiryDate($request);
                break;
            case 'statement':
                static::validateStatementFields($request);
                break;
        }
    }

    public static function validateClientId(Request $request): void
    {
        if (
            empty($request->get('client_id'))
            || !static::validateInteger($request->get('client_id'))
        ) {
            throw new Exception('Invalid or missing client id');
        }
    }

    public static function validateUploadedSpreadsheet(Request $request): void
    {
        $fdata = $request->get('spreadsheet_file');

        if (empty($fdata) || !is_array($fdata) || !isset($fdata['hostpath'])) {
            throw new Exception('Spreadsheet file uploaded with errors');
        }
    }

    public static function validateUploadedDocument(Request $request): void
    {
        $ftype = $request->get('document_type');
        if ($ftype !== 'none'
            && (
                empty($request->getDocumentFileData())
                || !is_array($request->getDocumentFileData())
            )
        ) {
            throw new Exception('PDF document file uploaded with errors');
        }
    }

    public static function validateCertificateFields(Request $request): void
    {
        foreach (['producerName', 'certificationBodyName', 'expiryDate'] as $field) {
            if (!$request->get($field)) {
                throw new Exception($field . ' is required');
            }
        }
    }

    public static function validateStatementFields(Request $request): void
    {
        foreach (['statementSupplierName'] as $field) {
            if (!$request->get($field)) {
                throw new Exception($field . ' is required');
            }
        }
    }

    public static function validateExpiryDate(Request $request): void
    {
        $expiryDate = strtotime($request->get('expiryDate'));
        if (!$expiryDate) {
            throw new Exception('Expiry date is not a valid date');
        }
        if ($expiryDate <= strtotime('tomorrow')) {
            throw new Exception('Expiry date must be in the future');
        }
    }
}

class RowValidator
{
    use validatesIntegers;

    /**
     * @param array $row
     *
     * @return void
     * @throws Exception
     */
    public static function validateRow(array $row, $request): void
    {
        static::validateRowName($row);
        static::validateRowSource($row);
        static::validateRowPosition($row, $request);
        //static::validateRowCode($row);
    }

    public static function validateRowSource(array $row): void
    {
        $value = isset($row['Source'])
            ? trim($row['Source'])
            : '';

        if (empty($value)) {
            throw new Exception('Source is not specified');
        }

        if (!in_array(strtolower($value), ['animal', 'plant', 'synthetic', 'mineral', 'cleaning agents', 'packaging material', 'others'])) {
            throw new Exception('Unrecognized raw material source: ' . $value);
        }
    }

    public static function validateRowName(array $row): void
    {
        $value = isset($row['Name'])
            ? trim($row['Name'])
            : '';

        if (empty($value)) {
            throw new Exception('Name is not specified');
        }
    }

    public static function validateRowCode(array $row): void
    {
        $value = isset($row['Code'])
            ? trim($row['Code'])
            : '';

        if (empty($value)) {
            throw new Exception('Code is not specified');
        }
    }

    public static function validateRowPosition(array $row, Request $request): void
    {
      if ($request->get('document_type') !== 'none') {
        $value = isset($row['Position'])
          ? trim($row['Position'])
          : '';

        if (empty($value)) {
          throw new Exception('Position is not specified');
        }

        if (!static::validateInteger($value)) {
          throw new Exception('Position must be a positive integer number');
        }
      }
    }
}

class Importer
{
    protected $dbo;

    public function __construct()
    {
        $db = acsessDb::singleton();
        $this->dbo = $db->connect();

        if (!$this->dbo instanceof PDO) {
            throw new Exception('Database connection error');
        }
    }

    public function beginTransaction()
    {
        $this->dbo->beginTransaction();
    }

    public function commitTransaction()
    {
        $this->dbo->commit();
    }

    public function rollbackTransaction()
    {
        $this->dbo->rollBack();
    }

    public function importRow(array $row, Request $request)
    {
        $data     = $this->prepareInsertData($row, $request);
        $fields   = array_keys($data);
        $addColon = function ($k) {
            return ":$k";
        };

        $insertQuery = "INSERT INTO tingredients (" . implode(',', $fields) . ") VALUES (" . implode(',', array_map($addColon, $fields)) . ")";
        $insertStmt = $this->dbo->prepare($insertQuery);

        /** @var PDOStatement $insertStmt */
        if (!$insertStmt->execute($data)) {
            throw new Exception($insertStmt->errorInfo()[2]);
        }
    }

    protected function prepareInsertData(array $row, Request $request): array
    {
        if ($row['Code'] == false) {
            $row['Code'] = 0;
        }

        $data = [
            'idclient'   => $request->get('client_id'),
            'name'       => $row['Name'],
            'rmcode'     => $row['Code'],
            'material'   => $row['Source'],
            'rmposition' => (int)$row['Position'],
            'producer'   => $row['Producer name'],
            'halalcert'   => "1",
        ];

        if ($row['Supplier name'] != false) {
            $data['supplier'] = $row['Supplier name'];
        } else {
          $data['supplier'] = $row['Producer name'];
        }

        if ($request->get('document_type') === 'certificate') {
            $this->attachCertificate($data, $row, $request);
        }

        if ($request->get('document_type') === 'statement') {
            $this->attachStatement($data, $row, $request);
        }

        return $data;
    }

    protected function attachCertificate(array &$data, array $row, Request $request): void
    {
        $data['cert']     = json_encode($request->getDocumentFileData());
        $data['producer'] = $request->get('producerName');
        $data['cb']       = $request->get('certificationBodyName');
        $data['halalexp'] = date('Y-m-d', strtotime($request->get('expiryDate')));

        if ($request->get('supplierName') == '') {
            $data['supplier'] = $request->get('producerName');
        } else {
            $data['supplier'] = $request->get('supplierName');
        }
    }

    protected function attachStatement(array &$data, array $row, Request $request): void
    {
        $data['statement'] = json_encode($request->getDocumentFileData());
        $data['supplier'] = $request->get('statementSupplierName');
        $data['producer'] = $request->get('statementSupplierName');
    }
}

class Result {
    public int $cTotal = 0;
    public int $cFailed = 0;
    public int $cSuccess = 0;

    protected array $failedRows = [];

    public function pushFailedRow($number, $row, $error): void
    {
        $row['Name'] = $this->sanitizeRowName($row);

        $this->failedRows[] = [
            'number' => $number,
            'name'   => $row['Name'],
            'error'  => $error,
        ];

        $this->cFailed++;
        $this->cTotal++;
    }

    public function pushSuccessfulRow()
    {
        $this->cSuccess++;
        $this->cTotal++;
    }

    public function toArray(): array
    {
        return [
            'failed' => $this->cFailed,
            'total' => $this->cTotal,
            'success' => $this->cSuccess,
            'failed_rows' => $this->failedRows,
        ];
    }

    /**
     * @param array $row
     *
     * @return string
     */
    protected function sanitizeRowName(array $row): string
    {
        $name = isset($row['Name'])
            ? trim($row['Name'])
            : '';
        $name = mb_strlen($name) > 35
            ? mb_substr($name, 32) . '...'
            : $name;

        return $name;
    }
}

try {
    $request = new Request();
    RequestValidator::validateRequest($request);

    $rows = Reader::read($request->getSpreadsheetFilePath());

    $result = new Result;
    $importer = new Importer;

    $importer->beginTransaction();

    foreach ($rows as $i => $row) {
        try {
            RowValidator::validateRow($row, $request);
            $importer->importRow($row, $request);
        } catch (Exception $e) {
            $result->pushFailedRow($i, $row, $e->getMessage());
            continue;
        }

        $result->pushSuccessfulRow();
    }

    $importer->commitTransaction();

    Response::success($result->toArray());

} catch (PDOException $e) {

    if (isset($importer)) {
        $importer->rollbackTransaction();
    }

    Response::error($e->getMessage());

} catch (Throwable $e) {

    Response::error($e->getMessage());

}
