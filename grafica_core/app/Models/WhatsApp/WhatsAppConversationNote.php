<?php

declare(strict_types=1);

namespace App\Models\WhatsApp;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppConversationNote extends Model
{
    protected $table = 'whatsapp_conversation_notes';

    protected $fillable = [
        'loja_id',
        'conversation_id',
        'user_id',
        'note',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WhatsAppConversation::class, 'conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}
