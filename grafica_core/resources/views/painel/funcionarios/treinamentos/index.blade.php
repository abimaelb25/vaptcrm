<x-layouts.app titulo="Treinamentos da Equipe">
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Equipe - Treinamentos</h1>
            <p class="mt-2 text-sm text-slate-500">Acompanhe quem começou, quem está avançando e quem precisa de retomada.</p>
        </div>
        <a href="{{ route('admin.support.help.index') }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
            Abrir biblioteca
        </a>
    </div>

    @if(count($alerts ?? []) > 0)
        <div class="mb-6 grid gap-4 lg:grid-cols-3">
            @foreach($alerts as $alert)
                <div class="rounded-3xl border p-5 shadow-sm {{ $alert['tone'] === 'amber' ? 'border-amber-200 bg-amber-50' : ($alert['tone'] === 'rose' ? 'border-rose-200 bg-rose-50' : 'border-slate-200 bg-slate-50') }}">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm font-black text-slate-800">{{ $alert['title'] }}</div>
                        <span class="rounded-full px-3 py-1 text-xs font-black uppercase tracking-wider {{ $alert['tone'] === 'amber' ? 'bg-amber-200 text-amber-800' : ($alert['tone'] === 'rose' ? 'bg-rose-200 text-rose-800' : 'bg-slate-200 text-slate-700') }}">{{ $alert['count'] }}</span>
                    </div>
                    <p class="mt-3 text-sm text-slate-600">{{ $alert['message'] }}</p>
                    <p class="mt-2 text-xs font-bold uppercase tracking-wider text-slate-500">{{ $alert['action'] }}</p>
                    @php $alertEmployees = collect($alert['employees'] ?? []); @endphp
                    @if($alertEmployees->count() > 0)
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($alertEmployees as $employeeAlert)
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-700 shadow-sm">{{ $employeeAlert['employee']->nome_completo }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Colaboradores</div>
            <div class="mt-2 text-3xl font-black text-slate-900">{{ $summary['colaboradores'] }}</div>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Avanço médio do time</div>
            <div class="mt-2 text-3xl font-black text-brand-primary">{{ $summary['percentual_medio'] }}%</div>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Trilhas concluídas</div>
            <div class="mt-2 text-3xl font-black text-emerald-600">{{ $summary['trilhas_concluidas'] }}</div>
        </div>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-widest text-slate-500">Colaborador</th>
                    <th class="px-6 py-4 text-center text-[11px] font-black uppercase tracking-widest text-slate-500">Progresso</th>
                    <th class="px-6 py-4 text-center text-[11px] font-black uppercase tracking-widest text-slate-500">Trilhas concluídas</th>
                    <th class="px-6 py-4 text-center text-[11px] font-black uppercase tracking-widest text-slate-500">Desempenho</th>
                    <th class="px-6 py-4 text-center text-[11px] font-black uppercase tracking-widest text-slate-500">Quiz</th>
                    <th class="px-6 py-4 text-center text-[11px] font-black uppercase tracking-widest text-slate-500">Último avanço</th>
                    <th class="px-6 py-4 text-right text-[11px] font-black uppercase tracking-widest text-slate-500">Detalhes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($rows as $row)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800">{{ $row['employee']->nome_completo }}</div>
                            <div class="text-xs text-slate-500">{{ $row['employee']->cargo_interno ?: ($row['user']->perfil ?? 'Sem perfil') }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="mx-auto max-w-[160px]">
                                <div class="mb-1 flex items-center justify-between text-xs font-bold text-slate-600">
                                    <span>{{ $row['completed_lessons'] }}/{{ $row['total_lessons'] }} aulas</span>
                                    <span>{{ $row['progress_percent'] }}%</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-2 bg-brand-primary" style="width: {{ $row['progress_percent'] }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-black text-emerald-600">{{ $row['tracks_completed'] }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="rounded-full px-3 py-1 text-xs font-black uppercase tracking-wider {{ $row['status_tone'] === 'emerald' ? 'bg-emerald-100 text-emerald-700' : ($row['status_tone'] === 'blue' ? 'bg-blue-100 text-blue-700' : ($row['status_tone'] === 'amber' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600')) }}">{{ $row['status_label'] }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($row['quiz_score_percent'] !== null)
                                <div class="text-sm font-black {{ $row['quiz_score_percent'] >= 70 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $row['quiz_score_percent'] }}%</div>
                                <div class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ $row['quiz_total_avaliacoes'] }} aula(s)</div>
                            @else
                                <span class="text-xs font-bold text-slate-400">Sem quiz</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-xs font-bold text-slate-500">
                            {{ $row['last_activity_at'] ? $row['last_activity_at']->diffForHumans() : 'Sem avanço ainda' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.system.equipe.treinamentos.show', $row['employee']) }}" class="text-sm font-bold text-brand-primary hover:underline">Ver progresso</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">Nenhum colaborador com acesso sistêmico encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
