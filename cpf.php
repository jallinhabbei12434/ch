<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// tratar preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// receber JSON ou form-data
$input = json_decode(file_get_contents('php://input'), true);
$cpf = null;

if (!empty($input['cpf'])) {
    $cpf = $input['cpf'];
} elseif (!empty($_POST['cpf'])) {
    $cpf = $_POST['cpf'];
}

if (!$cpf) {
    http_response_code(400);
    echo json_encode(['status' => 400, 'error' => 'CPF não informado']);
    exit;
}

// limpar CPF (manter apenas dígitos)
$cpf_digits = preg_replace('/\D/', '', $cpf);

// validar tamanho
if (strlen($cpf_digits) !== 11) {
    http_response_code(400);
    echo json_encode(['status' => 400, 'error' => 'CPF inválido (deve ter 11 dígitos)']);
    exit;
}

// SEU TOKEN DO HUB DO DESENVOLVEDOR
$apiToken = '190873360MAeVzvSNkK344616064';

// URL da API do Hub do Desenvolvedor
$remoteUrl = "https://ws.hubdodesenvolvedor.com.br/v2/nome_cpf/?cpf=" 
    . urlencode($cpf_digits) 
    . "&token=" . urlencode($apiToken);

// fazer requisição cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $remoteUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $curlErr) {
    http_response_code(502);
    echo json_encode(['status' => 502, 'error' => 'Erro ao consultar serviço remoto', 'detail' => $curlErr]);
    exit;
}

// tentar decodificar
$json = json_decode($response, true);

if ($json === null) {
    http_response_code(500);
    echo json_encode(['status' => 500, 'error' => 'Erro ao processar resposta da API', 'raw' => $response]);
    exit;
}

// A API retorna um array, pegar o primeiro elemento
$data = $json[0] ?? null;

if (!$data || !isset($data['status']) || $data['status'] !== true) {
    http_response_code(404);
    echo json_encode(['status' => 404, 'error' => 'CPF não encontrado ou inválido']);
    exit;
}

// Extrair dados do result
$result = $data['result'] ?? [];

// Montar resposta no formato que seu script.js espera
$response_data = [
    'status' => 200,
    'nome' => $result['nome'] ?? 'Não informado',
    'cpf' => $cpf_digits,
    'nascimento' => $result['data_de_nascimento'] ?? 'Não informado',
    'sexo' => 'Não informado', // API não retorna
    'endereco' => 'Não informado' // API não retorna
];

http_response_code(200);
echo json_encode($response_data);
exit;
?>
