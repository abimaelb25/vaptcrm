{{-- Autoria: Abimael Borges | https://abimaelborges.adv.br | Data: 2026-04-20 --}}
<x-layouts.super-admin>
    <div class="mb-8">
        <a href="{{ route('superadmin.support.central-de-ajuda.index') }}" class="mb-2 inline-flex items-center text-xs font-bold text-gray-400 transition-colors hover:text-indigo-600">
            <i class="fas fa-chevron-left mr-1"></i> Voltar
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $helpContent->exists ? 'Editar Aula da VaptAcademy' : 'Nova Aula da VaptAcademy' }}</h1>
        <p class="mt-1 text-sm text-gray-500">Cadastre trilha, módulo, formato do conteúdo, materiais e visibilidade por plano.</p>
    </div>

    <div class="mb-6 grid gap-4 rounded-2xl border border-indigo-100 bg-indigo-50 p-5 md:grid-cols-3">
        <div>
            <div class="text-[10px] font-black uppercase tracking-widest text-indigo-400">Estrutura</div>
            <div class="mt-1 text-sm font-bold text-indigo-900">Trilha - Módulo - Aula</div>
        </div>
        <div>
            <div class="text-[10px] font-black uppercase tracking-widest text-indigo-400">Visibilidade</div>
            <div class="mt-1 text-sm font-bold text-indigo-900">Liberado geral ou por planos</div>
        </div>
        <div>
            <div class="text-[10px] font-black uppercase tracking-widest text-indigo-400">Mídia</div>
            <div class="mt-1 text-sm font-bold text-indigo-900">Thumbnail automática com override manual</div>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="mb-2 font-semibold">Nao foi possivel salvar. Revise os campos abaixo:</p>
            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php($selectedPlans = collect(old('visivel_para_planos', $helpContent->visivel_para_planos ?? []))->map(fn ($item) => (string) $item)->all())
    @php($selectedTrackId = (string) old('track_id', $helpContent->course?->track_id))
    @php($selectedCourseId = (string) old('course_id', $helpContent->course_id))
    @php($quizQuestionsForm = collect(old('quiz_questions', $quizQuestionsDraft ?? []))->values()->all())

    <form id="academy-lesson-form" action="{{ $helpContent->exists ? route('superadmin.support.central-de-ajuda.update', $helpContent) : route('superadmin.support.central-de-ajuda.store') }}" method="POST" class="max-w-5xl rounded-2xl border border-gray-100 bg-white p-8 shadow-xl">
        @csrf
        @if($helpContent->exists)
            @method('PUT')
        @endif

        <div class="space-y-7">
            <div>
                <label for="titulo" class="mb-2 block text-xs font-bold uppercase tracking-widest text-gray-400">Titulo *</label>
                <input id="titulo" type="text" name="titulo" value="{{ old('titulo', $helpContent->titulo) }}" required maxlength="255" placeholder="Ex: Primeiros passos no PDV" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="tipo" class="mb-2 block text-xs font-bold uppercase tracking-widest text-gray-400">Tipo de Conteúdo *</label>
                    <select id="tipo" name="tipo" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                        <option value="video" {{ old('tipo', $helpContent->tipo ?? 'video') === 'video' ? 'selected' : '' }}>Video</option>
                        <option value="texto" {{ old('tipo', $helpContent->tipo) === 'texto' ? 'selected' : '' }}>Texto / Aula escrita</option>
                        <option value="pdf" {{ old('tipo', $helpContent->tipo) === 'pdf' ? 'selected' : '' }}>PDF / Apostila</option>
                        <option value="imagem" {{ old('tipo', $helpContent->tipo) === 'imagem' ? 'selected' : '' }}>Imagem / Guia visual</option>
                        <option value="quiz" {{ old('tipo', $helpContent->tipo) === 'quiz' ? 'selected' : '' }}>Quiz</option>
                        <option value="treinamento" {{ old('tipo', $helpContent->tipo) === 'treinamento' ? 'selected' : '' }}>Treinamento</option>
                        <option value="comunicado" {{ old('tipo', $helpContent->tipo) === 'comunicado' ? 'selected' : '' }}>Comunicado</option>
                    </select>
                </div>
                <div>
                    <label for="obrigatoriedade" class="mb-2 block text-xs font-bold uppercase tracking-widest text-gray-400">Obrigatoriedade</label>
                    <select id="obrigatoriedade" name="obrigatoriedade" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                        <option value="livre" {{ old('obrigatoriedade', $helpContent->obrigatoriedade ?? 'livre') === 'livre' ? 'selected' : '' }}>Livre</option>
                        <option value="recomendado" {{ old('obrigatoriedade', $helpContent->obrigatoriedade) === 'recomendado' ? 'selected' : '' }}>Recomendado</option>
                        <option value="obrigatorio" {{ old('obrigatoriedade', $helpContent->obrigatoriedade) === 'obrigatorio' ? 'selected' : '' }}>Obrigatório</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div>
                    <label for="track_id" class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-400">
                        Trilha do treinamento
                        <span title="Escolha primeiro a trilha. Depois o sistema libera apenas as etapas dessa trilha." class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-slate-100 text-[10px] text-slate-500">?</span>
                    </label>
                    <select id="track_id" name="track_id" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                        <option value="">Sem trilha</option>
                        @foreach($tracks as $track)
                            <option value="{{ $track->id }}" {{ $selectedTrackId === (string) $track->id ? 'selected' : '' }}>{{ $track->titulo }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="course_id" class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-400">
                        Etapa do treinamento
                        <span title="A etapa é filtrada pela trilha acima para reduzir erro de cadastro." class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-slate-100 text-[10px] text-slate-500">?</span>
                    </label>
                    <select id="course_id" name="course_id" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                        <option value="">Selecione a etapa</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" data-track-id="{{ $course->track_id }}" {{ $selectedCourseId === (string) $course->id ? 'selected' : '' }}>
                                {{ $course->nome }}
                            </option>
                        @endforeach
                    </select>
                    <p id="course-empty-hint" class="mt-2 hidden text-xs text-slate-400">Ainda não existem etapas nessa trilha.</p>
                </div>
                <div>
                    <label for="required_plan" class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-400">
                        Compatibilidade legada
                        <span title="Use apenas se precisar manter um comportamento antigo de plano. Para novos conteúdos, prefira os checkboxes de visibilidade." class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-slate-100 text-[10px] text-slate-500">?</span>
                    </label>
                    <input id="required_plan" type="text" name="required_plan" value="{{ old('required_plan', $helpContent->required_plan) }}" placeholder="Ex: pro, premium" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                </div>
            </div>

            <div>
                <label class="mb-3 block text-xs font-bold uppercase tracking-widest text-gray-400">Visível para Planos</label>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    @forelse($plans as $plan)
                        <label class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-700">
                            <input type="checkbox" name="visivel_para_planos[]" value="{{ $plan->slug }}" {{ in_array($plan->slug, $selectedPlans, true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600">
                            <span>{{ $plan->nome }} <span class="text-xs text-gray-400">({{ $plan->slug }})</span></span>
                        </label>
                    @empty
                        <div class="text-sm text-gray-400">Nenhum plano ativo encontrado.</div>
                    @endforelse
                </div>
            </div>

            <div>
                <label for="descricao" class="mb-2 block text-xs font-bold uppercase tracking-widest text-gray-400">Resumo</label>
                <textarea id="descricao" name="descricao" rows="4" placeholder="Resumo do conteúdo" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">{{ old('descricao', $helpContent->descricao) }}</textarea>
            </div>

            <div>
                <label for="conteudo_texto" class="mb-2 block text-xs font-bold uppercase tracking-widest text-gray-400">Conteúdo complementar</label>
                <textarea id="conteudo_texto" name="conteudo_texto" rows="6" placeholder="Texto da aula, checklist, procedimento ou transcrição" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">{{ old('conteudo_texto', $helpContent->conteudo_texto) }}</textarea>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="youtube_url" class="mb-2 block text-xs font-bold uppercase tracking-widest text-gray-400">URL do YouTube</label>
                    <input id="youtube_url" type="url" name="youtube_url" value="{{ old('youtube_url', $helpContent->youtube_url) }}" placeholder="https://www.youtube.com/watch?v=..." class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                    <p class="mt-2 text-xs text-slate-400">Obrigatório apenas para vídeo e treinamento.</p>
                </div>
                <div>
                    <label for="thumbnail" class="mb-2 block text-xs font-bold uppercase tracking-widest text-gray-400">Thumbnail manual</label>
                    <input id="thumbnail" type="url" name="thumbnail" value="{{ old('thumbnail', $helpContent->thumbnail) }}" placeholder="https://.../thumb.jpg" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                    <div class="mt-3 overflow-hidden rounded-xl border border-gray-200 bg-slate-50">
                        <img id="thumbnail-preview-image" src="{{ $helpContent->thumbnail_resolved ?? '' }}" alt="Preview da thumbnail" class="{{ $helpContent->thumbnail_resolved ? '' : 'hidden ' }}h-24 w-full object-cover">
                        <div id="thumbnail-preview-empty" class="{{ $helpContent->thumbnail_resolved ? 'hidden ' : '' }}flex h-24 items-center justify-center text-xs font-semibold text-slate-400">A prévia da capa aparece aqui automaticamente.</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="material_apoio_titulo" class="mb-2 block text-xs font-bold uppercase tracking-widest text-gray-400">Título do Material de Apoio</label>
                    <input id="material_apoio_titulo" type="text" name="material_apoio_titulo" value="{{ old('material_apoio_titulo', $helpContent->material_apoio_titulo) }}" placeholder="Ex: Checklist do primeiro pedido" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                </div>
                <div>
                    <label for="material_apoio_url" class="mb-2 block text-xs font-bold uppercase tracking-widest text-gray-400">URL do Material de Apoio</label>
                    <input id="material_apoio_url" type="url" name="material_apoio_url" value="{{ old('material_apoio_url', $helpContent->material_apoio_url) }}" placeholder="https://.../arquivo.pdf" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                </div>
            </div>

            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5">
                <h2 class="mb-2 text-sm font-black uppercase tracking-widest text-amber-700">Quiz estruturado (multipergunta)</h2>
                <p class="mb-4 text-xs font-semibold text-amber-700">Preencha somente as questões necessárias. Cada questão precisa de pelo menos 2 alternativas e 1 correta.</p>

                <div class="space-y-5">
                    @for($q = 0; $q < 5; $q++)
                        <div class="rounded-xl border border-amber-200 bg-white p-4">
                            <div class="mb-3 text-xs font-black uppercase tracking-widest text-amber-700">Questão {{ $q + 1 }}</div>
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-500">Pergunta</label>
                                <input type="text" name="quiz_questions[{{ $q }}][pergunta]" value="{{ $quizQuestionsForm[$q]['pergunta'] ?? '' }}" class="w-full rounded-xl border-amber-200 bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10">
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                @for($a = 0; $a < 4; $a++)
                                    <div class="rounded-lg border border-slate-100 p-3">
                                        <label class="mb-2 block text-[11px] font-bold uppercase tracking-widest text-slate-500">Alternativa {{ $a + 1 }}</label>
                                        <input type="text" name="quiz_questions[{{ $q }}][alternativas][{{ $a }}][texto]" value="{{ $quizQuestionsForm[$q]['alternativas'][$a]['texto'] ?? '' }}" class="w-full rounded-xl border-slate-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10">
                                        <label class="mt-2 flex items-center gap-2 text-xs font-bold text-slate-600">
                                            <input type="hidden" name="quiz_questions[{{ $q }}][alternativas][{{ $a }}][is_correct]" value="0">
                                            <input type="checkbox" name="quiz_questions[{{ $q }}][alternativas][{{ $a }}][is_correct]" value="1" {{ !empty($quizQuestionsForm[$q]['alternativas'][$a]['is_correct']) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-amber-600">
                                            Correta
                                        </label>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endfor
                </div>

                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-500">
                    Compatibilidade legada: os campos abaixo continuam opcionais e serão sincronizados automaticamente pela primeira questão.
                </div>
                <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-3">
                    <input type="text" name="quiz_payload[pergunta]" value="{{ old('quiz_payload.pergunta', $helpContent->quiz_payload['pergunta'] ?? '') }}" placeholder="Pergunta legada (opcional)" class="w-full rounded-xl border-amber-200 bg-white text-xs focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10">
                    <input type="text" name="quiz_payload[resposta_correta]" value="{{ old('quiz_payload.resposta_correta', $helpContent->quiz_payload['resposta_correta'] ?? '') }}" placeholder="Resposta legada" class="w-full rounded-xl border-amber-200 bg-white text-xs focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10">
                    <div class="grid grid-cols-2 gap-2 md:col-span-1">
                        @for($legacyOption = 0; $legacyOption < 4; $legacyOption++)
                            <input type="text" name="quiz_payload[opcoes][]" value="{{ old('quiz_payload.opcoes.' . $legacyOption, $helpContent->quiz_payload['opcoes'][$legacyOption] ?? '') }}" placeholder="Opção legada {{ $legacyOption + 1 }}" class="w-full rounded-xl border-amber-200 bg-white text-xs focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10">
                        @endfor
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 items-end gap-6 md:grid-cols-3">
                <div>
                    <label for="ordem" class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-400">
                        Ordem na etapa
                        <span title="A ordem define a sequência da aula dentro da etapa. Não pode repetir na mesma etapa." class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-slate-100 text-[10px] text-slate-500">?</span>
                    </label>
                    <input id="ordem" type="number" name="ordem" min="0" step="1" value="{{ old('ordem', $helpContent->ordem ?? 0) }}" class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                </div>

                <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <input type="hidden" name="publicado" value="0">
                    <input type="checkbox" name="publicado" value="1" {{ old('publicado', $helpContent->exists ? $helpContent->publicado : true) ? 'checked' : '' }} class="h-5 w-5 rounded-lg border-gray-300 text-indigo-600 focus:ring-0">
                    <span class="text-sm font-bold text-gray-700">Publicado</span>
                </label>

                <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <input type="hidden" name="destaque" value="0">
                    <input type="checkbox" name="destaque" value="1" {{ old('destaque', $helpContent->destaque) ? 'checked' : '' }} class="h-5 w-5 rounded-lg border-gray-300 text-indigo-600 focus:ring-0">
                    <span class="text-sm font-bold text-gray-700">Destaque</span>
                </label>
            </div>
        </div>

        <div class="mt-10 flex justify-end gap-3 border-t border-gray-50 pt-8">
            <a href="{{ route('superadmin.support.central-de-ajuda.index') }}" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-bold text-gray-600 transition-colors hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="rounded-xl bg-indigo-600 px-8 py-3 text-sm font-bold uppercase tracking-widest text-white shadow-lg transition-all hover:scale-[1.02] hover:shadow-indigo-500/20 active:scale-95">
                {{ $helpContent->exists ? 'Atualizar Aula' : 'Salvar Aula' }}
            </button>
        </div>
    </form>

    <script>
        (function () {
            const trackSelect = document.getElementById('track_id');
            const courseSelect = document.getElementById('course_id');
            const emptyHint = document.getElementById('course-empty-hint');
            const youtubeInput = document.getElementById('youtube_url');
            const thumbnailInput = document.getElementById('thumbnail');
            const previewImage = document.getElementById('thumbnail-preview-image');
            const previewEmpty = document.getElementById('thumbnail-preview-empty');

            if (!trackSelect || !courseSelect) {
                return;
            }

            const courseOptions = Array.from(courseSelect.querySelectorAll('option[data-track-id]'));

            function resolveYoutubeId(url) {
                if (!url) {
                    return '';
                }

                if (url.includes('youtu.be/')) {
                    return url.split('youtu.be/').pop().split('?')[0];
                }

                try {
                    const parsed = new URL(url);
                    return parsed.searchParams.get('v') || '';
                } catch (error) {
                    return '';
                }
            }

            function updateThumbnailPreview() {
                const manualThumbnail = thumbnailInput.value.trim();
                const autoVideoId = resolveYoutubeId(youtubeInput.value.trim());
                const previewUrl = manualThumbnail || (autoVideoId ? `https://img.youtube.com/vi/${autoVideoId}/hqdefault.jpg` : '');

                if (previewUrl) {
                    previewImage.src = previewUrl;
                    previewImage.classList.remove('hidden');
                    previewEmpty.classList.add('hidden');
                } else {
                    previewImage.src = '';
                    previewImage.classList.add('hidden');
                    previewEmpty.classList.remove('hidden');
                }
            }

            function filterCourses() {
                const selectedTrackId = trackSelect.value;
                let visibleCount = 0;

                courseOptions.forEach((option) => {
                    const shouldShow = !selectedTrackId || option.dataset.trackId === selectedTrackId;
                    option.hidden = !shouldShow;
                    option.disabled = !shouldShow;
                    if (shouldShow) {
                        visibleCount++;
                    }
                });

                const selectedOption = courseSelect.options[courseSelect.selectedIndex];
                if (selectedOption && selectedOption.dataset.trackId && selectedOption.hidden) {
                    courseSelect.value = '';
                }

                emptyHint.classList.toggle('hidden', !(selectedTrackId && visibleCount === 0));
            }

            trackSelect.addEventListener('change', filterCourses);
            youtubeInput.addEventListener('input', updateThumbnailPreview);
            thumbnailInput.addEventListener('input', updateThumbnailPreview);

            filterCourses();
            updateThumbnailPreview();
        })();
    </script>
</x-layouts.super-admin>
