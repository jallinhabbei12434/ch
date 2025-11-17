<?php

$secretKey = 'sk_live_v20eBk9U4vc2gb6Y7wa4RyAwZI65OR0QYjBeZwvavo';

// MONTA O AUTH BASIC
$auth = base64_encode("$secretKey:x");

$payload = [
    "amount" => 500, 
    "paymentMethod" => "pix",
    "customer" => [
        "name" => "João Silva",
        "email" => "joao@email.com",
        "phone" => "+5538999999999", 
        "document" => [
            "type" => "cpf",
            "number" => "12345678909"
        ]
    ],
    "items" => [
        [
            "title" => "Produto de teste",
            "unitPrice" => 500,
            "quantity" => 1,
            "tangible" => false
        ]
    ],
    "pix" => [
        "expiresIn" => 3600
    ],
    "postbackUrl" => "https://seusite.com.br/postback"
];

$ch = curl_init("https://api.simpayments.com.br/v1/transactions");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Basic $auth",
        "Content-Type: application/json",
        "Accept: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($payload)
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    die("Erro: " . curl_error($ch));
}

curl_close($ch);

// DECODIFICA RESPOSTA
$data = json_decode($response, true);

// EXIBE COMO JSON
header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
