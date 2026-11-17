<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elaborador de Peças e Dossiês Jurídicos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Summernote Lite (WYSIWYG sem necessidade de API key) -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #f1f4f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 80px;
        }

        .card {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            margin-bottom: 22px;
        }

        .card-header-main {
            background-color: #1b2232;
            color: #fff;
            font-weight: 600;
            font-size: 1.05rem;
        }

        .bloco-card {
            border-left: 5px solid #0d6efd;
            background-color: #fff;
            margin-bottom: 20px;
        }

        .tese-card {
            border-left: 4px solid #198754;
            background-color: #fafbfc;
            margin-bottom: 15px;
        }

        .citacao-box {
            background-color: #f8fafc;
            border-left: 4px solid #1e3a5f;
            border-top: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 16px;
            border-radius: 0 6px 6px 0;
        }

        /* Estilos de Arraste e Minimização */
        .cursor-grab {
            cursor: grab;
        }

        .cursor-grab:active {
            cursor: grabbing;
        }

        .sortable-ghost {
            opacity: 0.35;
            background-color: #e9ecef !important;
            border: 2px dashed #0d6efd !important;
        }

        .cabecalho-bloco,
        .cabecalho-tese {
            user-select: none;
        }

        .titulo-resumo-tese {
            max-width: 450px;
            color: #198754;
            font-weight: 600;
        }
    </style>
    <!-- SortableJS para Arrastar e Organizar -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>

