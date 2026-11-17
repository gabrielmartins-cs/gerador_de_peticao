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
                // 1. Título da Tese (higienizado contra 'Enter' e quebras de linha)
                $tituloBruto = strip_tags($tese['titulo'] ?? '');
                // Converte qualquer quebra de linha (\r, \n, tabulações) em um espaço simples
                $tituloLimpo = trim(preg_replace('/\s+/', ' ', $tituloBruto));
                // Remove eventual numeração manual repetida (ex: se a IA já colocou "2.1." ou "2.1 - ")
                $tituloLimpo = preg_replace('/^\d+(\.\d+)*\s*[-–.]?\s*/', '', $tituloLimpo);

                if (!empty($tituloLimpo)) {
                    $numeracao = "{$contadorBloco}.{$subItem}. ";
                    $section->addText(
                        $numeracao . mb_strtoupper($tituloLimpo, 'UTF-8'),
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
                        } elseif ($tipo === 'imagem' && !empty($conteudo)) {
                            $this->adicionarImagemElemento($section, $conteudo, $elem['legenda'] ?? '');
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
     * Insere imagens/prints Base64 com redimensionamento proporcional e legenda
     */
    private function adicionarImagemElemento($section, string $base64Dados, string $legenda = ''): void
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Dados, $tipoImg)) {
            $ext = strtolower($tipoImg[1]);
            $raw = substr($base64Dados, strpos($base64Dados, ',') + 1);
            $binario = base64_decode($raw);

            if ($binario !== false) {
                $arquivoTemp = sys_get_temp_dir() . '/peticao_img_' . uniqid() . '.' . $ext;
                file_put_contents($arquivoTemp, $binario);

                $dimensoes = @getimagesize($arquivoTemp);
                $largura = $dimensoes ? $dimensoes[0] : 400;
                $altura  = $dimensoes ? $dimensoes[1] : 300;

                // Limita a largura máxima para caber na página A4 sem estourar margem (~440pt)
                $larguraMax = 440;
                if ($largura > $larguraMax) {
                    $altura = ($altura * $larguraMax) / $largura;
                    $largura = $larguraMax;
                }

                $section->addTextBreak(1, ['size' => 6]);
                $section->addImage($arquivoTemp, [
                    'width'       => $largura,
                    'height'      => $altura,
                    'alignment'   => Jc::CENTER
                ]);
                @unlink($arquivoTemp);

                // Legenda opcional centralizada em cinza/itálico
                if (!empty(trim($legenda))) {
                    $section->addText(
                        trim($legenda),
                        ['name' => 'Times New Roman', 'size' => 9, 'italic' => true, 'color' => '555555'],
                        ['alignment' => Jc::CENTER, 'spaceBefore' => 60, 'spaceAfter' => 120]
                    );
                } else {
                    $section->addTextBreak(1, ['size' => 6]);
                }
            }
        }
    }
    /**
     * Citação Direta Longa - Estilo Minimalista / Legal Design:
     * - Caixa com borda lateral azul sóbrio (1E3A5F)
     * - Fundo suave (F8FAFC)
     * - Recuo de 4 cm à esquerda (2268 twips)
     * - Fonte tamanho 9.5 em tom grafite
     */
    private function adicionarCitacaoLonga($section, string $textoCitacao): void
    {
        // Tabela de 1 célula para criar a caixa com cor de fundo e borda lateral
        $tabela = $section->addTable([
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::START,
            'indent'    => new \PhpOffice\PhpWord\ComplexType\TblWidth(2268, 'dxa'), // Recuo de 4 cm
        ]);

        $celula = $tabela->addRow()->addCell(6800, [
            'bgColor'          => 'F8FAFC',
            'borderLeftSize'   => 18,          // ~2.5pt de espessura
            'borderLeftColor'  => '1E3A5F',    // Azul marinho/ardósia
            'borderTopSize'    => 0,
            'borderRightSize'  => 0,
            'borderBottomSize' => 0,
            'paddingTop'       => 120,
            'paddingBottom'    => 120,
            'paddingLeft'      => 180,
            'paddingRight'     => 120,
        ]);

        // Renderiza o HTML da citação preservando marca-texto, cores e negrito
        $htmlTratado = str_ireplace(['<br>', '<br/>', '<br />'], '</p><p>', $textoCitacao);
        $htmlTratado = strip_tags($htmlTratado, '<p><b><strong><i><em><u><span><font><mark>');

        preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $htmlTratado, $matches);
        $paragrafos = !empty($matches[1]) ? $matches[1] : explode("\n", $htmlTratado);

        $estiloCitacao = [
            'alignment'   => Jc::BOTH,
            'lineHeight'  => 1.15,
            'spaceBefore' => 30,
            'spaceAfter'  => 30
        ];

        foreach ($paragrafos as $p) {
            $pLimpo = trim(strip_tags($p));
            if (!empty($pLimpo)) {
                $run = $celula->addTextRun($estiloCitacao);
                $fragmentoHtml = '<span style="font-family: Times New Roman; font-size: 9.5pt; color: #2D3748;">' . $p . '</span>';
                Html::addHtml($run, $fragmentoHtml, false, false);
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

        // Preserva tags de cor, estilo e marca-texto (span, font, mark)
        $htmlTratado = str_ireplace(['<br>', '<br/>', '<br />'], '</p><p>', $html);
        $htmlTratado = strip_tags($htmlTratado, '<p><b><strong><i><em><u><span><font><mark>');

        preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $htmlTratado, $matches);
        $paragrafos = !empty($matches[1]) ? $matches[1] : explode("\n", $htmlTratado);

        $estiloParagrafo = [
            'alignment'  => Jc::BOTH,
            'lineHeight' => 1.5,
            'spaceAfter' => 120
        ];

        foreach ($paragrafos as $p) {
            $pLimpo = trim(html_entity_decode(strip_tags($p), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $pLimpo = str_replace('&nbsp;', ' ', $pLimpo);

            if (!empty($pLimpo)) {
                $run = $section->addTextRun($estiloParagrafo);
                if (!$semRecuoPrimeiraLinha) {
                    $run->addText(self::RECUO_PRIMEIRA_LINHA);
                }

                $fragmentoHtml = '<span style="font-family: Times New Roman; font-size: 12pt;">' . $p . '</span>';
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
