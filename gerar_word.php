<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Shared\Html;

class PeticaoWordxGenerator
{
    private const RECUO_PRIMEIRA_LINHA = "\u{00A0}\u{00A0}\u{00A0}\u{00A0}\u{00A0}\u{00A0}\u{00A0}";

    public function gerarDocumento(array $dados): string
    {
        set_time_limit(300);

        $phpWord = new PhpWord();
        Settings::setDefaultPaper('A4');
        Settings::setOutputEscapingEnabled(true);

        $diretorioTmp = __DIR__ . '/temp';
        if (!is_dir($diretorioTmp)) {
            mkdir($diretorioTmp, 0777, true);
        }
        Settings::setTempDir($diretorioTmp);
        $caminhoArquivo = $diretorioTmp . '/peticao_' . uniqid() . '.docx';

        // Configuração da Página A4 conforme ABNT Forense
        $section = $phpWord->addSection([
            'paperSize'    => 'A4',
            'pageSizeW'    => 11906,
            'pageSizeH'    => 16838,
            'marginTop'    => 1701, // 3 cm
            'marginBottom' => 1134, // 2 cm
            'marginLeft'   => 1701, // 3 cm
            'marginRight'  => 1134  // 2 cm
        ]);

        // 1. Marca d'água / Imagem de fundo (se houver em assets/)
        $this->adicionarImagemFundo($section);



        // 3. Endereçamento (Juízo competente)
        $this->adicionarEnderecamento($section, $dados['enderecamento'] ?? '');

        // 4. Qualificação do Autor, Nome da Ação e Réu
        $this->adicionarPreambuloEPartes($section, $dados);

        // 5. Dos Fatos
        if (!empty(trim(strip_tags($dados['dos_fatos'] ?? '')))) {
            $this->adicionarSecaoFatos($section, $dados['dos_fatos']);
        }

        // 6. Das Teses em Blocos Estruturados
        if (!empty($dados['blocos']) && is_array($dados['blocos'])) {
            $this->adicionarBlocosTeses($section, $dados['blocos']);
        }

        // 7. Dos Pedidos e Requerimentos
        if (!empty(trim(strip_tags($dados['conclusao_pedidos'] ?? '')))) {
            $this->adicionarPedidos($section, $dados['conclusao_pedidos']);
        }

        // Gravação
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($caminhoArquivo);

        return $caminhoArquivo;
    }

    private function adicionarImagemFundo($section): void
    {
        $imagemfundo = __DIR__ . '/assets/imageheader.jpg';
        if (file_exists($imagemfundo)) {
            $header = $section->addHeader();
            $header->addImage($imagemfundo, [
                'width'            => 595.28,
                'height'           => 841.89,
                'positioning'      => 'absolute',
                'posHorizontal'    => 'absolute',
                'posHorizontalRel' => 'page',
                'posVertical'      => 'absolute',
                'posVerticalRel'   => 'page',
                'marginLeft'       => 0,
                'marginTop'        => 0,
                'wrappingStyle'    => 'behind',
            ]);
        }
    }


    private function adicionarEnderecamento($section, string $enderecamento): void
    {
        $section->addText(
            mb_strtoupper(trim($enderecamento), 'UTF-8'),
            ['name' => 'Times New Roman', 'size' => 12, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 480, 'lineHeight' => 1.5]
        );
    }

    private function adicionarPreambuloEPartes($section, array $dados): void
    {
        // Qualificação Autor
        $this->renderizarConteudoHtml($section, $dados['qualificacao_autor'] ?? '', true);

        // Nome da Ação em Destaque Centralizado
        $nomeAcao = mb_strtoupper(trim($dados['nome_acao'] ?? 'PETIÇÃO'), 'UTF-8');
        $section->addText(
            $nomeAcao,
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true, 'underline' => 'single'],
            ['alignment' => Jc::CENTER, 'spaceBefore' => 240, 'spaceAfter' => 240]
        );