<body>

    <div class="container mt-4">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-dark">Gerador de Peças & Dossiês</h2>
            <p class="text-muted">Estruturação forense padronizada (ABNT / Prática Cível e Penal)</p>

            <!-- Barra de Ações JSON Global -->
            <div class="d-flex justify-content-center gap-2 flex-wrap mt-2">
                <input type="file" id="inputJsonCompleto" accept=".json" class="d-none">
                <input type="file" id="inputJsonPreambulo" accept=".json" class="d-none">

                <button type="button" class="btn btn-sm btn-outline-dark shadow-sm" id="btnExportarTudo">
                    <i class="bi bi-download me-1"></i> Exportar Tudo (JSON Completo)
                </button>
                <button type="button" class="btn btn-sm btn-dark shadow-sm btnAbrirModal" data-tipo="completo">
                    <i class="bi bi-upload me-1"></i> Importar Tudo (JSON Completo)
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary shadow-sm" id="btnExportarPreambulo">
                    <i class="bi bi-file-earmark-arrow-down me-1"></i> Exportar Preâmbulo/Fatos
                </button>
                <button type="button" class="btn btn-sm btn-secondary shadow-sm btnAbrirModal" data-tipo="preambulo">
                    <i class="bi bi-file-earmark-arrow-up me-1"></i> Importar Preâmbulo/Fatos
                </button>
            </div>
        </div>

        <form action="gerar_word.php" method="POST" id="formDossie">

            <!-- 1. ENDEREÇAMENTO -->
            <div class="card">
                <div class="card-header card-header-main"><i class="bi bi-geo-alt-fill me-2"></i>1. Endereçamento</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Endereçamento (Juízo / Tribunal):</label>
                            <input type="text" name="enderecamento" class="form-control"
                                value="EXCELENTÍSSIMO(A) SENHOR(A) DOUTOR(A) JUIZ(A) DE DIREITO DA ____ª VARA CÍVEL DA COMARCA DE ..." required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. PARTES E NOME DA AÇÃO -->
            <div class="card">
                <div class="card-header card-header-main"><i class="bi bi-people-fill me-2"></i>2. Qualificação das Partes e Peça</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Qualificação do Autor / Requerente / Querelante:</label>
                            <textarea name="qualificacao_autor" class="form-control wysiwyg" rows="3" placeholder="Nome completo, nacionalidade, estado civil, profissão, portador do RG nº..., inscrito no CPF sob o nº..., residente e domiciliado em..."></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-primary">Nome da Ação / Peça Processual:</label>
                            <input type="text" name="nome_acao" class="form-control form-control-lg fw-bold text-uppercase" placeholder="Ex: CONTESTAÇÃO / AÇÃO ORDINÁRIA / QUEIXA-CRIME" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Qualificação do Réu / Requerido / Querelado:</label>
                            <textarea name="qualificacao_reu" class="form-control wysiwyg" rows="3" placeholder="Nome completo/razão social, pessoa jurídica/física, inscrito no CPF/CNPJ..., com endereço em..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. DOS FATOS -->
            <div class="card">
                <div class="card-header card-header-main"><i class="bi bi-card-text me-2"></i>3. Dos Fatos (Síntese da Demanda)</div>
                <div class="card-body">
                    <textarea name="dos_fatos" class="form-control wysiwyg" rows="6" placeholder="Narre aqui os acontecimentos fáticos detalhados..."></textarea>
                </div>
            </div>

            <!-- 4. TESES JURÍDICAS ORGANIZADAS EM BLOCOS -->
            <div class="card">
                <div class="card-header card-header-main d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-journal-bookmark-fill me-2"></i>4. Da Fundamentação Jurídica (Teses em Blocos)</span>
                    <button type="button" class="btn btn-sm btn-light fw-bold text-dark" id="btnAdicionarBloco">
                        <i class="bi bi-plus-circle-fill text-primary"></i> Adicionar Bloco de Teses
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Crie blocos estruturados (Preliminares, Prejudiciais, Mérito ou Subsidiárias) e insira teses divididas entre parágrafos explicativos e citações doutrinárias/jurisprudenciais longas.</p>
                    <div id="containerBlocos">
                        <!-- Blocos são inseridos dinamicamente aqui -->
                    </div>
                </div>
            </div>

            <!-- 5. CONCLUSÃO E PEDIDOS -->
            <div class="card">
                <div class="card-header card-header-main d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-check2-circle me-2"></i>5. Dos Pedidos e Requerimentos Finais</span>
                    <div class="d-flex gap-2">
                        <input type="file" id="inputJsonPedidos" accept=".json" class="d-none">
                        <button type="button" class="btn btn-sm btn-light py-0 px-2" id="btnExportarPedidos" title="Exportar apenas esta seção">
                            <i class="bi bi-download"></i> Exportar Pedidos
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light py-0 px-2 btnAbrirModal" data-tipo="pedidos" title="Importar apenas esta seção">
                            <i class="bi bi-upload"></i> Importar Pedidos
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <textarea name="conclusao_pedidos" class="form-control wysiwyg" rows="5" placeholder="Diante do exposto, requer a Vossa Excelência: a) O acolhimento da preliminar... b) No mérito, a total improcedência..."></textarea>
                </div>
            </div>

            <div class="text-center mt-4 d-flex justify-content-center gap-3">
                <button type="submit" formaction="gerar_word.php" class="btn btn-primary btn-lg px-4 shadow">
                    <i class="bi bi-file-earmark-word me-2"></i>Gerar Word (.docx)
                </button>
                <button type="submit" formaction="gerar_pdf.php" class="btn btn-danger btn-lg px-4 shadow">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Gerar PDF
                </button>
            </div>
        </form>

        <!-- MODAL DE IMPORTAÇÃO COM PROMPT PARA IA -->
        <div class="modal fade" id="modalImportarJson" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title" id="modalTituloImportacao"><i class="bi bi-robot me-2"></i>Importar Conteúdo via IA (JSON)</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- 1. CAIXA DO PROMPT PARA COPIAR -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold small text-primary mb-0">
                                    <i class="bi bi-clipboard-check me-1"></i> 1º Copie o Prompt abaixo e envie para a sua IA (ChatGPT/Claude/Gemini):
                                </label>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0" id="btnCopiarPrompt">
                                    <i class="bi bi-copy me-1"></i> Copiar Prompt
                                </button>
                            </div>
                            <textarea id="textoPromptIa" class="form-control form-control-sm bg-light font-monospace" rows="6" readonly></textarea>
                            <span id="feedbackCopia" class="text-success small fw-bold d-none"><i class="bi bi-check-circle"></i> Prompt copiado para a área de transferência!</span>
                        </div>

                        <!-- 2. CAIXA PARA COLAR O JSON GERADO -->
                        <div class="mb-2">
                            <label class="form-label fw-bold small text-success mb-1">
                                <i class="bi bi-code-slash me-1"></i> 2º Cole aqui o JSON retornado pela IA:
                            </label>
                            <textarea id="textareaColarJson" class="form-control font-monospace" rows="7" placeholder='Cole o código JSON aqui (ex: { "enderecamento": "...", ... })'></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success fw-bold" id="btnProcessarJsonColado">
                            <i class="bi bi-check2-circle me-1"></i> Carregar Dados na Peça
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DE IMPORTAÇÃO COM PROMPT PARA IA -->
        <div class="modal fade" id="modalImportarJson" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title" id="modalTituloImportacao"><i class="bi bi-robot me-2"></i>Importar Conteúdo via IA (JSON)</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- 1. CAIXA DO PROMPT PARA COPIAR -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold small text-primary mb-0">
                                    <i class="bi bi-clipboard-check me-1"></i> 1º Copie o Prompt abaixo e envie para a sua IA (ChatGPT/Claude/Gemini):
                                </label>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0" id="btnCopiarPrompt">
                                    <i class="bi bi-copy me-1"></i> Copiar Prompt
                                </button>
                            </div>
                            <textarea id="textoPromptIa" class="form-control form-control-sm bg-light font-monospace" rows="6" readonly></textarea>
                            <span id="feedbackCopia" class="text-success small fw-bold d-none"><i class="bi bi-check-circle"></i> Prompt copiado para a área de transferência!</span>
                        </div>

                        <!-- 2. CAIXA PARA COLAR O JSON GERADO -->
                        <div class="mb-2">
                            <label class="form-label fw-bold small text-success mb-1">
                                <i class="bi bi-code-slash me-1"></i> 2º Cole aqui o JSON retornado pela IA:
                            </label>
                            <textarea id="textareaColarJson" class="form-control font-monospace" rows="7" placeholder='Cole o código JSON aqui (ex: { "enderecamento": "...", ... })'></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success fw-bold" id="btnProcessarJsonColado">
                            <i class="bi bi-check2-circle me-1"></i> Carregar Dados na Peça
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DE IMPORTAÇÃO COM PROMPT PARA IA -->
        <div class="modal fade" id="modalImportarJson" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title" id="modalTituloImportacao"><i class="bi bi-robot me-2"></i>Importar Conteúdo via IA (JSON)</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- 1. CAIXA DO PROMPT PARA COPIAR -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold small text-primary mb-0">
                                    <i class="bi bi-clipboard-check me-1"></i> 1º Copie o Prompt abaixo e envie para a sua IA (ChatGPT/Claude/Gemini):
                                </label>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0" id="btnCopiarPrompt">
                                    <i class="bi bi-copy me-1"></i> Copiar Prompt
                                </button>
                            </div>
                            <textarea id="textoPromptIa" class="form-control form-control-sm bg-light font-monospace" rows="6" readonly></textarea>
                            <span id="feedbackCopia" class="text-success small fw-bold d-none"><i class="bi bi-check-circle"></i> Prompt copiado para a área de transferência!</span>
                        </div>

                        <!-- 2. CAIXA PARA COLAR O JSON GERADO -->
                        <div class="mb-2">
                            <label class="form-label fw-bold small text-success mb-1">
                                <i class="bi bi-code-slash me-1"></i> 2º Cole aqui o JSON retornado pela IA:
                            </label>
                            <textarea id="textareaColarJson" class="form-control font-monospace" rows="7" placeholder='Cole o código JSON aqui (ex: { "enderecamento": "...", ... })'></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success fw-bold" id="btnProcessarJsonColado">
                            <i class="bi bi-check2-circle me-1"></i> Carregar Dados na Peça
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS: JQuery, Bootstrap e Summernote -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    <script>
        let contadorBloco = 0;

        function iniciarWysiwyg(seletor, altura = 140) {
            $(seletor).summernote({
                height: altura,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']], // << Habilita Marca-Texto (fundo) e Cor da Fonte
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['view', ['codeview']]
                ],
                placeholder: 'Digite ou cole o conteúdo formatado aqui...'
            });
        }

        $(document).ready(function() {
            iniciarWysiwyg('.wysiwyg');

            // Adiciona um Bloco de Teses
            $('#btnAdicionarBloco').click(function() {
                contadorBloco++;
                const blocoId = contadorBloco;

                const blocoHtml = `
            <div class="card bloco-card p-3 shadow-sm" id="bloco_${blocoId}">
                <div class="d-flex justify-content-between align-items-center mb-2 cabecalho-bloco">
                    <div class="d-flex align-items-center gap-2 flex-grow-1 me-2">
                        <span class="btn btn-sm btn-light p-1 cursor-grab handle-drag-bloco text-muted" title="Segure e arraste para reordenar este Bloco">
                            <i class="bi bi-grip-vertical fs-5"></i>
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btnToggleBloco" title="Minimizar / Expandir Bloco">
                            <i class="bi bi-chevron-down icone-toggle-bloco"></i>
                        </button>
                        <span class="badge bg-primary fs-6">Bloco</span>
                        <select name="blocos[${blocoId}][categoria]" class="form-select form-select-sm fw-bold w-auto">
                            <option value="PRELIMINARES">DAS PRELIMINARES</option>
                            <option value="PREJUDICIAIS">DAS PREJUDICIAIS DE MÉRITO</option>
                            <option value="MERITO" selected>DO MÉRITO</option>
                            <option value="SUBSIDIARIAS">DOS PEDIDOS SUBSIDIÁRIOS</option>
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-sm btn-outline-success btnAdicionarTese" data-bloco="${blocoId}" title="Adicionar Tese a este Bloco">
                            <i class="bi bi-plus-lg"></i> Adicionar Tese
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger btnRemoverBloco" data-bloco="${blocoId}" title="Excluir Bloco Inteiro">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="corpo-bloco mt-2">
                    <div class="containerTeses" id="containerTeses_${blocoId}">
                        <!-- Teses pertencentes a este bloco serão inseridas aqui -->
                    </div>
                </div>
            </div>`;

                $('#containerBlocos').append(blocoHtml);
                // Ativa a capacidade de arrastar teses dentro deste novo bloco
                ativarSortableTeses(document.getElementById(`containerTeses_${blocoId}`));
                // Cria uma tese inicial por padrão dentro do novo bloco
                adicionarTese(blocoId);
            });

            // Evento para remover bloco
            $(document).on('click', '.btnRemoverBloco', function() {
                const bId = $(this).data('bloco');
                $(`#bloco_${bId}`).remove();
            });

            // Evento para adicionar tese
            $(document).on('click', '.btnAdicionarTese', function() {
                const bId = $(this).data('bloco');
                adicionarTese(bId);
            });

            // Evento para remover tese
            $(document).on('click', '.btnRemoverTese', function() {
                $(this).closest('.tese-card').remove();
            });

            function adicionarTese(blocoId) {
                const teseId = Date.now();
                const teseHtml = `
            <div class="card tese-card p-3 shadow-sm" id="tese_${teseId}">
                <div class="d-flex justify-content-between align-items-center mb-2 cabecalho-tese">
                    <div class="d-flex align-items-center gap-2 flex-grow-1 me-2 overflow-hidden">
                        <span class="btn btn-sm btn-light p-1 cursor-grab handle-drag-tese text-muted" title="Segure e arraste para reordenar esta Tese">
                            <i class="bi bi-grip-vertical fs-5"></i>
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btnToggleTese" title="Minimizar / Expandir Tese">
                            <i class="bi bi-chevron-down icone-toggle-tese"></i>
                        </button>
                        <span class="fw-bold text-success small text-nowrap"><i class="bi bi-file-text me-1"></i>Tese:</span>
                        <span class="titulo-resumo-tese text-truncate small" title="Clique para expandir/ver">(Sem título definido)</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <input type="file" class="inputJsonTese d-none" accept=".json">
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btnExportarTese" title="Exportar esta Tese em JSON">
                            <i class="bi bi-download"></i> Exportar
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 btnImportarTese" title="Importar dados para esta Tese">
                            <i class="bi bi-upload"></i> Importar
                        </button>
                        <button type="button" class="btn btn-sm text-danger btnRemoverTese p-0 ms-2" title="Excluir Tese"><i class="bi bi-x-circle-fill fs-5"></i></button>
                    </div>
                </div>

                <div class="corpo-tese mt-2">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Título da Tese:</label>
                        <input type="text" name="blocos[${blocoId}][teses][${teseId}][titulo]" class="form-control form-control-sm fw-bold input-titulo-tese" placeholder="Ex: Da Ausência de Interesse Processual">
                    </div>
                    
                    <!-- Container de Blocos de Conteúdo Ordenáveis -->
                    <div class="containerElementos mb-2" id="elementos_${blocoId}_${teseId}"></div>

                    <!-- Botões para adicionar Parágrafos, Citações ou Imagens onde desejar -->
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-primary btnAddElemento" data-bloco="${blocoId}" data-tese="${teseId}" data-tipo="texto">
                            <i class="bi bi-plus-lg"></i> Parágrafo Normal
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning text-dark btnAddElemento" data-bloco="${blocoId}" data-tese="${teseId}" data-tipo="citacao">
                            <i class="bi bi-quote"></i> Citação Longa (ABNT)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info text-dark btnAddElemento" data-bloco="${blocoId}" data-tese="${teseId}" data-tipo="imagem">
                            <i class="bi bi-image"></i> Inserir Imagem / Print
                        </button>
                    </div>
                </div>
            </div>`;

                $(`#containerTeses_${blocoId}`).append(teseHtml);
                // Ativa a reordenação por arraste nos elementos internos (parágrafos, citações e imagens)
                ativarSortableElementos(document.getElementById(`elementos_${blocoId}_${teseId}`));
                // Inicia por padrão com 1 parágrafo normal
                adicionarElementoConteudo(blocoId, teseId, 'texto');
            }

            // Função para criar o elemento (texto ou citação)
            function adicionarElementoConteudo(blocoId, teseId, tipo) {
                const elemId = Date.now() + Math.floor(Math.random() * 1000);
                let campoHtml = '';

                if (tipo === 'texto') {
                    campoHtml = `
                    <div class="item-elemento card mb-2 p-2 border">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-secondary">Parágrafo Comum</span>
                            <div>
                                <button type="button" class="btn btn-sm btn-light btnMoverCima" title="Mover para Cima"><i class="bi bi-arrow-up"></i></button>
                                <button type="button" class="btn btn-sm btn-light btnMoverBaixo" title="Mover para Baixo"><i class="bi bi-arrow-down"></i></button>
                                <button type="button" class="btn btn-sm text-danger btnRemoverElemento"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                        <input type="hidden" name="blocos[${blocoId}][teses][${teseId}][elementos][${elemId}][tipo]" value="texto">
                        <textarea name="blocos[${blocoId}][teses][${teseId}][elementos][${elemId}][conteudo]" class="form-control wysiwyg-dinamico" rows="3"></textarea>
                    </div>`;
                } else if (tipo === 'citacao') {
                    campoHtml = `
                    <div class="item-elemento citacao-box mb-2 p-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-warning text-dark"><i class="bi bi-quote"></i> Citação Longa Direta (Editável com Cores e Marca-texto)</span>
                            <div>
                                <button type="button" class="btn btn-sm btn-light btnMoverCima" title="Mover para Cima"><i class="bi bi-arrow-up"></i></button>
                                <button type="button" class="btn btn-sm btn-light btnMoverBaixo" title="Mover para Baixo"><i class="bi bi-arrow-down"></i></button>
                                <button type="button" class="btn btn-sm text-danger btnRemoverElemento"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                        <input type="hidden" name="blocos[${blocoId}][teses][${teseId}][elementos][${elemId}][tipo]" value="citacao">
                        <textarea name="blocos[${blocoId}][teses][${teseId}][elementos][${elemId}][conteudo]" class="form-control wysiwyg-dinamico" rows="3"></textarea>
                    </div>`;
                } else if (tipo === 'imagem') {
                    campoHtml = `
                    <div class="item-elemento card mb-2 p-2 border-info shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-info text-dark"><i class="bi bi-image me-1"></i>Imagem / Print (Visual Law)</span>
                            <div>
                                <button type="button" class="btn btn-sm btn-light btnMoverCima" title="Mover para Cima"><i class="bi bi-arrow-up"></i></button>
                                <button type="button" class="btn btn-sm btn-light btnMoverBaixo" title="Mover para Baixo"><i class="bi bi-arrow-down"></i></button>
                                <button type="button" class="btn btn-sm text-danger btnRemoverElemento"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                        <input type="hidden" name="blocos[${blocoId}][teses][${teseId}][elementos][${elemId}][tipo]" value="imagem">
                        <input type="hidden" name="blocos[${blocoId}][teses][${teseId}][elementos][${elemId}][conteudo]" class="input-imagem-base64">
                        
                        <div class="row g-2 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold mb-1">Arquivo da Imagem (PNG, JPG, WebP):</label>
                                <input type="file" class="form-control form-control-sm input-escolher-imagem" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold mb-1">Legenda da Imagem (opcional):</label>
                                <input type="text" name="blocos[${blocoId}][teses][${teseId}][elementos][${elemId}][legenda]" class="form-control form-control-sm input-legenda-imagem" placeholder="Ex: Print da conversa de WhatsApp / Comprovante PIX">
                            </div>
                        </div>
                        <div class="preview-imagem-container mt-2 text-center d-none">
                            <img src="" class="img-thumbnail" style="max-height: 180px; object-fit: contain;">
                        </div>
                    </div>`;
                }

                const $container = $(`#elementos_${blocoId}_${teseId}`);
                $container.append(campoHtml);

                if (tipo === 'texto' || tipo === 'citacao') {
                    iniciarWysiwyg($container.find('.wysiwyg-dinamico').last(), tipo === 'citacao' ? 110 : 140);
                }
            }

            // Adicionar elemento pelo clique do botão
            $(document).on('click', '.btnAddElemento', function() {
                const bId = $(this).data('bloco');
                const tId = $(this).data('tese');
                const tipo = $(this).data('tipo');
                adicionarElementoConteudo(bId, tId, tipo);
            });

            // Remover elemento
            $(document).on('click', '.btnRemoverElemento', function() {
                $(this).closest('.item-elemento').remove();
            });

            // Carregamento e Preview da Imagem em Base64
            $(document).on('change', '.input-escolher-imagem', function() {
                const file = this.files[0];
                if (!file) return;

                const $card = $(this).closest('.item-elemento');
                const $hidden = $card.find('.input-imagem-base64');
                const $previewContainer = $card.find('.preview-imagem-container');
                const $previewImg = $previewContainer.find('img');

                const reader = new FileReader();
                reader.onload = function(e) {
                    $hidden.val(e.target.result);
                    $previewImg.attr('src', e.target.result);
                    $previewContainer.removeClass('d-none');
                };
                reader.readAsDataURL(file);
            });

            // Mover elemento para cima (▲)
            $(document).on('click', '.btnMoverCima', function() {
                const $item = $(this).closest('.item-elemento');
                const $anterior = $item.prev('.item-elemento');
                if ($anterior.length > 0) {
                    $item.insertBefore($anterior);
                }
            });

            // Mover elemento para baixo (▼)
            $(document).on('click', '.btnMoverBaixo', function() {
                const $item = $(this).closest('.item-elemento');
                const $proximo = $item.next('.item-elemento');
                if ($proximo.length > 0) {
                    $item.insertAfter($proximo);
                }
            });

            // ========================================================
            // UTILITÁRIOS PARA IMPORTAÇÃO E EXPORTAÇÃO JSON
            // ========================================================
            function baixarArquivoJson(objeto, nomeArquivo) {
                const blob = new Blob([JSON.stringify(objeto, null, 2)], {
                    type: 'application/json'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = nomeArquivo;
                a.click();
                URL.revokeObjectURL(url);
            }

            function lerArquivoJson(inputElement, callback) {
                const file = inputElement.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const json = JSON.parse(e.target.result);
                        callback(json);
                    } catch (err) {
                        alert('Erro: Arquivo JSON inválido!');
                    }
                    inputElement.value = ''; // Limpa para permitir subir o mesmo arquivo novamente
                };
                reader.readAsText(file);
            }

            // 1. EXPORTAR / IMPORTAR TUDO (COMPLETO)
            $('#btnExportarTudo').click(function() {
                const dados = {
                    enderecamento: $('input[name="enderecamento"]').val(),
                    qualificacao_autor: $('textarea[name="qualificacao_autor"]').summernote('code'),
                    nome_acao: $('input[name="nome_acao"]').val(),
                    qualificacao_reu: $('textarea[name="qualificacao_reu"]').summernote('code'),
                    dos_fatos: $('textarea[name="dos_fatos"]').summernote('code'),
                    conclusao_pedidos: $('textarea[name="conclusao_pedidos"]').summernote('code'),
                    blocos: []
                };

                $('.bloco-card').each(function() {
                    const bloco = {
                        categoria: $(this).find('select[name*="[categoria]"]').val(),
                        teses: []
                    };

                    $(this).find('.tese-card').each(function() {
                        const tese = {
                            titulo: $(this).find('input[name*="[titulo]"]').val(),
                            elementos: []
                        };

                        $(this).find('.item-elemento').each(function() {
                            const tipo = $(this).find('input[type="hidden"][name*="[tipo]"]').val();
                            let conteudo = '';
                            let legenda = '';
                            if (tipo === 'texto' || tipo === 'citacao') {
                                conteudo = $(this).find('textarea').summernote('code');
                            } else if (tipo === 'imagem') {
                                conteudo = $(this).find('.input-imagem-base64').val();
                                legenda = $(this).find('.input-legenda-imagem').val();
                            }
                            tese.elementos.push({
                                tipo,
                                conteudo,
                                legenda
                            });
                        });

                        bloco.teses.push(tese);
                    });

                    dados.blocos.push(bloco);
                });

                baixarArquivoJson(dados, 'peticao_completa_' + Date.now() + '.json');
            });

            $('#inputJsonCompleto').change(function() {
                lerArquivoJson(this, function(dados) {
                    if (dados.enderecamento !== undefined) $('input[name="enderecamento"]').val(dados.enderecamento);
                    if (dados.qualificacao_autor !== undefined) $('textarea[name="qualificacao_autor"]').summernote('code', dados.qualificacao_autor);
                    if (dados.nome_acao !== undefined) $('input[name="nome_acao"]').val(dados.nome_acao);
                    if (dados.qualificacao_reu !== undefined) $('textarea[name="qualificacao_reu"]').summernote('code', dados.qualificacao_reu);
                    if (dados.dos_fatos !== undefined) $('textarea[name="dos_fatos"]').summernote('code', dados.dos_fatos);
                    if (dados.conclusao_pedidos !== undefined) $('textarea[name="conclusao_pedidos"]').summernote('code', dados.conclusao_pedidos);

                    if (dados.blocos && Array.isArray(dados.blocos)) {
                        $('#containerBlocos').empty();
                        dados.blocos.forEach(blocoData => {
                            contadorBloco++;
                            const bId = contadorBloco;
                            const blocoHtml = `
                            <div class="card bloco-card p-3" id="bloco_${bId}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center gap-2 flex-grow-1 me-3">
                                        <span class="badge bg-primary fs-6">Bloco</span>
                                        <select name="blocos[${bId}][categoria]" class="form-select form-select-sm fw-bold w-auto">
                                            <option value="PRELIMINARES" ${blocoData.categoria === 'PRELIMINARES' ? 'selected' : ''}>DAS PRELIMINARES</option>
                                            <option value="PREJUDICIAIS" ${blocoData.categoria === 'PREJUDICIAIS' ? 'selected' : ''}>DAS PREJUDICIAIS DE MÉRITO</option>
                                            <option value="MERITO" ${blocoData.categoria === 'MERITO' ? 'selected' : ''}>DO MÉRITO</option>
                                            <option value="SUBSIDIARIAS" ${blocoData.categoria === 'SUBSIDIARIAS' ? 'selected' : ''}>DOS PEDIDOS SUBSIDIÁRIOS</option>
                                        </select>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-success btnAdicionarTese" data-bloco="${bId}">
                                            <i class="bi bi-plus-lg"></i> Adicionar Tese
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btnRemoverBloco" data-bloco="${bId}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="containerTeses" id="containerTeses_${bId}"></div>
                            </div>`;
                            $('#containerBlocos').append(blocoHtml);

                            if (blocoData.teses && Array.isArray(blocoData.teses)) {
                                blocoData.teses.forEach(teseData => {
                                    const tId = Date.now() + Math.floor(Math.random() * 1000);
                                    const teseHtml = `
                                    <div class="card tese-card p-3" id="tese_${tId}">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-success small"><i class="bi bi-file-text me-1"></i>Tese Jurídica</span>
                                            <div class="d-flex align-items-center gap-1">
                                                <input type="file" class="inputJsonTese d-none" accept=".json">
                                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btnExportarTese" title="Exportar esta Tese em JSON">
                                                    <i class="bi bi-download"></i> Exportar Tese
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 btnImportarTese" title="Importar dados para esta Tese">
                                                    <i class="bi bi-upload"></i> Importar Tese
                                                </button>
                                                <button type="button" class="btn btn-sm text-danger btnRemoverTese p-0 ms-2"><i class="bi bi-x-circle-fill fs-5"></i></button>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Título da Tese:</label>
                                            <input type="text" name="blocos[${bId}][teses][${tId}][titulo]" class="form-control form-control-sm fw-bold" value="${teseData.titulo || ''}">
                                        </div>
                                        <div class="containerElementos mb-2" id="elementos_${bId}_${tId}"></div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary btnAddElemento" data-bloco="${bId}" data-tese="${tId}" data-tipo="texto">
                                                <i class="bi bi-plus-lg"></i> Parágrafo Normal
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark btnAddElemento" data-bloco="${bId}" data-tese="${tId}" data-tipo="citacao">
                                                <i class="bi bi-quote"></i> Citação Longa (ABNT)
                                            </button>
                                        </div>
                                    </div>`;
                                    $(`#containerTeses_${bId}`).append(teseHtml);

                                    if (teseData.elementos && Array.isArray(teseData.elementos)) {
                                        teseData.elementos.forEach(el => {
                                            adicionarElementoConteudo(bId, tId, el.tipo);
                                            const $ultimo = $(`#elementos_${bId}_${tId} .item-elemento`).last();
                                            if (el.tipo === 'texto') {
                                                $ultimo.find('textarea').summernote('code', el.conteudo || '');
                                            } else {
                                                $ultimo.find('textarea').val(el.conteudo || '');
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    }
                });
            });

            // 2. EXPORTAR / IMPORTAR PREÂMBULO E FATOS (PARCIAL)
            $('#btnExportarPreambulo').click(function() {
                const preambulo = {
                    tipo_parcial: 'preambulo_fatos',
                    enderecamento: $('input[name="enderecamento"]').val(),
                    qualificacao_autor: $('textarea[name="qualificacao_autor"]').summernote('code'),
                    nome_acao: $('input[name="nome_acao"]').val(),
                    qualificacao_reu: $('textarea[name="qualificacao_reu"]').summernote('code'),
                    dos_fatos: $('textarea[name="dos_fatos"]').summernote('code')
                };
                baixarArquivoJson(preambulo, 'preambulo_fatos_' + Date.now() + '.json');
            });

            $('#inputJsonPreambulo').change(function() {
                lerArquivoJson(this, function(dados) {
                    if (dados.enderecamento !== undefined) $('input[name="enderecamento"]').val(dados.enderecamento);
                    if (dados.qualificacao_autor !== undefined) $('textarea[name="qualificacao_autor"]').summernote('code', dados.qualificacao_autor);
                    if (dados.nome_acao !== undefined) $('input[name="nome_acao"]').val(dados.nome_acao);
                    if (dados.qualificacao_reu !== undefined) $('textarea[name="qualificacao_reu"]').summernote('code', dados.qualificacao_reu);
                    if (dados.dos_fatos !== undefined) $('textarea[name="dos_fatos"]').summernote('code', dados.dos_fatos);
                });
            });

            // 3. EXPORTAR / IMPORTAR PEDIDOS (PARCIAL)
            $('#btnExportarPedidos').click(function() {
                const pedidos = {
                    tipo_parcial: 'pedidos',
                    conclusao_pedidos: $('textarea[name="conclusao_pedidos"]').summernote('code')
                };
                baixarArquivoJson(pedidos, 'pedidos_' + Date.now() + '.json');
            });

            $('#inputJsonPedidos').change(function() {
                lerArquivoJson(this, function(dados) {
                    if (dados.conclusao_pedidos !== undefined) {
                        $('textarea[name="conclusao_pedidos"]').summernote('code', dados.conclusao_pedidos);
                    }
                });
            });

            // 4. EXPORTAR / IMPORTAR UMA TESE ESPECÍFICA (PARCIAL)
            $(document).on('click', '.btnExportarTese', function() {
                const $tese = $(this).closest('.tese-card');
                const tese = {
                    tipo_parcial: 'tese_individual',
                    titulo: $tese.find('input[name*="[titulo]"]').val(),
                    elementos: []
                };

                $tese.find('.item-elemento').each(function() {
                    const tipo = $(this).find('input[type="hidden"]').val();
                    let conteudo = '';
                    if (tipo === 'texto') {
                        conteudo = $(this).find('textarea').summernote('code');
                    } else {
                        conteudo = $(this).find('textarea').val();
                    }
                    tese.elementos.push({
                        tipo,
                        conteudo
                    });
                });

                baixarArquivoJson(tese, 'tese_' + (tese.titulo ? tese.titulo.substring(0, 15).replace(/\s+/g, '_') : 'individual') + '.json');
            });

            // ========================================================
            // MODAL INTELIGENTE DE IMPORTAÇÃO (PROMPT + COLAR JSON)
            // ========================================================
            let contextoImportacao = 'completo';
            let $teseAlvoContexto = null;
            const modalImportacao = new bootstrap.Modal(document.getElementById('modalImportarJson'));

            // Prompts especializados para cada tipo de importação
            const PROMPTS = {
                completo: `Aja como um redator forense especializado. Crie uma petição completa e retorne ESTRITAMENTE um objeto JSON válido (sem textos antes ou depois, sem aspas markdown adicionais), seguindo rigorosamente esta estrutura:
{
  "enderecamento": "EXCELENTÍSSIMO(A) SENHOR(A) DOUTOR(A) JUIZ(A) DE DIREITO DA ...",
  "qualificacao_autor": "<p>Nome do Autor, qualificação completa...</p>",
  "nome_acao": "NOME DA PEÇA EM CAIXA ALTA",
  "qualificacao_reu": "<p>Nome do Réu, qualificação completa...</p>",
  "dos_fatos": "<p>Parágrafos detalhados contando os fatos...</p>",
  "blocos": [
    {
      "categoria": "PRELIMINARES", 
      "teses": [
        {
          "titulo": "Da Inépcia da Inicial",
          "elementos": [
            { "tipo": "texto", "conteudo": "<p>Argumentação jurídica do parágrafo comum...</p>" },
            { "tipo": "citacao", "conteudo": "Jurisprudência ou citação direta longa com mais de 3 linhas..." }
          ]
        }
      ]
    },
    {
      "categoria": "MERITO",
      "teses": [
        {
          "titulo": "Da Inexistência de Dano Moral",
          "elementos": [
            { "tipo": "texto", "conteudo": "<p>Fundamentação fática e de direito...</p>" }
          ]
        }
      ]
    }
  ],
  "conclusao_pedidos": "<p>Diante do exposto, requer: a) ...; b) ...</p>"
}
Categorias válidas para blocos: PRELIMINARES, PREJUDICIAIS, MERITO, SUBSIDIARIAS.
Tipos de elementos válidos: "texto" (parágrafo normal HTML) ou "citacao" (citação direta longa sem tags HTML).`,

                preambulo: `Aja como um redator jurídico. Formule apenas o preâmbulo e os fatos de uma petição inicial/defesa. Retorne ESTRITAMENTE um JSON válido com esta estrutura:
{
  "enderecamento": "EXCELENTÍSSIMO(A) SENHOR(A) DOUTOR(A) JUIZ(A) DE DIREITO DA ...",
  "qualificacao_autor": "<p>Qualificação completa do autor...</p>",
  "nome_acao": "NOME DA AÇÃO",
  "qualificacao_reu": "<p>Qualificação completa do réu...</p>",
  "dos_fatos": "<p>Narrativa detalhada dos fatos em parágrafos...</p>"
}`,

                pedidos: `Aja como um redator jurídico. Formule a seção final de pedidos e requerimentos forenses. Retorne ESTRITAMENTE um JSON válido com esta estrutura:
{
  "conclusao_pedidos": "<p>Diante do exposto, requer a Vossa Excelência:<br>a) O acolhimento...<br>b) A total improcedência...</p>"
}`,

                tese: `Aja como um advogado especialista. Elabore uma tese jurídica completa contendo fundamentação e jurisprudência. Retorne ESTRITAMENTE um JSON válido com esta estrutura:
{
  "titulo": "Título da Tese em Caixa Alta/Destaque",
  "elementos": [
    { "tipo": "texto", "conteudo": "<p>Primeiro parágrafo de argumentação teórica e jurídica...</p>" },
    { "tipo": "citacao", "conteudo": "Trecho exato do acórdão/jurisprudência para citação direta longa (+ de 3 linhas)..." },
    { "tipo": "texto", "conteudo": "<p>Parágrafo de conclusão e aplicação ao caso concreto...</p>" }
  ]
}`
            };

            function abrirModalComContexto(tipo, $teseRef = null) {
                contextoImportacao = tipo;
                $teseAlvoContexto = $teseRef;

                const titulos = {
                    completo: 'Importar Petição Completa (JSON)',
                    preambulo: 'Importar Preâmbulo e Fatos (JSON)',
                    pedidos: 'Importar Pedidos e Conclusão (JSON)',
                    tese: 'Importar Tese Individual (JSON)'
                };

                $('#modalTituloImportacao').html('<i class="bi bi-robot me-2"></i>' + (titulos[tipo] || 'Importar JSON'));
                $('#textoPromptIa').val(PROMPTS[tipo] || PROMPTS.completo);
                $('#textareaColarJson').val('');
                $('#feedbackCopia').addClass('d-none');
                modalImportacao.show();
            }

            // Abertura do modal pelos botões gerais
            $(document).on('click', '.btnAbrirModal', function() {
                abrirModalComContexto($(this).data('tipo'));
            });

            // Abertura do modal pelo botão de tese individual
            $(document).on('click', '.btnImportarTese', function() {
                const $tese = $(this).closest('.tese-card');
                abrirModalComContexto('tese', $tese);
            });

            // Botão copiar prompt
            $('#btnCopiarPrompt').click(function() {
                const texto = $('#textoPromptIa').val();
                navigator.clipboard.writeText(texto).then(() => {
                    $('#feedbackCopia').removeClass('d-none');
                    setTimeout(() => $('#feedbackCopia').addClass('d-none'), 3500);
                });
            });

            // Processar e aplicar o JSON colado
            $('#btnProcessarJsonColado').click(function() {
                const raw = $('#textareaColarJson').val().trim();
                if (!raw) {
                    alert('Por favor, cole o JSON no campo indicado antes de continuar.');
                    return;
                }

                // Remove possíveis blocos de formatação markdown caso a IA envie ```json ... ```
                const jsonLimpo = raw.replace(/^```json\s*/i, '').replace(/^```\s*/i, '').replace(/\s*```$/i, '');

                let dados;
                try {
                    dados = JSON.parse(jsonLimpo);
                } catch (e) {
                    alert('Erro de sintaxe no JSON colado! Verifique se a IA não cortou parte do texto.');
                    return;
                }

                // 1. Aplicação conforme o contexto
                if (contextoImportacao === 'tese' && $teseAlvoContexto) {
                    const $btn = $teseAlvoContexto.find('.btnAddElemento').first();
                    const bId = $btn.data('bloco');
                    const tId = $btn.data('tese');

                    if (dados.titulo !== undefined) $teseAlvoContexto.find('input[name*="[titulo]"]').val(dados.titulo);

                    if (dados.elementos && Array.isArray(dados.elementos)) {
                        const $container = $(`#elementos_${bId}_${tId}`);
                        $container.empty();
                        dados.elementos.forEach(el => {
                            adicionarElementoConteudo(bId, tId, el.tipo);
                            const $ultimo = $container.find('.item-elemento').last();
                            if (el.tipo === 'texto') {
                                $ultimo.find('textarea').summernote('code', el.conteudo || '');
                            } else {
                                $ultimo.find('textarea').val(el.conteudo || '');
                            }
                        });
                    }
                } else if (contextoImportacao === 'pedidos') {
                    if (dados.conclusao_pedidos !== undefined) $('textarea[name="conclusao_pedidos"]').summernote('code', dados.conclusao_pedidos);
                } else if (contextoImportacao === 'preambulo') {
                    if (dados.enderecamento !== undefined) $('input[name="enderecamento"]').val(dados.enderecamento);
                    if (dados.qualificacao_autor !== undefined) $('textarea[name="qualificacao_autor"]').summernote('code', dados.qualificacao_autor);
                    if (dados.nome_acao !== undefined) $('input[name="nome_acao"]').val(dados.nome_acao);
                    if (dados.qualificacao_reu !== undefined) $('textarea[name="qualificacao_reu"]').summernote('code', dados.qualificacao_reu);
                    if (dados.dos_fatos !== undefined) $('textarea[name="dos_fatos"]').summernote('code', dados.dos_fatos);
                } else {
                    // Completo
                    if (dados.enderecamento !== undefined) $('input[name="enderecamento"]').val(dados.enderecamento);
                    if (dados.qualificacao_autor !== undefined) $('textarea[name="qualificacao_autor"]').summernote('code', dados.qualificacao_autor);
                    if (dados.nome_acao !== undefined) $('input[name="nome_acao"]').val(dados.nome_acao);
                    if (dados.qualificacao_reu !== undefined) $('textarea[name="qualificacao_reu"]').summernote('code', dados.qualificacao_reu);
                    if (dados.dos_fatos !== undefined) $('textarea[name="dos_fatos"]').summernote('code', dados.dos_fatos);
                    if (dados.conclusao_pedidos !== undefined) $('textarea[name="conclusao_pedidos"]').summernote('code', dados.conclusao_pedidos);

                    if (dados.blocos && Array.isArray(dados.blocos)) {
                        $('#containerBlocos').empty();
                        dados.blocos.forEach(blocoData => {
                            contadorBloco++;
                            const bId = contadorBloco;
                            const blocoHtml = `
                            <div class="card bloco-card p-3" id="bloco_${bId}">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center gap-2 flex-grow-1 me-3">
                                        <span class="badge bg-primary fs-6">Bloco</span>
                                        <select name="blocos[${bId}][categoria]" class="form-select form-select-sm fw-bold w-auto">
                                            <option value="PRELIMINARES" ${blocoData.categoria === 'PRELIMINARES' ? 'selected' : ''}>DAS PRELIMINARES</option>
                                            <option value="PREJUDICIAIS" ${blocoData.categoria === 'PREJUDICIAIS' ? 'selected' : ''}>DAS PREJUDICIAIS DE MÉRITO</option>
                                            <option value="MERITO" ${blocoData.categoria === 'MERITO' ? 'selected' : ''}>DO MÉRITO</option>
                                            <option value="SUBSIDIARIAS" ${blocoData.categoria === 'SUBSIDIARIAS' ? 'selected' : ''}>DOS PEDIDOS SUBSIDIÁRIOS</option>
                                        </select>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-success btnAdicionarTese" data-bloco="${bId}">
                                            <i class="bi bi-plus-lg"></i> Adicionar Tese
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btnRemoverBloco" data-bloco="${bId}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="containerTeses" id="containerTeses_${bId}"></div>
                            </div>`;
                            $('#containerBlocos').append(blocoHtml);

                            if (blocoData.teses && Array.isArray(blocoData.teses)) {
                                blocoData.teses.forEach(teseData => {
                                    const tId = Date.now() + Math.floor(Math.random() * 1000);
                                    const teseHtml = `
                                    <div class="card tese-card p-3" id="tese_${tId}">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-success small"><i class="bi bi-file-text me-1"></i>Tese Jurídica</span>
                                            <div class="d-flex align-items-center gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btnExportarTese" title="Exportar esta Tese em JSON">
                                                    <i class="bi bi-download"></i> Exportar Tese
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 btnImportarTese" title="Importar dados para esta Tese">
                                                    <i class="bi bi-upload"></i> Importar Tese
                                                </button>
                                                <button type="button" class="btn btn-sm text-danger btnRemoverTese p-0 ms-2"><i class="bi bi-x-circle-fill fs-5"></i></button>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Título da Tese:</label>
                                            <input type="text" name="blocos[${bId}][teses][${tId}][titulo]" class="form-control form-control-sm fw-bold" value="${teseData.titulo || ''}">
                                        </div>
                                        <div class="containerElementos mb-2" id="elementos_${bId}_${tId}"></div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary btnAddElemento" data-bloco="${bId}" data-tese="${tId}" data-tipo="texto">
                                                <i class="bi bi-plus-lg"></i> Parágrafo Normal
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark btnAddElemento" data-bloco="${bId}" data-tese="${tId}" data-tipo="citacao">
                                                <i class="bi bi-quote"></i> Citação Longa (ABNT)
                                            </button>
                                        </div>
                                    </div>`;
                                    $(`#containerTeses_${bId}`).append(teseHtml);

                                    if (teseData.elementos && Array.isArray(teseData.elementos)) {
                                        teseData.elementos.forEach(el => {
                                            adicionarElementoConteudo(bId, tId, el.tipo);
                                            const $ultimo = $(`#elementos_${bId}_${tId} .item-elemento`).last();
                                            if (el.tipo === 'texto') {
                                                $ultimo.find('textarea').summernote('code', el.conteudo || '');
                                            } else {
                                                $ultimo.find('textarea').val(el.conteudo || '');
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    }
                }

                modalImportacao.hide();
            });

            // ========================================================
            // FUNÇÕES DE ARRASTAR E MINIMIZAR / EXPANDIR (INTERATIVIDADE)
            // ========================================================

            // 1. Alternar Minimizar / Expandir Bloco
            $(document).on('click', '.btnToggleBloco', function() {
                const $corpo = $(this).closest('.bloco-card').find('.corpo-bloco').first();
                const $icone = $(this).find('.icone-toggle-bloco');
                $corpo.slideToggle(200, function() {
                    if ($corpo.is(':visible')) {
                        $icone.removeClass('bi-chevron-right').addClass('bi-chevron-down');
                    } else {
                        $icone.removeClass('bi-chevron-down').addClass('bi-chevron-right');
                    }
                });
            });

            // 2. Alternar Minimizar / Expandir Tese
            $(document).on('click', '.btnToggleTese', function() {
                const $tese = $(this).closest('.tese-card');
                const $corpo = $tese.find('.corpo-tese').first();
                const $icone = $(this).find('.icone-toggle-tese');
                $corpo.slideToggle(200, function() {
                    if ($corpo.is(':visible')) {
                        $icone.removeClass('bi-chevron-right').addClass('bi-chevron-down');
                    } else {
                        $icone.removeClass('bi-chevron-down').addClass('bi-chevron-right');
                    }
                });
            });

            // 3. Atualizar resumo do título da tese em tempo real no cabeçalho
            $(document).on('input', '.input-titulo-tese', function() {
                const valor = $(this).val().trim();
                const $resumo = $(this).closest('.tese-card').find('.titulo-resumo-tese');
                $resumo.text(valor ? valor : '(Sem título definido)');
            });

            // 4. Configuração do SortableJS para Blocos de Teses
            if (document.getElementById('containerBlocos')) {
                new Sortable(document.getElementById('containerBlocos'), {
                    handle: '.handle-drag-bloco',
                    animation: 150,
                    ghostClass: 'sortable-ghost'
                });
            }

            // 5. Função para ativar arraste de Teses (permite mover entre blocos!)
            function ativarSortableTeses(elementoContainer) {
                if (!elementoContainer) return;
                new Sortable(elementoContainer, {
                    group: 'teses-compartilhadas', // Permite arrastar uma tese de um bloco para outro!
                    handle: '.handle-drag-tese',
                    animation: 150,
                    ghostClass: 'sortable-ghost'
                });
            }

            // 6. Função para ativar arraste de Elementos (parágrafos, citações, imagens)
            function ativarSortableElementos(elementoContainer) {
                if (!elementoContainer) return;
                new Sortable(elementoContainer, {
                    animation: 150,
                    ghostClass: 'sortable-ghost'
                });
            }

            // Inicia com um bloco pré-criado
            $('#btnAdicionarBloco').click();
        });
    </script>
</body>

</html>