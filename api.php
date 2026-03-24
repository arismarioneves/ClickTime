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

    $allTasks = [];
    $totalTime = 0;

    // Para cada team, buscar os dados de tempo
    foreach ($teamsData['teams'] as $team) {
        $teamId = $team['id'];

        // Buscar time entries do team no período especificado
        $timeUrl = "https://api.clickup.com/api/v2/team/{$teamId}/time_entries";
        $timeUrl .= "?start_date={$startDate}&end_date={$endDate}&assignee={$userId}";

        $timeEntries = makeClickUpRequest($timeUrl, $token);

        if ($timeEntries && isset($timeEntries['data'])) {
            foreach ($timeEntries['data'] as $entry) {
                $duration = intval($entry['duration']);
                $totalTime += $duration;

                // Obter informações da task
                if (isset($entry['task'])) {
                    $taskId = $entry['task']['id'];
                    $taskName = $entry['task']['name'];

                    // Verificar se a task já existe no array
                    $taskExists = false;
                    foreach ($allTasks as &$task) {
                        if ($task['id'] === $taskId) {
                            $task['time'] += $duration;
                            $taskExists = true;
                            break;
                        }
                    }

                    // Se a task não existe, adicionar
                    if (!$taskExists) {
                        $allTasks[] = [
                            'id' => $taskId,
                            'name' => $taskName,
                            'time' => $duration,
                            'date' => isset($entry['start']) ? gmdate('Y-m-d', intval($entry['start'] / 1000)) : date('Y-m-d')
                        ];
                    }
                }
            }
        }
    }

    // Retornar dados processados
    echo json_encode([
        'success'    => true,
        'total_time' => $totalTime,
        'tasks'      => $allTasks,
        'period'     => [
            'start' => $startDate,
            'end'   => $endDate,
        ],
        'user' => [
            'name'      => $userData['user']['username'] ?? '',
            'avatar'    => $userData['user']['profilePicture'] ?? null,
            'workspace' => $teamsData['teams'][0]['name'] ?? 'Workspace',
        ],
    ]);
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
