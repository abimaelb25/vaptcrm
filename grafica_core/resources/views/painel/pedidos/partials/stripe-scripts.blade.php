{{-- Modal QR Code Stripe (Presencial) --}}
<div id="modal-stripe-qr" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="modal-stripe-titulo">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center animate-bounce-in">
        <h2 id="modal-stripe-titulo" class="text-xl font-black text-slate-800 dark:text-white mb-1">QR Code de Pagamento</h2>
        <p id="modal-stripe-pedido" class="text-sm text-slate-500 mb-4"></p>

        <div id="modal-stripe-loading" class="py-10">
            <div class="mx-auto h-12 w-12 border-4 border-violet-100 border-t-violet-600 rounded-full animate-spin"></div>
            <p class="mt-4 text-xs font-bold text-slate-400 uppercase tracking-widest">Iniciando Checkout Seguro...</p>
        </div>

        <div id="modal-stripe-qr-area" class="hidden">
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 mb-4 shadow-inner">
                <img id="modal-stripe-qr-img" src="" alt="QR Code de pagamento" class="mx-auto max-w-[200px] w-full">
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mb-5">
                O cliente aponta a câmera para pagar com <span class="text-indigo-600">Pix ou Cartão</span>.
            </p>
            <a id="modal-stripe-link" href="#" target="_blank" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-black text-white hover:bg-indigo-700 transition shadow-lg">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                ABRIR LINK DE PAGAMENTO
            </a>
        </div>

        <div id="modal-stripe-erro" class="hidden py-4 text-red-500 font-bold text-sm">
            <p id="modal-stripe-erro-msg"></p>
        </div>

        <button onclick="fecharModalQR()" class="mt-6 text-xs font-black text-slate-300 hover:text-slate-500 uppercase tracking-widest transition">CANCELAR E FECHAR</button>
    </div>
</div>

<script>
    const _stripeToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function abrirQRStripe(pedidoId, pedidoNum, url) {
        const modal = document.getElementById('modal-stripe-qr');
        const loading = document.getElementById('modal-stripe-loading');
        const qrArea = document.getElementById('modal-stripe-qr-area');
        const erroArea = document.getElementById('modal-stripe-erro');

        loading.classList.remove('hidden');
        qrArea.classList.add('hidden');
        erroArea.classList.add('hidden');
        document.getElementById('modal-stripe-pedido').textContent = 'Finalizar Pedido #' + pedidoNum;
        modal.classList.replace('hidden', 'flex');

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': _stripeToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        })
        .then(res => res.json())
        .then(data => {
            loading.classList.add('hidden');
            if (data.sucesso && data.checkout_url) {
                const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(data.checkout_url);
                document.getElementById('modal-stripe-qr-img').src = qrUrl;
                document.getElementById('modal-stripe-link').href = data.checkout_url;
                qrArea.classList.remove('hidden');
            } else {
                document.getElementById('modal-stripe-erro-msg').textContent = data.mensagem || 'Houve um erro técnico.';
                erroArea.classList.remove('hidden');
            }
        })
        .catch(() => {
            loading.classList.add('hidden');
            document.getElementById('modal-stripe-erro-msg').textContent = 'Erro crítico de conexão.';
            erroArea.classList.remove('hidden');
        });
    }

    function fecharModalQR() {
        const modal = document.getElementById('modal-stripe-qr');
        modal.classList.replace('flex', 'hidden');
    }

    function copiarLinkStripe(pedidoId, btn) {
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-pulse">Link...</span>';

        fetch('{{ url("painel/gestao-financeira/checkout/presencial") }}/' + pedidoId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': _stripeToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        })
        .then(res => res.json())
        .then(data => {
            if (data.sucesso && data.checkout_url) {
                navigator.clipboard.writeText(data.checkout_url).then(() => {
                    btn.innerHTML = '✅ OK';
                    btn.classList.add('bg-emerald-500', 'text-white');
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.classList.remove('bg-emerald-500', 'text-white');
                        btn.disabled = false;
                    }, 2000);
                });
            } else {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>
