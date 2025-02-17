<?php
namespace App\Services;

use Google\Client;
use Google\Service\Sheets;

class GoogleSheetsService
{
    protected $client;
    protected $service;
    protected $spreadsheetId;

    public function __construct()
    {
        $this->spreadsheetId = env('GOOGLE_SHEET_ID');

        $this->client = new Client([
            'verify' => false
        ]);
        $this->client->setAuthConfig(storage_path('app/google-sheets.json'));
        $this->client->addScope(Sheets::SPREADSHEETS);

        $this->service = new Sheets($this->client);
    }

    public function addTask($task)
    {
        $values = [
            [$task->id, $task->title, $task->description ?: '', $task->due_date ?: '', $task->is_completed ? '✅' : '❌']
        ];

        $body = new Sheets\ValueRange([
            'values' => $values
        ]);

        $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            'Лист1!A:E',  // Диапазон (столбцы A-E)
            $body,
            ['valueInputOption' => 'RAW']
        );
    }

    public function updateTask($task)
    {
        // Находим строку с задачей по ID. ID задачи в столбце A
        $response = $this->service->spreadsheets_values->get(
            $this->spreadsheetId,
            'Лист1!A:A' // Только столбец с ID задачи
        );

        $rows = $response->getValues();
        $rowIndex = null;

        foreach ($rows as $index => $row) {
            if ($row[0] == $task->id) {
                $rowIndex = $index + 1;  // Строки в Sheets начинаются с 1, а не с 0
                break;
            }
        }

        if ($rowIndex !== null) {
            // Обновляем данные задачи в найденной строке
            $values = [
                [$task->id, $task->title, $task->description ?: '', $task->due_date ?: '', $task->is_completed ? '✅' : '❌']
            ];

            $body = new Sheets\ValueRange([
                'values' => $values
            ]);

            $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                "Лист1!A{$rowIndex}:E{$rowIndex}",
                $body,
                ['valueInputOption' => 'RAW']
            );
        }
    }

    public function deleteTask($taskId)
    {
        // Получаем данные из столбца A (ID задач)
        $response = $this->service->spreadsheets_values->get(
            $this->spreadsheetId,
            'Лист1!A:A' // Только столбец с ID задачи
        );

        $rows = $response->getValues();
        $rowIndex = null;

        foreach ($rows as $index => $row) {
            if ($row[0] == $taskId) {
                $rowIndex = $index; // Тут НЕ +1, так как индексы в batchUpdate начинаются с 0
                break;
            }
        }

        if ($rowIndex !== null) {
            // Удаляем строку
            $batchUpdateRequest = new Sheets\BatchUpdateSpreadsheetRequest([
                'requests' => [
                    [
                        'deleteDimension' => [
                            'range' => [
                                'sheetId' => 0, // ID листа (0 - если это первый лист)
                                'dimension' => 'ROWS',
                                'startIndex' => $rowIndex,
                                'endIndex' => $rowIndex + 1
                            ]
                        ]
                    ]
                ]
            ]);

            $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);
        }
    }

}
