<x-layouts.app titulo="Nova Campanha WhatsApp - VaptCRM">
<div class="max-w-2xl mx-auto space-y-6">

    <div>
        <a href="{{ route('admin.whatsapp.campaigns.index') }}"
            class="text-sm text-slate-500 hover:text-slate-700">← Campanhas</a>
        <h1 class="text-2xl font-bold text-slate-800 mt-2">Nova campanha</h1>
        <p class="text-sm text-slate-500 mt-1">Configure o segmento e a mensagem. Os destinatários são resolvidos com validação de opt-in.</p>
    </div>

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 text-sm rounded-xl px-4 py-3">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.whatsapp.campaigns.store') }}" class="space-y-5">
        @csrf

        {{-- Nome --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nome da campanha <span class="text-rose-500">*</span></label>
            <input type="text" name="nome" value="{{ old('nome') }}" required maxlength="120"
                placeholder="Ex: Recuperação de orçamentos — Junho"
                class="w-full text-sm border-slate-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
        </div>

        {{-- Segment --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Segmento <span class="text-rose-500">*</span></label>
            <select name="segment_type" id="segment_type" required
                class="w-full text-sm border-slate-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <option value="">Selecione...</option>
                @foreach($segmentOptions as $key => $label)
                    <option value="{{ $key }}" {{ old('segment_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Segment params --}}
        <div id="params_pending_quote" class="segment-params hidden space-y-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Dias sem resposta</label>
                <input type="number" name="segment_params[days_without_response]" min="1" max="365"
                    value="{{ old('segment_params.days_without_response', 3) }}"
                    class="w-40 text-sm border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                <p class="text-xs text-slate-400 mt-1">Orçamentos parados há pelo menos X dias serão incluídos.</p>
            </div>
        </div>

        <div id="params_repeat_product" class="segment-params hidden space-y-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Produto</label>
                <select name="segment_params[product_id]"
                    class="w-full text-sm border-slate-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500">
                    <option value="">Selecione um produto...</option>
                    @foreach($produtos as $produto)
                        <option value="{{ $produto->id }}" {{ old('segment_params.product_id') == $produto->id ? 'selected' : '' }}>
                            {{ $produto->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="params_inactive_days" class="segment-params hidden space-y-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Dias sem compra</label>
                <input type="number" name="segment_params[days]" min="1" max="730"
                    value="{{ old('segment_params.days', 60) }}"
                    class="w-40 text-sm border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500">
                <p class="text-xs text-slate-400 mt-1">Clientes sem nenhum pedido nos últimos X dias.</p>
            </div>
        </div>

        {{-- Recipient preview --}}
        <div id="recipient_preview" class="hidden bg-sky-50 border border-sky-200 rounded-lg px-4 py-3">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-sky-800">
                    Estimativa: <span id="preview_count" class="font-bold">—</span> destinatário(s) com opt-in válido
                </p>
                <button type="button" id="btn_preview"
                    class="text-xs text-sky-600 hover:underline font-medium">Atualizar</button>
            </div>
        </div>

        {{-- Message type --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Tipo de mensagem <span class="text-rose-500">*</span></label>
            <div class="flex gap-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="message_type" value="manual_link"
                        {{ old('message_type', 'manual_link') === 'manual_link' ? 'checked' : '' }}
                        class="text-emerald-600">
                    <span class="text-sm text-slate-700">Envio manual (link wa.me)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="message_type" value="template"
                        {{ old('message_type') === 'template' ? 'checked' : '' }}
                        class="text-emerald-600">
                    <span class="text-sm text-slate-700">Template oficial</span>
                </label>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                No modo manual, você recebe links wa.me para abrir cada conversa individualmente. O envio automático requer integração API ativa.
            </p>
        </div>

        {{-- Manual message --}}
        <div id="manual_message_block">
            <label class="block text-sm font-medium text-slate-700 mb-1">Mensagem</label>
            <textarea name="manual_message" maxlength="4096" rows="4"
                placeholder="Olá {{nome_cliente}}, temos uma novidade para você..."
                class="w-full text-sm border-slate-200 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 resize-none">{{ old('manual_message') }}</textarea>
            <p class="text-xs text-slate-400 mt-1">Use <code class="bg-slate-100 px-1 rounded">{{nome_cliente}}</code> para personalizar o nome do destinatário.</p>
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="bg-emerald-600 text-white text-sm font-medium px-6 py-2.5 rounded-lg hover:bg-emerald-700 transition">
                Criar campanha
            </button>
            <a href="{{ route('admin.whatsapp.campaigns.index') }}"
                class="text-sm text-slate-500 hover:text-slate-700">Cancelar</a>
        </div>
    </form>

</div>

<script>
(function () {
    const segmentSelect = document.getElementById('segment_type');
    const paramBlocks   = document.querySelectorAll('.segment-params');
    const previewBox    = document.getElementById('recipient_preview');
    const previewCount  = document.getElementById('preview_count');
    const btnPreview    = document.getElementById('btn_preview');

    function showParamsFor(val) {
        paramBlocks.forEach(el => el.classList.add('hidden'));
        const target = document.getElementById('params_' + val);
        if (target) target.classList.remove('hidden');
        if (val) previewBox.classList.remove('hidden');
        else previewBox.classList.add('hidden');
    }

    segmentSelect.addEventListener('change', function () {
        showParamsFor(this.value);
        fetchPreview();
    });

    if (segmentSelect.value) showParamsFor(segmentSelect.value);

    function fetchPreview() {
        const segmentType = segmentSelect.value;
        if (!segmentType) return;

        const params = { segment_type: segmentType, segment_params: {} };
        const block = document.getElementById('params_' + segmentType);
        if (block) {
            block.querySelectorAll('input, select').forEach(el => {
                const match = el.name.match(/segment_params\[(.+)\]/);
                if (match) params.segment_params[match[1]] = el.value;
            });
        }

        previewCount.textContent = '...';
        fetch('{{ route("admin.whatsapp.campaigns.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
            },
            body: JSON.stringify(params),
        })
        .then(r => r.json())
        .then(data => { previewCount.textContent = data.count ?? '—'; })
        .catch(() => { previewCount.textContent = '—'; });
    }

    if (btnPreview) btnPreview.addEventListener('click', fetchPreview);

    // Show/hide template fields vs manual message
    document.querySelectorAll('input[name=message_type]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.getElementById('manual_message_block').style.display =
                this.value === 'manual_link' ? '' : 'none';
        });
    });
})();
</script>
</x-layouts.app>
