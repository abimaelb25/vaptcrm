<?php

declare(strict_types=1);

namespace App\Models\WhatsApp;

use App\Models\Loja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class WhatsAppAccount extends Model
{
    use SoftDeletes;

    protected $table = 'whatsapp_accounts';

    public const STATUS_PENDING      = 'pending';
    public const STATUS_ACTIVE       = 'active';
    public const STATUS_SUSPENDED    = 'suspended';
    public const STATUS_BANNED       = 'banned';
    public const STATUS_DISCONNECTED = 'disconnected';

    public const PROVIDER_META_CLOUD = 'meta_cloud';
    public const PROVIDER_BSP        = 'bsp_adapter';

    protected $fillable = [
        'loja_id',
        'provider',
        'display_name',
        'phone_number',
        'phone_number_id',
        'waba_id',
        'business_id',
        'access_token_encrypted',
        'webhook_verify_token',
        'quality_rating',
        'status',
        'is_primary',
        'connected_at',
        'last_activity_at',
        'meta',
    ];

    protected $casts = [
        'is_primary'        => 'boolean',
        'connected_at'      => 'datetime',
        'last_activity_at'  => 'datetime',
        'meta'              => 'array',
    ];

    /** Never expose the encrypted token in serialisation. */
    protected $hidden = ['access_token_encrypted'];

    // -------------------------------------------------------------------------
    // Accessors / Mutators
    // -------------------------------------------------------------------------

    public function setAccessTokenAttribute(string $token): void
    {
        $this->attributes['access_token_encrypted'] = Crypt::encryptString($token);
    }

    public function getAccessTokenAttribute(): ?string
    {
        if (empty($this->attributes['access_token_encrypted'])) {
            return null;
        }
        return Crypt::decryptString($this->attributes['access_token_encrypted']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isMetaCloud(): bool
    {
        return $this->provider === self::PROVIDER_META_CLOUD;
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(WhatsAppTemplate::class, 'whatsapp_account_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(WhatsAppConversation::class, 'whatsapp_account_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'whatsapp_account_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForLoja($query, int $lojaId)
    {
        return $query->where('loja_id', $lojaId);
    }
}
