<?php

declare(strict_types=1);

namespace App\Models;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-05 00:16 -03:00
*/

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produto extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\Authorable, \App\Traits\HasTenancy;

    protected $table = 'produtos';

    protected $fillable = [
        'loja_id',
        'modelo_cadastro',
        'segmento',
        'nome',
        'slug',
        'subtitulo_comercial',
        'frase_efeito',
        'badge_comercial',
        'categoria_id',
        'descricao_curta',
        'descricao_completa',
        'imagem_principal',
        'preco_base',
        'unidade_venda',
        'custo_base',
        'custo_producao',
        'margem_lucro',
        'preco_sugerido',
        'prazo_estimado',
        'exige_arte',
        'oferece_design',
        'preco_arte',
        'custo_design',
        'largura',
        'altura',
        'area_m2',
        'formato',
        'orientacao',
        'gramatura',
        'tipo_impressao',
        'cor_impressao',
        'modo_producao',
        'instrucoes_internas',
        'checklist_producao',
        'destaque',
        'ordem_exibicao',
        'ativo',
        'visibilidade',
        'meta_title',
        'meta_description',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'preco_base' => 'float',
        'custo_base' => 'float',
        'custo_producao' => 'float',
        'margem_lucro' => 'float',
        'preco_sugerido' => 'float',
        'preco_arte' => 'float',
        'custo_design' => 'float',
        'largura' => 'float',
        'altura' => 'float',
        'area_m2' => 'float',
        'destaque' => 'boolean',
        'ativo' => 'boolean',
        'exige_arte' => 'boolean',
        'oferece_design' => 'boolean',
        'ordem_exibicao' => 'integer',
        'gramatura' => 'integer',
    ];

    public function categoriaRel(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function itensPedido(): HasMany
    {
        return $this->hasMany(ItemPedido::class, 'produto_id', 'id');
    }

    /**
     * Variações anexadas do Item (tamanho, papel, acabamentos) - Legado.
     */
    public function variacoes(): HasMany
    {
        return $this->hasMany(ProdutoVariacao::class, 'produto_id', 'id');
    }

    /**
     * Evolução: Grupos de Variação (Premium).
     */
    public function gruposVariacao(): HasMany
    {
        return $this->hasMany(ProdutoGrupoVariacao::class, 'produto_id')->orderBy('ordem');
    }

    /**
     * Evolução: Materiais (Premium).
     */
    public function materiais(): HasMany
    {
        return $this->hasMany(ProdutoMaterial::class, 'produto_id')->where('ativo', true);
    }

    /**
     * Evolução: Acabamentos (Premium).
     */
    public function acabamentos(): HasMany
    {
        return $this->hasMany(ProdutoAcabamento::class, 'produto_id')->where('ativo', true);
    }

    /**
     * Evolução: Faixas de Tiragem (Premium).
     */
    public function faixasQuantidade(): HasMany
    {
        return $this->hasMany(ProdutoFaixaQuantidade::class, 'produto_id')->orderBy('quantidade_minima');
    }

    public function imagens(): HasMany
    {
        return $this->hasMany(ProdutoImagem::class, 'produto_id')->orderBy('ordem');
    }
}

