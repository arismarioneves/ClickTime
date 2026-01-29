<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Obter dados da requisição
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['token']) || !isset($input['start_date']) || !isset($input['end_date'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetros obrigatórios: token, start_date, end_date']);
    exit;
}

$token = $input['token'];
$startDate = $input['start_date'];
$endDate = $input['end_date'];
$timezone = new DateTimeZone('America/Sao_Paulo');

try {
    // Buscar dados do usuário para obter os teams
    $userData = makeClickUpRequest('https://api.clickup.com/api/v2/user', $token);

    if (!$userData || !isset($userData['user'])) {
        throw new Exception('Erro ao obter dados do usuário');
    }

    $userId = $userData['user']['id'];

    // Buscar teams do usuário
    $teamsData = makeClickUpRequest('https://api.clickup.com/api/v2/team', $token);

    if (!$teamsData || !isset($teamsData['teams'])) {
        throw new Exception('Erro ao obter teams do usuário');
    }

    $totalTime = 0;
    $processedEntries = []; // Controle de entradas já processadas pelo ID
    $uniqueEntries = []; // Entradas únicas antes da agregação

    // Para cada team, buscar os dados de tempo
    foreach ($teamsData['teams'] as $team) {
        $teamId = $team['id'];

        // Buscar time entries do team no período especificado
        $timeUrl = "https://api.clickup.com/api/v2/team/{$teamId}/time_entries";
        $timeUrl .= "?start_date={$startDate}&end_date={$endDate}&assignee={$userId}";

        $timeEntries = makeClickUpRequest($timeUrl, $token);

        if ($timeEntries && isset($timeEntries['data'])) {
            foreach ($timeEntries['data'] as $entry) {
                $entryId = $entry['id'] ?? null;
                if (!$entryId || isset($processedEntries[$entryId])) {
                    continue;
                }

                // Guardar a entrada única e marcar como processada
                $processedEntries[$entryId] = true;
                $uniqueEntries[] = $entry;
            }
        }
    }

    // Agregar entradas únicas por task + data
    $allTasks = [];
    foreach ($uniqueEntries as $entry) {
        $duration = intval($entry['duration']);
        $totalTime += $duration;

        $hasTask = isset($entry['task']);
        $entryId = $entry['id'] ?? uniqid('entry_');
        $taskId = $hasTask ? $entry['task']['id'] : 'manual_' . $entryId;
        $rawName = $hasTask ? $entry['task']['name'] : ($entry['description'] ?? 'Nenhuma tarefa selecionada');
        $taskName = trim($rawName) !== '' ? $rawName : 'Nenhuma tarefa selecionada';

        $startMillis = isset($entry['start']) ? intval($entry['start']) : null;
        $entryTimestamp = null;
        $entryDate = date('Y-m-d');
        $entryTime = '--:--:--';

        if ($startMillis !== null) {
            $timestampSeconds = intdiv($startMillis, 1000);
            $dateTime = (new DateTimeImmutable('@' . $timestampSeconds))->setTimezone($timezone);
            $entryTimestamp = $dateTime->getTimestamp();
            $entryDate = $dateTime->format('Y-m-d');
            $entryTime = $dateTime->format('H:i:s');
        }

        $uniqueKey = $entryDate . '_' . $taskId;

        if (!isset($allTasks[$uniqueKey])) {
            $allTasks[$uniqueKey] = [
                'id' => $taskId,
                'name' => $taskName,
                'time' => 0,
                'date' => $entryDate,
                'time_start' => $entryTime,
                'timestamp' => $entryTimestamp,
                'unique_key' => $uniqueKey,
                'has_task' => $hasTask,
                'description' => $entry['description'] ?? null,
                'raw_entry_ids' => []
            ];
        }

        $allTasks[$uniqueKey]['time'] += $duration;
        $allTasks[$uniqueKey]['raw_entry_ids'][] = $entryId;

        // Atualizar para o horário mais antigo
        if ($entryTimestamp !== null && ($allTasks[$uniqueKey]['timestamp'] === 0 || $entryTimestamp < $allTasks[$uniqueKey]['timestamp'])) {
            $allTasks[$uniqueKey]['timestamp'] = $entryTimestamp;
            $allTasks[$uniqueKey]['time_start'] = $entryTime;
        }
    }

    // Reindexar array para ordenação
    $allTasks = array_values($allTasks);

    // Ordenar tarefas por data e depois por horário
    usort($allTasks, function($a, $b) {
        if ($a['date'] !== $b['date']) {
            return strcmp($a['date'], $b['date']);
        }
        return $a['timestamp'] - $b['timestamp'];
    });

    // Agrupar tarefas por data
    $tasksByDate = [];
    foreach ($allTasks as $task) {
        $date = $task['date'];
        if (!isset($tasksByDate[$date])) {
            $tasksByDate[$date] = [];
        }
        
        unset($task['unique_key']);
        unset($task['timestamp']);
        $tasksByDate[$date][] = $task;
    }

    // Converter para array associativo mantendo a ordem
    $tasksByDate = (object)$tasksByDate;

    // Retornar dados processados
    echo json_encode([
        'success' => true,
        'total_time' => $totalTime,
        'tasks_by_date' => $tasksByDate,
        'raw_entries' => $uniqueEntries,
        'period' => [
            'start' => $startDate,
            'end' => $endDate
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}

/**
 * Fazer requisição para a API do ClickUp
 */
function makeClickUpRequest($url, $token)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . $token,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        throw new Exception('Erro cURL: ' . $error);
    }

    if ($httpCode !== 200) {
        throw new Exception('Erro HTTP: ' . $httpCode);
    }

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Erro ao decodificar JSON: ' . json_last_error_msg());
    }

    return $data;
}
