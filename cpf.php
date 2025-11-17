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
$nascimento = null;

if (!empty($input['cpf'])) {
    $cpf = $input['cpf'];
} elseif (!empty($_POST['cpf'])) {
    $cpf = $_POST['cpf'];
}

if (!empty($input['nascimento'])) {
    $nascimento = $input['nascimento'];
} elseif (!empty($_POST['nascimento'])) {
    $nascimento = $_POST['nascimento'];
}

if (!$cpf) {
    http_response_code(400);
    echo json_encode(['status' => 400, 'error' => 'CPF não informado']);
    exit;
}

if (!$nascimento) {
    http_response_code(400);
    echo json_encode(['status' => 400, 'error' => 'Data de nascimento não informada']);
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

// limpar data de nascimento (manter apenas dígitos)
$nascimento_digits = preg_replace('/\D/', '', $nascimento);

// validar tamanho da data (formato DDMMAAAA = 8 dígitos)
if (strlen($nascimento_digits) !== 8) {
    http_response_code(400);
    echo json_encode(['status' => 400, 'error' => 'Data de nascimento inválida (use formato DD/MM/AAAA ou DDMMAAAA)']);
    exit;
}

// *** IMPORTANTE: Substitua pelo seu token do Hub do Desenvolvedor ***
$apiToken = '190873360MAeVzvSNkK344616064';

// preparar URL da API do Hub do Desenvolvedor
// Endpoint: http://ws.hubdodesenvolvedor.com.br/v2/cpf/
$remoteUrl = "http://ws.hubdodesenvolvedor.com.br/v2/cpf/?" 
    . "cpf=" . urlencode($cpf_digits) 
    . "&data=" . urlencode($nascimento_digits)
    . "&token=" . urlencode($apiToken);

// fazer requisição cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $remoteUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
// se o servidor remoto usar TLS correto, não precisa setar CURLOPT_SSL_VERIFYPEER = false.
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

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
    // se o remoto retornou texto, devolvemos como campo raw
    http_response_code(200);
    echo json_encode(['status' => $httpCode, 'raw' => $response]);
    exit;
}

// devolver a resposta direta (pode enriquecer se precisar)
http_response_code(200);
echo json_encode($json);
exit;
