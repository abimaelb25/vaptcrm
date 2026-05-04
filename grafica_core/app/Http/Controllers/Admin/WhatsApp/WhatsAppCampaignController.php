<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\WhatsApp;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppCampaign;
use App\Models\WhatsApp\WhatsAppCampaignRecipient;
use App\Services\WhatsApp\WhatsAppCampaignService;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Manages WhatsApp segment campaigns (manual-link and template).
 *
 * Campaigns never auto-send without an active API integration.
 * In manual mode, recipients get a list of wa.me links to open one by one.
 */
class WhatsAppCampaignController extends Controller
{
    public function __construct(
        private WhatsAppCampaignService $campaignService,
        private WhatsAppSettingsService $settingsService,
    ) {}

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function index(): View
    {
        $loja = Auth::user()->loja;

        $campaigns = WhatsAppCampaign::where('loja_id', $loja->id)
            ->with('createdBy')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('painel.whatsapp.campaigns.index', [
            'campaigns'      => $campaigns,
            'segmentOptions' => $this->campaignService->segmentOptions(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Create form
    // -------------------------------------------------------------------------

    public function create(): View
    {
        $loja     = Auth::user()->loja;
        $accounts = WhatsAppAccount::forLoja($loja->id)->active()->get();
        $produtos  = Produto::where('loja_id', $loja->id)->where('ativo', true)->orderBy('nome')->get(['id', 'nome']);

        return view('painel.whatsapp.campaigns.create', [
            'segmentOptions'      => $this->campaignService->segmentOptions(),
            'segmentParamDefs'    => $this->campaignService->segmentParamDefinitions(),
            'accounts'            => $accounts,
            'produtos'            => $produtos,
            'effectiveSendMode'   => $this->settingsService->resolveEffectiveSendMode($loja),
        ]);
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function store(Request $request): RedirectResponse
    {
        $loja = Auth::user()->loja;

        $validated = $request->validate([
            'nome'              => ['required', 'string', 'max:120'],
            'segment_type'      => ['required', 'in:' . implode(',', array_keys($this->campaignService->segmentOptions()))],
            'segment_params'    => ['nullable', 'array'],
            'manual_message'    => ['nullable', 'string', 'max:4096'],
            'message_type'      => ['required', 'in:manual_link,template'],
            'template_name'     => ['nullable', 'string', 'max:120'],
            'template_language' => ['nullable', 'string', 'max:20'],
        ]);

        $recipients = $this->campaignService->resolveRecipients(
            $loja->id,
            $validated['segment_type'],
            (array) ($validated['segment_params'] ?? [])
        );

        $campaign = WhatsAppCampaign::create([
            'loja_id'           => $loja->id,
            'nome'              => $validated['nome'],
            'segment_type'      => $validated['segment_type'],
            'segment_params'    => $validated['segment_params'] ?? [],
            'message_type'      => $validated['message_type'],
            'manual_message'    => $validated['manual_message'] ?? null,
            'template_name'     => $validated['template_name'] ?? null,
            'template_language' => $validated['template_language'] ?? 'pt_BR',
            'status'            => WhatsAppCampaign::STATUS_DRAFT,
            'total_recipients'  => count($recipients),
            'created_by'        => Auth::id(),
        ]);

        // Persist recipient rows with wa.me links if manual mode
        $manualMessage = $validated['manual_message'] ?? '';
        foreach ($recipients as $recipient) {
            $digits = preg_replace('/\D+/', '', $recipient['phone']);
            $link   = $manualMessage
                ? 'https://wa.me/' . $digits . '?text=' . rawurlencode(
                    str_replace(['{{nome_cliente}}', '{{nome do cliente}}'], $recipient['nome'], $manualMessage)
                )
                : null;

            WhatsAppCampaignRecipient::create([
                'campaign_id' => $campaign->id,
                'loja_id'     => $loja->id,
                'cliente_id'  => $recipient['cliente_id'],
                'phone'       => $recipient['phone'],
                'status'      => WhatsAppCampaignRecipient::STATUS_PENDING,
                'wa_me_link'  => $link,
            ]);
        }

        return redirect()->route('admin.whatsapp.campaigns.show', $campaign)
            ->with('sucesso', "Campanha criada com {$campaign->total_recipients} destinatário(s).");
    }

    // -------------------------------------------------------------------------
    // Show (manage recipients + manual sending)
    // -------------------------------------------------------------------------

    public function show(WhatsAppCampaign $campaign): View
    {
        $loja = Auth::user()->loja;
        $this->authorizeCampaign($campaign, $loja->id);

        $recipients = $campaign->recipients()
            ->with('cliente')
            ->orderBy('status')
            ->paginate(50);

        return view('painel.whatsapp.campaigns.show', [
            'campaign'   => $campaign,
            'recipients' => $recipients,
        ]);
    }

    // -------------------------------------------------------------------------
    // Mark recipient as sent (manual flow)
    // -------------------------------------------------------------------------

    public function markRecipientSent(WhatsAppCampaign $campaign, WhatsAppCampaignRecipient $recipient): RedirectResponse
    {
        $loja = Auth::user()->loja;
        $this->authorizeCampaign($campaign, $loja->id);

        $recipient->update([
            'status'  => WhatsAppCampaignRecipient::STATUS_SENT,
            'sent_at' => now(),
        ]);

        // Update campaign counters
        $campaign->increment('sent_count');
        if ($campaign->sent_count + $campaign->failed_count >= $campaign->total_recipients) {
            $campaign->update(['status' => WhatsAppCampaign::STATUS_DONE, 'finished_at' => now()]);
        }

        return back()->with('sucesso', 'Marcado como enviado.');
    }

    // -------------------------------------------------------------------------
    // Cancel
    // -------------------------------------------------------------------------

    public function cancel(WhatsAppCampaign $campaign): RedirectResponse
    {
        $loja = Auth::user()->loja;
        $this->authorizeCampaign($campaign, $loja->id);

        if (in_array($campaign->status, [WhatsAppCampaign::STATUS_DONE, WhatsAppCampaign::STATUS_CANCELLED], true)) {
            return back()->withErrors(['campaign' => 'Campanha não pode ser cancelada neste estado.']);
        }

        $campaign->update(['status' => WhatsAppCampaign::STATUS_CANCELLED]);

        return redirect()->route('admin.whatsapp.campaigns.index')->with('sucesso', 'Campanha cancelada.');
    }

    // -------------------------------------------------------------------------
    // Count preview (AJAX)
    // -------------------------------------------------------------------------

    public function countPreview(Request $request): \Illuminate\Http\JsonResponse
    {
        $loja = Auth::user()->loja;

        $validated = $request->validate([
            'segment_type'   => ['required', 'in:' . implode(',', array_keys($this->campaignService->segmentOptions()))],
            'segment_params' => ['nullable', 'array'],
        ]);

        $count = $this->campaignService->countRecipients(
            $loja->id,
            $validated['segment_type'],
            (array) ($validated['segment_params'] ?? [])
        );

        return response()->json(['count' => $count]);
    }

    // -------------------------------------------------------------------------
    // Private
    // -------------------------------------------------------------------------

    private function authorizeCampaign(WhatsAppCampaign $campaign, int $lojaId): void
    {
        abort_if($campaign->loja_id !== $lojaId, 403);
    }
}
