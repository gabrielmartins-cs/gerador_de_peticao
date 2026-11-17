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
            // Título da Tese
            if (!empty(trim($tese['titulo'] ?? ''))) {
                $numeracao = "{$contadorBloco}.{$subItem}. ";
                $htmlBlocos .= '<div class="titulo-tese">' . $numeracao . mb_strtoupper(htmlspecialchars(trim($tese['titulo'])), 'UTF-8') . '</div>';
            }

            // Elementos Sequenciais (Parágrafos e Citações na ordem escolhida)
            if (!empty($tese['elementos']) && is_array($tese['elementos'])) {
                foreach ($tese['elementos'] as $elem) {
                    $tipo = $elem['tipo'] ?? 'texto';
                    $conteudo = $elem['conteudo'] ?? '';

                    if ($tipo === 'citacao' && !empty(trim($conteudo))) {
                        // Citação Direta Longa (+ 3 linhas): Recuo 4cm, Fonte 10, Simples
                        $htmlBlocos .= '<div class="citacao-longa">' . nl2br(htmlspecialchars(trim($conteudo))) . '</div>';
                    } elseif ($tipo === 'texto' && !empty(trim(strip_tags($conteudo)))) {
                        // Parágrafo Normal vindo do Summernote
                        $htmlBlocos .= '<div class="paragrafo-comum">' . $conteudo . '</div>';
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
    <meta charset="UTF-8">
    <style>
        @page {
            margin-top: 3cm;
            margin-left: 3cm;
            margin-right: 2cm;
            margin-bottom: 2cm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            color: #000;
            line-height: 1.5;
        }
        /* Papel timbrado em segundo plano cobrindo a página inteira */
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
            margin-bottom: 2cm;
            line-height: 1.5;
        }
        /* Nome da Ação */
        .nome-acao {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            text-decoration: underline;
            text-transform: uppercase;
            margin-top: 1cm;
            margin-bottom: 1cm;
        }
        /* Título das Seções Principais (I. DOS FATOS, II. DAS PRELIMINARES...) */
        .titulo-secao, .titulo-bloco {
            font-weight: bold;
            font-size: 12pt;
            text-transform: uppercase;
            margin-top: 1cm;
            margin-bottom: 0.4cm;
            text-align: left;
        }
        /* Subtítulo da Tese (Ex: 2.1. DA AUSÊNCIA DE INTERESSE) */
        .titulo-tese {
            font-weight: bold;
            font-size: 12pt;
            text-decoration: underline;
            margin-top: 0.6cm;
            margin-bottom: 0.3cm;
            text-align: left;
        }
        /* Parágrafos comuns (Recuo ABNT de 1,5cm na 1ª linha e justificado) */
        p, .paragrafo-comum p {
            text-align: justify;
            text-indent: 1.5cm;
            margin-top: 0;
            margin-bottom: 0.4cm;
            line-height: 1.5;
        }
        /* Citação Longa Direta (Regra ABNT: Recuo 4cm, Fonte 10, Entrelinhas 1.0) */
        .citacao-longa {
            margin-left: 4cm;
            margin-top: 0.3cm;
            margin-bottom: 0.4cm;
            font-size: 10pt;
            line-height: 1.0;
            text-align: justify;
            text-indent: 0;
        }
        .fechamento {
            margin-top: 1cm;
            text-align: left;
            text-indent: 0;
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
$dompdf->loadHtml($htmlCompleto);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// 6. Envia o PDF para download no navegador
$nomeArquivo = 'Peticao_' . date('Y-m-d_His') . '.pdf';
$dompdf->stream($nomeArquivo, ['Attachment' => true]);
exit;
