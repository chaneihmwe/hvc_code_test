<?php

class FinalResult {
    
    public function results(string $filePath) : array
    {
        if (!file_exists($filePath)) {
            return [
                "filename" => basename($filePath),
                "document" => null,
                "failure_code" => 404,
                "failure_message" => "Failed to open stream: No such file or directory.",
                "records" => []
            ];
        }

        $file = fopen($filePath, "r");
        $header = fgetcsv($file);
        $records = [];

        while (!feof($file)) {
            $row = fgetcsv($file);

            if (count($row) === 16) {
                $records[] = $this->rowToFormattedRecord($header[0], $row);
            }
        }

        return [
            "filename" => basename($filePath),
            "document" => $file,
            "failure_code" => $header[1],
            "failure_message" => $header[2],
            "records" => array_filter($records)
        ];
    }

    private function rowToFormattedRecord(string $currency, array $row) : array 
    {
        $amount = !empty($row[8]) || $row[8] !== "0" ? (float) $row[8] : 0;
        $bankAccountNumber = !empty($row[6]) ? (int) $row[6] : "Bank account number missing";
        $bankBranchCode = !empty($row[2]) ? $row[2] : "Bank branch code missing";
        $endToEndId = !empty($row[10]) && !empty($row[11]) ? $row[10] . $row[11] : "End to end id missing" ;

        return [
            "amount" => [
                "currency" => $currency,
                "subunits" => (int) ($amount * 100)
            ],
            "bank_account_name" => str_replace(" ", "_", strtolower($row[7])),
            "bank_account_number" => $bankAccountNumber,
            "bank_branch_code" => $bankBranchCode,
            "bank_code" => $row[0],
            "end_to_end_id" => $endToEndId,
        ];
    }
}
?>
