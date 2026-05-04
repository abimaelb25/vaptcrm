<x-layouts.app>
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Importar <span class="text-brand-primary">nota XML</span></h1>
            <p class="text-slate-500 font-medium">Envie a nota de compra para preencher fornecedor, itens e entrada de estoque.</p>
        </div>
        <a href="{{ route('admin.inventory.nfe-importacao.index') }}" class="text-sm font-bold text-slate-400 hover:text-brand-primary transition">&larr; Ver historico de importacoes</a>
    </div>

    <div class="max-w-3xl rounded-3xl bg-white p-8 shadow-xl border border-slate-100">
        <form action="{{ route('admin.inventory.nfe-importacao.preview') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6">
                <label class="block text-xs font-black text-slate-400 uppercase mb-2">Arquivo XML da NF-e <span class="text-red-500">*</span></label>
                <input
                    type="file"
                    name="xml_file"
                    accept=".xml,text/xml,application/xml"
                    required
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                >
                <p class="mt-2 text-xs text-slate-500">Nao sera feita entrada de estoque agora. Primeiro voce vai revisar e conciliar item a item.</p>
                @error('xml_file')
                    <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-2xl bg-blue-50 border border-blue-100 p-4">
                <p class="text-sm font-bold text-blue-700">Fluxo da importacao</p>
                <ul class="mt-2 text-xs text-blue-700 space-y-1">
                    <li>1. Upload e leitura da NF-e</li>
                    <li>2. Preview dos dados fiscais e itens</li>
                    <li>3. Conciliacao assistida por item</li>
                    <li>4. Confirmacao final com persistencia</li>
                </ul>
            </div>

            <div class="pt-2">
                <button type="submit" class="rounded-2xl bg-brand-primary px-8 py-4 text-center font-black text-white shadow-lg hover:-translate-y-0.5 transition uppercase tracking-widest text-xs">
                    Ler XML e conferir itens
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
