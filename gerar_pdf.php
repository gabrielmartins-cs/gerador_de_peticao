<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acesso inválido.');
}

set_time_limit(300);

$dados = $_POST;

// 1. Processa imagem de fundo (se houver em assets/) em Base64 para o Dompdf
$imgTimbradoHtml = '';
$caminhoImagem = __DIR__ . '/assets/imageheader.jpg';
if (file_exists($caminhoImagem)) {
    $imgBase64 = base64_encode(file_get_contents($caminhoImagem));
    $imgTimbradoHtml = '<img class="bg-timbrado" src="data:image/jpeg;base64,' . $imgBase64 . '" />';
}

// 2. Mapeamento de numeração romana e títulos dos blocos
$romanos = [2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII'];
$titulosPadrao = [
    'PRELIMINARES' => 'DAS PRELIMINARES',
    'PREJUDICIAIS' => 'DAS PREJUDICIAIS DE MÉRITO',
    'MERITO'       => 'DO MÉRITO',
    'SUBSIDIARIAS' => 'DOS PEDIDOS SUBSIDIÁRIOS'
];

// 3. Montagem do HTML dos Blocos e Teses
$htmlBlocos = '';
if (!empty($dados['blocos']) && is_array($dados['blocos'])) {
    $contadorBloco = 2;

    foreach ($dados['blocos'] as $bloco) {
        $catKey = $bloco['categoria'] ?? 'MERITO';
        $tituloBloco = $titulosPadrao[$catKey] ?? 'DA FUNDAMENTAÇÃO JURÍDICA';
        $numRomano = $romanos[$contadorBloco] ?? (string)$contadorBloco;

        $htmlBlocos .= '<div class="titulo-bloco">' . $numRomano . '. ' . htmlspecialchars($tituloBloco) . '</div>';

        $teses = $bloco['teses'] ?? [];
        $subItem = 1;

        foreach ($teses as $tese) {
            // Título da Tese (higienizado contra 'Enter' e quebras de linha)
            $tituloBruto = strip_tags($tese['titulo'] ?? '');
            // Converte qualquer quebra de linha (\r, \n, tabulações) em um espaço simples
            $tituloLimpo = trim(preg_replace('/\s+/', ' ', $tituloBruto));
            // Remove eventual numeração manual repetida (ex: se a IA já colocou "2.1." ou "2.1 - ")
            $tituloLimpo = preg_replace('/^\d+(\.\d+)*\s*[-–.]?\s*/', '', $tituloLimpo);

            if (!empty($tituloLimpo)) {
                $numeracao = "{$contadorBloco}.{$subItem}. ";
                $htmlBlocos .= '<div class="titulo-tese">' . $numeracao . mb_strtoupper(htmlspecialchars($tituloLimpo, ENT_QUOTES, 'UTF-8'), 'UTF-8') . '</div>';
            }

            // Elementos Sequenciais (Parágrafos e Citações na ordem escolhida)
            if (!empty($tese['elementos']) && is_array($tese['elementos'])) {
                foreach ($tese['elementos'] as $elem) {
                    $tipo = $elem['tipo'] ?? 'texto';
                    $conteudo = $elem['conteudo'] ?? '';

                    if ($tipo === 'citacao' && !empty(trim(strip_tags($conteudo)))) {
                        // Citação Direta Longa com suporte a cores e marca-texto
                        $htmlBlocos .= '<div class="citacao-longa">' . $conteudo . '</div>';
                    } elseif ($tipo === 'texto' && !empty(trim(strip_tags($conteudo)))) {
                        // Parágrafo Normal vindo do Summernote
                        $htmlBlocos .= '<div class="paragrafo-comum">' . $conteudo . '</div>';
                    } elseif ($tipo === 'imagem' && !empty($conteudo)) {
                        // Imagem / Print anexado na Tese
                        $legenda = !empty($elem['legenda'] ?? '') ? '<div class="legenda-imagem">' . htmlspecialchars(trim($elem['legenda']), ENT_QUOTES, 'UTF-8') . '</div>' : '';
                        $htmlBlocos .= '<div class="container-imagem"><img src="' . $conteudo . '" class="img-anexo" />' . $legenda . '</div>';
                    }
                }
            }
            $subItem++;
        }
        $contadorBloco++;
    }
}

// 4. Montagem da Estrutura HTML Completa com CSS ABNT
$htmlCompleto = '
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="UTF-8">
    <style>
        @page {
            margin-top: 3cm;
            margin-left: 3cm;
            margin-right: 2cm;
            margin-bottom: 2cm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            color: #000;
            line-height: 1.5;
            word-wrap: break-word;
        }
        /* Papel timbrado em segundo plano */
        .bg-timbrado {
            position: fixed;
            top: -3cm;
            left: -3cm;
            width: 210mm;
            height: 297mm;
            z-index: -1000;
        }
        /* Endereçamento */
        .enderecamento {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 1.5cm;
            line-height: 1.5;
            page-break-inside: avoid;
        }
        /* Nome da Ação */
        .nome-acao {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            text-decoration: underline;
            text-transform: uppercase;
            margin-top: 0.8cm;
            margin-bottom: 0.8cm;
            page-break-inside: avoid;
        }
        /* Título das Seções Principais (não separa do texto seguinte) */
        .titulo-secao, .titulo-bloco {
            font-weight: bold;
            font-size: 12pt;
            text-transform: uppercase;
            margin-top: 0.8cm;
            margin-bottom: 0.4cm;
            text-align: left;
            page-break-after: avoid;
            page-break-inside: avoid;
        }
        /* Subtítulo da Tese (não fica órfão no rodapé) */
        .titulo-tese {
            font-weight: bold;
            font-size: 12pt;
            text-decoration: underline;
            margin-top: 0.6cm;
            margin-bottom: 0.3cm;
            text-align: left;
            page-break-after: avoid;
            page-break-inside: avoid;
        }
        /* Parágrafos comuns */
        p, .paragrafo-comum p, .paragrafo-comum {
            text-align: justify;
            text-indent: 1.5cm;
            margin-top: 0;
            margin-bottom: 0.35cm;
            line-height: 1.5;
            orphans: 2;
            widows: 2;
        }
        /* Citação Longa Direta com suporte a marca-texto e cores */
        .citacao-longa {
            margin-left: 4cm;
            margin-right: 0;
            margin-top: 0.35cm;
            margin-bottom: 0.45cm;
            padding: 8px 12px;
            background-color: #f8fafc;
            border-left: 3pt solid #1e3a5f;
            font-size: 9.5pt;
            color: #2d3748;
            line-height: 1.25;
            text-align: justify;
            text-indent: 0 !important;
            page-break-inside: avoid;
        }
        .citacao-longa p {
            text-indent: 0 !important;
            margin-bottom: 4px;
            font-size: 9.5pt;
            line-height: 1.25;
        }
/* Imagens e Prints (Visual Law) */
        .container-imagem {
            text-align: center;
            margin-top: 0.5cm;
            margin-bottom: 0.5cm;
            page-break-inside: avoid;
        }
        .img-anexo {
            max-width: 15cm;
            max-height: 12cm;
            border: 1px solid #d1d5db;
            border-radius: 4px;
        }
        .legenda-imagem {
            font-size: 8.5pt;
            color: #4b5563;
            font-style: italic;
            margin-top: 4px;
            text-align: center;
        }
        .fechamento {
            margin-top: 0.8cm;
            text-align: left;
            text-indent: 0;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    ' . $imgTimbradoHtml . '

    <!-- 1. Endereçamento -->
    <div class="enderecamento">
        ' . htmlspecialchars($dados['enderecamento'] ?? '') . '
    </div>

    <!-- 2. Qualificação do Autor -->
    <div>
        ' . ($dados['qualificacao_autor'] ?? '') . '
    </div>

    <!-- 3. Nome da Ação -->
    <div class="nome-acao">
        ' . htmlspecialchars($dados['nome_acao'] ?? 'PETIÇÃO') . '
    </div>

    <!-- 4. Qualificação do Réu -->
    <div>
        ' . ($dados['qualificacao_reu'] ?? '') . '
    </div>

    <!-- 5. Dos Fatos -->
    ' . (!empty(trim(strip_tags($dados['dos_fatos'] ?? ''))) ? '
        <div class="titulo-secao">I. DOS FATOS</div>
        <div>' . $dados['dos_fatos'] . '</div>
    ' : '') . '

    <!-- 6. Teses em Blocos -->
    ' . $htmlBlocos . '

    <!-- 7. Pedidos e Conclusão -->
    ' . (!empty(trim(strip_tags($dados['conclusao_pedidos'] ?? ''))) ? '
        <div class="titulo-secao" style="text-align: center;">DOS PEDIDOS E REQUERIMENTOS</div>
        <div>' . $dados['conclusao_pedidos'] . '</div>
    ' : '') . '

    <!-- Fechamento Forense -->
    <div class="fechamento">
        Termos em que,<br>
        Pede deferimento.
    </div>
</body>
</html>
';

// 5. Configuração e Renderização pelo Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'Times-Roman');

$dompdf = new Dompdf($options);
// Passa explicitamente a codificação UTF-8 para evitar caracteres quebrados
$dompdf->loadHtml($htmlCompleto, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// 6. Envia o PDF para download no navegador
$nomeArquivo = 'Peticao_' . date('Y-m-d_His') . '.pdf';
$dompdf->stream($nomeArquivo, ['Attachment' => true]);
exit;
