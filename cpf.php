<?php
// Headers CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// LOG para debug - remova em produção
error_log("=== Nova requisição ===");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));
error_log("GET data: " . print_r($_GET, true));
error_log("Input raw: " . file_get_contents('php://input'));

// tratar preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Aceitar tanto GET quanto POST para facilitar testes
$cpf = null;
$nascimento = null;

// Tentar pegar do JSON POST primeiro
$input = json_decode(file_get_contents('php://input'), true);
if (!empty($input['cpf'])) {
    $cpf = $input['cpf'];
    $nascimento = $input['nascimento'] ?? null;
}

// Tentar pegar do POST form-data
if (!$cpf && !empty($_POST['cpf'])) {
    $cpf = $_POST['cpf'];
    $nascimento = $_POST['nascimento'] ?? null;
}

// Tentar pegar do GET (útil para testes no navegador)
if (!$cpf && !empty($_GET['cpf'])) {
    $cpf = $_GET['cpf'];
    $nascimento = $_GET['nascimento'] ?? null;
}

if (!$cpf) {
    http_response_code(400);
    echo json_encode([
        'status' => 400, 
        'error' => 'CPF não informado',
        'debug' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'post' => $_POST,
            'get' => $_GET
        ]
    ]);
    exit;
}

if (!$nascimento) {
    http_response_code(400);
    echo json_encode([
        'status' => 400, 
        'error' => 'Data de nascimento não informada (formato: DD/MM/AAAA ou DDMMAAAA)',
        'debug' => [
            'cpf_recebido' => $cpf
        ]
    ]);
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

// *** SUBSTITUA PELO SEU TOKEN DO HUB DO DESENVOLVEDOR ***
$apiToken = '190873360MAeVzvSNkK344616064';

// Testar diferentes URLs possíveis da API
// Opção 1: Endpoint v2 (mais novo)
$remoteUrl = "http://ws.hubdodesenvolvedor.com.br/v2/cpf/?" 
    . "cpf=" . urlencode($cpf_digits) 
    . "&data=" . urlencode($nascimento_digits)
    . "&token=" . urlencode($apiToken);

// Opção 2: Se a v2 não funcionar, tente sem versão
// $remoteUrl = "http://ws.hubdodesenvolvedor.com.br/cpf/?" 
//     . "cpf=" . urlencode($cpf_digits) 
//     . "&data=" . urlencode($nascimento_digits)
//     . "&token=" . urlencode($apiToken);

error_log("URL da API: " . $remoteUrl);

// fazer requisição cURL com GET (não POST!)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $remoteUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPGET, true); // FORÇAR GET
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Seguir redirects
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Desabilitar verificação SSL para testes
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

// Headers adicionais
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'Content-Type: application/json'
));

$response = curl_exec($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlInfo = curl_getinfo($ch);
curl_close($ch);

error_log("HTTP Code retornado: " . $httpCode);
error_log("cURL Info: " . print_r($curlInfo, true));

if ($response === false || $curlErr) {
    http_response_code(502);
    echo json_encode([
        'status' => 502, 
        'error' => 'Erro ao consultar serviço remoto', 
        'detail' => $curlErr,
        'url_tentada' => $remoteUrl,
        'curl_info' => $curlInfo
    ]);
    exit;
}

// Se receber 405, retornar info útil
if ($httpCode == 405) {
    http_response_code(502);
    echo json_encode([
        'status' => 502,
        'error' => 'API retornou 405 Method Not Allowed',
        'message' => 'Possível problema: método HTTP incorreto, URL errada, ou token inválido',
        'url_tentada' => $remoteUrl,
        'response' => $response,
        'sugestao' => 'Verifique se o token está correto e se a URL da API está atualizada'
    ]);
    exit;
}

// tentar decodificar
$json = json_decode($response, true);

if ($json === null && $httpCode == 200) {
    // se o remoto retornou texto, devolvemos como campo raw
    http_response_code(200);
    echo json_encode(['status' => $httpCode, 'raw' => $response]);
    exit;
}

// devolver a resposta direta
http_response_code($httpCode);
echo json_encode($json ?: ['status' => $httpCode, 'raw' => $response]);
exit;