        // Qualificação Réu / Querelado
        $this->renderizarConteudoHtml($section, $dados['qualificacao_reu'] ?? '', true);
        $section->addTextBreak(1, ['size' => 15]);
    }

    private function adicionarSecaoFatos($section, string $fatosHtml): void
    {
        $section->addText(
            "I. DOS FATOS",
            ['name' => 'Times New Roman', 'size' => 12, 'bold' => true],
            ['spaceBefore' => 240, 'spaceAfter' => 180, 'alignment' => Jc::LEFT]
        );
        $this->renderizarConteudoHtml($section, $fatosHtml);
        $section->addTextBreak(1, ['size' => 15]);
    }

    private function adicionarBlocosTeses($section, array $blocos): void
    {
        $romanos = [2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII'];
        $contadorBloco = 2;

        $titulosPadrao = [
            'PRELIMINARES' => 'DAS PRELIMINARES',
            'PREJUDICIAIS' => 'DAS PREJUDICIAIS DE MÉRITO',
            'MERITO'       => 'DO MÉRITO',
            'SUBSIDIARIAS' => 'DOS PEDIDOS SUBSIDIÁRIOS'
        ];

        foreach ($blocos as $bloco) {
            $catKey = $bloco['categoria'] ?? 'MERITO';
            $tituloBloco = $titulosPadrao[$catKey] ?? 'DA FUNDAMENTAÇÃO JURÍDICA';
            $numRomano = $romanos[$contadorBloco] ?? (string)$contadorBloco;

            // TÍTULO DO BLOCO (Ex: II. DAS PRELIMINARES)
            $section->addText(
                "{$numRomano}. {$tituloBloco}",
                ['name' => 'Times New Roman', 'size' => 12, 'bold' => true],
                ['spaceBefore' => 300, 'spaceAfter' => 180, 'alignment' => Jc::LEFT]
            );

            $teses = $bloco['teses'] ?? [];
            $subItem = 1;

            foreach ($teses as $tese) {
                // 1. Título da Tese
                if (!empty(trim($tese['titulo'] ?? ''))) {
                    $numeracao = "{$contadorBloco}.{$subItem}. ";
                    $section->addText(
                        $numeracao . mb_strtoupper(trim($tese['titulo']), 'UTF-8'),
                        ['name' => 'Times New Roman', 'size' => 12, 'bold' => true, 'underline' => 'single'],
                        ['spaceBefore' => 180, 'spaceAfter' => 120, 'alignment' => Jc::LEFT]
                    );
                }

                // 2. Elementos sequenciais da tese (renderizados rigorosamente na ordem definida)
                if (!empty($tese['elementos']) && is_array($tese['elementos'])) {
                    foreach ($tese['elementos'] as $elem) {
                        $tipo = $elem['tipo'] ?? 'texto';
                        $conteudo = $elem['conteudo'] ?? '';

                        if ($tipo === 'citacao' && !empty(trim($conteudo))) {
                            $this->adicionarCitacaoLonga($section, $conteudo);
                        } elseif ($tipo === 'texto' && !empty(trim(strip_tags($conteudo)))) {
                            $this->renderizarConteudoHtml($section, $conteudo);
                        }
                    }
                }

                $subItem++;
            }

            $contadorBloco++;
        }
    }

    private function adicionarPedidos($section, string $pedidosHtml): void
    {
        $section->addText(
            "DOS PEDIDOS E REQUERIMENTOS",
            ['name' => 'Times New Roman', 'size' => 12, 'bold' => true],
            ['spaceBefore' => 300, 'spaceAfter' => 180, 'alignment' => Jc::CENTER]
        );
        $this->renderizarConteudoHtml($section, $pedidosHtml);

        // Fechamento forense
        $section->addTextBreak(1);
        $section->addText(
            "Termos em que,\nPede deferimento.",
            ['name' => 'Times New Roman', 'size' => 12],
            ['alignment' => Jc::LEFT, 'spaceAfter' => 360]
        );
    }

    /**
     * Citação Direta Longa (+ de 3 linhas):
     * - Recuo de 4 cm à esquerda (2268 twips)
     * - Fonte tamanho 10
     * - Espaçamento entrelinhas simples (lineHeight 1.0)
     */
    private function adicionarCitacaoLonga($section, string $textoCitacao): void
    {
        $linhas = explode("\n", trim($textoCitacao));
        foreach ($linhas as $linha) {
            $linhaLimpa = trim($linha);
            if (!empty($linhaLimpa)) {
                $section->addText(
                    $linhaLimpa,
                    ['name' => 'Times New Roman', 'size' => 10, 'italic' => false, 'color' => '1B2232'],
                    [
                        'alignment'   => Jc::BOTH,
                        'indentation' => ['left' => 2268], // 4 cm
                        'lineHeight'  => 1.0,
                        'spaceBefore' => 60,
                        'spaceAfter'  => 60
                    ]
                );
            }
        }
        $section->addTextBreak(1, ['size' => 6]);
    }

    /**
     * Renderiza o HTML limpo gerado pelo Summernote mantendo recuo de primeira linha e entrelinhas 1.5
     */
    private function renderizarConteudoHtml($section, string $html, bool $semRecuoPrimeiraLinha = false): void
    {
        if (empty(trim($html))) return;

        // Limpeza de tags desnecessárias para evitar quebras no PhpWord
        $htmlTratado = str_ireplace(['<br>', '<br/>', '<br />'], '</p><p>', $html);
        $htmlTratado = strip_tags($htmlTratado, '<p><b><strong><i><em><u>');

        preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $htmlTratado, $matches);
        $paragrafos = !empty($matches[1]) ? $matches[1] : explode("\n", strip_tags($htmlTratado));

        $estiloParagrafo = [
            'alignment'  => Jc::BOTH,
            'lineHeight' => 1.5,
            'spaceAfter' => 120
        ];

        foreach ($paragrafos as $p) {
            $pLimpo = trim(html_entity_decode($p, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $pLimpo = str_replace('&nbsp;', ' ', $pLimpo);

            if (!empty(trim(strip_tags($pLimpo)))) {
                $run = $section->addTextRun($estiloParagrafo);
                if (!$semRecuoPrimeiraLinha) {
                    $run->addText(self::RECUO_PRIMEIRA_LINHA);
                }

                $fragmentoHtml = '<span style="font-family: Times New Roman; font-size: 12pt; color: #000000;">' . $p . '</span>';
                Html::addHtml($run, $fragmentoHtml, false, false);
            }
        }
    }
}

// ==============================================================================
// PROCESSAMENTO DO FORMULÁRIO (POST) E DOWNLOAD DO DOCX
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gerador = new PeticaoWordxGenerator();
    $caminhoArquivo = $gerador->gerarDocumento($_POST);

    if (file_exists($caminhoArquivo)) {
        $nomeDownload = 'Peticao_' . date('Y-m-d_His') . '.docx';
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $nomeDownload . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($caminhoArquivo));
        readfile($caminhoArquivo);
        @unlink($caminhoArquivo);
        exit;
    } else {
        die('Erro ao gerar o arquivo .docx.');
    }
}
