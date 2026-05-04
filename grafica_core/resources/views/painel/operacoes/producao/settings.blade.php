{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 19:50
--}}
<x-layouts.app>
    <div class="min-h-screen" style="background-color:#f1f5f9">
    @php
        $phaseTheme = static function (string $name): array {
            $normalized = mb_strtolower(trim($name));

            return match (true) {
                str_contains($normalized, 'pre') => [
                    'color' => '#f97316',
                ],
                str_contains($normalized, 'produc') => [
                    'color' => '#3b82f6',
                ],
                str_contains($normalized, 'pos') => [
                    'color' => '#22c55e',
                ],
                str_contains($normalized, 'final') => [
                    'color' => '#8b5cf6',
                ],
                default => [
                    'color' => '#94a3b8',
                ],
            };
        };
    @endphp

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-[#111827] tracking-tighter">Fluxo <span class="text-2xl text-[#111827]">Operacional</span></h1>
            <p class="text-[#6b7280] font-medium tracking-widest uppercase text-[10px]">Padrao industrial por fases, etapas e sequencia operacional.</p>
        </div>
        <a href="{{ route('admin.ops.production.index') }}" class="text-xs font-black text-slate-400 hover:text-brand-primary transition uppercase tracking-widest border border-slate-200 px-4 py-2 rounded-xl bg-white shadow-sm">← Chao de Fabrica</a>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('erro'))
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-800">
            {{ session('erro') }}
        </div>
    @endif

    <div class="space-y-10 js-phase-board" data-dnd-ready="true">
        @forelse($phases as $phase)
            @php($theme = $phaseTheme($phase->nome))
            <section class="overflow-hidden js-phase-column" style="background:#fff;border:1px solid #dbe3ee;border-left:4px solid {{ $theme['color'] }};border-radius:18px;box-shadow:0 2px 8px rgba(0,0,0,.07)" data-phase-id="{{ $phase->id }}" data-phase-order="{{ $phase->ordem }}">
                <header class="px-6 py-5" style="border-bottom:1px solid #edf0f5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 min-w-9 items-center justify-center px-2 text-xs" style="background:{{ $theme['color'] }}18;color:{{ $theme['color'] }};font-weight:800;border-radius:10px">{{ $phase->ordem }}</span>
                            <div>
                                <h2 class="text-sm uppercase tracking-widest" style="color:#0f172a;font-weight:800">{{ $phase->nome }}</h2>
                                <p class="text-[10px] font-semibold uppercase tracking-widest" style="color:#94a3b8">{{ $phase->steps->count() }} etapa(s)</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" class="btn-nova-etapa px-4 py-2 text-xs font-bold uppercase tracking-widest transition" style="background:#0f172a;color:#fff;border-radius:999px" onclick="togglePhaseStepForm({{ $phase->id }})">
                                + Nova etapa
                            </button>
                        </div>
                    </div>
                </header>

                <div id="phase-form-{{ $phase->id }}" class="{{ old('phase_form_id') == $phase->id ? '' : 'hidden' }} border-b border-slate-100 bg-slate-50 px-6 py-4">
                    <form action="{{ route('admin.ops.production.phase.step.store', $phase) }}" method="POST" class="flex flex-col gap-3 md:flex-row md:items-center">
                        @csrf
                        <input type="hidden" name="phase_form_id" value="{{ $phase->id }}">
                        <div class="flex-1">
                            <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Nova etapa em {{ $phase->nome }}</label>
                            <input type="text" name="nome" required value="{{ old('phase_form_id') == $phase->id ? old('nome') : '' }}" placeholder="Ex: Acerto de Maquina" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/20">
                        </div>
                        <div class="md:pt-5">
                            <button type="submit" class="w-full rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-black uppercase tracking-widest text-white transition hover:bg-brand-primary">Salvar</button>
                        </div>
                    </form>
                </div>

                <div class="space-y-3 px-6 py-5">
                    @forelse($phase->steps as $step)
                        <article class="card-etapa p-4 transition js-step-item" style="background:#fff;border:1px solid #dbe3ee;border-radius:14px;box-shadow:0 2px 6px rgba(0,0,0,.05)" data-step-id="{{ $step->id }}" data-step-order="{{ $step->ordem }}" draggable="false">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                <div class="flex items-center gap-4">
                                    <span class="badge-ordem inline-flex h-9 min-w-9 items-center justify-center px-2 text-xs" style="background:#f1f5f9;color:#0f172a;font-weight:700;border:1px solid #dbe3ee;border-radius:10px">{{ $step->ordem }}</span>
                                    <div>
                                        <h3 class="text-sm" style="color:#0f172a;font-weight:700">{{ $step->nome }}</h3>
                                        <p class="text-[10px] font-medium" style="color:#94a3b8">Etapa {{ $step->ordem }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 xl:justify-end">
                                    <form action="{{ route('admin.ops.production.step.move', $step) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="direction" value="up">
                                        <button type="submit" class="rounded-lg px-3 py-1.5 text-xs transition hover:opacity-80" style="background:#fff;border:1px solid #cfd8e3;color:#0f172a;font-weight:700">Subir</button>
                                    </form>

                                    <form action="{{ route('admin.ops.production.step.move', $step) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="direction" value="down">
                                        <button type="submit" class="rounded-lg px-3 py-1.5 text-xs transition hover:opacity-80" style="background:#fff;border:1px solid #cfd8e3;color:#0f172a;font-weight:700">Descer</button>
                                    </form>

                                    <form action="{{ route('admin.ops.production.step.move', $step) }}" method="POST" class="phase-switch-form">
                                        @csrf
                                        <label class="sr-only" for="phase-switch-{{ $step->id }}">Fase da etapa {{ $step->nome }}</label>
                                        <select id="phase-switch-{{ $step->id }}" name="target_phase_id" class="rounded-lg px-2 py-1.5 text-xs" style="background:#fff;border:1px solid #cfd8e3;color:#0f172a;font-weight:700" onchange="autoMoveStepByPhase(this)">
                                            @foreach($phases as $phaseOption)
                                                <option value="{{ $phaseOption->id }}" {{ $phaseOption->id === $phase->id ? 'selected' : '' }}>{{ $phaseOption->ordem }} - {{ $phaseOption->nome }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-amber-300 bg-amber-50 px-4 py-3 text-xs font-bold uppercase tracking-widest text-amber-700">
                            Fase sem etapas. Adicione ao menos uma etapa para manter o fluxo operacional consistente.
                        </div>
                    @endforelse
                </div>
            </section>
        @empty
            <div class="p-16 text-center italic text-slate-400 font-medium rounded-3xl bg-white border border-slate-100 shadow-sm">Nenhuma fase ativa encontrada para esta loja.</div>
        @endforelse
    </div>

    <p class="mt-6 text-[10px] font-black uppercase italic tracking-widest" style="color:#94a3b8">Ordenacao global de execucao = ordem da fase + ordem da etapa.</p>
    </div>

    @push('styles')
        <style>
            .card-etapa {
                color: #0f172a;
                transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
            }

            .card-etapa:hover {
                background: #f8fafc !important;
                border-color: #c0c9d6 !important;
                box-shadow: 0 4px 12px rgba(0,0,0,.08) !important;
                transform: translateY(-1px);
            }

            .card-etapa.active {
                background: #fff7ed !important;
                border-color: #f97316 !important;
                box-shadow: 0 4px 12px rgba(249,115,22,.12) !important;
            }

            .btn-nova-etapa {
                transition: background .15s ease;
            }

            .btn-nova-etapa:hover {
                background: #1e293b !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function autoMoveStepByPhase(selectElement) {
                const form = selectElement.closest('form');

                if (!form) {
                    return;
                }

                form.submit();
            }

            function togglePhaseStepForm(phaseId) {
                const targetId = 'phase-form-' + String(phaseId);
                const allForms = document.querySelectorAll('[id^="phase-form-"]');

                allForms.forEach((formElement) => {
                    if (formElement.id !== targetId) {
                        formElement.classList.add('hidden');
                    }
                });

                const targetForm = document.getElementById(targetId);

                if (!targetForm) {
                    return;
                }

                targetForm.classList.toggle('hidden');

                if (!targetForm.classList.contains('hidden')) {
                    const input = targetForm.querySelector('input[name="nome"]');
                    if (input) {
                        input.focus();
                    }
                }
            }
        </script>
    @endpush
</x-layouts.app>
